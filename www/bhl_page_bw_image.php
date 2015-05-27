<?php

// DjVu XML for a BHL page

require_once ('../db.php');
require_once ('../lib.php');
require_once ('../bhl_utilities.php');


$PageID = $_GET['PageID']; 

$img = null;

$sql = 'SELECT * FROM bhl_page 
INNER JOIN page USING(PageID)
WHERE (PageID=' . $PageID . ') 
LIMIT 1';

//echo $sql;

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

if ($result->NumRows() == 1)
{
	$ItemID = $result->fields['ItemID'];
	$FileNamePrefix = $result->fields['FileNamePrefix'];
	
	if (preg_match('/_(?<page_num>\d+)$/', $FileNamePrefix, $m))
	{
		$page_num = $m['page_num'];
	
		$bw_filename = $cache_namespace = $config['cache_dir']. "/" . $ItemID . "/bw_images/" . $page_num . ".png";
	
		if (file_exists($bw_filename))
		{
			
			$image = file_get_contents($bw_filename);
		}
	}
}

if ($image)
{
	header('Content-type: image/png');
	echo $image;		
}

?>