<?php

// fetch ABBYY file for an item

require_once('../config.inc.php');
require_once('../db.php');
require_once('../bhl_utilities.php');


$items=array(107532);

$items=array(107743);

$items=array(134674);

$items=array(106479);

$items=array(84644);

$items=array(109905,128235,107523,21336);

$items=array(25862);


foreach ($items as $ItemID)
{
	// Files are cached in folders with the ItemID as the name
	$cache_namespace = $config['cache_dir']. "/" . $ItemID;
	

	// Get file prefix
	$file = bhl_file_from_itemid ($ItemID);
	
	if ($file)
	{
	
		// fetch names
		$abbyy_filename = $cache_namespace . '/' . $file->prefix . "_abbyy";
		
		if (!file_exists($abbyy_filename)) // don't fetch again if we don't need to
		{
			$url = 'http://www.archive.org/download/' . $file->prefix  . '/' . $file->prefix  . "_abbyy.gz";
	
			$command = "curl";
			
			if ($config['proxy_name'] != '')
			{
				$command .= " --proxy " . $config['proxy_name'] . ":" . $config['proxy_port'];
			}
			$command .= " --location " . $url . " > " . $abbyy_filename . ".gz";
			echo $command . "\n";
			system ($command);

			// Unpack
			$command = 'gunzip ' . $abbyy_filename . ".gz";
			echo $command . "\n";
			system ($command);
		}
		else
		{
			echo "Done!\n";
		}
	}

}

?>