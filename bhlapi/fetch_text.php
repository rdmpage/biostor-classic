<?php

// extract and store text

require_once('../config.inc.php');
require_once('../db.php');
require_once('../bhl_text.php');


$items=array(
'156605' => 'bonnerzoologis431992zool',
'157020' => 'bonnerzoolo4619951996zool',
'156592' => 'bonnerzoo414219901991zool',
'156622' => 'bonnerzoo444519931995zool',
'156668' => 'bonnerzoo555620072009zool',
'157027' => 'bonnzoological582010zool',
'156976' => 'bonnzoologica5712010zool',
'156977' => 'bonnzoologica5722010zool',
'156770' => 'bonnzoological6012011zool',
'156813' => 'bonnzoological6022011zool',
'156812' => 'bonnzoological6112012zool',
'156814' => 'bonnzoological6122012zool',
'157008' => 'bonnzoological6212013zool',
'156691' => 'bonnzoological6222013zool',
'156072' => 'bonnerzoologisc161982bonn',
'157023' => 'bonnerzoologis462000bonn',
'156794' => 'bonnerzoologisc472000bonn',
'156591' => 'bonnerzoologisc482000bonn',
'156792' => 'bonnerzoologisc492001bonn',
'156790' => 'bonnerzoologisc502002bonn',
'156593' => 'bonnerzoologis522004bonn',
'156793' => 'bonnerzoologisc532005bonn',
'156806' => 'bonnerzoologisc542007bonn',
'156795' => 'bonnerzoologisc552009bonn',
'156808' => 'bonnerzoologisc562011bonn',
'157028' => 'bonnerzoologis572011bonn',
'157022' => 'bonnerzoologisc582011bonn', //
'156595' => 'bonnerzoologi39411996bonn',
'156589' => 'bonnerzoolo3519731974bonn',
'157077' => 'bonnerzoo111519781980bonn',
'156773' => 'bonnerzoo262919881989bonn',
'156771' => 'bonnerzoo303419901994bonn',
'157021' => 'bonnerzoo424519971999bonn'
);

/*
$items=array(
'156605' => 'bonnerzoologis431992zool'
);
*/


$force = false;
//$force = true;

foreach ($items as $ItemID => $SourceIdentifier)
{
	// Images are cached in folders with the ItemID as the name
	$cache_namespace = $config['cache_dir']. "/" . $ItemID;
	
	// Ensure cache subfolder exists for this item
	if (!file_exists($cache_namespace))
	{
		$oldumask = umask(0); 
		mkdir($cache_namespace, 0777);
		umask($oldumask);
		
	}
	
	$text_dir = $cache_namespace . '/text';
	
	// Ensure cache subfolder exists for this item
	if (!file_exists($text_dir))
	{
		$oldumask = umask(0); 
		mkdir($text_dir, 0777);
		umask($oldumask);
	}
	
	
	echo $ItemID . ' ' . $SourceIdentifier . "\n";


	// fetch source
	$djvu_filename = $cache_namespace . '/' . $SourceIdentifier . ".djvu";
	
	$go = true;
	//$go = false;
	
	if ($force || !file_exists($djvu_filename)) // don't fetch again if we don't need to
	{
		$url = 'http://www.archive.org/download/' . $SourceIdentifier . '/' . $SourceIdentifier . '.djvu';

		//$url = 'http://cluster.biodiversitylibrary.org/' . $SourceIdentifier{0} . '/' . $SourceIdentifier . '/' . $SourceIdentifier . '.djvu';
		$command = "curl";
		
		if ($config['proxy_name'] != '')
		{
			$command .= " --proxy " . $config['proxy_name'] . ":" . $config['proxy_port'];
		}
		$command .= " --location " . $url . " > " . $djvu_filename;
		echo $command . "\n";
		system ($command);
		
		
		$go = true;
		
		// get file size 
		if (filesize($djvu_filename) < 10000)
		{
			$go = false;
		}
		
	}
	
	//exit();
	
	if ($go)
	{
		
		// Get pages
		$pages = bhl_retrieve_item_pages($ItemID);
		
		//print_r($pages);
		//exit();
		
		foreach ($pages as $page)
		{
			$text_filename = $text_dir . "/" . $page->FileNamePrefix . '.txt'; 
			
			if ($force || !file_exists($text_filename)) // don't over write
			{
				
				$command = "djvutxt -page=" . $page->page_order . " "
				 . $djvu_filename . " " . $text_filename;
				echo $command . "\n";
				system($command);
				
			}
			
			// store
			$text = file_get_contents($text_filename);
			$text = bhl_clean_ocr_text($text);
			bhl_store_ocr_text($page->PageID, $text);
			
			//echo $text;
				
			//exit();
			
		}
	}


}

?>