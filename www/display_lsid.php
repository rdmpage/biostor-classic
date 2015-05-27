<?php

/**
 * @file display_lsid.php
 *
 * Handle LSIDs. If object with LSID exists in database, redirect to web page displaying
 * reference, otherwise 404.
 *
 */
 
require_once('../db.php');
require_once('../lib.php');
 
$id = 0;
$lsid = '';

if (isset($_GET['lsid']))
{
	$lsid = $_GET['lsid'];
}

if ($lsid != '')
{
	$id = db_retrieve_reference_from_lsid($lsid);
}

if ($id == 0)
{
	header('HTTP/1.1 404 Not Found');
	header('Status: 404 Not Found');
	$_SERVER['REDIRECT_STATUS'] = 404;
	
	echo 'LSID "' . $lsid . '" not found';
}
else
{
	header('Location: ' . $config['web_root'] . 'reference/' . $id . "\n\n");
	$_SERVER['REDIRECT_STATUS'] = 200;
}
?>