<?php

require_once ('../db.php');
require_once ('../reference.php');

$since = date('Y-m-d', time());

if (isset($_GET['since']))
{
	$since = $_GET['since'];
	
	// Check format
	if (preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/', $since))
	{
	}
	else
	{
		$since = date('Y-m-d', time());
	}
}

$callback = '';

if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}

$obj = new stdclass;
$obj->since = $since;
$obj->count = 0;
$obj->limit = 1000;
$obj->items = array();

$sql = 'SELECT DISTINCT ItemID FROM bhl_page 
INNER JOIN rdmp_reference USING(PageID)
WHERE rdmp_reference.updated >= ' . $db->qstr($since) . ' LIMIT ' . $obj->limit;

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{	
	$obj->items[] = $result->fields['ItemID'];
	$result->MoveNext();		
}

$obj->count = count($obj->items);


header('Content-type: text/plain');
if ($callback != '')
{
	echo $callback .'(';
}
echo json_format(json_encode($obj));
if ($callback != '')
{
	echo ')';
}

?>