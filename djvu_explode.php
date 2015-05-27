<?php

require_once (dirname(__FILE__) . '/djvu_config.php');

// need djvu tols and optpng


//--------------------------------------------------------------------------------------------------

$filename = '';

if ($argc < 2)
{
	echo "Usage: djvu_explode.php <DjVu file>\n";
	exit(1);
}
else
{
	$filename = $argv[1];
	
	echo $filename . "\n";
	
	print_r($config);
	
	$base_dir = $filename;
	
	$base_dir = preg_replace('/(\.[a-z]{3,4})$/', '', $base_dir);
	
	// Directories
	if (!file_exists($base_dir))
	{
		$oldumask = umask(0); 
		mkdir($base_dir, 0777);
		umask($oldumask);
	}
		
	// subdirectories
	foreach ($config['subdirectories'] as $dir)
	{
		$subdir = $base_dir . '/' . $dir;
		
		if (!file_exists($subdir))
		{
			$oldumask = umask(0); 
			mkdir($subdir, 0777);
			umask($oldumask);
		}
	}
	
	// Get number of pages
	$command = 'djvused ' . $filename . ' -e "n"';
	
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
		
		// XML for each page
		if (1)
		{

			// XML
			// a) Extract individual DjVu pages
			$command = 'djvused ' . $filename . ' -e "select ' . $i 
				. ';save-page ' . $base_dir . '/' . $config['subdirectories']['djvu_dir'] . '/' . $i . '.djvu"';
			echo $command . "\n";
			system($command, $return_var);
			echo $return_var . "\n";
				
			// b) convert to XML
			$command = 'djvutoxml ' . $base_dir . '/' . $config['subdirectories']['djvu_dir'] . '/' . $i . '.djvu' 
			. ' ' . $base_dir . '/' . $config['subdirectories']['xml_dir'] . '/' . $pagenum . '.xml';
			echo $command . "\n";
			system($command, $return_var);
			echo $return_var . "\n";
		}
		
		// Page images
		if (1)
		{
			$tiff_filename = $base_dir . '/' . $config['subdirectories']['image_dir'] . '/' . $pagenum . '.tiff';
		
			$command = "ddjvu -format=tiff -page=" . $i;
			
			if ($config['djvu_mode'] != '')
			{
				$command .= " -mode=" . $config['djvu_mode'] . " ";
			}
			$command .= " -size=" . $config['image_width'] . "x2000 " . $filename . " " . $tiff_filename;
			echo $command . "\n";
			system($command, $return_var);
			//echo $return_var . "\n";
			
			
			// B&W PNG
			if (0)
			{
				// Convert PNG to save space
				$image_filename = $base_dir . '/' . $config['subdirectories']['image_dir'] . '/' . $pagenum . '.png';
				
				$command = "convert";			
				$command .= " -type Grayscale  -depth 8 ";

				$command .= $tiff_filename . "  " . $image_filename;  	
				echo $command . "\n";
				system($command, $return_var);
								
				// Optimise
				$command = "optipng-0.5/src/optipng " . $image_filename;  	
				echo $command . "\n";
				system($command, $return_var);
				//echo $return_var . "\n";
			}
			
			// JPEG with background toned down
			if (1)
			{
				// Convert JPEG to save space
				$image_filename = $base_dir . '/' . $config['subdirectories']['image_dir'] . '/' . $pagenum . '.jpg';
				
				$command = "convert";			
				$command .= " -depth 8 ";

				$command .= $tiff_filename . "  " . $image_filename;  	
				echo $command . "\n";
				system($command, $return_var);

				$command = "convert " . $image_filename . " -channel all -normalize " . $image_filename;		
				echo $command . "\n";
				system($command, $return_var);	
				
				// Optimise
			}
			
			
			// Delete TIFF
			unlink ($tiff_filename);
			
			// Thumbnails
			$command = "convert -thumbnail " . $config['thumbnail_width'] . " " . $image_filename . " " .  $base_dir . '/' . $config['subdirectories']['thumbnail_dir'] . '/' . $pagenum . '.jpeg';
			echo $command . "\n";
			system($command, $return_var);
			//echo $return_var . "\n";			
		}		
	}

}

?>
