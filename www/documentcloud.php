<?php

require_once ('../db.php');
require_once ('../bhl_text.php');

require_once ('../reference.php');

$id = $_GET['id'];

$callback = '';

if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}

$reference = db_retrieve_reference($id);

$obj = new stdclass;

$obj->title = $reference->title;
$obj->description = '<b>' . $reference->title . '</b>' 
	. "<br/>" . 'by ' . reference_authors_to_text_string($reference)
	. "<br/>" . reference_to_citation_text_string($reference);
	
$obj->id = $id;

$obj->canonical_url = $config['web_root'] . 'reference/' . $id;

$bhl_pages = bhl_retrieve_reference_pages($id);

$obj->pages = count($bhl_pages);

$obj->resources = new stdclass;

$obj->resources->page = new stdclass;
$obj->resources->page->text = 'http://biostor.org/reference/' . $id . '/pages/{page}';
$obj->resources->page->image = 'http://biostor.org/reference/' . $id . '/pages/{page}-{size}';

$obj->resources->search = 'http://biostor.org/dvs/' . $id . '/json?q={query}';

$obj->sections = array();

$obj->annotations = array();

// support text indexing

$sql = 'SELECT * FROM rdmp_documentcloud WHERE reference_id=' . $id . ' LIMIT 1';

//echo $sql;
$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

if ($result->NumRows() == 1)
{
}
else
{
	foreach ($bhl_pages as $p)
	{
		//print_r($p);
		
		$text = bhl_fetch_ocr_text($p->PageID);
		$text = str_replace("\\n", " ", $text);
		
		$sql = 'INSERT INTO rdmp_documentcloud(reference_id,page,ocr_text) VALUES('
		. $id
		. ',' . ($p->page_order + 1)
		. ',' . $db->qstr($text)
		. ')';
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
		
	}
}



header('Content-type: text/plain');
if ($callback != '')
{
	echo $callback .'(';
}

if (0)
{

$obj->title="a";
$obj->description="b";
$obj->canonical_url = "http://gallica.bnf.fr/ark:/12148/bpt6k31239/f1614";
$obj->pages = 3;
$obj->resources->page = new stdclass;
$obj->resources->page->text = 'http://iphylo.org/~rpage/itaxon/gallica/bpt6k31239/start/1614/pages/{page}';
$obj->resources->page->image = 'http://iphylo.org/~rpage/itaxon/gallica/bpt6k31239/start/1614/pages/{page}-{size}';

//$obj->resources->search = 'http://biostor.org/dvs/' . $id . '/json?q={query}';

$obj->sections = array();

$obj->annotations = array();

}

echo json_encode($obj);

if ($callback != '')
{
	echo ')';
}


?>