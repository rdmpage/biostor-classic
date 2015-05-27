<?php

// create GBIF paths

require_once('paths.php');

for ($i = 0; $i <= 5937422; $i++)
{
	//echo $i . "\n";
	echo '.';
	$sql = 'SELECT * FROM nub_2011_10_31 WHERE concept_id = ' . $i . ' AND path IS NULL LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$path = '';
		
		$keys=array('k','p','c','o','f','g','s');
		
		foreach ($keys as $k)
		{
			if ($result->fields[$k] != '')
			{
				$path .= '/' . $result->fields[$k];
			}
		}
			
		//echo $path . "\n";
		
		store_path($result->fields['concept_id'], $path);
	}
}


?>