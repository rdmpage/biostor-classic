<?php

/**
 * @file bhl_utilities.php
 *
 * Utility functions
 *
 */
 
require_once(dirname(__FILE__) . '/db.php');
require_once(dirname(__FILE__) . '/utilities.php');

//--------------------------------------------------------------------------------------------------
function bhl_item_from_pageid ($PageID)
{
	global $db;
	
	$ItemID = 0;
	
	$sql = 'SELECT ItemID FROM bhl_page WHERE PageID=' . $PageID . ' LIMIT 1';		

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$ItemID =  $result->fields['ItemID'];
	}
	
	return $ItemID;
}

//--------------------------------------------------------------------------------------------------
function bhl_item_from_reference_id($reference_id)
{
	global $db;
	
	$ItemID = 0;
	
	$sql = 'SELECT ItemID FROM bhl_page 
	INNER JOIN rdmp_reference USING(PageID)
	WHERE (reference_id=' . $reference_id . ') LIMIT 1';
	
	//echo $sql;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$ItemID = $result->fields['ItemID'];
	}
	
	return $ItemID;
}


//--------------------------------------------------------------------------------------------------
function bhl_institutions_with_title($TitleID)
{
	global $db;
	
	$institutions = array();

	$sql = 'SELECT COUNT(ItemID) AS c, InstitutionName FROM bhl_item
WHERE (TitleID=' . $TitleID . ') AND (InstitutionName IS NOT NULL)
GROUP BY InstitutionName';		

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	$items = array();
		
	while (!$result->EOF) 
	{	
		$institution = new stdclass;
		$institution->count = $result->fields['c'];
		$institution->name = $result->fields['InstitutionName'];
		$institutions[] = $institution;

		$result->MoveNext();		
	}
	
	return $institutions;
}


