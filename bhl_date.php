<?php

/**
 * @file bhl_date.php
 *
 * Extract dates, volume, and series information from BHL database fields
 *
 */
 
require_once(dirname(__FILE__) . '/utilities.php');
 

//--------------------------------------------------------------------------------------------------
function bhl_date_from_details($str, &$info)
{
	$debug = false;
	$matched = false;
	
	// Clean up
	$str = trim($str);
	
	//echo "$str\n";
	
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<yearstart>[0-9]{4})\s*\-\s*(?<yearend>[0-9]{4})\.?$/", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}	
	
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<year>[0-9]{4})\.?$/", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->start = $m['year'];
			$matched = true;
		}
		
		
	}
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<yearstart>[0-9]{4})\s*\-\s*(?<yearend>[0-9]{2})\.?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<yearstart>[0-9]{4})\s*\-\s*(?<yearend>[0-9]{2})\.?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}	
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<yearstart>[0-9]{4})\s*\-\s*\[(?<yearend>[0-9]{4})\]\.?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}	
	
	
	
	return $matched;
}

//--------------------------------------------------------------------------------------------------
function parse_bhl_date($str, &$info)
{
	$debug = false;
	$matched = false;
	
	// Clean up
	
//	$str = preg_replace('/^new ser./', '', $str);
//	$str = preg_replace('/text$/', '', $str);
	$str = preg_replace('/:plates$/', '', $str);
	$str = trim($str);
	
	if ($debug)
	{
		echo $str . '<br/>';
	}
	
	// v.I: no.5
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v.\s*(?<volume>[I]+)[:|,]\s*no\.?\s*(?<issue>\d+(-\d+)?)$/", $str, $m))
		{
			$info->volume = arabic($m['volume']);
			$info->issue = $m['issue'];		
			$matched = true;
		}	
	}	
	
	// t. 2:fasc. 1
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^t.\s*(?<volume>\d+):\s*fasc\.\s*(?<issue>\d+(-\d+)?)/", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];		
			$matched = true;
		}	
	}	
	
	
	// n.f., bd.56 (1916-1917)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^n.f.,\s*bd\.\s*(?<volume>\d+)\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];	
			$matched = true;
		}	
	}	
	
	
	
	// 3.Heft (1878)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<volume>\d+)\.\s*Heft\s*\((?<year>[0-9]{4})\)/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// 1913 V.5 pt.2
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<year>[0-9]{4})\s+V\.\s*(?<volume>\d+)\s*pt.(?<issue>\d+)/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$info->issue = $m['issue'];	
			$matched = true;
		}	
	}	
	// ser.3:t.2
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^ser.(?<series>\d+):t(?<volume>\d+)$/Uu", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}		
	
	// ser.1:t.3-4
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^ser.(?<series>\d+):t\.(?<volume_from>\d+)-(?<volume_to>\d+)$/Uu", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$matched = true;
		}	
	}		
	
	// ser.5:t.7:dispensa 1-9
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^ser.(?<series>\d+):t\.(?<volume>\d+):/Uu", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}		
	
	// 31. Bd. (1864)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)\.\s*Bd\.\s+\((?<year_from>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];		
			$matched = true;
		}	
	}		
	
	// 33. Bd. (1866-1867)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)\.\s*Bd\.\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];	
			$matched = true;
		}	
	}	
	
	
	// 51, part 3
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+), part\ (?<issue>\d+)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];
			$matched = true;
		}	
	}		
	
	
	//  v. 12-13 (1996)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v.\s+(?<volume_from>\d+)-(?<volume_to>\d+)\s+\((?<year_from>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$matched = true;
		}	
	}		
	
	// v.1-3 (1983-1985: Jan.-June)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\.(?<volume_from>\d+)\s*-\s*(?<volume_to>\d+)\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{4}):/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];	
			$matched = true;
		}	
	}	

	// v .4-5 (1986-1987)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\s*\.(?<volume_from>\d+)\s*-\s*(?<volume_to>\d+)\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];	
			$matched = true;
		}	
	}	
	
	// nr.48 (2000)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(nr)\.\s*(?<volume>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	
	// nr.39-41 (1996)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(n.s.\s+)?(bd|nr|nos)\.?\s*(?<volume_from>\d+)-(?<volume_to>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// bb 25
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd\s+(?<volume>\d+)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	// n.F.:11.Bd. (1891)	
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^n.F.:(?<volume>\d+)\.Bd\.\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	
	// bd 9 - 10
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd\s+(?<volume_from>\d+)\s+-\s+(?<volume_to>\d+)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$matched = true;
		}	
	}	
	
	
	// n.s. bd 5-6 (1882-83)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(n.s.\s+)?bd\.?\s*(?<volume_from>\d+)-(?<volume_to>\d+)\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{2})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = substr ($m['year_from'], 0, 2) . $m['year_to'];	
			$matched = true;
		}	
	}	
	
	
	// v 4 - 7 (1895-1900)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\s*(?<volume_from>\d+)\s*-\s*(?<volume_to>\d+)\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];	
			$matched = true;
		}	
	}	
	
	// v 10 - 11 (1903-04)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\s*(?<volume_from>\d+)\s*-\s*(?<volume_to>\d+)\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{2})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = substr ($m['year_from'], 0, 2) . $m['year_to'];	
			$matched = true;
		}	
	}	
	
	
	// bd.51-52 (2003-2004)
	// nr.11-15 (1978-1980)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(bd|nr)\.\s*(?<volume_from>\d+)-(?<volume_to>\d+)\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];	
			$matched = true;
		}	
	}	
	
	// bd.55-bd.56 (2003-2004)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd\.\s*(?<volume_from>\d+)-bd.(?<volume_to>\d+)\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{4})\)\$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];	
			$matched = true;
		}	
	}	
	
	
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd.\s*(?<volume>\d+);\s*Sonderheft\s+\((?<year>[0-9]{4})\)/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	
	
	// no.675, pt.1 (1975)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^no.\s*(?<volume>\d+),\s*pt\.(?<issue>\d+)\s+\((?<year>[0-9]{4})\)/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	
	// bd. 6, Text
	
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd\.\s*(?<volume>\d+),\s+Text/iUu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	// bd. 11, pt. 1
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd\.\s*(?<volume>\d+),\s+(H|Lfg|pt|T)\.\s+(?<issue>\d+)/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];
			$matched = true;
		}	
	}	
	
	
	// Pt.1
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Pt\.\s*(?<volume>\d+)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	// ser. 5, no. 5 (1861)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^ser.\s*(?<series>\d+),\s*no.\s*(?<volume>\d+)\s+\((?<year>[0-9]{4})\)/Uu", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^ser.\s*(?<series>\d+),\s*no.\s*(?<volume>\d+)\s+\((?<year_from>[0-9]{4})-(?<year_to>[0-9]{4})\)/Uu", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];
			$info->end = $m['year_to'];
			$matched = true;
		}	
	}	
	
	
	// no.6/7 (1997)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^no.\s*(?<volume>\d+\/\d+)\s+\((?<year>[0-9]{4})\)/Uu", $str, $m))
		{
			$info->volume = str_replace("/", "-", $m['volume']);
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// no. 187-190( 1975)
	
	// new ser.:fasc.5 (1889)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^new ser.:fasc.\s*(?<volume>\d+)\s+\((?<year>[0-9]{4})\)/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// Decade 2:v.10 (1883)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(\[)?Decade\s+\d+(\])?:v\.\s*(?<volume>\d+)\s+\((?<year>[0-9]{4})\)/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	

	// n.s., decade 4, v. 5 (1898)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^n.s.,? decade\s+\d+,\s*v\.\s+(?<volume>\d+)\s+\((?<year>[0-9]{4})\)/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	
	// v 1..no.102 (1908)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\s+(?<volume>\d+)\.\.no.\d+\s+\((?<year>[0-9]{4})\)/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	
	
	// anno 12 (1892-1893)
	// nuova serie:anno 19 (1906-1907)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(nuova serie:)?anno\s*(?<volume>\d+)\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{4})\)/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];	
			$matched = true;
		}	
	}	
	
	// 79, no.4
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+),\s+no.\s*(?<issue>\d+)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];
			$matched = true;
		}	
	}	
	
	// v 1
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\s+(?<volume>\d+)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	// v.115-117 (1984-1986) 
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v.\s*(?<volume_from>\d+)-(?<volume_to>\d+)\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{4})\)\s+/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];	
			$matched = true;
		}	
	}	
	
	// t. 62 (1955) + suppl.
	// t. 61 (1954) + suppl.
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^t.\s+(?<volume>\d+)\s+\((?<year>[0-9]{4})\) /Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// t. 118, no. 1 (2011)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^t.\s*(?<volume>\d+),\s+no.\s+(?<issue>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	
	// [ser.4]:v.60
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^\[ser.(?<series>\d+)\]:v.(?<volume>\d+)/", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	
	// new series, v. 4 (1868)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^new series, v.\s*(?<volume>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// ser. 5, v. 6 (1888) general index (1883-88)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^ser. (?<series>\d+),\s+v.\s*(?<volume>\d+)\s+\((?<year>[0-9]{4})\)\s+/Uu", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// Bd.1:Nr.4 (1916)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Bd\.(?<volume>\d+):Nr\.(?<issue>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	
	// #1-15 1961-66
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^#(?<volume_from>\d+)-(?<volume_to>\d+)\s+(?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{2})$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = substr ($m['year_from'], 0, 2) . $m['year_to'];	
			$matched = true;
		}	
	}	
	
	// Suppl 4-5 (1998-99)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Suppl\s+(?<volume_from>\d+)-(?<volume_to>\d+)\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{2})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = substr ($m['year_from'], 0, 2) . $m['year_to'];	
			$matched = true;
		}	
	}	
	
	
	// t.119:no.1-17 (1894)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^t\.\s*(?<volume>\d+):no(.*)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	
	// 8.Jahrg. (1914-1915)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)\.\s*Jahrg\.\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];	
			$matched = true;
		}	
	}	
	
	
	// Bd. 10 (1885)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Bd\.\s+(?<volume>\d+)(, afd.\s+(?<issue>\d+))?\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	
	// 8.Bd. (1864)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)\.Bd\.\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// fasc. 144-151
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^fasc.\s+(?<volume_from>\d+)-(?<volume_to>\d+)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$matched = true;
		}	
	}	
	// fasc. 90 (1909)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^fasc.\s+(?<volume>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	// fasc. 154me
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^fasc.\s+(?<volume>\d+)me$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	
	// Bd. 4; Hft. 4/5
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Bd.\s+(?<volume>\d+);?/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	
	// jahrg 11 (1884)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^jahrg\s+(?<volume>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	
	// t.1er (1898)
	// t.4e (1901)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^t.(?<volume>\d+)e[r]?\s*\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// cah.6(1851)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^cah.\s*(?<volume>\d+)\s*\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	
	// 23 cah. (1904)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)\s+cah.\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// 10 (1913 - 1915)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];	
			$matched = true;
		}	
	}	
	
	
	// 2nd. ed. v.1-2 1891-1892
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^2nd. ed. v.(?<volume_from>\d+)-(?<volume_to>\d+)\s+(?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{4})$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];	
			$matched = true;
		}	
	}	
	
	// 2nd. ed. v.3-4 1891
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^2nd. ed. v.(?<volume_from>\d+)-(?<volume_to>\d+)\s+(?<year_from>[0-9]{4})$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$matched = true;
		}	
	}	
	
	
	
	
	// roc. 14-17 1917-20
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^roc.\s+(?<volume_from>\d+)-(?<volume_to>\d+)\s+(?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{2})$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = substr ($m['year_from'], 0, 2) . $m['year_to'];	
			$matched = true;
		}	
	}	
	
	
	
	// t 60 (1912-13)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^t\s+(?<volume>\d+)\s+\((?<year_from>[0-9]{4})-(?<year_to>[0-9]{2})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];		
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];	
			$matched = true;
		}	
	}		

	// t 54 (1906-7)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^t\s+(?<volume>\d+)\s+\((?<year_from>[0-9]{4})-(?<year_to>[0-9]{1})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];		
			$info->end = substr ($m['yearstart'], 0, 3) . $m['yearend'];	
			$matched = true;
		}	
	}		
	
	
	// pt. 18 (1850)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^pt.\s+(?<volume>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// pt. 1-6 (1833-1838)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^pt.\s+(?<volume_from>\d+)-(?<volume_to>\d+)\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = $m['yearend'];	
			$matched = true;
		}	
	}	
	
	
	// Bd.8/h.1
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Bd\.\s*(?<volume>\d+)\/h\.(?<issue>\d+)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];		
			$matched = true;
		}	
	}	
	
	
	// 17.bd. (1895)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)\.\s*bd.\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];		
			$matched = true;
		}	
	}	
	
	// t. 44-45 1972
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^t.\s+(?<volume_from>\d+)-(?<volume_to>\d+)\s+(?<year>[0-9]{4})$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$matched = true;
		}	
	}	

	// t.. 72 2001
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^t..\s+(?<volume>\d+)\s+(?<year>[0-9]{4})$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	
	// n.s. t. 4 (1857)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^n.s. t.\s+(?<volume>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// n.s. t. 6 (1859-1860)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^n.s. t.\s+(?<volume>\d+)\s+\((?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->start = $m['year_from'];		
			$info->end = $m['yearend'];	
			$matched = true;
		}	
	}	
	
	
	
	// año 8 (1904)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^año\s+(?<volume>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// Bd. 4; Hft. 4/5
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Bd.\s+(?<volume>\d+);\s+Hft.\s+(?<issue>.*)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'] . 'A';
			$matched = true;
		}	
	}	
	
	// v.123:suppl. (2003) 
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v.(?<volume>\d+):suppl.\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'] . 'A';
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	
	// n.s. v. 25 Jan-June (1907)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^n.s.\s+v.\s+(?<volume>\d+)\s+\w+-\w+\s+\(?(?<year>[0-9]{4})\)?$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// 1857 - 1858
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<year_from>[0-9]{4})\s*-\s*(?<year_to>[0-9]{4})$/Uu", $str, $m))
		{
			$info->start = $m['year_from'];		
			$info->end = $m['yearend'];	
			$matched = true;
		}	
	}	
	
	// an.1900:52nd
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^an\.\s*(?<year>[0-9]{4}):(?<volume>\d+)[rd|nd]$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// an. 1917 69: T.80
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^an\.\s*(?<year>[0-9]{4})\s*(?<volume>\d+):/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// Tome 11 (3rd ser. Tome 1)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Tome\s*(?<volume>\d+)\s+\((.*)\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// T. 86 (1922)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^T.\s*(?<volume>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// heft 26-27 1986-1987
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^heft\s*(?<volume_from>\d+)-(?<volume_to>\d+)\s+(?<year_from>[0-9]{4})-(?<year_to>[0-9]{4})$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = $m['yearend'];	
			$matched = true;
		}	
	}	
	
	
	// no. 330-346 1990
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^no.\s*(?<volume_from>\d+)-(?<volume_to>\d+)((\s+\w+\.?)?\s+(?<year_from>[0-9]{4}))?$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$matched = true;
		}	
	}	
	
	// no. 187-190( 1975)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^no.\s*(?<volume_from>\d+)-(?<volume_to>\d+)\s*\(\s*(?<year_from>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$matched = true;
		}	
	}	
	
	// no. 531/542
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^no.\s*(?<volume_from>\d+)\/(?<volume_to>\d+)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$matched = true;
		}	
	}	
	
	
	// no. 494 Dec. 2001
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^no.\s*(?<volume_from>\d+)(\s+\w+\.?)?\s+(?<year_from>[0-9]{4})$/Uu", $str, $m))
		{
			$info->volume = $m['volume_from'];
			$info->start = $m['year_from'];		
			$matched = true;
		}	
	}	
	
	// 
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/((Ser.\s+(?<series>\d+),|Suppl.)\s+)?d.\s*(?<volume>\d+)\s+\((?<year>[0-9]{4})\)$/", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume = $m['volume'];			
			$info->start = $m['year'];
			$matched = true;
		}
		
	}	
	
	
	// Biology: v.3 
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Biology: v.(?<volume>\d+)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	
	// t. 47; (ser. 5, t. 7) (1911)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^t\.\s*(?<v>\d+);\s+\(ser\.\s+(?<series>\d+),\s+t\.\s+(?<volume>\d+)\)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->series = $m['series'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	
	// Bd 3..Lfg..2
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Bd\s*(?<volume>\d+)((\s*(..)?)Lfg[\.]+(?<issue>.*))?$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	// t. 116 :fasc. 2 (2009)
	// t. 94 fasc. 3-4 1987
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^t\.\s*(?<volume>\d+)\s+[:]?fasc\.\s*(?<issue>.*)\s*\(?(?<year>[0-9]{4})\)?$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	
	// 72, no. 1
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)[,]?\s+no[\.]?\s+(?<issue>\d+)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	
	// 47 (1904-1905)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)\s+\((?<year_from>[0-9]{4})-(?<year_to>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];	
			$matched = true;
		}	
	}	
	
	// Vol 59-60
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Vol\s*(?<volume_from>\d+)-(?<volume_to>\d+)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$matched = true;
		}	
	}	
	
	// n. s. v. 15-16 (1920-21)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^n. s. v.\s*(?<volume_from>\d+)-(?<volume_to>\d+)\s+\((?<year_from>[0-9]{4})-(?<year_to>[0-9]{2})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];	
			$matched = true;
		}	
	}	
	
	
	// Bnd.01
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Bnd\.\s*0(?<volume>\d+)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	// Vol 65(1)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Vol\s*(?<volume>\d+)\s*\((?<issue>\d+)\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	// Vol 63, No. 2
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Vol\.?\s*(?<volume>\d+)[,]?\s+No[\.]?\s*(?<issue>\d+)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	// Vol.23 (1854)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Vol\.\s*(?<volume>\d+)(\((?<issue>\d+)\))?\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// new ser:v.15 (1919)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^new ser:v.\s*(?<volume>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// new ser:v.11 (1915-1916)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^new ser:v.\s*(?<volume>\d+)\s+\((?<year_from>[0-9]{4})-(?<year_to>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];		
			$matched = true;
		}	
	}	
	
	// Vol. 57-58
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Vol.\s+(?<volume_from>\d+)-(?<volume_to>\d+)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$matched = true;
		}	
	}	
	
	// v108-109 2001-2003
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v(?<volume_from>\d+)-(?<volume_to>\d+)\s+(?<year_from>[0-9]{4})-(?<year_to>[0-9]{4})$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];		
			$matched = true;
		}	
	}	
	
	// No. 12-13
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^No\.\s*(?<volume_from>\d+)-(?<volume_to>\d+)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];		
			$info->volume_to = $m['volume_to'];		
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// no. 272-280 (1999)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^no\.\s*(?<volume_from>\d+)-(?<volume_to>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];		
			$info->volume_to = $m['volume_to'];		
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	// bd. 1 1911-12
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd\.\s*(?<volume>\d+)\s+(?<year_from>[0-9]{4})-(?<year_to>[0-9]{2})$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];		
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];	
			$matched = true;
		}	
	}	
	
	
	// bd. 3-4 1914-16
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd\.\s*(?<volume_from>\d+)-(?<volume_to>\d+)\s+(?<year_from>[0-9]{4})-(?<year_to>[0-9]{2})$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];	
			$matched = true;
		}	
	}	
	
	
	// Vol.82
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^[V|v]ol\.\s*(?<volume>\d+)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];		
			$matched = true;
		}	
	}	

	// Vols 87-88
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Vols\s*(?<volume_from>\d+)-(?<volume_to>\d+)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];		
			$info->volume_to = $m['volume_to'];		
			$matched = true;
		}	
	}	
	
	// new. ser.:v.43 (1900):text
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^new.? ser.:v.(?<volume>[0-9]+)\s+\((?<year_from>[0-9]{4})(-(?<year_to>[0-9]{4}))?\)(:text)?$/i", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];		
			if ($m['year_to'] != '')
			{
				$info->end = $m['year_to'];	
			}
			
			$matched = true;
		}
	}		
	
	// n.s., v.55, 1910
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^n.s.,. v.(?<volume>[0-9]+)\s+(?<year_from>[0-9]{4})(-(?<year_to>[0-9]{4}))?$/i", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];		
			if ($m['year_to'] != '')
			{
				$info->end = $m['year_to'];	
			}
			
			$matched = true;
		}
	}		
	
	
	// new ser.:v.27 (1886-1887):text
	
	// Vols 65, no 1 
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Vols\s+(?<volume>[0-9]+),\s+no\s+(?<issue>\d+)$/i", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];
			$matched = true;
		}
	}		
	
	// pt. 11, 1883
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^pt\.\s*(?<volume>[0-9]+),\s+(?<year>[0-9]{4})$/i", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}
	}		
	
	
	// 38 - part 2
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<volume>[0-9]+)\s+-\s+part\s+\d+$/i", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];	
			$matched = true;
		}
	}		
	
	// jahrg 45 - 46 (1913-14)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^jahrg\s+(?<volume_from>\d+)\s*-\s*(?<volume_to>\d+)\s+\((?<year_from>[0-9]{4})-(?<year_to>[0-9]{2})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];	
			$matched = true;
		}	
	}	
	
	
	// Nr.1-50 (1957-1960)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Nr.(?<volume_from>\d+)-(?<volume_to>\d+)\s+\((?<year_from>[0-9]{4})-(?<year_to>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year_from'];		
			$info->end = $m['year_to'];		
			$matched = true;
		}	
	}	
	
	
	
	// Bd. 1-2 (1918-19) 
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Bd\.?\s+(?<volume_from>\d+)-(?<volume_to>\d+)\s+\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}	
	}	
	
	
	
	// t. 70 fasc. 4 Dec 1963
	if (!$matched)
	{
		if (preg_match('/^t\.\s+(?<volume>\d+)\s+fasc\.\s+\d+\s+\w+\s+(?<year>[0-9]{4})$/Uu',$str,$m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}
	
	// t. 67 1960 suppl.
	if (!$matched)
	{
		if (preg_match('/^t\.\s+(?<volume>\d+)\s+(?<year>[0-9]{4})\s+suppl\.$/Uu',$str,$m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// t. 26, suppl. (1918)
	if (!$matched)
	{
		if (preg_match('/^t\.\s+(?<volume>\d+),\s+suppl\.\s+\((?<year>[0-9]{4})\)$/Uu',$str,$m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	
	// v. XLIII (1899)
	if (!$matched)
	{
		if (preg_match('/^[V|v]\.\s+(?<volume>[XLVICM]+)\s+\((?<year>[0-9]{4})\)$/Uu',$str,$m))
		{
			$info->volume = arabic($m['volume']);
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// ser.1, v. 16 (1880-81)
	if (!$matched)
	{
		if (preg_match('/^ser\.\s*(?<series>\d+),\s*v\.\s*(?<volume>\d+)\s+\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})\)$/Uu',$str,$m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}	
	}	
	
	// ser.3:v.28 (1884)
	if (!$matched)
	{
		if (preg_match('/^ser\.(?<series>\d+):v\.(?<volume>\d+)\s+\((?<year>[0-9]{4})\)$/Uu',$str,$m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// Jan-Mai 1882 
	if (!$matched)
	{
		if (preg_match('/^[A-Z][a-z]+\-[A-Z][a-z]+\s+(?<year>[0-9]{4})$/Uu',$str,$m))
		{
			$info->volume = $m['year'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	
	// Jahrg. 44 (1891)
	if (!$matched)
	{
		if (preg_match('/^Jahrg.\s+(?<volume>\d+)\s+\((?<year>[0-9]{4})\)$/Uu',$str,$m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
			
	// d. 1 1901-05
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^d\.\s+(?<volume>\d+)\s+(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}	
	}	
	
	
	
	// 79th 1958-59
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^\d+(st|th|rd|nd)\s+(?<volume>[0-9|-]+)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	
	// v. 2-3 (1916-1917)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\.?\s*(?<volume_from>\d+)-(?<volume_to>\d+)\s+\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}	
	}	
	
	
	// v 12-13 (1916-19)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\.?\s+(?<volume_from>\d+)-(?<volume_to>\d+)\s+\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})\)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}	
	}	
	
	
	
	// t.49-50 (1900)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^t\.(?<volume_from>\d+)-(?<volume_to>\d+)\s+\((?<year>[0-9]{4})\)$/i", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['year'];
			$matched = true;
		}
	}	
	


	// ser.3:t.4 (1905)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^ser\.(?<series>\d+):t\.(Vol\s+)?(?<volume>[0-9]+)\s+\((?<yearstart>[0-9]{4})(-(?<yearend>[0-9]{4}))?\)$/i", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->series = $m['series'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
	}	
	
	
	// Vol 22 (3rd Series)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(Vol\s+)?(?<volume>[0-9]+)\s+\((?<series>\d+)(nd|st|rd|th) Series\)$/i", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->series = $m['series'];
			$matched = true;
		}
	}	
	
	
	// 53 part 2
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<volume>[0-9]+)\s+part\s+\d+$/i", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}
	}	
	
	// 55 number 1 part 2
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<volume>[0-9]+)\s+number\s+(?<issue>\d+)\s+part\s+(\d+)$/i", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];
			$matched = true;
		}
	}	
	
	
	// t.6, 1881
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^t\.\s*(?<volume>[0-9]+)\,\s+(?<year>[0-9]{4})$/i", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
	}	
	

	// 13.Bd. (1883-1884)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]+)\.Bd\.\s+\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)/i", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
	}	
	
	
	// f.56 (Simenchelys parasiticus)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^f\.(?<volume>\d+)\s+\((.*)\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	// Dec.1-5 
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Dec\.(?<volume_from>\d+)-(?<volume_to>\d+)$/Uu", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$matched = true;
		}	
	}	
	
	
	// ser. 2, v. 1 (1881-1883)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^ser\.\s+(?<series>\d+),\s*v.\s*(?<volume>[0-9]+)\s+\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)$/i", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
	}	
	
	
	// an. 1920, 72: T. 83 
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^an.\s*(?<year>[0-9]{4})[:|,](.*)[T|t].\s*(?<volume>\d+)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];		
			$info->start = $m['year'];		
			$matched = true;
		}	
	}
	
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\.(?<volume>\d+)\s+(?<year>[0-9]{4})$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];		
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	
	// Jahrig. 38, bd. 1 (1872)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Jahrig.\s+(?<volume>\d+),\s+bd.\s+(\d+(-\d+)?)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];		
			$info->start = $m['year'];		
			$matched = true;
		}	
	}	
	
	

	// 22 Part 9
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)\s+Part\s+(?<issue>\d+)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];		
			$info->issue = $m['issue'];
			$matched = true;
		}	
	}	
	
	// vol.72(1908)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^vol\.(?<volume>\d+)\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];		
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// jahrg. 20 (1902)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^jahrg.\s+(?<volume>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];		
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	//jahrg. 30, beiheft 5, 6, 8, 9 , 11 (1912) 
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^jahrg.\s+(?<volume>\d+),\s+beiheft (.*)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];		
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	
	// 1902, v. 2 (May-Dec.)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<year>[0-9]{4}),\s+v\.\s+(?<volume>\d+)\s+\(\w+\.?-\w+\.?\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];		
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	//
	// Jahrg.1892 
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Jahrg.(?<year>[0-9]{4})$/Uu", $str, $m))
		{
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	
	// v. 40-45 1991-96 (Inc.)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\.\s+(?<volume_from>\d+)\s*-\s*(?<volume_to>\d+)\s+(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})\s+/Uu", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}	
	}	
	
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Bd\.\s*(?<volume>\d+)$/Uiu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	
	
	// Bd.47, 1916
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Bd\.(?<volume>\d+),\s+(?<year>[0-9]{4})$/Uiu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// v.34:pt.1 (1951)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\.\s*(?<volume>\d+):(pt|no)\.(?<issue>\d+(-\d+)?)\s+\((?<year>[0-9]{4})\)$/Uiu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	

	//  v.51:no.12-25 (1978-1980) 
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\.\s*(?<volume>\d+)(:(pt|no)\.(\d+-\d+))?\s+\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)$/Uiu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year_from'];
			$info->end = $m['year_to'];
			$matched = true;
		}	
	}	
	
	
	// no.36
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^no\.\s*(?<volume>\d+)$/Uiu", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	

	// Stuttgarter Beiträge zur Naturkunde
	// nr. 588 1999
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^nr\.\s*(?<volume>\d+)\s*(?<year>[0-9]{4})$/Uiu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// nr. 506-517 1994
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^nr\.?\s+(?<volume_from>\d+)-(?<volume_to>\d+)\s+(?<yearstart>[0-9]{4})(\-(?<yearend>[0-9]{2}))?$/Uu", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['yearstart'];
			
			if ($m['yearend'] == '')
			{
				$info->end = $info->start;
			}
			else
			{
				$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			}
			$matched = true;
		}	
	}	

	
	// n.s. no.77(1994)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/n.s. no.\s*(?<volume>\d+)\s*\((?<year>[0-9]{4})\)/Uiu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// an.1902:54th
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^an.\s*(?<year>[0-9]{4}):(?<volume>\d+)th/Uiu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// bd. 26 heft 1 Mar 2003
	// Bd. 27 :heft 2 (2004: July)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd.\s+(?<volume>\d+)\s+[:]?heft\.?\s+(?<issue>\d+)\s+(\w+\s+|\()(?<year>[0-9]{4})(: \w+\.?\))?$/Uiu", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
		
	
	}
	
	
	
	// 6e s&#233;r.:t.3e (1883)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<series>\d+)e\s+s&#233;r.:t.(?<volume>\d+)e\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
		
	
	}
	
	
	// 4 (1970)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// t. 12, Index t. 1-12 (1904)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^t. (?<volume>\d+), Index t. 1-12 \((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// ser. 7, t.11-12 (1886-88)
	// ser 10, t. 5-8 (1913-16)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^ser\.?\s+(?<series>\d+),\s+t\.\s*(?<volume_from>\d+)\s*-\s*(?<volume_to>\d+)\s+\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})\)$/Uu", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}	
	}	
	
	// ser. 9, t. 5 (1902-03)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^ser\.?\s+(?<series>\d+),\s+t\.\s*(?<volume>\d+)\s+\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})\)$/Uu", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}	
	}
	
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^ser\.?\s+(?<series>\d+),\s+t\.\s*(?<volume>\d+)\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	

	// 36.Jahrg. (1891) 
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)\.\s*Jahrg\.\s+\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	

	
	// jahrg. 29 1912 
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^jahrg.\s*(?<volume>\d+) (?<year>[0-9]{4})$/Uui", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	

	// jahrg. 32-34 1915-17
	if (!$matched)
	{
		if (preg_match("/^jahrg.\s*(?<volume_from>\d+)-(?<volume_to>\d+) (?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})/Uui", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
	}
	
	// bd. 35 pt. 1-4 1921
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd. (?<volume>\d+) pt. (?<issue>\d+-\d+) (?<year>[0-9]{4})$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}	
	
	// 4th ser. v. 12, p. 695-1320
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<series>\d+)th ser. v. (?<volume>\d+),? p[t]?/Uu", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$matched = true;
		}	
	}	
	
	// no. 202 v. 2 1960
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/no.\s+(?<volume>\d+) v. \d+ (?<year>[0-9]{4})$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}
	
	
	// Jaarg.1 (1863)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/Jaarg\.(?<volume>\d+)\b(.*)\((?<year>[0-9]{4})\)$/Uu", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}
	
	// 4e sér.:t.10e (1870)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<series>\d+)e sér.:t.(?<volume>\d+)e \((?<year>[0-9]{4})\)$/", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}
	
	
	// v. 1F (1958)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v. (?<volume>\d+[A-Z]) \((?<year>[0-9]{4})\)$/", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}

	// v. 1D (1956-57)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v. (?<volume>\d+[A-Z]) \((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})\)$/", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}	
	}
	
	
	// no. 86 (June 1999)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^no. (?<volume>\d+) \(\w+ (?<year>[0-9]{4})\)$/", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}
	
	// arg. 59 (1902)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^arg. (?<volume>\d+) \((?<year>[0-9]{4})\)$/", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}
	
	
	// n.s., v. 4 1856-58
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/n.s., v. (?<volume>\d+) (?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})/", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}	
	}
	
	// 3rd ser., v. 3 1864-69
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<series>\d+)rd ser., v. (?<volume>\d+) (?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})/", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}	
	}
	
	
	// ser. 2 v. 6 (1894-1897)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/ser. (?<series>\d+) v. (?<volume>\d+) \((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)/", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}	
	}
	
	
	// 35-36, 1918-1920
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume_from>\d+)\-(?<volume_to>\d+),\s+(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})$/", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}	
	}

	// 34, 1917-1918
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+),\s+(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})$/", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}	
	}
	
	
	// v. 99- 100 1956-57
	if (!$matched)
	{
		if ($str == 'v. 99- 100 1956-57')
		{
			$info->volume_from = 99;
			$info->volume_to = 100;
			$info->start = 1956;
			$info->end = 1957;
			$matched = true;
			
		}	
	}
	
	// bd. 17-18 (1899-1900)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd\.\s+(?<volume_from>\d+)\-(?<volume_to>\d+)\s+\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)$/", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
	}
	
	
	// bd. 19-20 (1901-02)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd\.\s+(?<volume_from>\d+)\-(?<volume_to>\d+)\s+\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})\)$/", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
	}
	
	// v. 88/89 1977/78
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\.\s+(?<volume_from>\d+)\/(?<volume_to>\d+)\s+(?<yearstart>[0-9]{4})\/(?<yearend>[0-9]{2})/", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
	}
	
	// 1901, v. 1 (Jan.-Apr.)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]{4}),\s+v.\s+\d+\s+\($/", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}
	}
	
	// 1867 (incomplete)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]{4})\s+\(incomplete\)$/", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}
	}
	
	// 1921 v. 1-2
	if (!$matched)
	{
		if ($str == '1921 v. 1-2')
		{
			$info->volume = 1921;
			$matched = true;
		}
	}
	
	// Part 19  - Part 20 (1851-52)
	if (!$matched)
	{
		if ($str == 'Part 19  - Part 20 (1851-52)')
		{
			$info->volume_from = 1851;
			$info->volume_to = 1852;
			$matched = true;
		}
	}
	
	// [1908-1944]
	if (!$matched)
	{
		if ($str == '[1908-1944]')
		{
			$info->start = 1908;
			$info->end = 1944;
			$matched = true;
		}
	}
	
	
	// Vol 10 - Vol 10
	if (!$matched)
	{
		if ($str == 'Vol 10 - Vol 10')
		{
			$info->volume = 10;
			$matched = true;
		}
	}
	if (!$matched)
	{
		if ($str == 'Vol 20 - Vol 20')
		{
			$info->volume = 10;
			$matched = true;
		}
	}
	
	// v 11 (1914-15)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v (?<volume>\d+) \((?<yearstart>[0-9]{4})-(?<yearend>[0-9]{2})\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}	
	
	
	// 1923, pt. 3-4 (pp. 483-1097)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]{4}),\s*p[p|t]\./", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}
		
	}		
	
	// Vol 10 (3)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Vol (?<volume>[0-9]+) \((?<issue>[0-9]+)\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volume'];
			$info->issue = $m['issue'];
			$info->issue = $info->issue;
			$matched = true;
		}
		
	}	
	
	//
	
	
	// Band I - Band II
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Band (?<volumefrom>[XVI]+) - Band (?<volumeto>[XVI]+)/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = arabic($m['volumefrom']);
			$info->volume_to = arabic($m['volumeto']);
			$matched = true;
		}		
	}		
	
	// Band V
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Band (?<volume>[XVI]+)/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = arabic($m['volume']);
			$matched = true;
		}		
	}		
	
	
	// Bd.2.E.b
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Bd\.(?<volume>[0-9]+)\./", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}		
	}		
	
	
	// Jahrg. 74:bd. 2:heft 2 (1908)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Jahrg\.\s+(?<volume>[0-9]+):(.*)\((?<year>[0-9]{4})\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}		
	}
	
	// Jahrg. 73:bd. 2:heft 2: Lief. 3 - Jahrg. 73:bd. 2:
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Jahrg\.\s+(?<volume>[0-9]+):/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}		
	}		
	
	// 88.d. (1945)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)\.d\.\s+\((?<year>[0-9]{4})\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
	}		
	// 65./66. d. 1922/23
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volumefrom>\d+)\.\/(?<volumeto>\d+)\.\s*d\.\s*(?<yearstart>[0-9]{4})\/(?<yearend>[0-9]{2})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}		
	
	
	// bd. 2 (1901-04)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd.\s*(?<volume>\d+)\s*\((?<yearstart>[0-9]{4})-(?<yearend>[0-9]{2})\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}		
	
	
	
	// nuova ser.:v.1 (1901-1905)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^nuova ser.:v.(?<volume>[0-9]+)\s*\((?<yearstart>[0-9]{4})-(?<yearend>[0-9]{4})\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}	
	
	
	// 8 (Series 2)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)\s+\(Series\s+(?<series>\d+)\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->series = $m['series'];
			$matched = true;
		}
	}		
	
	// 1912 v. 59
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<year>1[8|9][0-9]{2}) v.\s*(?<volume>[0-9]{1,2})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
	}	
	
	// 1916-18 v. 63-65
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<yearstart>1[8|9][0-9]{2})-(?<yearend>[0-9]{2}) v. 
		(?<volumefrom>[0-9]{1,2})-(?<volumeto>[0-9]{1,2})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}		
	
	
	// 3. d. 1859
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]+).\s*(d\.|jaarg\.)\s*(?<year>[0-9]{4})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
	}	
	

	
	// 29. d. 1885/86
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]+).\s*(d\.|jaarg\.)\s*(?<yearstart>[0-9]{4})\/(?<yearend>[0-9]{2})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}	
	
	// 46./47. d. 1903/04
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\.\s*(?<volumefrom>[0-9]+).\s*(d\.|jaarg\.)\s*(?<yearstart>[0-9]{4})\/(?<yearend>[0-9]{2})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
	}			
	
	// Volume XI
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Volume\s+(?<volume>[XVI]+)$/", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume = arabic($m['volume']);
			$matched = true;
		}
	}		
	
	// 14, 1891
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]+),\s*(?<year>[0-9]{4})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
		
	}	
	
	// jahrg.5
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^jahr[g]?\.(?<volume>[0-9]+)$/u", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}
		
	}	
	
	// jahr.21-25
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^jahr[g]?\.(?<volumefrom>[0-9]+)([-|—](?<volumeto>[0-9]+))?$/u", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$matched = true;
		}
		
	}		
	
	//v.58-59
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\.\s*(?<volumefrom>[0-9]+)-(?<volumeto>[0-9]+)$/u", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$matched = true;
		}
		
	}	
	
	// 31 n.01
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volumefrom>[0-9]+)\s+n\.(?<issue>[0-9]+)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->issue = $m['issue'];
			$info->issue = preg_replace('/^0/', '', $info->issue);
			$matched = true;
		}
		
	}	


	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) { echo "Trying " . __LINE__ . "\n"; }
		if (preg_match("/^(?<year>[0-9]{4})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->start = $m['year'];
			$matched = true;
		}
		
	}
	
	// Volume 46
	// vol 4
	if (!$matched)
	{
		$m = array();
		
		if ($debug) { echo "Trying " . __LINE__ . "\n"; }
		if (preg_match("/^(Volume|vol) (?<volume>[0-9]+)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];			
			$matched = true;
		}
		
	}
	
	// no. 224 pt. 1 1962
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^no\.\s*(?<volume>[0-9]+)\s*pt\.\s*(?<issue>[0-9]+)\s*\(?(?<year>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];
			$info->start = $m['year'];
			$matched = true;
		}
		
	}
	
	// Bd.4
	if (!$matched)
	{
		$m = array();
		
		if ($debug) { echo "Trying " . __LINE__ . "\n"; }
		if (preg_match("/^Bd\.(?<volume>[0-9]+)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];			
			$matched = true;
		}
		
	}
	
	
	// Jahrg. 6, Bd. 1 (1840)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Jahrg\.\s*(?<volume>[0-9]+),\s*[B|b]d\.\s*(?<issue>[0-9]+)\s*\(?(?<year>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];
			$info->start = $m['year'];
			$matched = true;
		}		
	}
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}

	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}
	
	// Fieldiana Zoology v.24, no.16
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<title>.*)\s+v.(?<volume>[0-9]+), no.(?<issue>[0-9]+)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->title = $m['title'];
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];
			$matched = true;
		}
		
	}
	
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(no|v|t)\.\s*(?<volume>[0-9]+)(.*)\((?<yearstart>[0-9]{4})(?<yearend>[0-9]{4})\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(bd|v|t|ser|Haft)\.\s*(?<volume>[0-9]+)(.*)\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)$/i", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
	}	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(v|t|bd|Bd|anno|Haft)\.?\s*(?<volume>[0-9]+)\s*\(?(?<year>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
		
		
	}


	
	// no.180 (1996)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^no\.\s*(?<volume>[0-9]+)\s*\(?(?<year>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
		
	}
	
	// bd.52, 1921
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd\.\s*(?<volume>[0-9]+),\s*(?<year>[0-9]{4})?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
		
	}
	
	// e.g. Ann. mag. nat. hist., Proc Calif Acad Sci
	// 3rd ser. v. 8 (1861) 
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<series>[0-9]+)(rd|th|nd)\.?\s+ser.\s*v.\s*(?<volume>[0-9]+)\s*\(?((?<yearstart>[0-9]{4})(\-(?<yearend>[0-9]{4}))?)?\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			if (isset($m['yearstart']))
			{
				$info->start = $m['yearstart'];
			}
			if (isset($m['yearend']))
			{
				$info->end = $m['yearend'];
			}
			$info->series = $m['series'];
			$matched = true;
		}
		
	}
	
	// *** WARNING *** Line:369 Not matched "4th ser. v. 41 1977-79"<
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<series>[0-9]+)(rd|th|nd)\.?\s+ser.\s*v.\s*(?<volume>[0-9]+)\s*\(?((?<yearstart>[0-9]{4})(\-(?<yearend>[0-9]{2})))\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$info->series = $m['series'];
			$matched = true;
		}
		
	}	
	
	// this one is v dangerous as has (*.) in middle, use only as last resort...!!!!!!
