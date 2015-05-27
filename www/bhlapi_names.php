<?php

require_once(dirname(dirname(__FILE__)) . '/db.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');

// http://www.php.net/manual/en/function.str-getcsv.php#95132
function csv_to_array($csv, $delimiter = ',', $enclosure = '"', $escape = '\\', $terminator = "\n") { 
    $r = array(); 
    $rows = explode($terminator,trim($csv)); 
    $names = array_shift($rows); 
    $names = str_getcsv($names,$delimiter,$enclosure,$escape); 
    $nc = count($names); 
    foreach ($rows as $row) { 
        if (trim($row)) { 
            $values = str_getcsv($row,$delimiter,$enclosure,$escape); 
            if (!$values) $values = array_fill(0,$nc,null); 
            $r[] = array_combine($names,$values); 
        } 
    } 
    return $r; 
} 

$callback = '';

$q = '';
if (isset($_GET['q']))
{
	$q = $_GET['q'];
}

if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}

$hits = array();

if ($q != '')
{
	// Grab results from BHL
	
	$url = 'http://www.biodiversitylibrary.org/Services/NameListDownloadService.ashx?type=c&name=' . urlencode($q) . '&lang=';
	
	//echo $url . "\n";
	
	$csv = get($url);
	
	//echo $csv;
	
	$r = csv_to_array($csv);
	
	if (0)
	{
		echo '<pre>';
		print_r($r);	
		echo '</pre>';
	}
	
	
	// OK, can we match hits to BioStor references?
	
	$pages = array();
	foreach ($r as $row)
	{
		if (is_array($row))
		{
			if (isset($row['Url']))
			{
				$pages[] = str_replace('http://www.biodiversitylibrary.org/page/', '', $row['Url']);
			}
		}
	}
	
	//print_r($pages);
	
	$page2biostor = array();
	
	
	$sql = "SELECT PageID, reference_id FROM rdmp_reference_page_joiner WHERE PageID IN (" . join(",",$pages) . ")";
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
		
	while (!$result->EOF) 
	{
		$page2biostor[$result->fields['PageID']]  = $result->fields['reference_id'];
		$result->MoveNext();
	}
	/*
	echo '<pre>';
	
	print_r($page2biostor);
	echo '</pre>';
	*/
	
	$count = 0;
	
	
	foreach ($r as $row)
	{
		if (is_array($row))
		{
			if (isset($row['Url']))
			{
	
				$PageID = str_replace('http://www.biodiversitylibrary.org/page/', '', $row['Url']);
				
				//echo $PageID . '<br/>';
			
				if (isset($page2biostor[$PageID]))
				{
					if (!isset($hits[$page2biostor[$PageID]]))
					{
						
						$reference = db_retrieve_reference($page2biostor[$PageID]);
						$hit = reference_to_bibjson($reference);
						$hit->PageID = $reference->PageID;
						$hit->biostor = $page2biostor[$PageID];
						$hits[$page2biostor[$PageID]] = $hit;
						
					}
					
					
				}
				else
				{
		/*			$ItemID = bhl_retrieve_ItemID_from_PageID($PageID);
					
					if ($ItemID == 0)
					{
						$id = $count;
						$count++;
					}
					else
					{
						$id = $ItemID;
					}
					
					$id = 'item' . $id;*/
					
					$id = $row['Title'] . $row['Volume']; 
					
					if (!isset($hits[$id]))
					{
						$hit = new stdclass;
						$hit->PageID = $PageID;
						$hit->identifiers = array();
						
						$hit->title = $row['Title'];	
						
						$info = new stdclass;
						parse_bhl_date($row['Volume'], $info);
						
						if (isset($info->start))
						{
							$hit->year = $info->start;
						}
						else
						{
							$hit->year = $row['Date'];
						}
						
						$identifier = new stdclass;
						$identifier->type = 'bhl';
						$identifier->id = $PageID;
						$hit->identifiers[] = $identifier;
						
			
						$hits[$id] = $hit;
					}
				}
			}
		}
	}
}

/*echo '<pre>';
print_r($hits);
echo '</pre>';
*/
header('Content-type: text/plain');
if ($callback != '')
{
	echo $callback .'(';
}

$obj = new stdclass;
$obj->query = $q;


// sort
$keys = array();
$years = array();
foreach ($hits as $k => $hit)
{
	$keys[] = $k;
	$years[] = $hit->year;
}

array_multisort($years, SORT_NUMERIC, $keys);

/*
echo '<pre>';
print_r($keys);
print_r($years);
echo '</pre>';
*/

$obj->hits = array();
foreach ($keys as $k) {
    $obj->hits[$k] = $hits[$k];
}

echo json_format(json_encode($obj));
if ($callback != '')
{
	echo ')';
}



?>