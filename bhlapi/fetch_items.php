<?php

require_once('../config.inc.php');
require_once('../lib.php');

$djvu_string = '';


$Titles=array(
116226,116503
);
$want=array(

);

$Titles=array(
112965,65344,7414,8981
);
$want=array(
202229,202386,202512,202571
);

// http://biodiversitylibrary.org/bibliography/116503
// http://biodiversitylibrary.org/bibliography/112965
$Titles=array(
8981,
79076,
6170,
62643
);
$want=array(
// Rev Sui
203304,
203184,
// Naut
203169,
203168,
203167,
// Nota Lep
203185,
203048,
// J Lep
203166,
202862,
202847,
202846


);



$Titles=array(
118453,118615,8089,117069
);
$want=array(

);

$Titles=array(

6525
);
$want=array(
207082
);


$Titles=array(
11199
);
$want=array(
207100,
207114,
207146,
207147,
207171,
207186,
207190,
207193,
207234,
207235,
207423,
207447

);

$Titles=array(
119064
);
$want=array(
);


$Titles=array(
110135
);
$want=array(
207230,
207219,
207178,
207163,
207136
);

$Titles=array(
112965
);
$want=array(
200713,
202229,
203206,
204735,
207236,
207394,
207395,
207429

);

// Acta Botánica Mexicana
$Titles=array(
119685,
119760,
119766,
119767,
119773
);
$want=array(

);
$Titles=array(
114584
);
$want=array(
208273
);

$Titles=array(
5943,
61449,
8985
);
$want=array(

217367,

217476,
217512,

217331,
217332,
217364,
217391,
217504,
217561


);

$Titles=array(
115964
);
$want=array(

);

// 7536, 123721



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