/*	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]+)(.*)\(?(?<year>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
		
	}
*/	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(no|v|t)\.\s*((?<volumefrom>[0-9]+)\-(?<volumeto>[0-9]+))(.*)\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}
	
	// no. 85-93 1991-92
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(no|v|t)\.\s*((?<volumefrom>[0-9]+)\-(?<volumeto>[0-9]+))(.*)\(?(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}	
	
	// no. 85-93 1991-92
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(Bd|no|v|t)\.\s*(?<volume>[0-9]+)(.*)\(?(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}	
	
	
	// v. 1-2 (1814-1826)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(no|v|t)\.\s*((?<volumefrom>[0-9]+)\-(?<volumeto>[0-9]+))(.*)\(?(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}	
	
	// ser.2 t.1-2 1895-1897
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(ser)\.\s*(?<series>[0-9]+)\s*t\.((?<volumefrom>[0-9]+)\-(?<volumeto>[0-9]+))\s*\(?(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->series = $m['series'];
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}	
	
	// ser.3, v. 6, 1914
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^ser\.\s*(?<series>[0-9]+),?\s*[v|t]\.\s*(?<volume>[0-9]+),?\s*(.*)\(?(?<year>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
		
	}
	
	// new ser.:v.44 (1900-1901):plates
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(new )ser\.\s*[,|:]?\s*[v|t]\.\s*(?<volume>[0-9]+)\s*\(?((?<yearstart>[0-9]{4})(\-(?<yearend>[0-9]{4}))?)?\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->series ='new series';
			$info->volume = $m['volume'];
			if (isset($m['yearstart']))
			{
				$info->start = $m['yearstart'];
			}
			if (isset($m['yearend']))
			{
				$info->end = $m['yearend'];
			}
			$matched = true;
		}
		
	}
	
	
	// No date, just volume (e.g. Bull brit Mus Nat Hist)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^((Vol|tome\.?)\s*)?(?<volume>[0-9]+[A-Z]?)$/", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}
		
	}	
	
	// Tome 20
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Tome\s+(?<volume>[0-9]+)$/", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}
		
	}	
	

	// 18 and index to v.1-17
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]+) and index/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}
		
	}	
	
	// 22-24
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volumefrom>[0-9]+)\-(?<volumeto>[0-9]+)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$matched = true;
		}
		
	}	

	// 20:pt.1
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]+):/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}
		
	}	
	
	// v.33, no.1
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\.\s*(?<volume>[0-9]+)(, (no|pt).\s*(?<issue>[0-9]+))?/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			if (isset($m['issue']))
			{
				$info->issue = $m['issue'];
			}
			$matched = true;
		}
		
	}	
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/new series,? no.\s*(?<volume>[0-9]+)?/i", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->series = 'new series';
			$matched = true;
		}
		
	}	
	
	return $matched;
}
	
