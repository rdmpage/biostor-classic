#!/usr/bin/perl -w

use strict;
use warnings;
use FileHandle;
use Fcntl ':flock';
use File::Path qw( mkpath );
use LWP;


my $DotCommand = "/Applications/Graphviz/Graphviz.app/Contents/MacOS/neato";
#my $DotCommand = "/Applications/Graphviz/Graphviz.app/Contents/MacOS/circo";
#my $DotCommand = "/Applications/Graphviz/Graphviz.app/Contents/MacOS/dot";

# set $Tdir to the webdot cache directory.  note that this script must have
# write permission on the directory when it is run by your web server.
# for example apache's default httpd.conf specifies that CGI programs such
# as this one run as user 'nobody'.  in that case the cache directory must
# be writable by 'nobody' - either mode 0777 or chown to nobody.
my $Tdir = '/tmp';



my %KnownTypes = (
	dot =>    'application/x-dot',
	gif =>    'image/gif',
	png =>    'image/png',
	mif =>    'application/x-mif',
	hpgl =>   'application/x-hpgl',
	pcl =>    'application/x-pcl',
	vrml =>   'x-world/x-vrml',
	vtx =>    'application/x-vtx',
	ps =>     'application/postscript',
	epsi =>   'application/postscript',
	pdf =>    'application/pdf',
	map =>    'text/plain',
	txt =>    'text/plain',
	src =>    'text/plain',
	svg =>    'image/svg+xml',
);

# What content type is returned.  Usually $KnownTypes{$tag},
# but not always.
my $ContentType = 'text/plain';

# What is returned.  In good times, the results of running dot,
# (and maybe a postprocessor), in bad times, an apologetic message.
my $TheGoods = 'Server Error, profound apologies';

# Arrange to return an error message
sub trouble {
    $TheGoods = shift;
    $ContentType = 'text/plain';
}

sub up_doc {
    my ($base, $url, $tag) = @_;
    my $dotdir = "$Tdir/$base";
    my $dotfile = "$dotdir/source";
    my $tagfile = "$dotdir/$tag";
    my $dotfh = new FileHandle;
    my $tagfh = new FileHandle;
    my $fh = new FileHandle;
    my ($size, $mtime, $cmd, $webdoc, $content);
    my ($ttime, $rc);
    my $now = time();

    unless (-d $dotdir) {
	unless (mkpath( [ $dotdir ], 0, 02775)) {
	    trouble("Server error: Unable to make directory $dotdir: $!");
	    return;
	}
    }
    
    $TheGoods .= "dotfile=";
    $TheGoods .= $dotfile . "\n";
	
    unless (open($dotfh, "+>> $dotfile")) {
	trouble("Server error: Open failed on $dotfile: $!");
	return;
    }
    flock($dotfh, LOCK_SH);
    ($size, $mtime) = (stat($dotfh))[7,9];
	
    my $browser = LWP::UserAgent->new();   ## Create a virtual browser
    $browser->agent("Kipper Browser");     ## Name it
    ## Do a GET request on the URL with the fake browser
    
    # Weird bug
    if ($url =~ m/http:\/\//g)
    {
    }
    else
    {
    	$url =~ s/http:\//http:\/\//g;
    }
    
    
    $webdoc = $browser->request(HTTP::Request->new(GET => $url));
    if($webdoc->is_success){ ## found it 
	$content = $webdoc->content();
	$TheGoods .= "Content=";
	$TheGoods .= $content;
	flock($dotfh, LOCK_EX);
	truncate($dotfh, 0);
	print $dotfh $content;
	$dotfh->autoflush();
	flock($dotfh, LOCK_SH);
	($size, $mtime) = (stat($dotfh))[7,9];
    } else {                 ## did not find it
	trouble("Server error: Could not find $url\n" . $webdoc->message);
	return;
    }
    
     unless (open($tagfh, "+>> $tagfile")) {
	trouble("Server error: Open failed on $tagfile: $!");
	return;
    }
    flock($tagfh, LOCK_SH);
    ($size, $ttime) = (stat($tagfh))[7,9];

	my $dottag = $tag;
	my $tmpfile;
	my $tmpfh;
	    $tmpfile = $tagfile;
	    $tmpfh = $tagfh;
	$cmd = "$DotCommand -T$dottag < $dotfile > $tmpfile";
	$rc = system($cmd);		# Run command to load file
    unless ($rc == 0) {
	trouble("Server error: Non-zero exit $rc from $cmd\n");
	return;
	}

    
    seek($tagfh,0,0);
    {
	local($/);	# slurp mode
	$TheGoods = <$tagfh>;
    }
	1;	


}

sub get_dot {
    my $urltag = shift;
    my ($url, $base, $tag);

    if ($urltag =~ /^(.+)[.]([^.]+)$/) {
	($url, $tag) = ($1, $2);
	unless ($ContentType = $KnownTypes{$tag}) {
	    trouble("Unknown tag type $tag from $url\n");
	    return;
	}
	($base = $url) =~ s:/:-:g;
	
	$TheGoods .= "base=";
	$TheGoods .= $base . "\n";
	$TheGoods .= "url=";
	$TheGoods .= $url . "\n";
	up_doc($base, $url, $tag);	
    } else {
	trouble("Unknown url format: $url\n");
    }
}

    
sub main {
    my $arg;
    if ($arg = ($ENV{'PATH_INFO'})) {
	    $arg =~ s:/::;
	}
	else  {
		$arg = $ARGV[0];
	}

	$TheGoods=$arg;
	get_dot ($arg);


print "Content-type: $ContentType\n\n";
#print "Cache-Control: no-cache, must-revalidate\n\n";
#print "Expires: Mon, 26 Jul 1997 05:00:00 GMT\n\n";
#print "Content-length: $size\n\n";

    print $TheGoods;






}
main();

