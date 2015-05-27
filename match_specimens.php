<?php

// map specimens to GBIF

require_once (dirname(__FILE__) . '/specimens.php');

function store_specimen($specimen)
{
	global $db;
	
	$sql = 'SELECT * FROM rdmp_specimen WHERE occurrenceID=' . $specimen->occurrenceID . ' LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 0)
	{
		$keys = array();
		$values = array();
		
		foreach ($specimen as $k => $v)
		{
			switch ($k)
			{
				case 'occurrenceID':
				case 'datasetID':
				case 'institutionCode':
				case 'collectionCode':
				case 'catalogueNumber':
				case 'taxonID':
				case 'scientificName':
				case 'kingdom':
				case 'class':
				case 'family':
					$keys[] = $k;
					$values[] = $db->qstr($v);
					break;
					
				
				case 'lineage':
					$keys[] = $k;
					$values[] = $db->qstr(json_encode($v));
					break;
				
					
				default:
					break;
			}
		}
		
		$sql = "INSERT INTO rdmp_specimen(" . join(",", $keys) . ") VALUES(" . join(",", $values) . ")";
		
		//echo $sql;
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	}
}

/*

$sql = 'SELECT DISTINCT reference_id FROM rdmp_reference_specimen_joiner WHERE code IS NOT NULL';

//$sql .= ' AND code LIKE "CAS-SU %"';

//$sql .= ' AND reference_id > 101000';

//$sql .= ' ORDER BY reference_id';


$ids = array();

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{	
	$ids[] = $result->fields['reference_id'];

	$result->MoveNext();		
}
*/

//$ids=array(95679);
//$ids=array(65896);
//$ids=array(81423);

//$ids=array(10);
//$ids=array(15);

//$ids=array(81423,65896,95679);
//$ids=array(65542);
//$ids=array(85759);
//$ids=array(752);
//$ids=array(83093);
//$ids=array(206);

//$ids=array(101954);

/*
$ids=array(101964);
$ids=array(102054);
$ids=array(102056);

$ids=array(78401);

$ids = array(14515);
*/

$ids = array(105389);

$ids = array(110934);

$ids = array(117848);

$ids = array(128037);


foreach ($ids as $reference_id)
{
	
	echo $reference_id . "\n";
	

	$specimens = specimens_from_db($reference_id);
	
	if (count($specimens) == 0)
	{
		$specimens = specimens_from_reference($reference_id);
	}
	
	echo "Specimens:\n";
	print_r($specimens);
	
	$nm = bhl_names_in_reference_by_page($reference_id);
	$nm->names;
	
	// Get majority rule taxon (what paper is about)
	$tags = array();
	foreach ($nm->names as $name)
	{
		$tags[] = $name->namestring;
	}
	
	$paths = get_paths($tags);
	$majority_rule = majority_rule_path($paths);
	$expanded = expand_path($majority_rule);
	
	print_r($expanded);
	
	// OK, now match...
	foreach ($specimens as $specimen)
	{
		//$go = false;
		$go = true;
	
	
	
		$code = $specimen->code;
		
		if (preg_match('/^AMNH /', $code))
		{
			if (in_array('Amphibia', $expanded))
			{
				$code = str_replace('AMNH ', 'AMNH A-', $code);
			}
			if (in_array('Reptilia', $expanded))
			{
				$code = str_replace('AMNH ', 'AMNH R-', $code);
			}
			if (in_array('Aves', $expanded))
			{
				$code = str_replace('AMNH ', 'AMNH Skin-', $code);
			}
		}
		if (preg_match('/^MCZ /', $code))
		{
			if (in_array('Amphibia', $expanded))
			{
				$code = str_replace('MCZ ', 'MCZ A-', $code);
			}
			if (in_array('Reptilia', $expanded))
			{
				$code = str_replace('MCZ ', 'MCZ R-', $code);
			}
		}
		if (preg_match('/^LSUMZ /', $code))
		{
			//$go = true;
			$code = str_replace('LSUMZ ', 'LSU ', $code);
		}
		if (preg_match('/^UAZ /', $code))
		{
			//$go = true;
			$code = str_replace('UAZ ', 'UAZ UAZ ', $code);
		}
		if (preg_match('/^CAS-SU /', $code))
		{
			$go = true;
			$code = str_replace('CAS-SU ', 'CAS ', $code);
		}
	
		if ($go)
		{
			$url = 'http://iphylo.org/~rpage/phyloinformatics/services/get_gbif_occurrence.php?code=' 
				. rawurlencode($code);
			
			echo $url . "\n";
			
			$json = get($url);
			
			//echo $json;
			
			$hits = json_decode($json);
			
			print_r($hits);
			
			// score match
			
			$hit = 0;
			foreach ($hits->occurrences as $occurrenceID => $occurrence)
			{
				// cache
				store_specimen($occurrence);
			
			
				if (in_array($occurrence->class, $expanded))
				{
					echo $occurrence->class . "\n";
					$hit = $occurrenceID;
				}
			}
			
			if ($hit != 0)
			{
				echo "===hit===\n";
				echo $specimen->code . "\n";
				print_r($hits->occurrences->{$hit});
				
				{				
					// Update Database
					
					$sql = "UPDATE rdmp_reference_specimen_joiner SET occurrenceID=" . $hits->occurrences->{$hit}->occurrenceID . " WHERE reference_id="
					. $reference_id . " AND code=" . $db->qstr($specimen->code);
					
					echo $sql . "\n";
		
					$result = $db->Execute($sql);
					if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
				}				
				
				
			}
			$go = false;
		}
	}
	
	
}

?>

