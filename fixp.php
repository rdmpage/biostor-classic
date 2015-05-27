<?php

require_once('db.php');

//for ($reference_id=66009;$reference_id<=66058;$reference_id++)

// 85560-85454


//for ($reference_id=85454;$reference_id<=85560;$reference_id++)
//for ($reference_id=85581;$reference_id<=85582;$reference_id++)

$ids=array(
45062,
44966,
44952,
44816,
44626,
44625,
47587
);

$ids=array(85596);

$ids=array(85604);

$ids=array(51688);

$ids=array(85711,85717);

//$ids=array(86103);

$ids=array(80076,80077,80078,80079,80080,80082,80083,80084,80085,80089,80090,80091,80093,80095,80100,80103,80104,80106,80109,80112,80115,80117);

$ids=array(133261);

$ids=array(144878);




//foreach ($ids as $reference_id)

for ($reference_id=145855;$reference_id<=145855;$reference_id++)
{
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