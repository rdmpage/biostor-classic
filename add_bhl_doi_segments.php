<?php

// Add BHL DOIs for parts
require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/lib.php');


$filename = 'SegmentsFromBioStor.txt';
$file_handle = fopen($filename, "r");

$count = 0;

$ids = array();

while (!feof($file_handle)) 
{
	$row = trim(fgets($file_handle));
	
	//echo $row . "\n";
	
	if ($count > 0)
	{
		$parts = explode("\t", $row);
		
		//print_r($parts);
		
		//echo "|" . $parts[2] . "|\n";
		
		if (strlen($parts[2]) > 1)
		{
			$reference_id = $parts[1];
			$doi = $parts[2];
			echo $reference_id . ' ' . $doi . "\n";
			$ids[] = $reference_id;

			$sql = 'UPDATE rdmp_reference SET doi="' . $doi . '" WHERE reference_id=' . $reference_id;

			echo $sql . "\n";
			
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);						
		}
	}
	
	$count++;
	//if ($count > 100) break;
}

file_put_contents('parts.txt', join(",\n", $ids));

echo join(",\n", $ids);
echo "\n";
?>
