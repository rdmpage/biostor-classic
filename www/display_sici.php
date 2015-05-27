<?php

/**
 * @file display_sici.php
 *
 * Handle SICIs. If reference with SICI exists in database, redirect to web page displaying
 * reference, otherwise 404.
 *
 */
 
require_once('../db.php');
 
$id = 0;
$sici = '';

if (isset($_GET['sici']))
{
	$sici = $_GET['sici'];
}

if ($sici != '')
{
	$id = db_retrieve_reference_from_sici($sici);
}

if ($id == 0)
{
	header('HTTP/1.1 404 Not Found');
	header('Status: 404 Not Found');
	$_SERVER['REDIRECT_STATUS'] = 404;
	
	echo 'SICI "' . $sici . '" not found';
}
else
{
	header('Location: ' . $config['web_root'] . 'reference/' . $id . "\n\n");
}
?>