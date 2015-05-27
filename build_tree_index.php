<?php

require_once(dirname(__FILE__) . '/bhl_names.php');
require_once(dirname(__FILE__) . '/www/tagtree/paths.php');

// build taxon index table for heirarchical searching indexing (need to make this incremental at some point)

// Clean
$sql = 'DELETE FROM rdmp_tree_index;';
	
$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

$references = array();

$sql = 'SELECT reference_id FROM rdmp_reference';
$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	
	$references[] = $result->fields['reference_id'];
	
	$result->MoveNext();				
}

print_r($references);

foreach ($references as $reference_id)
{
	// Classify document taxonomically
	$o = bhl_names_in_reference($reference_id);
	$p = get_paths($o->tags);
	
	if (0)
	{
		// majority rule, won't work for things like treemaps...
		$majrule = majority_rule_path($p);
		
		$sql = 'INSERT INTO rdmp_tree_index(reference_id, path) VALUES (' . $reference_id . ',' . $db->qstr($majrule) . ')';
	
		echo $sql . "\n";
	
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	}
	else
	{
		foreach ($p as $path)
		{
			$sql = 'INSERT INTO rdmp_tree_index(reference_id, path) VALUES (' . $reference_id . ',' . $db->qstr($path) . ')';
		
			echo $sql . "\n";
		
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
		}
	
	}
}


?>