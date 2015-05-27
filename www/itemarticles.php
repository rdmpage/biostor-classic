<?php

require_once ('itemutilities.php');

$ItemID = -1;

if (isset($_GET['item']))
{
	$ItemID = $_GET['item'];
}

// handle possible synonym
if (isset($_GET['ItemID']))
{
	$ItemID = $_GET['ItemID'];
}

$callback = '';

if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}

$obj = new stdclass;

if ($ItemID != -1)
{
	$obj = articles_for_item($ItemID);
}

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