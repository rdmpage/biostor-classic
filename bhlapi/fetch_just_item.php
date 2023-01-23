<?php

// fecth items independently of title

require_once('../config.inc.php');
require_once('../lib.php');

$items=array(
//114020,
//282441,
//310731,
//310791,

// Botanische Jahrbücher fur Systematik...
/*310913,
310808,
310935,

310912,
310911,*/

/*
311777,
311821,
312397,
312398,
*/

//312655

/*
312796,
312799,
312807,
312842,

312787,
312798,
312813,
312823,
312828,
312829,
312839,
312840,
312841,
*/

208047,
210297,
210310,
219508,
222623,
222679,
222763,
229889,
230750,
255534,
255568,
255597,
255616,
255617,
255618,
255641,
255642,
255643,
255679,
255680,
255692,
255696,
255699,
255813,
255814,
256983,
270769,
270770,
270779,
270783,
270997,
281103,
281107,
281142,




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
		
			//print_r($v);
		
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