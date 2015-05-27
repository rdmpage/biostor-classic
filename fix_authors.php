<?php

// Fix authors buggered by BMCZ RIS import, and other issues

// SELECT * FROM rdmp_author WHERE forename REGEXP '^[A-Z][a-z]$'

require_once (dirname(__FILE__) . '/db.php');

global $config;
global $db;

//--------------------------------------------------------------------------------------------------
if (0)
{
	// fix trailing space in first author name
	
	$authors = array();
	
	$sql = 'SELECT DISTINCT(author_id), forename, lastname  
	FROM rdmp_author 
	INNER JOIN rdmp_author_reference_joiner 
	USING(author_id)'; // WHERE reference_id > 500';
	
	
	echo $sql . "\n";
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		if (preg_match('/\s+$/', $result->fields['forename']))
		{
			echo $result->fields['forename'] . "|\n";
		
			$author = new stdclass;
			$author->id = $result->fields['author_id'];
			$author->forename = trim($result->fields['forename']);
			$author->lastname = trim($result->fields['lastname']);
			
			$authors[] = $author;
		}
		
		$result->MoveNext();				
	}
	
	print_r($authors);
	
	foreach ($authors as $author)
	{
		$sql = 'UPDATE rdmp_author SET forename=' . $db->qstr($author->forename) . ' WHERE author_id=' . $author->id;
		echo $sql . "\n";
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	}
}

// 

if (1)
{
	// fix mangled forename 
	
	$authors = array();
	
	$sql = "SELECT * FROM rdmp_author WHERE forename REGEXP '^[A-Z][a-z]$' AND author_id > 47408";
	
	echo $sql . "\n";
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		$author = new stdclass;
		$author->id = $result->fields['author_id'];
		$author->forename = trim($result->fields['forename']);
		$author->lastname = trim($result->fields['lastname']);
	
		$authors[] = $author;
		$result->MoveNext();				
	}
	
	print_r($authors);	
	
	
	foreach ($authors as $author)
	{
		if (preg_match('/^[A-Z][a-z]$/', $author->forename))
		{
			$sql = '';
			
			$parts = array();
			$str = mb_substr($author->forename, 0, 1) . ' ' . mb_convert_case(mb_substr($author->forename, 1, 1), MB_CASE_UPPER);
			//echo $author->forename . '=>' . $str . "\n";
			
			$sql = 'UPDATE rdmp_author SET forename=' . $db->qstr($str) . ' WHERE author_id=' . $author->id;
			echo $sql . "\n";
			
			
			if ($sql != '')
			{
				$result = $db->Execute($sql);
				if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
			}
			
		}
			
	}	
	
}

//--------------------------------------------------------------------------------------------------
/*
foreach ($authors as $author)
{
	if (preg_match('/^[A-Z]$/', $author->lastname))
	{
		$sql = '';
		
		if (preg_match('/^(?<lastname>\w+)$/', $author->forename))
		{
			$sql = 'UPDATE rdmp_author SET forename=' . $db->qstr($author->lastname) . ', lastname=' . $db->qstr($author->forename) . ' WHERE author_id=' . $author->id;
			
			echo $sql . "\n";
		}
		
		if (preg_match('/^(?<lastname>\w+) (?<initial>[A-Z])$/', $author->forename, $matches))
		{
			$sql = 'UPDATE rdmp_author SET forename=' . $db->qstr($matches['initial'] . ' ' . $author->lastname) . ', lastname=' . $db->qstr($matches['lastname']) . ' WHERE author_id=' . $author->id;
			
			echo $sql . "\n";
		}
		
		if ($sql != '')
		{
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
		}
	}
		
}
*/



?>