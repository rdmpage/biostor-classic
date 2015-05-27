<?php

/**
 * @file display_page.php
 *
 * Display a BHL page. If the page is part of an aticle we've identified display the article,
 * otherwise display item containing page
 *
 */
 
require_once('../db.php');
 
$PageID = 0;
$reference_id = 0;
$ItemID = 0;

if (isset($_GET['PageID']))
{
	$PageID = $_GET['PageID'];
}

if ($PageID != '')
{
	$references = bhl_retrieve_reference_id_from_PageID($PageID);
	if (count($references) == 1)
	{
		$reference_id = $references[0];
	}
	else
	{
		// Get containing item
		$ItemID = bhl_retrieve_ItemID_from_PageID($PageID);
	}
}

if (($reference_id == 0) && ($ItemID == 0))
{
	header('HTTP/1.1 404 Not Found');
	header('Status: 404 Not Found');
	$_SERVER['REDIRECT_STATUS'] = 404;
	
	echo 'PageID "' . $PageID . '" not found';
}
else
{
	if ($reference_id != 0)
	{
		header('Location: ' . $config['web_root'] . 'reference/' . $reference_id . "\n\n");
	}
	else
	{
		header('Location: ' . $config['web_root'] . 'item/' . $ItemID . '/page/' . $PageID . "\n\n");
	}	
}
?>