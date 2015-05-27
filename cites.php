<?php

/**
 * @file cites.php
 *
 * Citations
 */
 
require_once(dirname(__FILE__) . '/db.php');
 
 
//--------------------------------------------------------------------------------------------------
function num_cited_by($reference_id)
{
	global $db;
	
	$count = 0;
	
	$sql = 'SELECT COUNT(DISTINCT(rdmp_reference_cites.reference_id)) AS c 
	FROM rdmp_reference_citation_string_joiner
INNER JOIN rdmp_reference_cites USING(citation_string_id)
WHERE rdmp_reference_citation_string_joiner.reference_id=' . $reference_id;

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	$count = $result->fields['c'];
	
	return $count;
}

//--------------------------------------------------------------------------------------------------
function num_cites($reference_id)
{
	global $db;
	
	$count = 0;
	
	$sql = 'SELECT COUNT(citation_string_id) AS c
	FROM rdmp_reference_cites
INNER JOIN rdmp_citation_string USING(citation_string_id)
WHERE rdmp_reference_cites.reference_id = ' . $reference_id;

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	$count = $result->fields['c'];
	
	return $count;
}
 
 
?>