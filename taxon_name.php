<?php

/**
 * @file taxon_name.php
 *
 * Encapsulate a taxon name
 *
 */
 
/*
public $namestring_id		= '';
public $local_id			= '';
public $global_id			= '';
public $versionInfo			= '';
public $nameComplete		= '';
public $uninomial			= '';
public $genusPart			= '';
public $specificEpithet		= '';
public $nomenclaturalCode	= '';
public $rank				= '';
public $rankString			= '';
public $authorship			= '';
public $year				= '';
public $publishedInCitation	= '';
public $publishedIn			= '';
public $microreference		= '';
public $publication			= '';
*/

//--------------------------------------------------------------------------------------------------
class TaxonName
{
	public $nameComplete		= '';
	
	//----------------------------------------------------------------------------------------------
	function __construct()
	{
	}
	
	//----------------------------------------------------------------------------------------------
	function ToHTML()
	{
		$html = $this->nameComplete;
		return $html;
	}

}

?>