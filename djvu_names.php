<?php

// Extract names from IA XML file and create SQL dump to add to database
require_once(dirname(__FILE__) . '/config.inc.php');

$ItemID = 108199;
$names_filename = 'proceedingsofb1041991biol_names.xml';

$ItemID=110199;
$names_filename = 'proceedingsofb1051992biol_names.xml';



//--------------------------------------------------------------------------------------------------
function name_to_sql($name, $PageID, &$sql)
{
	if (isset($name->confirmed))
	{
		// Add names
		$sql .= "INSERT INTO bhl_page_name(NameBankID, NameConfirmed, PageID) VALUES (";
		
		if (isset($name->ubio))
		{
			$sql .= $name->ubio;
		}
		else
		{
			$sql .= "0";
		}
		$sql .= ", '" . $name->confirmed . "'";
		$sql .= ", " . $PageID . ");";
		$sql .= "\n";
	}
}

//--------------------------------------------------------------------------------------------------
function extract_name($n)
{
	$name = new stdclass;
	if ($n->hasAttributes())
	{
		$attributes = array();
		$attrs = $n->attributes;
		
		foreach ($attrs as $i => $attr)
		{
			$attributes[$attr->name] = $attr->value;
		}
		
		//print_r($attributes);
		
		if (isset($attributes['bhlurl']))
		{
			$name->bhl = str_replace('http://www.biodiversitylibrary.org/page/', '', $attributes['bhlurl']);
		}

		if (isset($attributes['dateadded']))
		{
			$name->dateAdded = $attributes['dateadded'];
		}		
		
		if (isset($attributes['found']))
		{
			$name->confirmed = $attributes['found'];
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
			if (preg_match('/^http:\/\/www.ubio.org\/browser\/details.php\?namebankID=(?<id>\d+)$/',$attributes['ubiourl'], $m))
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
	
	return $name;
}




// Images are cached in folders with the ItemID as the name
$cache_namespace = $config['cache_dir']. "/" . $ItemID;

// Ensure cache subfolder exists for this item
if (!file_exists($cache_namespace))
{
	$oldumask = umask(0); 
	mkdir($cache_namespace, 0777);
	umask($oldumask);
}


$xml = file_get_contents($cache_namespace . '/' . $names_filename);

$sql = '';

$dom= new DOMDocument;
$dom->loadXML($xml);
$xpath = new DOMXPath($dom);

$has_page = false;

// Pages
$nodeCollection = $xpath->query ('//page');
foreach ($nodeCollection as $node)
{
	$has_page = true;
	
       $page = new stdclass;
       $page->names = array();

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
                       if (preg_match('/^(.*)_(?<leaf>\d+)$/', $attributes['map'], $m))
                       {
                               $page->leafNum = (Integer)$m['leaf'];
                       }
               }

               if (isset($attributes['bhlurl']))
               {
                       $page->bhl =
str_replace('http://www.biodiversitylibrary.org/page/', '',
$attributes['bhlurl']);
               }
               if (isset($attributes['imageurl']))
               {
                       $page->imageurl = $attributes['imageurl'];
               }

       }

		// names 
       $nc = $xpath->query ('name', $node);
       foreach ($nc as $n)
       {
 			$name = extract_name($n);
            $page->names[] = $name;
        }

       if (count($page->names) == 0)
       {
               unset($page->names);
       }

       print_r($page);	
       
       if (isset($page->names))
       {
       	// Delete any existing names
       	$sql .= "DELETE FROM bhl_page_name WHERE PageID=" . $page->bhl . ";\n";
       
       	foreach ($page->names as $name)
       	{
       		name_to_sql($name, $page->bhl, $sql);
       	}
       }

}

if (!$has_page)
{
	$names = array();
	$nc = $xpath->query ('//name');
	foreach ($nc as $n)
	{
		$name = extract_name($n);
		
		$names[] = $name;
	}
	
	$pages = array();
	foreach ($names as $name)
	{
		$pages[] = $name->bhl;
	}
	$pages = array_unique($pages);
	foreach ($pages as $PageID)
	{
		$sql .= "DELETE FROM bhl_page_name WHERE PageID=" . $PageID . ";\n";
	}
	foreach ($names as $name)
	{
		name_to_sql($name, $name->bhl, $sql);
	}
	
}

file_put_contents($cache_namespace . '/' . $names_filename . '.sql', $sql);

?>