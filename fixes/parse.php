<?php

require_once('../db.php');

//----------------------------------------------------------------------------------------

$filename = 'BiostorAuthorsWithoutIDs.txt';

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
		
			// print_r($obj);	
			
			$terms = array();
			
			$output = array();
			
			if (isset($obj->Lastname))
			{
				$terms[] = 'lastname="' . addcslashes($obj->Lastname, '"') . '"';
				
				$output[] = $obj->Lastname;
			}
			else
			{
				$output[] = "";
			}
			
			if (isset($obj->Firstnamee))
			{
				$terms[] = 'forename="' . addcslashes($obj->Firstnamee, '"') . '"';
				
				$output[] = $obj->Firstnamee;
			}
			else
			{
				$output[] = "";
			}
			
			$sql = 'SELECT * FROM rdmp_author WHERE ' . join (" AND ", $terms) . ' LIMIT 1';
			
			// echo $sql . "\n";
			
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

			if ($result->NumRows() == 1)
			{
				$obj->id = $result->fields['author_id'];
				
				$output[] = $obj->id;
				
				echo join("\t", $output) . "\n";
			}

			
		}
	}	
	$row_count++;
}
?>

