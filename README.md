# biostor-classic

BioStor provides tools for extracting, annotating, and visualising literature from the Biodiversity Heritage Library (and other sources). For background and further details please see:

Page, R. D. (2011). Extracting scientific articles from a large digital archive: BioStor and the Biodiversity Heritage Library. BMC Bioinformatics. Springer Science + Business Media. http://dx.doi.org/10.1186/1471-2105-12-187

This repository is the original site (PHP + MySQL + Solr), with minor modifications as site is moved to new server.


## Notes on installing BioStor (classic) from scratch


### VirtualHostX

httpd-vhosts.conf

NameVirtualHost *:80


<Directory “/Users/rpage/Sites/biostor-classic/www/“>
Allow From All
AllowOverride All
Options +Indexes
Require all granted
</Directory>
<VirtualHost 130.209.46.59:80>
	ServerName “biostor.org”
	DocumentRoot “/Users/rpage/Sites/biostor/www”
</VirtualHost>


### MySQL

http://stackoverflow.com/a/7264459

If you have /tmp/mysql.sock but no /var/mysql/mysql.sock then…

    cd /var 
    sudo mkdir mysql
    sudo chmod 755 mysql
    cd mysql
    ln -s /tmp/mysql.sock mysql.sock

If you have /var/mysql/mysql.sock but no /tmp/mysql.sock then

    cd /tmp
    ln -s /var/mysql/mysql.sock mysql.sock

You will need permissions to create the directory and link, so just prefix the commands above with sudo if necessary.

### cache

If image files are being stored on same hard drive as web server, then make sure www/cache is writable by web server.

If cache is on another drive, make www/cache a symbolic link to external drive (drive also needs to be writable by web server.

### ImageMagick

You need ImageMagick installed, and the path to the convert executable specified in config.inc.php. You need support for GIF, JPG, PNG, and TIFF. 

You can get a Mac OS X installer from CactusLabs http://cactuslab.com/imagemagick/, but this generates “convert: Saving binary kernel failed” errors.

Installed using HomeBrew http://brew.sh There have been issues reported with ImageMagick and HomeBrew, e.g. http://stackoverflow.com/questions/5624778/imagemagick-jpeg-decode-delegate-missing-with-os-x-homebrew-install. This worked for me:

    brew install —force jpeg
    brew install —force imagemagick

### ExifTools

To add metadata to PDFs we need [ExifTools](http://www.sno.phy.queensu.ca/~phil/exiftool/) by Phil Harvey. Download and install the Mac OS X Package.


### Apache configuration

Make sure to uncomment

    LoadModule rewrite_module libexec/apache2/mod_rewrite.so

in httpd.conf.

In .htaccess in the www folder, add:

    # Rewrite /foo/bar to /foo/bar.php
    RewriteRule ^openurl\??$ %{REQUEST_URI}.php [L]

(from http://php.net/manual/en/security.hiding.php). This ensures the URL /openurl will work.


### mysqlbigram

We create a bigram index on the bhl_title table to help search for journal matches. This requires a MySQL plugin to support bigram searches.

Grab mysqlbigram from https://sites.google.com/site/mysqlbigram/

The code expects to find the mysql header files in /usr/local/include, so I created a symbolic link: 

    cd /usr/local/include
    sudo ln -s /usr/local/mysql/include mysql

Then

    ./configure --prefix=/usr/local
    make
    sudo make install

The plugin is installed by default in

   /usr/local/lib/mysql/

MySQL expects these in

   /usr/local/mysql/lib/plugin/

so we move them

    cd /usr/local/lib/mysql/
    sudo cp * /usr/local/mysql/lib/plugin/

Edit my.cnf - on my Mac running Yosemite this is located here:

    /usr/local/mysql-5.6.21-osx10.8-x86_64/my.cnf

Add these lines:

    [mysqld]
    ft_min_word_len=1

Restart MySQL, then run this query to install plugin:

    INSTALL PLUGIN bi_gram SONAME ‘bi_gramlib.so’;

verify with command:

    SHOW PLUGINS;

This enables us to create the bhl_title table:

    CREATE TABLE `hl_title` (
      `TitleID` int(11) NOT NULL DEFAULT ‘0’,
      `MARCBibID` varchar(50) DEFAULT NULL,
      `MARCLeader` varchar(24) DEFAULT NULL,
      `FullTitle` text,
      `ShortTitle` varchar(255) DEFAULT NULL,
      `PublicationDetails` varchar(255) DEFAULT NULL,
      `CallNumber` varchar(100) DEFAULT NULL,
      `StartYear` int(11) DEFAULT NULL,
      `EndYear` int(11) DEFAULT NULL,
      `LanguageCode` varchar(10) DEFAULT NULL,
      `TL2Author` varchar(100) DEFAULT NULL,
      `TitleURL` varchar(100) DEFAULT NULL,
      PRIMARY KEY (`TitleID`),
      FULLTEXT KEY `ShortTitle_2` (`ShortTitle`) /*!50100 WITH PARSER `bi_gram` */ 
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;


## Image cache

In the www directory there should be a folder called “cache”. This can be a physical folder while setting up, but in production the images will be stored on an external hard drive. For example:

    cd www
    ln -s /Volumes/LaCie/WebServer/biostor/cache cache


