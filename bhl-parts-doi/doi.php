<?php

//----------------------------------------------------------------------------------------

$filename = 'doi.txt';

$headings = array();

$row_count = 0;

$file = @fopen($filename, "r") or die("couldn't open $filename");
		
$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$row = fgetcsv(
		$file_handle, 
		0, 
		"\t" 
		);
		
	$go = is_array($row);
	
	if ($go)
	{
		if ($row_count == 0)
		{
			$headings = $row;	
		}
		else
		{
			$obj = new stdclass;
		
			foreach ($row as $k => $v)
			{
				if ($v != '')
				{
					$obj->{$headings[$k]} = $v;
				}
			}
		
			//print_r($obj);	
			
			if ($obj->{$headings[0]} == 'Part')
			{
				if (preg_match('/^10.5962/', $obj->DOI))
				{
					echo 'UPDATE rdmp_reference SET doi="' . $obj->DOI . '" WHERE PartID=' . $obj->EntityID . ' AND doi IS NULL;' . "\n";
				}
			}
	
		}
	}	
	$row_count++;
}
?>

