<?php

require_once (dirname(__FILE__) . '/db.php');

// build tables for text indexing (need to make this incremental at some point)

// Clean
$sql = 'DELETE FROM rdmp_text_index WHERE (object_type=' . $db->qstr('title') . ')';
	
$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

$titles = array();

$sql = 'SELECT reference_id, title FROM rdmp_reference';
$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	
	$titles[$result->fields['reference_id']] = $result->fields['title'];
	
	$result->MoveNext();				
}

print_r($titles);

foreach ($titles as $k => $v)
{

	$sql = 'INSERT INTO rdmp_text_index(object_type, object_id, object_uri, object_text)
	VALUES ("title"'
	. ', ' . $k 
	. ', ' . $db->qstr($config['web_root'] . 'reference/' . $k) 
	. ', ' . $db->qstr($v) 
	. ')';
	
	echo $sql;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
}


?>