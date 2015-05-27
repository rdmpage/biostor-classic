<?php

require_once ('../db.php');

$id = $_GET['id'];
$query = $_GET['q'];

$callback = '';

if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}

$obj = new stdclass;
//$obj->matches = 0;
$obj->results = array();
$obj->query = $query;

$bhl_pages = bhl_retrieve_reference_pages($id);

$sql = 'SELECT distinct page FROM rdmp_documentcloud WHERE ocr_text LIKE ' . $db->qstr('%' . $query . '%') 
	. ' ORDER BY page '
	. ' LIMIT ' . count($bhl_pages);

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$obj->results[] = (Integer)$result->fields['page'];		
	$result->MoveNext();
}

//$obj->matches = count($obj->results);


header('Content-type: text/plain');
if ($callback != '')
{
	echo $callback .'(';
}
echo json_encode($obj);
if ($callback != '')
{
	echo ')';
}


?>