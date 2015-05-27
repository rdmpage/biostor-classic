<?php

require_once ('../bhl_utilities.php');


$ItemID = $_GET['item'];


$callback = '';

if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}


$obj = new stdclass;
$obj->id = $ItemID;
$obj->title = '';
$obj->description = '';

$bhl_pages = bhl_retrieve_item_pages($ItemID);

$sql = 'SELECT * FROM bhl_title INNER JOIN bhl_item USING(TitleID) WHERE ItemID=' . $ItemID . ' LIMIT 1';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

if ($result->NumRows() == 1)
{
	$obj->title	= $result->fields['FullTitle'];
	$obj->description	= $result->fields['VolumeInfo'];
}

$obj->sections = array();

$sql = 'SELECT rdmp_reference.title, page.SequenceOrder 
FROM rdmp_reference_page_joiner
INNER JOIN page USING(PageID) 
INNER JOIN rdmp_reference USING(PageID)
WHERE ItemID=' . $ItemID . '
ORDER BY page.SequenceOrder';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{	
	$section = new stdclass;
	$section->title =  $result->fields['title'];
	$section->page = (Integer)$result->fields['SequenceOrder'];
	
	$obj->sections[] = $section;

	$result->MoveNext();		
}



$obj->pages = count($bhl_pages);
$obj->resources->page = new stdclass;
$obj->resources->page->text = 'http://biostor.org/bhldc/' . $ItemID . '/pages/{page}';
$obj->resources->page->image = 'http://biostor.org/bhldc/' . $ItemID . '/pages/{page}-{size}';



$obj->annotations = array();


header('Content-type: text/plain');
if ($callback != '')
{
	echo $callback .'(';
}
echo json_encode($obj);
if ($callback != '')
{
	echo ')';
}

?>