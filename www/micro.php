<?php

require_once(dirname(dirname(__FILE__)) . '/db.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(dirname(__FILE__)) . '/bhl_search.php');


function find_reference($journal, $volume, $page, $year = '')
{
	global $db;
	
	$references = array();
	$hits = array();
	
	
	if (
		($journal == '')
		|| ($volume == '')
		|| ($page == ''))
	{
		//echo "journal=$journal\n";
		return $hits;
	}
	
	$series = '';
	
	$issn = '';
	$oclc = 0;
	
	if (preg_match('/^(?<journal>.*),\s+ser\.\s+(?<series>.*)$/', $journal, $m))
	{
		$journal = $m['journal'];
		$series = $m['series'];
	}
	if (preg_match('/^(?<journal>.*),\s+(ns|n.s.)$/', $journal, $m))
	{
		$journal = $m['journal'];
		//$series = $m['series'];
	}
	if (preg_match('/^(?<page>\d+),(.*)$/', $page, $m))
	{
		$page = $m['page'];
	}


	
	$issn = issn_from_title($journal);
	if ($issn == '')
	{
		$oclc = oclc_for_title($journal);
	}
	
	if (preg_match('/^[ixvlcm]+$/', $volume))
	//if (!is_numeric($volume))
	{
		$volume = arabic($volume);
	}
	
	if (0)
	{
		echo $issn . "\n";
		echo $volume. "\n";
		echo $page. "\n";
	}
	
	$sql = '';
	
	if ($issn != '')
	{
		$sql = 'SELECT * FROM rdmp_reference WHERE issn=' . $db->qstr($issn)
			. ' AND volume=' . $db->qstr($volume)
			. ' AND ' . $page . ' BETWEEN spage AND epage';
			
	}
	if ($oclc != '')
	{
		$sql = 'SELECT * FROM rdmp_reference WHERE oclc=' . $db->qstr($oclc)
			. ' AND volume=' . $db->qstr($volume)
			. ' AND ' . $page . ' BETWEEN spage AND epage';
	}
	
	//echo $sql;
		
	if ($sql != '')
	{
		if ($series != '')
		{
			$sql .= ' AND series=' . $db->qstr($series);
		}
		if ($year != '')
		{
			$sql .= ' AND year=' . $db->qstr($year);
		}
		
		//echo $sql;
	
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
		while (!$result->EOF) 
		{
			$references[] = $result->fields['reference_id'];
			$result->MoveNext();				
		}	

	
	
	}
	
	foreach ($references as $reference_id)
	{
		$hits[] = reference_to_bibjson(db_retrieve_reference($reference_id));
	}
	
	return $hits;
	
}

if (0)
{
	//$r = find_reference('Notes Leyden Mus.', 2,16,1880);
	//$r = find_reference('Ann. Mag. Nat. Hist., ser. 6', 2, 409);
	//$hits = find_reference('Ann. Mag. Nat. Hist', 20, 294, 1907);
	//$hits = find_reference('Fieldiana Zool., n.s.', 70, 37, 1992);
	
	$hits = find_reference('J. Arnold Arbor.', 'xliii', 74, 1962);
	
	print_r($hits);
}


$callback = '';

$journal = '';
$volume = '';
$page = '';
$year = '';

if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}
if (isset($_GET['journal']))
{
	$journal = $_GET['journal'];
}
if (isset($_GET['volume']))
{
	$volume = $_GET['volume'];
}
if (isset($_GET['page']))
{
	$page = $_GET['page'];
}
if (isset($_GET['year']))
{
	$year = $_GET['year'];
}

//print_r($_GET);

$search = new stdclass;
$search->results = find_reference($journal, $volume, $page, $year);
$search->count = count($search->results);

header("Content-type: text/plain; charset=utf-8\n\n");
if ($callback != '')
{
	echo $callback . '(';
}
echo json_format(json_encode($search));
if ($callback != '')
{
	echo ')';
}	

?>