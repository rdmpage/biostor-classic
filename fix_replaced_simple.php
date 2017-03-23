<?php

// simple page fix, assuming we know new pages

require_once('db.php');

// bisotor id => new PageID
$fix = array(
99459 => 37074528,
79960 => 37074524,
79962 => 37074490,
59419 => 37074498,
79961 => 37074612,
69243 => 37074636,
85526 => 37074788

);


foreach ($fix as $reference_id => $PageID)
{
	// update PageID
	$sql = "UPDATE rdmp_reference SET PageID=$PageID where reference_id=$reference_id;";
	echo $sql . "\n";
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	
	// clean up old pages
	$sql = "DELETE FROM rdmp_reference_page_joiner WHERE reference_id=$reference_id;";
	echo $sql . "\n";
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);



	$article = db_retrieve_reference($reference_id);


	$page_range = array();
	if (isset($article->spage) && isset($article->epage))
	{
		$page_range = 
			bhl_page_range($article->PageID, $article->epage - $article->spage + 1);
	}
	else
	{
		// No epage, so just get spage (to do: how do we tell user we don't have page range?)
		$page_range = 
			bhl_page_range($article->PageID, 0);				
	}
	
	//print_r($page_range);
	
	
	// new pages
	$count = 0;
	foreach ($page_range as $page)
	{
		$sql = 'INSERT INTO rdmp_reference_page_joiner (reference_id, PageID, page_order) VALUES (' . $reference_id . ',' . $page . ',' . $count++ . ');';
		echo $sql . "\n";
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	}		




}

?>