<?php

require_once (dirname(__FILE__) . '/db.php');

$sql = "SET character_set_results=utf8";
$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


$sql = 'SELECT distinct rdmp_author.author_id, 
rdmp_author.author_cluster_id, 
rdmp_author.lastname, 
rdmp_author.forename, 
rdmp_author.suffix FROM rdmp_author 
INNER JOIN rdmp_author_reference_joiner ON rdmp_author.author_id = rdmp_author_reference_joiner.author_id 
INNER JOIN rdmp_reference ON rdmp_reference.reference_id = rdmp_author_reference_joiner.reference_id
WHERE rdmp_reference.PageID <> 0
;';

//$sql = 'select * from rdmp_author where author_id=16850;';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

$items = array();
	
while (!$result->EOF) 
{	
	//print_r($result);
	
	//echo $result->fields['forename'] . "\n";
	
	$go = false;
	
	/*
	// long names
	if (strlen($result->fields['forename']) > 20)
	{
		$go = true;
	}
	*/
	
	/*
	$forename = utf8_encode($result->fields['forename']);
	if (preg_match('/;/', $forename))
	{
		$go = true;
	}
	*/
	
	if (0)
	{
		$forename = $result->fields['forename'];
		if (preg_match('/[A-Z ]\?[A-Z ]?/ui', $forename))
		{
			$go = true;
		}
	}

	if (0)
	{
		$lastname = $result->fields['lastname'];
		if (preg_match('/[A-Z ]\?[A-Z ]?/ui', $lastname))
		{
			$go = true;
		}
	}	

	// names with ;
	if (0)
	{
		$forename = $result->fields['lastname'];
		if (preg_match('/;/ui', $forename))
		{
			$go = true;
		}
	}	
	if (0)
	{
		$string = $result->fields['forename'];
		if (preg_match('/\)/u', $string))
		{
			$go = true;
		}
	}	
	if (0)
	{
		$string = $result->fields['lastname'];
		if (strlen($string) > 20)
		{
			$go = true;
		}
	}	
	
	if (0)
	{
		$string = $result->fields['forename'];
		if (preg_match('/[A-Z] [A-Z]/ui', $string))
		{
			$go = true;
		}
	}	
	
	if (0)
	{
		$string = $result->fields['forename'];
		if (preg_match('/^[A-Z](\s+[A-Z]){3,}$/ui', $string))
		{
			$go = true;
		}
	}	
	
	if (0)
	{
		$string = $result->fields['lastname'];
		if (preg_match('/^Jr /u', $string))
		{
			$go = true;
		}
	}	
	
	if (1)
	{
		$string = $result->fields['suffix'];
		if ($string != '')
		{
			$go = true;
			//$go = false;
			if (preg_match('/[J|S]\.?r\.?/i', $result->fields['suffix']))
			{
				//$go = false;
			}
			if ($result->fields['forename'] != '')
			{
				//$go = false;
			}
			if (preg_match('/^I[i]+$/i', $string))
			{
				//$go = false;
			}
			
		}
	}		
		
		
	//$go = true;
	
	
	
	
	if ($go)
	{
		$values = array();
	
		$values[] =  $result->fields['author_id'];
		$values[] =  $result->fields['author_cluster_id'];
		//$values[] =  utf8_encode($result->fields['lastname']);
		//$values[] =  utf8_encode($result->fields['forename']);
		$values[] =  $result->fields['lastname'];
		$values[] =  $result->fields['forename'];
		if ($result->fields['suffix'] != '')
		{
			$values[] =  $result->fields['suffix'];
		}
		else
		{
			$values[] = '';
		}
	
		echo "-- " . join(" | ", $values) . "\n";
		
		
		//echo 'UPDATE rdmp_author SET forename="' . str_replace('.', '', $result->fields['forename']) . '" where author_id=' . $result->fields['author_id'] . ';' . "\n";
		
		/*
		$s = $result->fields['forename'];
		$s = str_replace(' ', '', $s);
		$s = mb_convert_case($s, MB_CASE_TITLE);
		echo 'UPDATE rdmp_author SET forename="' . $s . '" where author_id=' . $result->fields['author_id'] . ';' . "\n";
		*/
		
		//echo 'UPDATE rdmp_author SET forename="' . str_replace('.', '', $result->fields['suffix']) . '", suffix=NULL where author_id=' . $result->fields['author_id'] . ';' . "\n";
		//echo 'UPDATE rdmp_author SET forename="' . $result->fields['forename'] . ' ' . str_replace('.', '', $result->fields['suffix']) . '", suffix=NULL where author_id=' . $result->fields['author_id'] . ';' . "\n";
		//echo 'UPDATE rdmp_author SET forename="' . str_replace('.', '', $result->fields['suffix']) . '", suffix="' . $result->fields['forename'] . '" where author_id=' . $result->fields['author_id'] . ';' . "\n";
		//echo 'UPDATE rdmp_author SET suffix="' . strtoupper($result->fields['suffix']) . '" where author_id=' . $result->fields['author_id'] . ';' . "\n";
		
		
	}
	
	$result->MoveNext();		
}



?>