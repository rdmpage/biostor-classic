<?php

require_once('../config.inc.php');
require_once('../db.php');


$items=array(
'123998' => 'MemoirsQueensla37QueeA',
'123990' => 'MemoirsQueensla43Quee'
);

foreach ($items as $ItemID => $SourceIdentifier)
{
	// Files are cached in folders with the ItemID as the name
	$cache_namespace = $config['cache_dir']. "/" . $ItemID;
	
	// Ensure cache subfolder exists for this item
	if (!file_exists($cache_namespace))
	{
		$oldumask = umask(0); 
		mkdir($cache_namespace, 0777);
		umask($oldumask);
		
		// Thumbnails are in a subdirectory
		$oldumask = umask(0); 
		mkdir($cache_namespace . '/thumbnails', 0777);
		umask($oldumask);
	}


	// fetch names
	$name_filename = $cache_namespace . '/' . $SourceIdentifier . "_names.xml";
	
	if (!file_exists($name_filename)) // don't fetch again if we don't need to
	{
		$url = 'http://www.archive.org/download/' . $SourceIdentifier . '/' . $SourceIdentifier . "_names.xml";

		$command = "curl";
		
		if ($config['proxy_name'] != '')
		{
			$command .= " --proxy " . $config['proxy_name'] . ":" . $config['proxy_port'];
		}
		$command .= " --location " . $url . " > " . $name_filename;
		echo $command . "\n";
		system ($command);
	}
	
	// Get names from this file
	$xml = file_get_contents($name_filename);
	
	$dom= new DOMDocument;
	$dom->loadXML($xml);
	$xpath = new DOMXPath($dom);
	

	// Pages
	$nodeCollection = $xpath->query ('//name');
	foreach ($nodeCollection as $node)
	{
		$name = new stdclass;
	
		if ($node->hasAttributes()) 
		{ 
			$attributes = array();
			$attrs = $node->attributes; 
			
			foreach ($attrs as $i => $attr)
			{
				$attributes[$attr->name] = $attr->value; 
			}
			
			//print_r($attributes);
			
			if (isset($attributes['map']))
			{
				$name->map = $attributes['map'];
			}
			
			if (isset($attributes['bhlurl']))
			{
				$name->PageID = (Integer)str_replace('http://www.biodiversitylibrary.org/page/', '', $attributes['bhlurl']);
			}
			if (isset($attributes['imageurl']))
			{
				$name->imageurl = $attributes['imageurl'];
			}
				
			if (isset($attributes['found']))
			{
				$name->found = $attributes['found'];
			}
			
			if (isset($attributes['confirmed']))
			{
				$name->confirmed = $attributes['confirmed'];
			}
	
			if (isset($attributes['dateadded']))
			{
				$name->dateAdded = $attributes['dateadded'];
			}			
			
			if (isset($attributes['ubiourl']))
			{
				if (preg_match('/^http:\/\/www.ubio.org\/browser\/details.php\?namebankID=(?<id>\d+)$/', $attributes['ubiourl'], $m))
				{
					$name->ubio = (Integer)$m['id'];
				}
			}
			if (isset($attributes['eolurl']))
			{
				if (preg_match('/^http:\/\/www.eol.org\/pages\/(?<id>\d+)$/', $attributes['eolurl'], $m))
				{
					$name->eol = (Integer)$m['id'];
				}
			}
			
			
			
		}
		//print_r($name);
		
		if (isset($name->found))
		{
			$sql = "DELETE FROM bhl_page_name WHERE NameConfirmed='" . addslashes($name->found) . "' AND PageID=" . $name->PageID . ";";
			
			echo $sql . "\n";
			
			$sql = "INSERT INTO bhl_page_name(NameBankID,PageID,NameConfirmed,CreationDate) VALUES (";
			
			if (isset($name->ubio))
			{
				$sql .= $name->ubio;
			}
			else
			{
				$sql .= '0';
			}
			$sql .= ',' . $name->PageID;
			$sql .= ",'" . addslashes($name->found) . "'";
			$sql .= ",'" . $name->dateAdded . "'";
			$sql .= ');';
			
			echo $sql . "\n";
			
			// Store in database
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
			
		}
	}


}

?>