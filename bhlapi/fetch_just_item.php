<?php

// fecth items independently of title

require_once('../config.inc.php');
require_once('../lib.php');

$items=array(
306660,
306659,
);



foreach ($items as $item)
{
	$url = 'https://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetItemMetadata&itemid=' . $item . '&pages=t&apikey=' . '0d4f0303-712e-49e0-92c5-2113a5959159' . '&format=json';

	$json = get($url);
	
	//echo $url;

	$obj = json_decode($json);

	//print_r($obj);
	//exit();

	if (($obj->Status == 'ok') && ($obj->Result != '')) // ok doesn't mean we have anything :(
	{
	
		// Item
		$sql = "DELETE FROM bhl_item WHERE ItemID=" . $obj->Result->ItemID . ";";
		echo $sql . "\n";
	
		$sql = "INSERT INTO bhl_item(ItemID,TitleID,VolumeInfo) VALUES("
		. $obj->Result->ItemID . ',' . $obj->Result->PrimaryTitleID . ",'" . addslashes($obj->Result->Volume) . "');";
	
		echo $sql . "\n";
	
		$sql = 'DELETE FROM bhl_page WHERE ItemID=' . $obj->Result->ItemID . ";";
		echo $sql;
	
		// Pages
		foreach ($obj->Result->Pages as $k => $v)
		{
		
			// print_r($v);
		
			// Metadata about pages
			$keys = array();
			$values = array();
		
			// PageID
			$keys[] = 'PageID';
			$values[] = $v->PageID;
		
			// ItemID
			$keys[] = 'ItemID';
			$values[] = $v->ItemID;

			// Is page numbered?
			if (count($v->PageNumbers) > 0)
			{
				$keys[] = 'PagePrefix';
				$values[] = '"' . $v->PageNumbers[0]->Prefix . '"';

				$keys[] = 'PageNumber';
				
				$value = $v->PageNumbers[0]->Number;
				
				$value = preg_replace('/^Page%/', '', $value); // Iberus ItemID 221112
				$value = preg_replace('/^p\.%/', '', $value); // Iberus ItemID 221112
				
				$values[] = '"' . $value . '"';
			}

			if (count($v->PageTypes) > 0)
			{
				$keys[] = 'PageTypeName';
				$values[] = '"' . $v->PageTypes[0]->PageTypeName . '"';
			}
			$sql = 'DELETE FROM bhl_page WHERE PageID=' . $v->PageID . ';';
			//echo $sql . "\n";
			$sql = 'INSERT INTO bhl_page (' . implode (",", $keys) . ') VALUES (' . implode (",", $values) . ');';
			echo $sql . "\n";
	
	
			// Order of pages
			// pages has PageID as primary key
			$sql = 'REPLACE INTO page (PageID,ItemID,FileNamePrefix,SequenceOrder) VALUES ('
				.        $v->PageID
				. ','  . $v->ItemID
				. ',"' . $obj->Result->SourceIdentifier . sprintf("_%04d",  ($k+1)) . '"'
				. ','  . ($k+1)
				. ');';
			
			echo $sql . "\n";
			
		} 
	}
}

?>