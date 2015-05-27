<?php

// Find original PDFs and delete

// ls -R | grep pdf_original > /Library/WebServer/Sites/biostor/original.txt

require_once('config.inc.php');

$f = dirname(__FILE__) . '/original.txt';

$file_handle = fopen($f, "r");

while (!feof($file_handle)) 
{
	$filename = trim(fgets($file_handle));

	preg_match('/^(..)(..)(..)/', $filename, $matches);
	
	// Do we have PDF already?
	$cache_namespace = $config['cache_dir']. '/pdf';
	
	$pdf_path = $cache_namespace . '/' . $matches[1] . '/' . $matches[2] . '/' . $matches[3];
	$pdf_filename = $pdf_path . '/' . $filename;
	
	echo $pdf_filename . "\n";
	system('unlink ' . $pdf_filename);
	
	
}


?>