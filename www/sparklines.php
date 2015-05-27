<?php

// Sparklines

/**
 * @file sparklines.php
 *
 */

require_once ('../db.php');

define('START_DATE', '2009-12-20');

//--------------------------------------------------------------------------------------------------
// get number of days since project started
function days_since_start()
{
	$date_diff = time() - strtotime(START_DATE);
	$time_span = floor($date_diff/(60*60*24));
	return $time_span;
}

//--------------------------------------------------------------------------------------------------
// Generate a sparkline (normalises values)
function make_sparkline($values,$max_value,$width=200,$height=50)
{
	// Normalise
	$n = count($values);
	for ($i = 0; $i < $n; $i++)
	{
		$values[$i] = round(($values[$i] * 100.0)/$max_value);
	}

	$url = 'http://chart.apis.google.com/chart?chs=' . $width . 'x' . $height . '&cht=ls&chco=0077CC&chm=B,e6f2fa,0,0.0,0.0&chd=t:';

	$url .= join(",", $values);
	
	return $url;
}

//--------------------------------------------------------------------------------------------------
// Sparkline of articles added to a journal, units are days
function sparkline_articles_added_for_issn($issn)
{
	global $db;
	
	// Get daily counts of articles created since start of project
	$sql = 'SELECT count(reference_id) AS c, year(created), month(created), day(created), 
	datediff(created, ' . $db->qstr(START_DATE) . ') AS days, created FROM rdmp_reference 
	WHERE issn=' . $db->qstr($issn) . '
	GROUP BY day(created)
	ORDER BY created';
	
	$time_span = days_since_start();
	
	// Initialise array
	$count = array();
	for ($i = 0; $i < $time_span; $i++)
	{
		$count[$i] = 0;
	}
	
	$max_count = 0;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		$count[$result->fields['days']] = $result->fields['c'];
		$max_count = max($max_count, $result->fields['c']);
		$result->MoveNext();
	}
	
	return make_sparkline($count, $max_count, 100, 50);
}

//--------------------------------------------------------------------------------------------------
// Sparkline of articles added to database
function sparkline_cummulative_articles_added()
{
	global $db;
	
	// Get daily counts of articles created since start of project
	$sql = 'SELECT count(reference_id) AS c, year(created), month(created), day(created), 
	datediff(created, ' . $db->qstr(START_DATE) . ') AS days, created FROM rdmp_reference 
	GROUP BY days
	ORDER BY created';
	
	$time_span = days_since_start();
	
	// Initialise array
	$count = array();
	for ($i = 0; $i < $time_span; $i++)
	{
		$count[$i] = 0;
	}
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	$running_total = 0;
	
	while (!$result->EOF) 
	{
		$running_total += $result->fields['c'];
		$count[$result->fields['days']] = $running_total;
		$result->MoveNext();
	}
	
	for ($i = 1; $i < $time_span; $i++)
	{
		if ($count[$i] == 0)
		{
			$count[$i] = $count[$i-1];
		}
	}
	
	
	return make_sparkline($count, $running_total, 200, 100);
}

//--------------------------------------------------------------------------------------------------
// Sparkline of author references over time
function sparkline_author_articles($author_id, $width=300, $height=50)
{
	global $db;
	
	$author_cluster_id = db_get_author_cluster_id($author_id);	
	
	$sql = 'SELECT year, count(reference_id) AS c FROM rdmp_reference
INNER JOIN rdmp_author_reference_joiner USING (reference_id)
INNER JOIN rdmp_author USING (author_id)
WHERE (author_cluster_id = ' . $author_cluster_id . ')
GROUP BY YEAR
ORDER BY year';

	$start_year = 3000;
	$end_year = 0;
	$max_value = 0;

	$count = array();

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		$start_year = min($start_year, $result->fields['year']);
		$end_year = max($end_year, $result->fields['year']);
		$max_value = max($max_value, $result->fields['c']);
		$count[$result->fields['year']] = $result->fields['c'];
		$result->MoveNext();
	}
	
	$start_year = floor($start_year/10) * 10;
	$end_year = floor(($end_year+9)/10) * 10;
	
	for($i = $start_year; $i <= $end_year; $i++)
	{
		if (!isset($count[$i]))
		{
			$count[$i] = 0;
		}
	}
	
	ksort($count);
	
	// Axes
	$decades = array();
	for($i = $start_year; $i <= $end_year; $i+=10)
	{
		$decades[] = $i;
	}
	
	$values = array();
	foreach ($count as $k => $v)
	{
		$values[] = round(($v * 100.0)/$max_value);
	}

	$url = 'http://chart.apis.google.com/chart?chs=' . $width . 'x' . $height . '&cht=ls&chco=0077CC&chm=B,e6f2fa,0,0.0,0.0&chd=t:';
	$url .= join(",", $values);
	$url .= '&chxt=x,y&chxl=0:|' . join("|", $decades) .  '|1:||' . $max_value;
	
	return $url;
}


