<?php

error_reporting(E_ALL);

require_once ('../db.php');

$response = new stdclass;
$response->ok = false;


$json = '';
$callback = '';

if (isset($_GET['json']))
{
	$json = $_GET['json'];
}

if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}


if ($json != '')
{
	$obj = json_decode($json);
	
	// print_r($obj);
	
	if (isset($obj->reference_id))
	{
		// remove old pages
		$sql = 'DELETE FROM rdmp_reference_page_joiner WHERE reference_id=' . $obj->reference_id . ';';
		
		// echo $sql;		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
		
		// update article
		$sql = 'UPDATE rdmp_reference SET PageID=' . $obj->bhl_pages[0] . ' WHERE reference_id=' . $obj->reference_id. ';';
		
		//echo $sql;
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
		
		// update pages
		$n = count($obj->bhl_pages);
		for ($i = 0; $i < $n; $i++)
		{
			$sql = 'INSERT INTO rdmp_reference_page_joiner (reference_id, PageID, page_order) VALUES (' 
			. $obj->reference_id . ',' . $obj->bhl_pages[$i] . ',' . $i . ')'. ';';
			
			//echo $sql;
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);			
		}
		
		$response->ok = true;
	}

}

if ($callback != '')
{
	echo $callback . '(';
}

echo json_encode($response);

if ($callback != '')
{
	echo ')';
}


?>

