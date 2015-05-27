<?php

/**
 * @file Information about BHL journal
 *
 */

require_once(dirname(__FILE__) . '/db.php');
require_once(dirname(__FILE__) . '/bhl_search.php');

//--------------------------------------------------------------------------------------------------
function bhl_articles_for_issn ($issn)
{
	global $db;

	$sql = 'SELECT COUNT(reference_id) AS c
	FROM rdmp_reference WHERE (issn=' . $db->qstr($issn) . ')';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	$num_articles = $result->fields['c'];
	
	return $num_articles;
}

//--------------------------------------------------------------------------------------------------
function bhl_articles_for_oclc ($oclc)
{
	global $db;

	$sql = 'SELECT COUNT(reference_id) AS c
	FROM rdmp_reference WHERE (oclc=' . $oclc . ') AND (genre="article")';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	$num_articles = $result->fields['c'];
	
	return $num_articles;
}

//--------------------------------------------------------------------------------------------------
function bhl_num_pages_in_item ($ItemID)
{
	global $db;

	$sql = 'SELECT COUNT(PageID) as c FROM page
	WHERE ItemID = ' . $ItemID;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	$num_pages = $result->fields['c'];
	
	return $num_pages;
}

//--------------------------------------------------------------------------------------------------
function bhl_item_page_coverage($ItemID)
{
	global $db;
	
	$sql = 'SELECT SequenceOrder FROM page
	INNER JOIN rdmp_reference_page_joiner USING(PageID)
	WHERE ItemID = ' . $ItemID . '
	ORDER BY SequenceOrder';
	
	$pages = array();
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		$pages[] = $result->fields['SequenceOrder'];
		$result->MoveNext();		
	}
	
	$coverage = array();
	
	$last = -1;
	$start = -1;
	$end = 0;
	foreach ($pages as $k => $v)
	{
		if (($v-1) == $last)
		{
			// continuation
			$end = $v;
		}
		else
		{
			if ($last != -1)
			{
				$c = new stdclass;
				$c->start = $start;
				$c->end = $end;
				$coverage[] = $c;
			}
		
			// new
			$start = $v;
			$end = $v;
		}
		$last = $v;
	}
	if ($start != -1)
	{
		$c = new stdclass;
		$c->start = $start;
		$c->end = $end;
		$coverage[] = $c;
	}	
	return $coverage;
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Retrieve list of BHL titles for a ISSN
 *
 * List is based on manual matching in database table rdmp_issn_title_joiner, as well as by
 * implication from rdmp_reference_page_joiner
 *
 * @param issn ISSN of journal
 *
 * @return Array of BHL TitleIDs
 *
 */
function bhl_titles_for_issn($issn)
{
	global $db;
	
	$titles = array();
	
	// 1. We have a mapping of ISSN to titles
	$sql = 'SELECT * FROM rdmp_issn_title_joiner WHERE (issn=' . $db->qstr($issn) . ')';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
			
	while (!$result->EOF) 
	{
		$titles[] = $result->fields['TitleID'];

		$result->MoveNext();		
	}
	//print_r($titles);
	
	// 2. We may also have discovered additional titles if individual articles are titles
	// e.g., Fieldiana
	
	$sql = 'SELECT DISTINCT(TitleID) FROM rdmp_reference
INNER JOIN rdmp_reference_page_joiner USING(reference_id)
INNER JOIN bhl_page ON bhl_page.PageID = rdmp_reference_page_joiner.PageID
INNER JOIN bhl_item USING(ItemID)
WHERE (issn=' . $db->qstr($issn) . ')';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
			
	while (!$result->EOF) 
	{
		$titles[] = $result->fields['TitleID'];

		$result->MoveNext();		
	}
	//print_r($titles);
	
	// 3. We might not have any articles from this ISSN yet
	
	$sql = 'SELECT TitleID FROM bhl_title_identifier
	WHERE (IdentifierName="ISSN") AND (IdentifierValue=' . $db->qstr($issn) . ')';
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
			
	while (!$result->EOF) 
	{
		$titles[] = $result->fields['TitleID'];

		$result->MoveNext();		
	}
	//print_r($titles);
	$titles = array_unique($titles);
	
	return $titles;
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Retrieve list of BHL titles for a OCLC
 *
 * List is based on manual matching in database table rdmp_issn_title_joiner, as well as by
 * implication from rdmp_reference_page_joiner
 *
 * @param oclc OCLC
 *
 * @return Array of BHL TitleIDs
 *
 */
function bhl_titles_for_oclc($oclc)
{
	global $db;
	
	$titles = array();
	
	// 1. We have a mapping of ISSN to titles
	$sql = 'SELECT * FROM rdmp_oclc_title_joiner WHERE (oclc=' . $db->qstr($oclc) . ')';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
			
	while (!$result->EOF) 
	{
		$titles[] = $result->fields['TitleID'];
		$result->MoveNext();		
	}	
	
	$sql = 'SELECT TitleID FROM bhl_title_identifier
	WHERE (IdentifierName="OCLC") AND (IdentifierValue=' . $oclc . ')';
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
			
	while (!$result->EOF) 
	{
		$titles[] = $result->fields['TitleID'];

		$result->MoveNext();		
	}

	$titles = array_unique($titles);
	
	return $titles;
}

//--------------------------------------------------------------------------------------------------
function institutions_from_titles($titles)
{
	$institutions = array();
	foreach ($titles as $TitleID)
	{
		$who = bhl_institutions_with_title($TitleID);
		foreach ($who as $w)
		{
			if (!isset($institutions[$w->name]))
			{
				$institutions[$w->name] = 0;
			}
			$institutions[$w->name] += $w->count;
		}
	}
	return $institutions;
}

//--------------------------------------------------------------------------------------------------
function items_from_titles($titles, &$items, &$volumes)
{
	global $db;
	
	$count = 0;
	
	foreach ($titles as $TitleID)
	{	
		$sql = 'SELECT * FROM bhl_item WHERE TitleID=' . $TitleID;
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
			
		while (!$result->EOF) 
		{
			$item = new stdclass;
			$item->ItemID = $result->fields['ItemID'];
			$item->VolumeInfo = $result->fields['VolumeInfo'];
	
			$item->info = new stdclass;
			if (parse_bhl_date($result->fields['VolumeInfo'], &$item->info))
			{
				if (isset($item->info->volume))
				{
					$volumes[$item->info->volume] = $count;
				}
				else if (isset($item->info->volume_from))
				{
					$volumes[$item->info->volume_from] = $count;
				}
				else
				{
					$volumes[$result->fields['VolumeInfo']] = $count;
				}
			}
			else
			{
				$volumes[$result->fields['VolumeInfo']] = $count;
			}
						
			$items[] = $item;
			$count++;
			
			$result->MoveNext();		
		}
	}
	
	// Sort
	ksort($volumes);
	
	//print_r($volumes);
}

?>