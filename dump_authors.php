<?php

require_once (dirname(__FILE__) . '/db.php');

$sql = 'SELECT * FROM rdmp_author;';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

$items = array();
	
while (!$result->EOF) 
{	
	$values = array();
	
	$values[] =  $result->fields['author_id'];
	$values[] =  $result->fields['author_cluster_id'];
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
	
	echo join("\t", $values) . "\n";

	$result->MoveNext();		
}



?>