<?php

// If item has been replaced, and BHL has moved the articles,
// then get new PageIDs and update BioStor

require_once(dirname(__FILE__) . '/lib.php');

$config['api_key'] = '0d4f0303-712e-49e0-92c5-2113a5959159';


$ItemID = 245864;
$ItemID = 259150;


// get item and its parts
$url = 'https://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetItemMetadata&itemid=' 
	. $ItemID . '&parts=t&apikey=' . $config['api_key'] . '&format=json';

$json = get($url);

$obj = json_decode($json);

//print_r($obj);

$parts = array();

foreach ($obj->Result->Parts as $part)
{
	if ($part->Contributors[0]->ContributorName == "BioStor")
	{
		$parts[] = $part->PartID;
	}

}

//print_r($parts);

$biostor = array();

// for each part get pages
foreach ($parts as $PartID)
{
	$url = 'https://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetPartMetadata&partid=' 
		. $PartID . '&apikey=' . $config['api_key'] . '&format=json';

	$json = get($url);

	$obj = json_decode($json);

	// print_r($obj);
	
	$reference_id = 0;
	
	foreach ($obj->Result->Identifiers as $identifier)
	{
		if ($identifier->IdentifierName == 'BioStor')
		{
			$reference_id = $identifier->IdentifierValue;
		}	
	}
	
	$biostor[] = $reference_id;
	
	$pages = array();
	
	foreach ($obj->Result->Pages as $Page)
	{
		$pages[] = $Page->PageID;
	}
	
	// echo $reference_id . "\n";
	// print_r($pages);
	
	// output as SQL
	
	echo 'UPDATE rdmp_reference SET PageID=' . $pages[0] . ' WHERE reference_id=' . $reference_id . ';' . "\n";
	echo 'DELETE FROM rdmp_reference_page_joiner WHERE reference_id=' . $reference_id . ';' . "\n";
	
	$count = 0;
	foreach ($pages as $PageID)
	{
		echo  'INSERT INTO rdmp_reference_page_joiner (reference_id, PageID, page_order) VALUES (' . $reference_id . ',' . $PageID . ',' . $count++ . ');' . "\n";
	}
	echo "\n";



}

// print_r($biostor);

echo '$ids=array(' . "\n";
echo join(",\n", $biostor);
echo ');' . "\n";

?>

