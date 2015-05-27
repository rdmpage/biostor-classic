<?php

require_once ('../bhl_text.php');
require_once ('../bhl_utilities.php');

$ItemID = $_GET['item'];

$page = 0;
$image = false;
$size = 'normal';

$callback = '';

if (isset($_GET['page']))
{
	$page = $_GET['page'];
	$page--;
}

if (isset($_GET['size']))
{
	$size = $_GET['size'];
	$image = true;
}

if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}

// Get page array
$bhl_pages = bhl_retrieve_item_pages($ItemID);

$PageID = $bhl_pages[$page]->PageID;

if ($image)
{
	$image = bhl_fetch_page_image($PageID);
	
	if ($size == 'small')
	{
		header("Location: " . $image->thumbnail->url . "\n\n");	
	}
	else
	{
		header("Location: " . $image->url . "\n\n");		
	}
}
else
{
	$text = bhl_fetch_ocr_text($PageID);
	$text = str_replace("\\n", "\n", $text);
	
	
	
	header('Content-type: text/plain');
	if ($callback != '')
	{
		echo $callback .'(';
	}
	echo json_encode($text);
	if ($callback != '')
	{
		echo ')';
	}
	
}

?>