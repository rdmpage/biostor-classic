<?php

require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/bhl_names.php');
require_once (dirname(__FILE__) . '/bhl_text.php');
require_once (dirname(__FILE__) . '/www/tagtree/paths.php');
require_once (dirname(__FILE__) . '/extract_specimens.php');


//--------------------------------------------------------------------------------------------------
function specimens_has_been_parsed($reference_id)
{
	global $db;

	$sql = 'SELECT COUNT(reference_id) AS c FROM rdmp_reference_specimen_joiner 
WHERE (reference_id = ' . $reference_id . ')';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	return ($result->fields['c'] != 0);

}

//--------------------------------------------------------------------------------------------------
function specimens_with_code($code)
{
	global $db;
	
	$specimens = array();

	$sql = 'SELECT distinct code, occurrenceID FROM rdmp_reference_specimen_joiner 
WHERE (code = ' . $db->qstr($code) . ') AND code IS NOT NULL';


	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{	
		$occurence = new stdclass;
		$occurence->code = $result->fields['code'];
		
		if ($result->fields['occurrenceID'] != 0)
		{
			$occurence->occurrenceID = $result->fields['occurrenceID'];
		}
		$specimens[] = $occurence;
	
		$result->MoveNext();		
	}
	

	return $specimens;
}

//--------------------------------------------------------------------------------------------------
function specimens_references_with_code($code)
{
	global $db;
	
	$references = array();

	$sql = 'SELECT DISTINCT occurrenceID, reference_id FROM rdmp_reference_specimen_joiner 
WHERE (code = ' . $db->qstr($code) . ')';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{	
		$occurrenceID = $result->fields['occurrenceID'];
		if (!isset($references[$occurrenceID]))
		{
			$references[$occurrenceID] = array();
		}
	
		$references[$occurrenceID][] = $result->fields['reference_id'];
	
		$result->MoveNext();		
	}
	

	return $references;
}

//--------------------------------------------------------------------------------------------------
function specimens_dataset_from_occurrence($occurrenceID)
{
	global $db;
	
	$datasetID = 0;
		
	$sql = 'SELECT * FROM rdmp_specimen 
WHERE (occurrenceID = ' . $occurrenceID . ') 
LIMIT 1';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$datasetID = $result->fields['datasetID'];
	}
	
	return $datasetID;
}

//--------------------------------------------------------------------------------------------------
function specimens_dataset($datasetID)
{
	global $db;
	
	$dataset = new stdclass;
		
	$sql = 'SELECT * FROM rdmp_gbif_dataset 
WHERE (datasetID = ' . $datasetID . ') 
LIMIT 1';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$dataset->datasetID = $result->fields['datasetID'];
		$dataset->dataResourceName = $result->fields['dataResourceName'];
		$dataset->providerID = $result->fields['providerID'];
		$dataset->providerName = $result->fields['providerName'];
	}
	
	return $dataset;
}

//--------------------------------------------------------------------------------------------------
function specimens_from_occurrenceID($occurrenceID)
{
	global $db;
	
	$occurrence = new stdclass;

	$sql = 'SELECT * FROM rdmp_specimen
WHERE (occurrenceID = ' . $occurrenceID . ') LIMIT 1';

	//echo $sql;

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1) 
	{	
		$occurrence->occurrenceID 	= $result->fields['occurrenceID'];
		$occurrence->code 			= $result->fields['code'];
		$occurrence->scientificName 	= $result->fields['scientificName'];
		$occurrence->kingdom 		= $result->fields['kingdom'];
		$occurrence->class 			= $result->fields['class'];
		$occurrence->family 			= $result->fields['family'];
		$occurrence->lineage 			= json_decode($result->fields['lineage']);
		
	}
	
	//print_r($occurrence);

	return $occurrence;
}

//--------------------------------------------------------------------------------------------------
function specimens_delete($reference_id)
{
	global $db;
	
	$sql = 'DELETE FROM rdmp_reference_specimen_joiner WHERE reference_id=' . $reference_id;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

}

//--------------------------------------------------------------------------------------------------
function specimens_from_db($reference_id)
{
	global $db;
	
	$specimens = array();

	$sql = 'SELECT DISTINCT code, occurrenceID FROM rdmp_reference_specimen_joiner 
WHERE (reference_id = ' . $reference_id . ') AND code IS NOT NULL';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{	
		$occurence = new stdclass;
		$occurence->code = $result->fields['code'];
		
		if ($result->fields['occurrenceID'] != 0)
		{
			$occurence->occurrenceID = $result->fields['occurrenceID'];
		}
		$specimens[] = $occurence;
	
		$result->MoveNext();		
	}
	

	return $specimens;
}

//--------------------------------------------------------------------------------------------------
function specimens_from_reference($reference_id)
{
	global $db;
	
	// delete any existing specimens
	$sql = 'DELETE FROM rdmp_reference_specimen_joiner WHERE reference_id=' . $reference_id;
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	$pages = bhl_retrieve_reference_pages($reference_id);
	$page_ids = array();
	foreach ($pages as $p)
	{
		$page_ids[] = $p->PageID;
	}	
	
	//echo "PageIDs:\n";
	//print_r($page_ids);

	$text = bhl_fetch_text_for_pages($page_ids);
	
	$text = str_replace ('\n', "\n" , $text);
	$text = str_replace ("\n ", "\n" , $text);
	
	$specimens = extract_specimen_codes($text);
	
	$extra = array();
	foreach ($specimens as $code)
	{
		$extra = array_merge($extra, extend_specimens($code, $text));
	}
	$specimens = array_unique(array_merge($specimens, $extra));
	sort($specimens);
	
	if (count($specimens) == 0)
	{
		// none found, insert NULL entry to flag that we've processed this reference
		$sql = 'INSERT INTO rdmp_reference_specimen_joiner(reference_id,code) VALUES('
		. $reference_id . ',NULL)';
	}
	else
	{
		foreach ($specimens as $code)
		{
			$sql = 'INSERT INTO rdmp_reference_specimen_joiner(reference_id,code) VALUES('
			. $reference_id . ',' . $db->qstr($code) . ')';
	
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
			
			
		}
	}
	
	/*
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
	*/
	
	return $specimens;
	
}

if (0)
{
	$sql = 'SELECT * FROM rdmp_reference WHERE';
	
	$sql .=  ' issn="0091-7958" LIMIT 10';
	
	
	$ids = array();
	
	$ids=array(124);
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{	
		$ids[] = $result->fields['reference_id'];
	
		$result->MoveNext();		
	}
	
	foreach ($ids as $reference_id)
	{
		echo $reference_id . "\n";
		
		specimens_from_reference($reference_id);
	}
}


?>