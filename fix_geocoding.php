<?php

// geocode a reference
require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/geocoding.php');
require_once (dirname(__FILE__) . '/bhl_text.php');

$ids = array(133524);
$ids = array(63082);
$ids=array(115477);
$ids=array(134968);
$ids=array(81355);

$ids=array(136996);

$ids=array(137078);

$ids=array(378);

$ids=array(102755);
$ids=array(145796);

$ids=array(115586);
$ids=array(115671);
$ids=array(115653);
$ids=array(115485);
$ids=array(115346);
$ids=array(146611);

$ids=array(150786,150785);

$ids=array(4298);

$ids=array(164556);

$start 	= 166655;
$end 	= 166655;

for ($reference_id = $start; $reference_id <= $end; $reference_id++)
//foreach ($ids as $reference_id)
{
	global $db;
	
	echo "Reference = $reference_id\n";
	// remove any existing geocoding...
	
	$pages = bhl_retrieve_reference_pages($reference_id);
	
	foreach ($pages as $page)
	{
		$sql = 'DELETE FROM rdmp_locality_page_joiner WHERE PageID=' . $page->PageID;
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	}	
	
	// redo
	bhl_geocode_reference($reference_id);
	
}


?>