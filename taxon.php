<?php

// Handle taxon name lookup and harvesting


require_once(dirname(__FILE__) . '/bhl_names.php');
require_once(dirname(__FILE__) . '/bioguid.php');
require_once(dirname(__FILE__) . '/namestring.php');
require_once(dirname(__FILE__) . '/db.php');

//--------------------------------------------------------------------------------------------------
// Find namestring (if it exists) for a uBio NameBankID
// As we add missing names there may be names in uBio that aren't in the BHL database
function db_get_namestring_from_namebankid($NameBankID, &$namestring_info = NULL)
{
	global $db;
	
	$name = NULL;
	
	$sql = 'SELECT * FROM rdmp_namestring WHERE (NameBankID = ' . $NameBankID . ') LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$name = new NameString;
		$name->NameBankID = $result->fields['NameBankID'];
		$name->namestring = $result->fields['namestring'];
		$name->namestring_id = $result->fields['namestring_id'];
	}
	
	return $namestring_id;
}

//--------------------------------------------------------------------------------------------------
// Find namestring (if it exists) for a name
// NULL if not found
function db_get_namestring($namestring)
{
	global $db;
	
	$name = NULL;
	
	$sql = 'SELECT * FROM rdmp_namestring WHERE (namestring = ' . $db->qstr($namestring) . ') LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$name = new NameString;
		$name->NameBankID = $result->fields['NameBankID'];
		$name->namestring = $result->fields['namestring'];
		$name->namestring_id = $result->fields['namestring_id'];
	}
	
	return $name;
}

//--------------------------------------------------------------------------------------------------
// Store name and NameBankID (which is 0 if not in uBio)
function db_store_namestring($namestring, $NameBankID = 0)
{
	global $db;
	
	$namestring_id = 0;
	
	$sql = 'SELECT * FROM rdmp_namestring WHERE (namestring = ' . $db->qstr($namestring) . ') LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$namestring_id = $result->fields['namestring_id'];
	}
	else
	{
		$sql = 'INSERT INTO rdmp_namestring(namestring, NameBankID) VALUES(' .  $db->qstr($namestring) . ', ' . $NameBankID . ')';
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
		$namestring_id = $db->Insert_ID();
	}
	
	return $namestring_id;
}

//--------------------------------------------------------------------------------------------------
function ubio_lookup($namestring)
{
	$name = NULL;
	
	$NameBankID = bioguid_ubio_search($namestring);
	
	if ($NameBankID != 0)
	{
		$namestring_id = db_store_namestring($namestring, $NameBankID);

		$name = new NameString;
		$name->NameBankID 		= $NameBankID;
		$name->namestring 		= $namestring;
		$name->namestring_id 	= $namestring_id;
	}
	return $name;
}

//--------------------------------------------------------------------------------------------------
// find name string, adding the query string if needed, and return namestring_id
function find_namestring($str)
{
	$namestring_id = 0;
	
	$name = db_get_namestring($str);
	if ($name == NULL)
	{
		$name = ubio_lookup($str);
	}
	if ($name == NULL)
	{
		// not in local BHL copy, nor uBio webservice, so store this ourselves
		$namestring_id = db_store_namestring($str);
	}
	else
	{
		$namestring_id = $name->namestring_id;
	}
	return $namestring_id;
}
		

?>