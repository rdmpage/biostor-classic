<?php

// Find big page images and resize

date_default_timezone_set('Europe/London');

$result = array();

$dir = '/Volumes/Macintosh HD2/WebServer/biostor/cache';
$handle = opendir($dir);
while ($datei = readdir($handle))
{
	echo $datei . "\n";
	if (($datei != '.') && ($datei != '..'))
	{
		$file = $dir . "/" . $datei;
		if (is_dir($file))
		{
			$result[] = $file;
		}
	}
}
closedir($handle);

//print_r($result);


$now = date("Y-m-d", time());

foreach ($result as $file)
{

	$files = scandir($file);

	//print_r($files);
	
	foreach ($files as $f)
	{
		
		$filename = $file . '/' . $f;
		if (!is_dir($filename))
		{
			$t = date("Y-m-d", filemtime($filename));
			//if ($t == $now)
			{		
				if (filesize($filename) > 300000)
				{
				
					$command = '/usr/local/bin/mogrify -resize 800x \'' . $filename . '\'';
					echo $command . "\n";
					system($command);	
					
		
				}
			}
		}
	}


	/*
	$t = date("Y-m-d", filemtime($file));
	//if ($t == $now)
	{
		if (filesize($file) > 300000)
		{
			//echo $file . ' ' . $t . ' ' . filesize($file) . "\n";
			$command = '/usr/local/bin/mogrify -resize 800x \'' . $file . '\'';
			echo $command . "\n";
			system($command);	
			

		}
	}
		
	*/	
		
		
	
	//echo date("Y-m-d", filemtime($file)) . "\n";
}

?>