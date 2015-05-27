<?php

require_once(dirname(dirname(__FILE__)) . '/db.php');

// Fetch DjVu XML for a page in BHL

function fetch_djvu_xml_page($PageID)
{
	global $config;
	global $db;
	
	$xml = '';
	
	$sql = "SELECT ItemID, FileNamePrefix, SequenceOrder FROM page WHERE PageID=" . $PageID . " LIMIT 1";
	
	//echo $sql;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
		
	if ($result->NumRows() == 1)
	{
		$ItemID 		= $result->fields['ItemID'];
		$FileNamePrefix = $result->fields['FileNamePrefix'];
		$FileNamePrefix = preg_replace('/_\d+$/', '', $FileNamePrefix);
		$SequenceOrder  = $result->fields['SequenceOrder'];
		
		$cache_namespace = $config['cache_dir']. "/" . $ItemID;
		$pagenum = sprintf("%04d", $SequenceOrder);
		
		$xml_page_filename = $cache_namespace . '/' . $FileNamePrefix . '_' . $pagenum . '.xml';
		$djvu_page_filename = $cache_namespace . '/' . $FileNamePrefix . '_' . $pagenum . '.djvu';
		
		if (!file_exists($xml_page_filename))
		{	
			if (file_exists($cache_namespace))
			{
				// Does DjVu file exist?
				$djvu_filename = $cache_namespace . '/' . $FileNamePrefix . ".djvu";
				
				//echo $djvu_filename . "\n";
	
				if (file_exists($djvu_filename))
				{					
					// extract one DjVu page
					$command = 'djvused ' . $djvu_filename . ' -e "select ' . $SequenceOrder . ';save-page ' . $djvu_page_filename . '"';
					//echo $command . "\n";
					system($command, $return_var);
					//echo $return_var . "\n";
				
					$command = 'djvutoxml ' .  $djvu_page_filename . ' ' . $xml_page_filename;
					//echo $command . "\n";
					system($command, $return_var);
					//echo $return_var . "\n";
				}
			}
		}
		if (file_exists($xml_page_filename))
		{	
			$xml = file_get_contents($xml_page_filename);
			
			// clean it up			
			$xml = str_replace("&#31;", "", $xml);
			$xml = str_replace("&#11;", "", $xml);
		}
	}
	
	return $xml;
}
		
$PageID = 0;
$xml = '';

if (isset($_GET['PageID']))
{
	$PageID = $_GET['PageID'];
}
if ($PageID != 0)
{
	$xml = fetch_djvu_xml_page($PageID);
}

if ($xml == '')
{
	$xml = '<?xml version="1.0" ?><DjVuXML/>';
}
header('Content-type: application/xml; charset=UTF-8');
echo $xml;


?>