<?php

// Find zero size thumbnails and delete

date_default_timezone_set('Europe/London');

$result = array();

$dir = '/Volumes/My Book/WebServer/biostor/cache/';
$handle = opendir($dir);
while ($datei = readdir($handle))
{
	echo $datei . "\n";
	if (($datei != '.') && ($datei != '..'))
	{
		$file = $dir . $datei;
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
	$t = date("Y-m-d", filemtime($file));
	if ($t == $now)
	{
		echo $file . ' ' . $t . "\n";
		
		// thumbnails
		
		$dir = $file . '/thumbnails/';
		$handle = opendir($dir);
		while ($datei = readdir($handle))
		{
			if (($datei != '.') && ($datei != '..'))
			{
				$f = $dir . $datei;
				if (!is_dir($f))
				{
					 echo $f . " has zero bytes\n";
					 
					 if (filesize($f) == 0)
					 {
					 	unlink($f);
					 }
				}
			}
		}
		
		
	}
	//echo date("Y-m-d", filemtime($file)) . "\n";
}

?>