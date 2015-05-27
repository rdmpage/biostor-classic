<?php

/**
 * @file bhl_image.php
 *
 * Retrieve an image for a BHL page from the Internet Archive and cache it locally.
 *
 */

require_once ('../db.php');
require_once ('../lib.php');
require_once ('../bhl_utilities.php');


$PageID = $_GET['PageID']; 

//echo $PageID;

// Do we want a thumbnail or the full size page image?
$thumbnail = (isset($_GET['thumbnail']) ? true : false);


$image = bhl_fetch_page_image($PageID);

if ($image != NULL)
{
	//print_r($image);
	
	// Load image
	if ($thumbnail)
	{
		// thumbnail
		$file = @fopen($image->thumbnail->file_name, "r") or die("could't open file --\"$image->thumbnail->file_name\"");
		$img = fread($file, filesize($image->thumbnail->file_name));
		fclose($file);
		
		header('Content-type: image/gif');
		echo $img;		
		
		//header("Content-type: image/gif\nContent-type: image/jpeg\nLocation: " . $image->thumbnail->url . "\n\n");
		
	}
	else
	{
		$file = @fopen($image->file_name, "r") or die("could't open file --\"$image->file_name\"");
		$img = fread($file, filesize($image->file_name));
		fclose($file);
		
		header('Content-type: image/jpeg');
		echo $img;		
		
		//header("Content-type: image/jpeg\nLocation: " . $image->url . "\n\n");
	}
	
}	

?>