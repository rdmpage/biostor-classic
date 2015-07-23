<?php

// DjVu XML for a BHL page

require_once ('../db.php');
require_once ('../lib.php');
require_once ('../bhl_utilities.php');


$PageID = $_GET['PageID']; 

$xml = '<?xml version="1.0" ?>';

$sql = 'SELECT * FROM bhl_page 
INNER JOIN page USING(PageID)
WHERE (PageID=' . $PageID . ') 
LIMIT 1';

//echo $sql;
//exit();

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

if ($result->NumRows() == 1)
{
	$ItemID = $result->fields['ItemID'];
	$FileNamePrefix = $result->fields['FileNamePrefix'];
	
	//echo $FileNamePrefix;
	
	if (preg_match('/_(?<page_num>\d+)$/', $FileNamePrefix, $m))
	{
		$page_num = $m['page_num'];
	
		$xml_filename = $config['cache_dir']. "/" . $ItemID . "/xml/" . $page_num . ".xml";
	
		if (file_exists($xml_filename))
		{
			$xml = file_get_contents($xml_filename);
			
			// fix
			$xml = str_replace("&#31;", "", $xml);
			$xml = str_replace("&#11;", "", $xml);
		}
		else
		{
			// try long form
			$xml_filename = $config['cache_dir']. "/" . $ItemID . "/xml/" . $FileNamePrefix . ".xml";
			if (file_exists($xml_filename))
			{
				$xml = file_get_contents($xml_filename);
			
				// fix
				$xml = str_replace("&#31;", "", $xml);
				$xml = str_replace("&#11;", "", $xml);
			}
			
		}
	}
}

header('Content-type: application/xml');
echo $xml;		

?>