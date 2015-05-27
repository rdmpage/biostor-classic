<?php

// Bulk extra page images for an Item from the corresponding DjVu file

require_once(dirname(__FILE__) . '/db.php');

$ItemID = 108199;
$djvu_filename = 'proceedingsofb1041991biol.djvu';

$ItemID=107519;
$djvu_filename = 'proceedingsofbi841971biol.djvu';


$ItemID=107599;
$djvu_filename = 'proceedings7677196364biol.djvu';

$ItemID=110199;
$djvu_filename = 'proceedingsofb1051992biol.djvu';


// Get pages
$pages = bhl_retrieve_item_pages($ItemID);

print_r($pages);

// Images are cached in folders with the ItemID as the name
$cache_namespace = $config['cache_dir']. "/" . $ItemID;

// Ensure cache subfolder exists for this item
if (!file_exists($cache_namespace))
{
	$oldumask = umask(0); 
	mkdir($cache_namespace, 0777);
	umask($oldumask);
	
	// Thumbnails are in a subdirectory
	$oldumask = umask(0); 
	mkdir($cache_namespace . '/thumbnails', 0777);
	umask($oldumask);
}

foreach ($pages as $page)
{
	$filename = $cache_namespace . "/" . $page->FileNamePrefix . '.jpg'; 
	
	// only do this for images we don't already have
	if (!file_exists($filename))
	{

		// Image filename
		$tiff_filename = $cache_namespace . "/" . $page->FileNamePrefix . '.tiff'; 
		
		$command = "ddjvu -format=tiff -page=" . $page->page_order . " -size=800x2000 "
		 . $cache_namespace . "/" . $djvu_filename . " " . $tiff_filename;
		echo $command . "\n";
		system($command);
		
		// Convert to JPEG
		$command = $config['convert'] . " " . $tiff_filename . " " . $filename;
		echo $command . "\n";
		system($command);
		
		if (0)
		{
			// Try and remove background colour
			$command = $config['convert'] . " " . $filename . " -channel all normalize " . $filename;
			echo $command . "\n";
			system($command);
		}	
		
		// Thumbnail
		$thumbnail_filename = $cache_namespace . "/thumbnails/" . $page->FileNamePrefix . '.gif'; 
		$command = $config['convert']  . ' -thumbnail 100 ' . $filename . ' ' . $thumbnail_filename;
		echo $command . "\n";
		system($command);
			
		
		// Kill TIFF
		unlink ($tiff_filename);
	}
}


?>