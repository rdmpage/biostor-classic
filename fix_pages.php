<?php

// Get images, thumbnails, and OCR text for an item to pre-populate local copy of BHL

require_once (dirname(__FILE__) . '/bhl_text.php');
require_once (dirname(__FILE__) . '/bhl_utilities.php');


$pages=array(15631156,15631157);

$pages=array(19245535,19245536,19245537,19245538,19245539,19245540,19245541);

$pages=array(19265719,19265720,19265721,19265722,19265723,19265724,19265725);

$pages=array(15618386,15618387,15618388,15618389,15618390,15618391,15618392);

$pages=array(24254672,24254673,24254674);

foreach ($pages as $page)
{
	echo $page . "\n";
	bhl_fetch_page_image($page);
	bhl_fetch_ocr_text($page, '', 30);
}

?>