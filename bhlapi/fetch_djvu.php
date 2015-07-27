<?php

require_once (dirname(dirname(__FILE__)) . '/config.inc.php');
require_once (dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


function bhl_retrieve_item_pages($ItemID)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$pages = array();
	
	$sql = 'SELECT DISTINCT(PageID), PagePrefix, PageNumber, SequenceOrder, FileNamePrefix 
	FROM bhl_page
	INNER JOIN page USING(PageID)
	WHERE (bhl_page.ItemID = ' . $ItemID . ')
	ORDER BY SequenceOrder';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$page = new stdclass;
		$page->PageID 			= $result->fields['PageID'];
		$page->page_order 		= $result->fields['SequenceOrder'];
		$page->PagePrefix 		= $result->fields['PagePrefix'];
		$page->PageNumber 		= $result->fields['PageNumber'];
		$page->FileNamePrefix 	= $result->fields['FileNamePrefix'];
		
		$pages[] = $page;
		$result->MoveNext();
	}
	
	return $pages;
}

// to do
// Madrano
$items=array(
'185854' => 'madronowestamer421995cali',
'185037' => 'madronowestamer431996cali',
'185039' => 'madronowestamer441997cali',
'185600' => 'madronowestame5712010cali',
'185641' => 'madronowestame5722010cali',
'185627' => 'madronowestame5732010cali',
'185636' => 'madronowestame5742010cali',
'185607' => 'madronowestame5812011cali',
'185608' => 'madronowestame5832011cali',
'185625' => 'madronowestame5842011cali',
'185609' => 'madronowestame5912012cali',
'185614' => 'madronowestame5922012cali',
'185613' => 'madronowestame5932012cali',
'185599' => 'madronowestame5942012cali',
'185096' => 'madronowestam28291982cali',
'185506' => 'madronowesta219301934cali',
'185502' => 'madronowesta319351936cali',
'185852' => 'madronowesta419371938cali',
'185858' => 'madronowesta519391940cali',
'185355' => 'madronowesta619411942cali',
'185856' => 'madronowesta719431944cali',
'185359' => 'madronowesta819451946cali',
'185075' => 'madronowesta919471948cali',
'185220' => 'madronowest1019491950cali',
'185228' => 'madronowest1119511952cali',
'185221' => 'madronowest1219531954cali',
'185257' => 'madronowest1319551956cali',
'185080' => 'madronowest1419571958cali',
'185326' => 'madronowest1519591960cali',
'185084' => 'madronowest1619611962cali',
'185079' => 'madronowest1719631964cali',
'185256' => 'madronowest2019691970cali',
'185322' => 'madronowest2119711972cali',
'185260' => 'madronowest2219731974cali',
'185095' => 'madronowest2319751976cali',
'132036' => 'madroowest11319161922cali',
'185857' => 'madronowe151719231929cali',
'185219' => 'madronowe181919651968cali',
'185587' => 'madronowe242519771978cali',
'185361' => 'madronowe262719791980cali',
'185596' => 'madronowe303119831984cali',
'185637' => 'madronowe323319841985cali',
'185592' => 'madronowe343519861987cali',
'185620' => 'madronowe363719881989cali',
'185586' => 'madronowe383919911992cali',
'185594' => 'madronowe404119931994cali',
'185215' => 'madronowe454619981999cali',
'185049' => 'madronowe474820002001cali',
'185048' => 'madronowe515220042005cali',
'185047' => 'madronowe535420062007cali',
'185050' => 'madronowe555620082009cali'
);


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
		
		// Thumbnails are in a subdirectory
		$oldumask = umask(0); 
		mkdir($cache_namespace . '/thumbnails', 0777);
		umask($oldumask);
	}
	
	// XML
	if (!file_exists($cache_namespace . '/xml'))
	{
		$oldumask = umask(0); 
		mkdir($cache_namespace . '/xml', 0777);
		umask($oldumask);
	}
	if (!file_exists($cache_namespace . '/djvu'))
	{
		$oldumask = umask(0); 
		mkdir($cache_namespace . '/djvu', 0777);
		umask($oldumask);
	}
	
	// B&W
	if (!file_exists($cache_namespace . '/bw'))
	{
		$oldumask = umask(0); 
		mkdir($cache_namespace . '/bw', 0777);
		umask($oldumask);
	}
	if (!file_exists($cache_namespace . '/bw/thumbnails'))
	{
		$oldumask = umask(0); 
		mkdir($cache_namespace . '/bw/thumbnails', 0777);
		umask($oldumask);
	}
	

	// fetch source
	$djvu_filename = $cache_namespace . '/' . $SourceIdentifier . ".djvu";
	
	if (!file_exists($djvu_filename)) // don't fetch again if we don't need to
	{
		$url = 'http://www.archive.org/download/' . $SourceIdentifier . '/' . $SourceIdentifier . '.djvu';
		
		//echo $url . "\n";
		
		$command = "curl";
		
		if ($config['proxy_name'] != '')
		{
			$command .= " --proxy " . $config['proxy_name'] . ":" . $config['proxy_port'];
		}
		$command .= " --location " . $url . " > " . $djvu_filename;
		echo $command . "\n";
		system ($command);
	}
	
	// Get pages
	$pages = bhl_retrieve_item_pages($ItemID);
	
	$numpages = count($pages);
	$width = ceil(log10($numpages)) + 1;
	$width = max($width, 4);
	
	
	//print_r($pages);
	//exit();
	
	foreach ($pages as $page)
	{
		$filename = $cache_namespace . "/" . $page->FileNamePrefix . '.jpg'; 
		
		if (!file_exists($filename)) // don't over write
		{
		
			// Image filename
			$tiff_filename = $cache_namespace . "/" . $page->FileNamePrefix . '.tiff'; 
			
			$command = $config['djvu_path'] . "/ddjvu -format=tiff -page=" . $page->page_order . " -size=800x2000 "
			 . $djvu_filename . " " . $tiff_filename;
			echo $command . "\n";
			system($command);
			
			
			// Convert to JPEG
			$command = $config['convert'] . " " . $tiff_filename . " " . $filename;
			echo $command . "\n";
			system($command);
			
			exit();
			
			
			
			if (0)
			{
				// Try and remove background colour
				$command = $config['convert'] . " " . $filename . " -channel all -normalize " . $filename;
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
		
		// B&W
		$filename = $cache_namespace . "/bw/" . $page->FileNamePrefix . '.png'; 
		
		if (!file_exists($filename)) // don't over write
		{
		
			// Image filename
			$tiff_filename = $cache_namespace . "/bw/" . $page->FileNamePrefix . '.tiff'; 
			
			$command = $config['djvu_path'] . "/ddjvu -format=tiff -page=" . $page->page_order . " -size=800x2000 "
			 . " -mode=foreground "
			 . $djvu_filename . " " . $tiff_filename;
			echo $command . "\n";
			system($command);
			
			
			// Convert to PNG
			$command = $config['convert'] . " " . $tiff_filename . " " . $filename;
			echo $command . "\n";
			system($command);
			
			if (0)
			{
				// Optimise
				$command = "optipng-0.5/src/optipng " . $filename;  	
				echo $command . "\n";
				system($command, $return_var);
			}			
			
			// Thumbnail
			$thumbnail_filename = $cache_namespace . "/bw/thumbnails/" . $page->FileNamePrefix . '.gif'; 
			$command = $config['convert']  . ' -thumbnail 100 ' . $filename . ' ' . $thumbnail_filename;
			echo $command . "\n";
			system($command);
				
			
			// Kill TIFF
			unlink ($tiff_filename);
		}
		
		
			
		// XML
		$xml_filename = $cache_namespace . '/xml' . '/' . $page->FileNamePrefix . '.xml';
		
		if (!file_exists($xml_filename)) // don't over write
		{
				$command = $config['djvu_path'] . '/djvused ' . $djvu_filename . ' -e "select ' . $page->page_order 
					. ';save-page ' . $cache_namespace . '/djvu' . '/' . $page->page_order . '.djvu"';
				echo $command . "\n";
				system($command, $return_var);
				echo $return_var . "\n";
				
				$pagenum = sprintf("%0" . $width . "d", $page->page_order);
					
				// b) convert to XML
				$command = $config['djvu_path'] . '/djvutoxml ' . $cache_namespace . '/djvu'  . '/' . $page->page_order . '.djvu' 
				. ' ' . $xml_filename;
				echo $command . "\n";
				system($command, $return_var);
				echo $return_var . "\n";
		
		
		}

		
	}



}

?>