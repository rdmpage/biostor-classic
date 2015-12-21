<?php

require_once('../config.inc.php');
require_once('../lib.php');

$djvu_string = '';

$Titles=array(109678,
109775,
109289,
107602,
106809
);
$want=array();

$Titles=array(
8809
);
$want=array(
);

foreach ($Titles as $TitleID)
{
 //$want=array();

$use_bhl_au = false;
//$use_bhl_au = true;

if ($use_bhl_au)
{
	// BHL AU
	$url = 'http://bhl.ala.org.au/api/rest?op=GetTitleMetadata&titleid=' . $TitleID . '&items=true&apikey=' . $config['bhl_api_key'] . '&format=json';
}
else
{
	// BHL
	$url = 'http://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetTitleMetadata&titleid=' . $TitleID . '&items=true&apikey=' . '0d4f0303-712e-49e0-92c5-2113a5959159' . '&format=json';
}

//echo $url . "\n";

$json = get($url);
$title_obj = json_decode($json);

//print_r($title_obj);
//exit();



if ($title_obj->Status == 'ok')
{

	$sql = "REPLACE INTO bhl_title(TitleID, FullTitle, ShortTitle) VALUES ("
		. $title_obj->Result->TitleID . ",'" . addslashes($title_obj->Result->FullTitle) . "','" . addslashes($title_obj->Result->ShortTitle) . "');";
	echo $sql . "\n";

	foreach ($title_obj->Result->Items as $item_obj)
	{
		$go = false;
		if (count($want) == 0)
		{
			$go = true;
		}
		else
		{
			if (in_array($item_obj->ItemID, $want))
			{
				$go = true;
			}
		}
		if ($go)
		{
		
			if ($use_bhl_au)
			{
				$url = 'http://bhl.ala.org.au/api/rest?op=GetItemMetadata&itemid=' . $item_obj->ItemID . '&pages=t&apikey=' . $config['bhl_api_key'] . '&format=json';
			}
			else
			{
				$url = 'http://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetItemMetadata&itemid=' . $item_obj->ItemID . '&pages=t&apikey=' . '0d4f0303-712e-49e0-92c5-2113a5959159' . '&format=json';
			}
			
			$json = get($url);
			
			$obj = json_decode($json);
			
			//print_r($obj);
			//exit();
			
			if (($obj->Status == 'ok') && ($obj->Result != '')) // ok doesn't mean we have anything :(
			{
				
				$djvu_string .= "'" . $obj->Result->ItemID . "' => '" .  $obj->Result->SourceIdentifier . "',\n";
			
				
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
						$values[] = '"' . $v->PageNumbers[0]->Number . '"';
					}
			
					if (count($v->PageTypes) > 0)
					{
						$keys[] = 'PageTypeName';
						$values[] = '"' . $v->PageTypes[0]->PageTypeName . '"';
					}
					//$sql = 'DELETE FROM bhl_page WHERE PageID=' . $v->PageID . ';';
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
	}	
}

}

file_put_contents($TitleID . 'djvu.txt', $djvu_string);

?>