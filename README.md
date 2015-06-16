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


