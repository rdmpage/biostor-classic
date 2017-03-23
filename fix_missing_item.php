<?php

// Code to update page links for replaced BHL items. Assumes pages have same "names"
// in the two items.

require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/www/itemutilities.php');

$old_item = 43946;
$new_item = 130127;


$old_item = 136738;
$new_item = 137726;

    
$old_item = 13976;
$new_item = 86336;

$update = array();


$replace_list = array(
105799 => 101303
);

$done = array();
$manual = array();

foreach ($replace_list as $old_item => $new_item)
{
	echo "-- $old_item $new_item\n";

	$new_pages = array();

	$sql = 'SELECT PageID, PagePrefix, PageNumber FROM bhl_page WHERE ItemID=' . $new_item;
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	$ok = true;
	$rows = array();
	$count = 0;

	while (!$result->EOF) 
	{
		$page = $result->fields['PagePrefix'];
		$page .= ' ' . $result->fields['PageNumber'];
		$page = str_replace('[', '', $page);
		$page = str_replace(']', '', $page);
	
		if (!isset($new_pages[$page]))
		{
			$new_pages[$page] = array();
		}
		$new_pages[$page][] = $result->fields['PageID'];

		$result->MoveNext();				
	}

	//print_r($new_pages);

	//exit();

	// articles in item
	$articles = articles_for_item($old_item);
	
	//print_r($articles);
	
	if (count($articles->articles) == 0)
	{
		$done[] = $old_item;
	}

	foreach ($articles->articles as $article)
	{
		$reference_id = $article->reference_id;

		/*
			$sql = "SELECT  rdmp_reference_page_joiner.PageID, rdmp_reference_page_joiner.page_order, bhl_page.PagePrefix, bhl_page.PageNumber, 
		bhl_page_new.PagePrefix AS PagePrefix2, REPLACE(REPLACE(bhl_page_new.PageNumber,'[',''),']','') AS PageNumber2, bhl_page_new.PageID as PageID2
		FROM rdmp_reference_page_joiner
		INNER JOIN bhl_page USING (PageID)
		INNER JOIN bhl_page AS bhl_page_new ON bhl_page.PageNumber = bhl_page_new.PageNumber
		WHERE reference_id=" . $reference_id . " AND bhl_page.ItemID=" . $old_item . " AND bhl_page_new.ItemID=" . $new_item . " ORDER BY rdmp_reference_page_joiner.page_order";
		*/
			$sql = "SELECT DISTINCT rdmp_reference_page_joiner.PageID, rdmp_reference_page_joiner.page_order, bhl_page.PagePrefix, bhl_page.PageNumber
		FROM rdmp_reference_page_joiner
		INNER JOIN bhl_page USING (PageID)
		WHERE reference_id=" . $reference_id . " AND bhl_page.ItemID=" . $old_item 
		//. " AND reference_id=11818"
		. " ORDER BY rdmp_reference_page_joiner.page_order";


		//echo $sql;

		//exit();

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

		$ok = true;
		$rows = array();
		$count = 0;

		while (!$result->EOF) 
		{
	
			$page = $result->fields['PagePrefix'];
			$page .= ' ' . $result->fields['PageNumber'];
			$page = str_replace('[', '', $page);
			$page = str_replace(']', '', $page);
	
			if (isset($new_pages[$page]))
			{
	
				//echo $page . "\n";
				//print_r($new_pages[$page]);
	
				foreach ($new_pages[$page] as $PageID)
				{
					//echo "page=$page PageiD=$PageID\n";
		
					$row = new stdclass;
	
					$row->PageID = $result->fields['PageID'];
					$row->page_order = $result->fields['page_order'];
					$row->page = $page;
			
					$row->PageID2 = $PageID;
	
					if ($ok)
					{
						if ($count != $row->page_order)
						{
							$ok = false;
						}
					}
	
					$rows[] = $row;
					$count++;
				}
			}
	
			$result->MoveNext();				
		}

		//print_r($rows);


		if ($ok)
		{
			echo "-- updating article $reference_id\n";
	
			$update[] = $reference_id;

			//print_r($rows);
	
			// fix page SQL
	
			// article 
			$sql = 'UPDATE rdmp_reference SET PageID=' . $rows[0]->PageID2 . ' WHERE reference_id=' . $reference_id . ';' . "\n";
	
			foreach ($rows as $row)
			{
				$sql .= 'DELETE FROM rdmp_reference_page_joiner WHERE reference_id=' . $reference_id . ' AND PageID=' . $row->PageID . ';' . "\n";

				$sql .= 'INSERT INTO rdmp_reference_page_joiner(reference_id, PageID, page_order) VALUES(' . $reference_id . ',' . $row->PageID2 . ',' . $row->page_order . ');' . "\n";
			}
	
			echo $sql . "\n";
			
		}
		else
		{
			echo "-- can't update article $reference_id\n";
			
			$manual[] = $reference_id;
		}
	}
}


echo '-- $ids=array(' . join(",", $update) . ');' . "\n";

echo "Done already\n";
print_r($done);

echo "Fix manually\n";
print_r($manual);

?>