//--------------------------------------------------------------------------------------------------
function bhl_mobot_image_url($PageID)
{	
	$image_url = '';
	
	$url = 'http://www.biodiversitylibrary.org/services/pagesummaryservice.ashx?op=FetchPageUrl&pageID=' . $PageID;
	
	$json = get($url);
	
	$j = json_decode($json);
	
	$str = rawurldecode($j[0]);
	$str = str_replace("%2f","/", $str);
	$parts = explode("&amp;", $str);
	
	$fields = array();
	
	foreach ($parts as $part)
	{
		if (preg_match('/cat=(?<cat>.*)$/', $part, $matches))
		{
			$fields['cat'] = $matches['cat'];
		}
	
		if (preg_match('/image=(?<image_filename>.*)$/', $part, $matches))
		{
			$fields['filename'] = $matches['image_filename'];
		}
		
		if (preg_match('/client=(?<MARC001>[a-z]?[0-9]+)\/(?<prefix>[0-9]+)\//', $part, $matches))
		{
			$fields['MARC001'] = $matches['MARC001'];
			$fields['prefix'] = $matches['prefix'];
		}	
	}
	
	if (count($fields) == 4)
	{
		$image_url = 'http://images.biodiversitylibrary.org/adore-djatoka/resolver?url_ver=Z39.88-2004&rft_id=http://mbgserv09:8057/'
		. $fields['cat']
		. '/'
		. $fields['MARC001']
		. '/'
		. $fields['prefix']
		. '/jp2/'
		. $fields['filename']
		. '&svc_id=info:lanl-repo/svc/getRegion&svc_val_fmt=info:ofi/fmt:kev:mtx:jpeg2000&svc.format=image/jpeg&svc.scale=1000';
	}
	
	return $image_url;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Return Internet Archive image URL from file prefix in BHL database
 *
 * @param FileNamePrefix File name prefix
 *
 * @return URL
 */
function bhl_image_url_from_file_prefix ($FileNamePrefix)
{
	$prefix = explode("_", $FileNamePrefix);
	$ExternalURL = 'http://www.archive.org/download'
		. '/' . $prefix[0] . '/' . $prefix[0] . '_jp2.zip'
		. '/' . $prefix[0] . '_jp2'
		. '/' . $prefix[0] . '_' . $prefix[1] . '.jpg';

	return $ExternalURL;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Fetch page image, create thumbnail, and return object that describes image
 *
 * We cache images and thumbnails, so we only fetch image once.
 *
 * @param PageID BHL PageID
 *
 * @return Image object
 */
// Get page image from Internet archive
function bhl_fetch_page_image ($PageID)
{
	global $config;
	global $db;
	
	$image = NULL;
	
	if (1)
	{
		$image = new stdclass;
		$image->thumbnail = new stdclass;
		
		// page
		$image->url = 'http://www.biodiversitylibrary.org/pagethumb/' .  $PageID . ',500,500';
		$image->width = 500;
		$image->height = 800;			

		// thumbnail
		$image->thumbnail->url = 'http://www.biodiversitylibrary.org/pagethumb/' .  $PageID . ',80,80';
		$image->thumbnail->width = 80;
		$image->thumbnail->height = 100;			

	}
	else
	{
	
		$sql = 'SELECT * FROM bhl_page 
		INNER JOIN page USING(PageID)
		WHERE (PageID=' . $PageID . ') 
		LIMIT 1';
	
		//echo $sql;
	
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

		if ($result->NumRows() == 1)
		{
			$image = new stdclass;
			$image->thumbnail = new stdclass;
	
			$ItemID = $result->fields['ItemID'];
			$FileNamePrefix = $result->fields['FileNamePrefix'];
		
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
		
			// Generate URL to fetch image, and local file names for cached images
		
			// Use BHL API as per Chris Freeland's email 2009-12-28
			// <769697AE3E25EF4FBC0763CD91AB1B0205178B1E@MBGMail01.mobot.org>
			$image->ExternalURL = 'http://www.biodiversitylibrary.org/pageimage/' . $PageID;
		
			/*
			if (is_numeric($FileNamePrefix{0}))
			{
				$image->ExternalURL = bhl_mobot_image_url($PageID);
			}
			else
			{
				$image->ExternalURL = bhl_image_url_from_file_prefix ($FileNamePrefix);
			}
			*/
		
			//print_r($image); 
				
			$image->file_name = $cache_namespace . "/" . $FileNamePrefix . '.jpg'; 
			$image->url = $config['web_root']  . $config['cache_prefix'] . '/' .  $ItemID . "/" . $FileNamePrefix . '.jpg'; 	
		
			//echo $image->url . "\n";
		
			$image->thumbnail->file_name = $cache_namespace . "/thumbnails/" . $FileNamePrefix . '.gif'; 
			$image->thumbnail->url = $config['web_root']  . $config['cache_prefix'] . '/' .  $ItemID . "/thumbnails/" . $FileNamePrefix . '.gif'; 
		
			// Only fetch it we don't have cached copy	
			if (!file_exists($image->thumbnail->file_name))
			{
				if (!$config['fetch_images']) 
				{
					$image->file_name = dirname(__FILE__) . '/www/images/blank350x500.png';
					$image->thumbnail->file_name =  dirname(__FILE__) . '/www/images/blank70x100.png';
				
					$image->thumbnail->url = 'images/blank70x100.png';
					$image->url = 'images/blank350x500.png';
				}
				else
				{
					//echo $image->ExternalURL . "\n";
			
					$bits = get($image->ExternalURL);
					if ($bits != '')
					{
						$cache_file = @fopen($image->file_name, "w+") or die(__LINE__ . " could't open file --\"$image->file_name\"");
						@fwrite($cache_file, $bits);
						fclose($cache_file);
					
						// resize to 800 px wide to save space
					
						if (0)
						{
							// turned off as slows things down, and can lead to gray pages	
							$command = 'nice ' . $config['mogrify']  . ' -resize 800 ' . $image->file_name;
							system($command);
						}
	
						// thumbnail
						$command = $config['convert']  . ' -thumbnail 100 ' . $image->file_name . ' ' . $image->thumbnail->file_name . '  2>&1 &';
						//system($command);
					
						passthru($command);
					}
				}
			}
		
			// Sizes
			// get image size
			// sometimes we may be missing an image, so getimagesize will generate a warning message. 
			// putting @ in front supresses this!
			$size = @getimagesize($image->file_name);
			$image->width = $size[0];
			$image->height = $size[1];	
		
			// get thumbnail size
			$size = @getimagesize($image->thumbnail->file_name);
			$image->thumbnail->width = $size[0];
			$image->thumbnail->height = $size[1];	
		
		}
	}
	return $image;
}

function bhl_file_from_pageid ($PageID)
{
	global $config;
	global $db;
	
	$file = null;
	
	$sql = 'SELECT * FROM bhl_page 
	INNER JOIN page USING(PageID)
	WHERE (PageID=' . $PageID . ') 
	LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$file->ItemID = $result->fields['ItemID'];
		$file->prefix = $result->fields['FileNamePrefix'];
		
		$file->prefix = preg_replace('/_\d+$/', '', $file->prefix);
	}
	
	return $file;
}

function bhl_file_from_itemid ($ItemID)
{
	global $config;
	global $db;
	
	$file = null;
	
	$sql = 'SELECT * FROM page 
	WHERE (ItemID=' . $ItemID . ') 
	LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$file->ItemID = $result->fields['ItemID'];
		$file->prefix = $result->fields['FileNamePrefix'];
		
		$file->prefix = preg_replace('/_\d+$/', '', $file->prefix);
	}
	
	return $file;
}

function bhl_sequence_order_from_pageid($PageID)
{
	global $db;
	
	$sequence_order = 0;
	
	$sql = 'SELECT * FROM page WHERE PageID=' . $PageID . ' LIMIT 1';		

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$sequence_order =  $result->fields['SequenceOrder'];
	}
	
	return $sequence_order;
}


if (0)
{
	$PageID = 34570568;
	
	bhl_fetch_page_image($PageID);
	


}

?>