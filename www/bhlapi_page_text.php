<?php

require_once ('../bhl_text.php');

$PageID = 0;

if (isset($_GET['PageID']))
{
	$PageID = $_GET['PageID'];
}

$text = '';

if ($PageID != 0)
{
	$text = bhl_fetch_ocr_text($PageID);
	$text = str_replace("\\n", "\n", $text);
}

header('Content-type: text/plain; charset=UTF-8');
echo $text;

?>