//--------------------------------------------------------------------------------------------------
// Sparkline of references over time
function sparkline_references($issn = '', $oclc = '', $width=400, $height=50)
{
	global $db;
	
	$sql = 'SELECT year, count(reference_id) AS c FROM rdmp_reference';
	
	$sql .= ' WHERE (year IS NOT NULL) AND (year != "") AND (year != "YYYY")';
	if ($issn != '')
	{
		$sql .= ' AND (issn=' . $db->qstr($issn) . ')';
	}
	if ($oclc != '')
	{
		$sql .= ' AND (oclc=' . $oclc . ')';
	}
	$sql .= ' AND (PageID <> 0)';
	$sql .= ' GROUP BY year
ORDER BY year';

	$start_year = 3000;
	$end_year = 0;
	$max_value = 0;

	$count = array();

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		$start_year = min($start_year, $result->fields['year']);
		$end_year = max($end_year, $result->fields['year']);
		$max_value = max($max_value, $result->fields['c']);
		$count[$result->fields['year']] = $result->fields['c'];
		$result->MoveNext();
	}
	
//	$start_year = floor($start_year/10) * 10;
//	$end_year = floor(($end_year+9)/10) * 10;

	$start_year = floor($start_year/50) * 50;
	$end_year = floor(($end_year+49)/50) * 50;
	
	for($i = $start_year; $i <= $end_year; $i++)
	{
		if (!isset($count[$i]))
		{
			$count[$i] = 0;
		}
	}
	
	ksort($count);
	
	// Axes
	$decades = array();
//	for($i = $start_year; $i <= $end_year; $i+=10)
	for($i = $start_year; $i <= $end_year; $i+=50)
	{
		$decades[] = $i;
	}
	
	$values = array();
	foreach ($count as $k => $v)
	{
		$values[] = round(($v * 100.0)/$max_value);
	}

	$url = 'http://chart.apis.google.com/chart?chs=' . $width . 'x' . $height . '&amp;cht=ls&amp;chco=0077CC&amp;chm=B,e6f2fa,0,0.0,0.0&amp;chd=t:';
	$url .= join(",", $values);
	$url .= '&amp;chxt=x,y&amp;chxl=0:|' . join("|", $decades) .  '|1:||' . $max_value;
	
	return $url;
}

//--------------------------------------------------------------------------------------------------
// Sparkline of name over BHL
// hits comes from bhl_name_search
function sparkline_bhl_name($hits, $width=400, $height=100)
{
	$start_year = 3000;
	$end_year = 0;
	$max_value = 0;
	
	$count = array();
	
	//print_r($hits);
	
	foreach ($hits as $hit)
	{
		if (isset($hit->info->start))
		{	
			$year = $hit->info->start;
			$start_year = min($start_year, $year);
			$end_year = max($end_year, $year);
			if (!isset($count[$year]))
			{
				$count[$year] = 0;
			}
			$count[$year]++;
			$max_value = max($max_value, $count[$year]);
		}
	}

	$start_year = floor($start_year/50) * 50;
	$end_year = floor(($end_year+49)/50) * 50;
	
	for($i = $start_year; $i <= $end_year; $i++)
	{
		if (!isset($count[$i]))
		{
			$count[$i] = 0;
		}
	}
	
	ksort($count);
	
	// Axes
	$decades = array();
	for($i = $start_year; $i <= $end_year; $i+=50)
	{
		$decades[] = $i;
	}
	
	$values = array();
	foreach ($count as $k => $v)
	{
		$values[] = round(($v * 100.0)/$max_value);
	}

	$url = 'http://chart.apis.google.com/chart?chs=' . $width . 'x' . $height . '&amp;cht=ls&amp;chco=0077CC&amp;chm=B,e6f2fa,0,0.0,0.0&amp;chd=t:';
	$url .= join(",", $values);
	$url .= '&amp;chxt=x,y&amp;chxl=0:|' . join("|", $decades) .  '|1:||' . $max_value;

	return $url;
}



// test
if (0)
{
$issn = '0006-9698';
sparkline_articles_added_for_issn($issn);
}


?>
