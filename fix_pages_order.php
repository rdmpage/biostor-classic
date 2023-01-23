<?php

// reorder pages about a pivot, e.g. by putting plates at the end 
// this gives us a quick and dirty way to move plates
// need array of reference_id and index of start page in order (zero-offset). Hence
// to move one plate to the back of an article we the index would be 1

require_once(dirname(__FILE__) . '/db.php');


$ids = array(
	277932 => 1, // zero offset number of page that we want to be first
);

$ids = array(
	277924 => 9, // zero offset number of page that we want to be first
);

$ids = array(
277940 => 6,
277937 => 3,
277941 => 1,
);

$ids = array(
277942 => 10,
277947 => 2,
277949 => 1,
277959 => 1,
277955 => 1,
277950 => 3,


);

$ids=array(277985 => 1);

$ids=array(278122 => 2);

foreach ($ids as $reference_id => $pivot)
{
	echo "-- reference_id = $reference_id\n";
	
	$pages = bhl_retrieve_reference_pages($reference_id);	
	
	//print_r($pages);
	
	$page_ids = array();
	
	if ($pivot > 0)
	{
		$n = count($pages);
		
		for ($i = $pivot; $i < $n; $i++)
		{
			$page_ids[] = $pages[$i]->PageID;
		}
		for ($i = 0; $i < $pivot; $i++)
		{
			$page_ids[] = $pages[$i]->PageID;
		}
	
		//print_r($page_ids);
			
		echo "DELETE FROM rdmp_reference_page_joiner WHERE reference_id=$reference_id;\n";
		echo "UPDATE rdmp_reference SET PageID=" . $page_ids[0] . "  WHERE reference_id=$reference_id;\n";
				
		foreach ($page_ids as $i => $PageID)
		{
			$sql = 'INSERT INTO rdmp_reference_page_joiner (reference_id, PageID, page_order) VALUES (' . $reference_id . ',' . $PageID . ',' . $i . ');';
			echo $sql . "\n";
		}		
		
		
	}
	else
	{
		// just dump so we can manually edit
		
		foreach ($pages as $page)
		{
			$page_ids[] = $page->PageID;
		}
		
		echo "DELETE FROM rdmp_reference_page_joiner WHERE reference_id=$reference_id;\n";
		echo "UPDATE rdmp_reference SET PageID=" . $page_ids[0] . "  WHERE reference_id=$reference_id;\n";
				
		foreach ($page_ids as $i => $PageID)
		{
			$sql = 'INSERT INTO rdmp_reference_page_joiner (reference_id, PageID, page_order) VALUES (' . $reference_id . ', ' . $PageID . ', ' . $i . ');';
			echo $sql . "\n";
		}		
		
	
	}

}
?>
