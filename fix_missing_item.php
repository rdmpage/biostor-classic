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

// old_item => new_item
$replace_list = array(
108670 => 221241
);

$replace_list = array(
108590 => 223032
);

// Zoologische Jahrbücher 7
$replace_list = array(
37645 => 121145
);

// Memoirs of the National Museum, Melbourne
$replace_list = array(
120023 => 216415,
120130 => 216320,
46987 => 216345,
119076 => 215860
);

// more...
$replace_list = array(
//120129 => 215356
//120027 => 215247
// 120024 => 215248 // v 11
119480 => 214164, // 9
119464 => 214426, // 8
119061 => 214425// 3
);


$replace_list = array(
126382 => 179342
);

$replace_list = array(
49733 => 213414
);

$replace_list = array(
84743 => 183045
);


// Bianca's list
// https://github.com/rdmpage/biostor/issues/66

$replace_list = array(
49733	=>	213414,
45725	=>	87759,
17635	=>	84886,
17695	=>	84741,
18262	=>	84777,
18270	=>	84781,
18295	=>	84760,
37032	=>	99908,
108590	=>	223032,
108670	=>	221241,
13999	=>	89032,
119061	=>	214425,
119464	=>	214426,
119480	=>	214164,
120024	=>	215248,
120027	=>	215247,
120129	=>	215356,
43946	=>	130127,
45820	=>	214323,
51761	=>	220052,
53247	=>	55101,
53958	=>	220033,
54505	=>	220041,
105675	=>	101305,
84743	=>	183045,
84745	=>	30013,
84770	=>	18246,
84776	=>	37631
);

$replace_list = array(
108590 => 223032
);

$replace_list = array(
14006 => 87376,
14007 => 87375
);

// https://github.com/rdmpage/biostor/issues/81
$replace_list = array(
41412 => 18609
);

// Transactions of The Linnean Society of London volume 9
$replace_list = array(
46581 => 13719
);

// Bulletin of the Museum of Comparative Zoology at Harvard College v1
// old_item => new_item

$replace_list = array(
96183 => 30465
);

/*
DELETE FROM bhl_item WHERE ItemID=120129;
DELETE FROM bhl_item WHERE ItemID=120027;
DELETE FROM bhl_item WHERE ItemID=120024;
DELETE FROM bhl_item WHERE ItemID=119480;
DELETE FROM bhl_item WHERE ItemID=119464;
DELETE FROM bhl_item WHERE ItemID=119061;

DELETE FROM bhl_item WHERE ItemID=120023;
DELETE FROM bhl_item WHERE ItemID=120130;
DELETE FROM bhl_item WHERE ItemID=46987;
DELETE FROM bhl_item WHERE ItemID=119076;
*/

$replace_list = array(
23920 => 259461
);

$replace_list = array(
43773 => 105347
);

$replace_list = array(
44628 => 105674
);

// The journal of the Bombay Natural History Society
$replace_list = array(
95721 => 214677
);


$done = array();
$manual = array();

foreach ($replace_list as $old_item => $new_item)
{
	echo "-- $old_item $new_item\n";

	$new_pages = array();
	
	$affected_ids = array();

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
		
		$page = trim($page);
	
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
	
	if (
		!isset($articles->articles) 
		|| 
		count($articles->articles) == 0
		)
	{
		$done[] = $old_item;
	}
	
	if (isset($articles->articles)) {

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
		
			//print_r($new_pages);

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
				$page = trim($page);
				
				$page = $result->fields['PageNumber'];
				
				//echo $page . "\n";
				//exit();
	
				if (isset($new_pages[$page]))
				{
	
					//echo $page . "\n";
					//print_r($new_pages[$page]);
					
					
	
					foreach ($new_pages[$page] as $PageID)
					//$PageID = $new_pages[$page][0];
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
		
			if (count($rows) == 0)
			{
				$ok = false;
			}


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
				
				// get starting page number
				$spage = '';
				$title = '';
				$sql = 'SELECT * FROM rdmp_reference WHERE reference_id=' . $reference_id . ' LIMIT 1';
				$result = $db->Execute($sql);
				if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

				if ($result->NumRows() == 1) 
				{
					$spage = $result->fields['spage'];
					$title = $result->fields['title'];
				}
				
				echo "-- Page to match = $spage\n";
				echo "-- Title to match = $title\n";
				
				echo "DELETE FROM rdmp_reference_page_joiner WHERE reference_id=$reference_id;\n";
				echo "UPDATE rdmp_reference SET PageID=xxx WHERE reference_id=$reference_id;\n";
				echo "INSERT INTO rdmp_reference_page_joiner(reference_id, PageID, page_order) VALUES ($reference_id,xxx,0);\n";

				echo "\n\n";
			
				$manual[] = $reference_id;
				
				$affected_ids[] =  $reference_id;
			}
		
			//if ($reference_id == 68747) exit();
			//exit();
		}
	}
	
	if (count($affected_ids) > 0)
	{
		echo "-- Affected reference_ids for ItemID $old_item: " .  ' $ids=array(' . join(',', $affected_ids) . ');' . "\n\n";
	}
}


echo "\nAfter executing SQL above add these ids to import.php to reload fixed articles\n";

echo '$ids=array(' . join(",", $update) . ');' . "\n";

echo "Items done already\n";
print_r($done);

echo "References to fix manually\n";
print_r($manual);

?>