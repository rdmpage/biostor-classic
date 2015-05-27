<?php

require_once('../config.inc.php');
require_once('../lib.php');


$ItemID = 107532;

$cache_dir = '.';


{
	// Images are cached in folders with the ItemID as the name
	$basedir = $cache_dir. "/" . $ItemID;
	
	// Ensure cache subfolder exists for this item
	if (!file_exists($basedir))
	{
		$oldumask = umask(0); 
		mkdir($basedir, 0777);
		umask($oldumask);
	}
	
	$subdirectories_names = array('djvu', 'bw_images', 'images', 'xml', 'svg');
	
	foreach ($subdirectories_names as $dir)
	{
		$subdir = $basedir . '/' . $dir;
		
		if (!file_exists($subdir))
		{
			$oldumask = umask(0); 
			mkdir($subdir, 0777);
			umask($oldumask);
		}
		
		$subdirectories_names[$dir] = $subdir;
	}
	
	// JSON
	$json_filename = $basedir . '/' . $ItemID . '.json';
	
	if (!file_exists($json_filename))
	{
		$url = 'http://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetItemMetadata&itemid=' . $ItemID . '&pages=t&apikey=' . '0d4f0303-712e-49e0-92c5-2113a5959159' . '&format=json';
		$json = get($url);
	
		file_put_contents($json_filename, $json);
	}
	else
	{
		$json = file_get_contents($json_filename);
	}
			
	$obj = json_decode($json);
	
	if (($obj->Status == 'ok') && ($obj->Result != '')) // ok doesn't mean we have anything :(
	{		
		$djvu_filename = $basedir . '/' .  $obj->Result->SourceIdentifier . '.djvu';
	
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
		// Clean up	
		if (file_exists($djvu_filename))
		{
			unlink ($djvu_filename);
		}
		
		/*
		if (file_exists($subdirectories_names['djvu']))
		{
			$files = scandir($subdirectories_names['djvu']);
			
			foreach ($files as $filename)
			{
				if (preg_match('/\.djvu/', $filename))
				{
					unlink ($subdirectories_names['djvu'] . '/' . $filename);
				}
				rmdir($subdirectories_names['djvu']);
			}
		}
		*/
		
		// Package up
		$command = "tar -cvzpf" . $basedir . ".tar.gz " . $basedir;
		echo $command . "\n";
		system($command, $return_var);
		//echo $return_var . "\n";
		
	}


}

?>