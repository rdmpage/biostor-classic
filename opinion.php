<?php

require_once ('db.php');
require_once ('bhl_text.php');


$ids = array(
1429,1438,1446,1447,1448,1449,1451,1453,1541,1555,1556,1557,1558,1560,1561,1563,1564,1565,1566,1568,1571,1572,1573,1574,1575,1576,1577,1578,1580,1581,1585,1586,1587,1588,1591,1600,1601,1602,1609,1610,1615,1617,1618,1619,1625,1640,1641,1642,1643,1644,1645,1646,1647,1648,1651,1660,1664,1672,1674,1677,1678,1679,1680,1681,1695,1696,1698,1699,1701,1702,1704,1706,1707,1710,1712,1452,1433,1430,1445,1454,1431,1496,1432,1500,1499,1497,1697,1594,1441,1450,1498,1455,1567,1579,1737,1442,1590,1584,1589,1495,1718,1719,1732,1733,1734,1767,1769,1778,1779,1780,1810,1811,1812,1813,1814,1815,1816,1817,1818,1819,1820,1821,1822,1823,1825,1826,1828,1829,1830,1831,1832,1834,1835,1836,1837,1841,1842,1854,1857,1858,1859,1861,1875,1876,1877,1888,1890,1891,1892,1894,1895,1896,1897,1898,1899,1900,1901,1910,1916,1917,1919,1920,1925,1926,1934,1935,1939,1940,1949,1951,1952,1957,1958,1959,1960,1966,1967,1982,1983,1984,1986,1987,1992,1995,1996,1997,1998,1999,2000,2002,2003,2004,2005,2006,2013,2014,2019,2020,2053,2054,2055,2067,2068,2069,2070,2084,1673,2087,2112,2113,2114,2116,2117,1559,2135,2137,2139,2146,2147,2148,2153,2154,2163,2167,1956,2182,2184,2186,2187,2205,2209,2219,2221,2222,2224,2227,2228,2238,1923,2262,2220,1570,1639,1444,1569,2253,2258,2259,2264,2226,1752,1583,1443,2261);

// query

$ids = array();

// with author "Opinion"
$sql = 'select reference_id from rdmp_author_reference_joiner where author_id=1085;';

// with Opinion cliped off then (Case
//$sql = 'select reference_id from rdmp_reference where title like "(Case %" AND issn="0007-5167"';

// whole lot
//$sql = 'select reference_id from rdmp_reference where issn="0007-5167" LIMIT 1000';

// Opinion only titles
$sql = 'select reference_id from rdmp_reference where issn="0007-5167" AND title REGEXP "^Opinion [0-9]+$"';

$sql = 'select reference_id from rdmp_reference where reference_id=80624';

// Not opinion titles
$sql = 'select reference_id from rdmp_reference where issn="0007-5167" AND title NOT REGEXP "^Case [0-9]+$" AND year > 1980';


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$ids[] = $result->fields['reference_id'];
	$result->MoveNext();
}



foreach ($ids as $reference_id)
{
	$reference = db_retrieve_reference($reference_id);
	
	// Get text of first page
	
	$text = bhl_fetch_ocr_text($reference->PageID);
	
	//echo $text;
	
	$lines = explode("\\n", $text);
	
	/*
	print_r($lines);
	
	$n = count($lines);
	$i = 1;
	$done = false;
	
	$title = '';
	
	while (($i < $n) && !$done)
	{
		//echo $lines[$i] . "\n";
		if (preg_match('/^RULING/i', $lines[$i]))
		{
			$done = true;
		}
		if (preg_match('/^Keywords/i', $lines[$i]))
		{
			$done = true;
		}
		if (!$done)
		{
			$title .= $lines[$i];
		}
		$i++;
	
	}
	
	$title = trim($title);
	
	if ($i < 8)
	{
		$title = preg_replace('/\-\s+/', '', $title);
		$title = preg_replace('/\s+;/', ';', $title);
		$title = preg_replace('/\s+:/', ':', $title);
		$title = preg_replace('/\s\s+/', ' ', $title);
		
		echo "-- $title\n";
		if (preg_match('/^OPINION \d+/i', $title))
		{
			echo "-- " . $reference->title . "\n";
			echo "UPDATE rdmp_reference SET title=" . $db->qstr($title) . " WHERE reference_id=$reference_id;" . "\n";
		}
	}
	
	*/
	
	
	
	if (preg_match('/^(?<opinion>CASE \d+)/i', $lines[1], $m))
	{
		echo "-- " . $reference->title . "\n";
		if (preg_match('/^CASE /i', $reference->title))
		{
			// all good
			echo "-- OK\n";
		}
		else
		{
			$title = mb_convert_case(trim($m['opinion']), MB_CASE_TITLE) . '. ' . $reference->title;
			
			//$title = mb_convert_case(trim($m['opinion']), MB_CASE_TITLE) . ' ' . $reference->title;
			
			echo "-- $title\n";
			
			echo "UPDATE rdmp_reference SET title=" . $db->qstr($title) . " WHERE reference_id=$reference_id;" . "\n";
		}
		
	
	}
	else
	{
		echo "-- xx $reference_id " . $lines[1] . "\n";
	}
	
	
	
	//exit();
	
}

?>