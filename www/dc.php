<?php

if (isset($_GET['id']))
{
	$id = $_GET['id'];
}

preg_match('/^(?<id>.*)f(?<start>\d+)$/', $id, $m);

$id = $m['id'];
$start = $m['start'];

//echo $url;

$obj = new stdclass;
$callback = '';

if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}

$url = 'http://gallica.bnf.fr/ark:/12148/' . $id . '/f' . $start;

/*

	$obj->title 		= "a"; //$result->fields['title'];
	$obj->description 	= "b"; // $result->fields['publication'];
	$obj->canonical_url = $url;
	
	$obj->pages = 3;
		
	$obj->resources = new stdclass;
	
	$obj->resources->page = new stdclass;
	$obj->resources->page->text 	= 'http://iphylo.org/~rpage/itaxon/gallica/' . $id . '/start/' . $start . '/pages/{page}';
	$obj->resources->page->image 	= 'http://iphylo.org/~rpage/itaxon/gallica/' . $id . '/start/' . $start . '/pages/{page}-{size}';
	
	//$obj->resources->search = $config['web_root'] . 'gallica/';

	
	$obj->sections = array();
	
	$obj->annotations = array();
*/

$obj->title="a test string";
$obj->description="description";
$obj->canonical_url = "http://gallica.bnf.fr/";
$obj->pages = 3;
$obj->id = 4;
$obj->resources->page = new stdclass;
$obj->resources->page->text = 'http://iphylo.org/~rpage/itaxon/gallica/bpt6k31239/start/1614/pages/{page}';
$obj->resources->page->image = 'http://iphylo.org/~rpage/itaxon/gallica/bpt6k31239/start/1614/pages/{page}-{size}';

$obj->resources->search = 'http://biostor.org/dvs/' . $id . '/json?q={query}';

$obj->sections = array();

$obj->annotations = array();


header("Content-type: text/plain");
if ($callback != '')
{
	echo $callback . '(';
}
echo json_encode($obj);

if ($callback != '')
{
	echo ')';
}
?>