if (0)
{
	

	$dates = array();
	$failed = array();
	
	array_push($dates, '[Monaco]Impr. de Monaco,1889-19<14>');
	array_push($dates, 'Lille :Le Bigot,1888-[1895]');
	array_push($dates, 'London,Longmans, Green and Co.,1922.');
	array_push($dates, 'Copenhagen,[G. & C. Gads Forlag],1910.');
	array_push($dates, 'Boston,S.E. Cassino and company,1884-85.');

	
	$ok = 0;
	foreach ($dates as $str)
	{
		$info = new stdclass;
		$matched = bhl_date_from_details($str, $info);
		
		if ($matched)
		{
			$ok++;
			
			print_r($info);
		}
		else
		{
			array_push($failed, $str);
		}
	}
	
	// report
	
	echo "--------------------------\n";
	echo count($refs) . ' dates, ' . (count($dates) - $ok) . ' failed' . "\n";
	print_r($failed);
}

	
	
//--------------------------------------------------------------------------------------------------
/**
 * @brief Test parse_bhl_date function using a range of test cases
 *
 */
function test_parse_bhl_date()
{
	

	$dates = array();
	$failed = array();
	
	array_push($dates, 'v.35:pt.1 (1952)');
	array_push($dates, 'v.15 (1961-1966)');
	array_push($dates, 'v. 34 (1921)');
	array_push($dates, 'no.180 (1996)');
	array_push($dates, 'no.296-325 (1968-1969)');
	array_push($dates, 'no. 85-93 1991-92');
	array_push($dates, 'v. 1-2 1991-24');
	array_push($dates, 'v. 1-2 (1814-1826)');
	array_push($dates, 'v. 39, no. 2 (1996)');
	array_push($dates, 'v. 85, no. 1-4 (1986)');

	array_push($dates, 't. 4 (1891-1892)');
	array_push($dates, 't. 17-18 1882-85');
	array_push($dates, 't. 17; (ser. 2, t.7) (1889)');

	array_push($dates, 't. 3 no. 3-4 marzo-abr 1920');

	array_push($dates, 'new ser. v. 1 (1883-1886)');
	array_push($dates, 'v. 5 (18501851)');
	array_push($dates, 'no. 138 1926');
	
	array_push($dates, '3rd ser. v. 8 (1861) ');
	
	array_push($dates, 'new ser.:v.5');
	array_push($dates, 'new ser.:v.45 (1901-1902)');
	array_push($dates, 'new ser. v. 19 (1906-1907)');
	
	$dates[] = 'v. 58-59';

	
	$ok = 0;
	foreach ($dates as $str)
	{
		$info = new stdclass;
		$matched = parse_bhl_date($str, $info);
		
		if ($matched)
		{
			$ok++;
			
			print_r($info);
		}
		else
		{
			array_push($failed, $str);
		}
	}
	
	// report
	
	echo "--------------------------\n";
	echo count($refs) . ' dates, ' . (count($dates) - $ok) . ' failed' . "\n";
	print_r($failed);
}

if (0)
{
	test_parse_bhl_date();
}

	
	
?>