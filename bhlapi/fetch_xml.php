<?php

// extract DjVu XML and B&W images for OCR editing

require_once('../config.inc.php');
require_once('../lib.php');


$items = array(107532);
$items = array(107523);


// The Annals and magazine of natural history; zoology, botany, and geolo 6th ser. v 15
$items=array(54281);

// Proceedings of the Biological Society of Washington
$items = array(107532);

// Records of the Indian Museum
$items = array(109280);

$items=array(14546,59466);
$items=array(59466);

$items=array(87679);

$items=array(107577);

$items=array(48797);
$items=array(137061);

$items=array(25867);
$items=array(47042);
$items=array(15462);
$items=array(123332);
$items=array(136890);

$items=array(107599);
$items=array(148220);

$items=array(49419);
$items=array(125569);
$items=array(91490);


$items=array(96683);

// SIDA
$items=array(
38228,
38229,
38230,
38231,
38232,
38233,
38209,
38210,
38211,
38212,
38226,
38227,
34595,
34596,
34597,
34587,
34588,
34589,
36567,
34584,
34585,
34586
);

$items=array(112907);

//$items=array(49423);


$items=array(112908);

$items=array(
/*'112905',
'112906' ,
'112909' ,
'112931' ,
'112938' ,
'112939' ,
'112941' ,
'112942' ,
'112955' ,
'112956' ,
'112959' ,
'112960' ,
'112961' ,
'112962' ,
'112963' ,
'112965' ,
'112967' ,*/
'112968' ,
'112981' ,
'112983' ,
'158956' 
);


$items=array(
107533
);

$items=array(
91477
);

$items=array(
7377
);


$items=array(
109654
);

$items=array(32981);


$items=array(
'51769',
'52318',
'52058',
'52404',
'52056',
'88582',
'52054',
'91165'
);

$items=array(19428);

$items=array(33608);

$items=array(126838);

$items=array(
50578,
50592,
50577,
50573,
50583,
50576,
50584,
50637,
50585,
50601,
50594,
50590,
54612,
50593,
50586,
50598,
50574
);

$items=array(
125660
);


foreach ($items as $ItemID)
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

	// Subdirectories for exploding
	$subdirectories_names = array('djvu', 'bw_images', 'xml');
	
	foreach ($subdirectories_names as $dir)
	{
		$subdir = $cache_namespace . '/' . $dir;
		
		if (!file_exists($subdir))
		{
			$oldumask = umask(0); 
			mkdir($subdir, 0777);
			umask($oldumask);
		}
		
		$subdirectories_names[$dir] = $subdir;
	}
	
	// JSON
	$url = 'http://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetItemMetadata&itemid=' . $ItemID . '&pages=t&apikey=' . '0d4f0303-712e-49e0-92c5-2113a5959159' . '&format=json';
		
	echo $url;
	$json = get($url);
			
	$obj = json_decode($json);
	
	//print_r($obj);
	//exit();
	
	if (($obj->Status == 'ok') && ($obj->Result != '')) // ok doesn't mean we have anything :(
	{		
		$djvu_filename = $cache_namespace . '/' .  $obj->Result->SourceIdentifier . '.djvu';
	
		if (!file_exists($djvu_filename)) // don't fetch again if we don't need to
		{
			$url = 'http://www.archive.org/download/' . $obj->Result->SourceIdentifier . '/' . $obj->Result->SourceIdentifier . '.djvu';
	
			$command = "curl";
			
			if ($config['proxy_name'] != '')
			{
				$command .= " --proxy " . $config['proxy_name'] . ":" . $config['proxy_port'];
			}
			$command .= " --location " . $url . " > '" . $djvu_filename . "'";
			echo $command . "\n";
			system ($command);
		}
		
		// Get number of pages
		$command = 'djvused \'' . $djvu_filename . '\' -e "n"';
		
		$output = array();
		$return_var = 0;
		exec($command, $output, $return_var);
		
		print_r($output);
		echo $return_var . "\n";
		
		$numpages = $output[0];
		$width = ceil(log10($numpages)) + 1;
		//$width = min($width, 4);
		$width = max($width, 4);
		
		for ($i=1; $i <= $numpages; $i++)
		{
			$pagenum = sprintf("%0" . $width . "d", $i);
			echo $pagenum . "\n";
			
			//print_r($subdirectories);
			
			// XML for each page
			if (1)
			{
	
				// XML
				// a) Extract individual DjVu pages
				$command = 'djvused ' . $djvu_filename . ' -e "select ' . $i 
					. ';save-page ' . $subdirectories_names['djvu'] . '/' . $i . '.djvu"';
				echo $command . "\n";
				system($command, $return_var);
				echo $return_var . "\n";
					
				// b) convert to XML
				$command = 'djvutoxml ' . $subdirectories_names['djvu'] . '/' . $i . '.djvu' 
				. ' ' . $subdirectories_names['xml'] . '/' . $pagenum . '.xml';
				echo $command . "\n";
				system($command, $return_var);
				echo $return_var . "\n";
			}
			
			
			// BW Images
			if (0)
			{
				$image_filename = $subdirectories_names['bw_images'] . '/' . $pagenum . '.png';
				
				if (!file_exists($image_filename))
				{
				
					$tiff_filename = $subdirectories_names['bw_images'] . '/' . $pagenum . '.tiff';
				
					$command = "ddjvu -format=tiff -page=" . $i;
					
					$command .= " -mode=foreground ";
	
					$command .= " -size=800x2000 " . $djvu_filename . " " . $tiff_filename;
					echo $command . "\n";
					system($command, $return_var);
					
					// Convert PNG to save space
					
					$command = "convert";			
					$command .= " -type Grayscale  -depth 8 ";
	
					$command .= $tiff_filename . "  " . $image_filename;  	
					echo $command . "\n";
					system($command, $return_var);
							
					
					if (0)
					{
						// Optimise (savings for BW aren't much)
						$command = "optipng " . $image_filename;  	
						echo $command . "\n";
						system($command, $return_var);
						//echo $return_var . "\n";
					}
					
					// Clean up
					// Delete TIFF
					unlink ($tiff_filename);
				}
				
			}
			
		
		}
	
		
	}


}

?>