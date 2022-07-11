<?php

require_once(dirname(__FILE__) . '/db.php');

$start 	= 264889;
$end 	= 264900;

$ids=array(
//62545,
57991
);



foreach ($ids as $reference_id)
//for ($reference_id=$start;$reference_id<=$end;$reference_id++)
{

	echo "-- reference_id = $reference_id\n";
	
	$article = db_retrieve_reference($reference_id);

	$page_range = array();
	
	if ($article->spage > 100000)
	{
		$page_range = 
			bhl_page_range_from_pageids($article->spage, $article->epage);
	}
	else
	{	
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
	}
		
	//print_r($page_range);
	
	echo "DELETE FROM rdmp_reference_page_joiner WHERE reference_id=$reference_id;\n";
	
	$count = 0;
	foreach ($page_range as $page)
	{
		$sql = 'INSERT INTO rdmp_reference_page_joiner (reference_id, PageID, page_order) VALUES (' . $reference_id . ',' . $page . ',' . $count++ . ');';
		echo $sql . "\n";
		
		//$result = $db->Execute($sql);
		//if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	}		




}

?>