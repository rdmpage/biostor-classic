<?php

/**
 * @file display_oclc.php
 *
 * Handle OCLC numbers. If reference with OCLC exists in database, redirect to web page displaying
 * reference, otherwise 404.
 *
 */
 
require_once('../db.php');
 
$id = 0;
$oclc = 0;
$genre = 'book';

if (isset($_GET['oclc']))
{
	$oclc = $_GET['oclc'];
}

if (($oclc != '') && ($oclc != 0))
{
	$id = db_retrieve_reference_from_oclc($oclc);
	
	// if reference is an article, assume OCLC identifies journal
	if ($id != 0)
	{
		$reference = db_retrieve_reference($id);
		$genre = $reference->genre;
	}
}

if ($id == 0)
{
	header('HTTP/1.1 404 Not Found');
	header('Status: 404 Not Found');
	$_SERVER['REDIRECT_STATUS'] = 404;
	
	echo 'OCLC "' . $oclc . '" not found';
}
else
{
	switch ($genre)
	{
			
		case 'article':
			header('Location: ' . $config['web_root'] . 'display_journal.php?oclc=' . $oclc . "\n\n");
			$_SERVER['REDIRECT_STATUS'] = 200;
			break;
			
		case 'book':
		default:
			header('Location: ' . $config['web_root'] . 'reference/' . $id . "\n\n");
			$_SERVER['REDIRECT_STATUS'] = 200;
			break;
	}
}
?>