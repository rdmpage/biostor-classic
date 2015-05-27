<?php

/**
 * @file display_doi.php
 *
 * Handle DOIs. If reference with DOI exists in database, redirect to web page displaying
 * reference, otherwise 404.
 *
 */
 
require_once('../db.php');
 
$id = 0;
$doi = '';

if (isset($_GET['doi']))
{
	$doi = $_GET['doi'];
}

if ($doi != '')
{
	$id = db_retrieve_reference_from_doi($doi);
}

if ($id == 0)
{
	header('HTTP/1.1 404 Not Found');
	header('Status: 404 Not Found');
	$_SERVER['REDIRECT_STATUS'] = 404;
	
	echo 'DOI "' . $doi . '" not found';
}
else
{
	header('Location: ' . $config['web_root'] . 'reference/' . $id . "\n\n");
	$_SERVER['REDIRECT_STATUS'] = 200;
}
?>