<?php

/**
 * @file bhl_search.php
 *
 * Search BHL metadata
 *
 */

// Search BHL content for matches to OpenURL-style queries

// Functions to find PageID from volume and spage (using templates or regular expressions
// to extract info from VolumeInfo. Also need title search.
//

require_once (dirname(__FILE__) . '/config.inc.php');
require_once (dirname(__FILE__) . '/bhl_date.php');
require_once (dirname(__FILE__) . '/bioguid.php');
require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/lcs.php');
require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/bhl_utilities.php');


//--------------------------------------------------------------------------------------------------
function clean_string ($str)
{
	$str = str_replace ('.', '', $str);
	$str = preg_replace('/\s\s+/', ' ', $str);
	$str = trim($str);

	return $str;
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Retrieve details about a title from BHL database
 *
 * @param bhl_title_id BHL TitleID
 * @param obj Object we will populate
 *
 */
function bhl_title_retrieve ($bhl_title_id, &$obj)
{
	global $db;
	$PageID = array();	
	
	$sql = 'SELECT * FROM bhl_title
		INNER JOIN bhl_title_identifier USING(TitleID)
		WHERE (TitleID=' . $bhl_title_id . ')';
				
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		if (!isset($obj->FullTitle))
		{
			$obj->FullTitle = $result->fields['FullTitle'];
		}
	
		switch ($result->fields['IdentifierName'])
		{
			case 'ISBN':
				// may need to clean this
				$obj->ISBN = $result->fields['IdentifierValue'];
				break;
				
			default:
				$k = $result->fields['IdentifierName'];
				$obj->$k = $result->fields['IdentifierValue'];
				break;
		}
		$result->MoveNext();
	}
}


//--------------------------------------------------------------------------------------------------
// Return OCLC number for a BHL TitleID as an integer
function oclc_for_titleid($TitleID)
{
	global $db;
	
	$oclc = 0;
	
	$sql = 'SELECT * FROM bhl_title_identifier WHERE (IdentifierName="OCLC") AND (TitleID=' . $TitleID . ') LIMIT 1';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		// clean
		$oclc_string = $result->fields['IdentifierValue'];
		
		$oclc_string = preg_replace('/^ocm/', '', $oclc_string);
		$oclc_string = preg_replace('/^0/', '', $oclc_string);
		$oclc = $oclc_string;
	}
	
	if ($oclc == 0)
	{
		// local mapping
		$sql = 'SELECT * FROM rdmp_oclc_title_joiner WHERE (TitleID=' . $TitleID . ')';
	
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
				
		if ($result->NumRows() > 0)
		{
			// clean
			$oclc = $result->fields['oclc'];
		}
		
		
	}
	
	return $oclc;
}

//--------------------------------------------------------------------------------------------------
function oclc_for_title($title)
{
	global $debug;
	$oclc = 0;
	
	$hits = bhl_title_lookup($title);
		
	if (count($hits) > 0)
	{
		$TitleID = $hits[0]['TitleID'];
		$oclc = oclc_for_titleid($TitleID);
		
		if ($debug)
		{
			echo __FILE__ . ' line ' . __LINE__ . "\n";
			echo '<pre>';
			echo "OCLC=$oclc";
			echo '</pre>';
		}
	}	
	
	return $oclc;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Approximate string search for title
 *
 * Assumes n-gram index available for MySQL, see
 * http://iphylo.blogspot.com/2009/10/n-gram-fulltext-indexing-in-mysql.html for details on installing
 * this.
 *
 * @param str Title string to search for
 * @param threshold Percentage of str that we require to be in longest common subsequence (default is 75%)
 *
 * @return Array of matching titles, together with scores
 */
function bhl_title_lookup($str, $threshold = 70)
{
	global $db;
	
	$matches = array();
	
	$locs = array();
	
	$str = clean_string ($str);
	$str_length = strlen($str);
	
	$sql = 'SELECT TitleID, ShortTitle, MATCH(ShortTitle) AGAINST(' . $db->qstr($str) . ')
AS score FROM bhl_title
WHERE MATCH(ShortTitle) AGAINST(' . $db->qstr($str) . ') LIMIT 10';

//echo $sql;
	
	$lcs = array();
	$count = 0;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		// Get subsequence length
		$cleaned_hit = clean_string ($result->fields['ShortTitle']);
		$cleaned_hit_length = strlen($cleaned_hit);
		
		$C = LCSLength($cleaned_hit, $str);
		
		// length of subsequence as percentage of query string
		$subsequence_length =  round((100.0 * $C[$cleaned_hit_length][$str_length])/$str_length);
		
		// length of subsequence as percentage of hit
		$hit_subsequence_length = round((100.0 * $C[$cleaned_hit_length][$str_length])/$cleaned_hit_length);
		
		//echo $cleaned_hit . ' ' . $subsequence_length . ' ' . $hit_subsequence_length . '<br/>';
		
		if ($subsequence_length >= $threshold && $hit_subsequence_length >= 33) // added this stop v. bad matches in reverse direction
		{	
			array_push($matches, array(
				'TitleID' => $result->fields['TitleID'],
				'ShortTitle' => $result->fields['ShortTitle'],			
				'score' => $result->fields['score'],
				'sl' => $subsequence_length,
				'subsequence' => $C[$cleaned_hit_length][$str_length],
				'x' => $str,
				'y' => $cleaned_hit
				)
			);
			
			array_push ($lcs, array('row' => $count, 'subsequence' => $C[$cleaned_hit_length][$str_length]));
		}
		
		
		$count++;
		$result->MoveNext();
	}
	//print_r($lcs);
	$scores = array();
	$index = array();
	foreach ($lcs as $key => $row) 
	{
		$scores[$key]  = $row['subsequence'];
		$index[$key]  = $key;
	}
	array_multisort($scores, SORT_DESC, $index);
	//print_r($scores);
	//print_r($index);
	
	$scores = array();
	foreach ($matches as $m)
	{
		$scores[$m['TitleID']]  = $m['sl'] * $m['score'];

//		$scores[$m['TitleID']]  = $m['sl'];
//		$scores[$m['TitleID']]  = $m['score'];
	}
	array_multisort($scores, SORT_DESC, $matches);
	
	// sort by sl
	/*
	echo '<pre>';
	print_r($matches);
	echo '</pre>';
	*/
	
	return $matches;
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Retrieve BHL TitleID corresponding to ISSN
 *
 * @param issn ISSN of journal
 *
 * @return BHL TitleID if found, 0 if not found
 *
 */
function bhl_titleid_from_issn($issn)
{
	global $db;
	
	$TitleID = 0;
	
	$sql = 'SELECT * FROM bhl_title_identifier 
	WHERE (IdentifierName = "ISSN") 
	AND (IdentifierValue = ' . $db->qstr($issn) . ') LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$TitleID = $result->fields['TitleID'];
	}
	
	return $TitleID;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Return the title page(s) of an item
 *
 * For given ItemID returns array of PageIDs for which PageTypeName is 'Title Page'
 *
 * @param ItemID BHL ItemID
 *
 * @return array of PageIDs
 */
function bhl_title_page($ItemID)
{
	global $db;
	
	$pages = array();
	
	$sql = 'SELECT * FROM bhl_page 
	WHERE (ItemID = ' . $ItemID . ') 
	AND (PageTypeName = ' . $db->qstr('Title Page') . ')';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		array_push($pages, $result->fields['PageID']);
		$result->MoveNext();
	}
	
	return $pages;
}

//--------------------------------------------------------------------------------------------------
// Crude search for items based just on year (which for some journals is all we have in the VolumeInfo field
function bhl_itemid_from_year($TitleID, $year)
{
	global $db;
	global $debug;
		
	// Find ItemID of item that contains relevant volume
	$sql = 'SELECT * FROM bhl_item WHERE TitleID=' . $TitleID . ' AND ((VolumeInfo LIKE ' . $db->qstr('%' . $year . '%')
	 . ') OR (VolumeInfo LIKE ' . $db->qstr('%' . ($year-1) . '%') . '))';
	
	//echo $sql;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	$items = array();
		
	while (!$result->EOF) 
	{	
		$item = new stdclass;
		$item->ItemID = $result->fields['ItemID'];
		
		$items[] = $item;
			
		$result->MoveNext();
	}
		
	return $items;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Find BHL item(s) that correspond to a given volume
 *
 * We return an array because some volumes (such as volume 16 of J Hymenopt Research)
 * may span more than one item.
 *
 * @param TitleID BHL TitleID for journal
 * @param volume Volume we want
 * @param series Optional series 
 *
 * @return Array of BHL items
 *
 */
function bhl_itemid_from_volume($TitleID, $volume, $series = '')
{
	global $db;
	global $debug;
		
	// Find ItemID of item that contains relevant volume
	$sql = 'SELECT * FROM bhl_item WHERE TitleID=' . $TitleID;
	
	if ($debug)
	{
		echo __LINE__ . "<br />";
		echo $sql . '<br />';
	}
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if (0)
	{
		echo '<pre>';
		print_r($result);
		echo '</pre>';
	}

	$items = array();
		
	while (!$result->EOF) 
	{	
		$info = new stdclass;	
		$VolumeInfo = $result->fields['VolumeInfo'];
		$matched = parse_bhl_date($VolumeInfo, $info);
		
		if ($debug)
		{
			echo '<hr />';
			echo "VolumeInfo $VolumeInfo<br/>";
			echo $result->fields['ItemID'] . '<br/>';
			echo "Volume=$volume" . '<br/>';
		}
				
		if ($matched)
		{
			if ($debug)
			{
				echo '<pre>';
				print_r($info);
				echo '</pre>';
			}
			
			if (isset($info->volume_from))
			{
				// range, we store the volume offset of target volume
				if (($volume >= $info->volume_from) && ($volume <= $info->volume_to))
				{
					$item = new stdclass;
					$item->ItemID = $result->fields['ItemID'];
					if (isset($info->series))
					{
						$item->series = $info->series;
					}
					$item->volume_offset = $volume - $info->volume_from;
					array_push($items, $item);
				}				
			}
			else
			{
				// Volume is single number
				if ($info->volume == $volume)
				{
					$found = true;
					
					//echo "<b>found</b><br/>";
					
					$item = new stdclass;
					$item->ItemID = $result->fields['ItemID'];
					if (isset($info->series))
					{
						$item->series = $info->series;
						if ($series != '')
						{
							if ($info->series == $series)
							{
								$found = true;
							}
							else
							{
								$found = false;
							}
						}
					}
					
					//print_r($item);
					
					$item->volume_offset = 0;
					if ($found)
					{
						array_push($items, $item);
					}
				}
				else
				{
					// Volume might also match year
					if (isset($info->start) && ($info->start == $volume))
					{
						
						$item = new stdclass;
						$item->ItemID = $result->fields['ItemID'];
						
						$item->volume_offset = 0;
						array_push($items, $item);
					}
					
				}
			}
				
		}
		else
		{
			if ($debug)
			{
				echo '<pre>';
				echo "*** WARNING *** Line:" . __LINE__ . " Not matched \"" . $VolumeInfo . "\"<\n";
				echo '</pre>';
			}
		}
			
		$result->MoveNext();
	}
	
	if ($debug)
	{
			echo '<b>Items</b><br/>';
			echo '<pre>';
			print_r($items);
			echo '</pre>';
	}
	
	
	
	return $items;
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Find set of BHL items whose VolumeInfo field match a pattern
 *
 * Some articles have been treated as titles, e.g. large articles and monographs that are bound 
 * as single books. For these articles the journal and volume information may be contained in the
 * VolumeInfo field.
 *
 * @param search_pattern SQL search pattern, e.g. 'Fieldiana Zoology%'
 * @param mask_pattern Regular expression to remove title, e.g. '/^Fieldiana Zoology/'
 * @param volume Article volume we are searching for
 *
 * @result Array of BHL items that match query
 *
 */
function bhl_itemid_from_pattern($search_pattern, $mask_pattern, $volume)
{
	global $db;
	
	// Find ItemID of item that contains relevant volume
	$sql = 'SELECT * FROM bhl_item WHERE VolumeInfo LIKE ' . $db->qstr($search_pattern);
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	$items = array();
	
	while (!$result->EOF) 
	{
		$info = new stdclass;
		
		$VolumeInfo = $result->fields['VolumeInfo'];
		if ($mask_pattern != '')
		{
			$VolumeInfo = trim(preg_replace($mask_pattern, '', $VolumeInfo));
		}
		$matched = parse_bhl_date($VolumeInfo, $info);
		
		if ($matched)
		{
			
			if (isset($info->volume_from))
			{
				// range, we store the volume offset of target volume
				if (($volume >= $info->volume_from) && ($volume <= $info->volume_to))
				{
					$item = new stdclass;
					$item->ItemID = $result->fields['ItemID'];
					if (isset($info->series))
					{
						$item->series = $info->series;
					}
					$item->volume_offset = $volume - $info->volume_from;
					array_push($items, $item);
				}				
			}
			else
			{
				if ($info->volume == $volume)
				{
					$item = new stdclass;
					$item->ItemID = $result->fields['ItemID'];
					if (isset($info->series))
					{
						$item->series = $info->series;
					}
					
					$item->volume_offset = 0;
					array_push($items, $item);
				}
			}
				
		}
		else
		{
			if ($debug)
			{
				echo '<pre>';
				echo "*** WARNING *** Line:" . __LINE__ . " Not matched \"" . $VolumeInfo . "\"<\n";
				echo '</pre>';
			}
			
		}
		$result->MoveNext();
	}
	
	return $items;
}


/*
The Bulletin of the British Museum (Natural History) has several series, all with the same FullTitle,
so we first use bioguid to get ISSN for journal name, then retrieve TitleID using ISSN

2192 is Entomology, 2197 is Geology, 2198 is Botany, 2202 is Zoology, 5067 Historical

2192	Bulletin of the British Museum (Natural History).  	0524-6431
2197	Bulletin of the British Museum (Natural History).  	0007-1471
2198	Bulletin of the British Museum (Natural History).  	0068-2292
2202	Bulletin of the British Museum (Natural History).  	0007-1498
5067	Bulletin of the British Museum (Natural History).	0068-2306

*/

//--------------------------------------------------------------------------------------------------
/**
 * @brief Find article in BHL database
 *
 * @param title Title of journal
 * @param volume Volume containing article
 * @param page Page of article (typically this will be the first page in the article
 * @param series Optional series, used in cases where journal has multiple series with same 
 * volume numbers
 *
 * @return Search results as array of objects containing BHL PageID, score of article title match
 * (default = -1) and snippet showing alignment of article title to BHL text (empty string if no
 * article title supplied.
 *
 * <pre>
 * Array
 * (
 *     [0] => stdClass Object
 *         (
 *             [PageID] => 4467383
 *             [score] => 0.583333333333
 *             [snippet] => Kansas Lawrence, Kansas NUMBER 31...
 *         )
 * )
 *
 */
function bhl_find_article($atitle, $title, $volume, $page, $series = '', $date = '', $issn= '')
{
	global $db;
	global $debug;
	
	// Data structure to hold search result
	$obj = new stdclass;
	$obj->TitleID = 0;
	$obj->ISSN = $issn;
	$obj->ItemIDs = array();
	$obj->hits = array();
	
	
	// hack
	if ($title == 'Transactions of the Geological Society of London')
	{
		$title = 'Transactions of the Geological Society';
	}

	if ($title == 'Notul.Syst. (Paris)')
	{
		$title = 'Notulae systematicae';
	}
	
	
	
	
	// Step one
	// --------
	// Map journal title to BHL titles. We try to achieve this by first finding ISSN for title,
	// then querying BHL for that ISSN in the bhl_title_identifier table. If we don't have an ISSN,
	// or BHL doesn't have this ISSN then we try approximate string matching. This may return multiple
	// hits, for now we take the best one. If we still haven't found the title, it may be in the
	// VolumeInfo field (for example, large articles or monographs may be bound separately and hence
	// treated as individual titles, rather than as items of a title (e.g., Fieldiana). If still no
	// hits, we abandon search.
	
	// Can we do this via ISSN?	
	if ($obj->ISSN == '')
	{
		$obj->ISSN = issn_from_title($title);
	}
	if ($obj->ISSN != '')
	{
		$obj->TitleID = bhl_titleid_from_issn($obj->ISSN);
	}

	if ($debug)
	{
		echo __FILE__ . ' line ' . __LINE__ . ' ISSN = ' . $obj->ISSN . "\n";
	}
	
	// Special cases where mapping is tricky
	switch ($obj->ISSN)
	{
		case '0016-5301':
			$obj->TitleID = 40896;
			break;
			
	
		case '0373-6660':
			$obj->TitleID = 13345;
			break;
			
		// Apex
		case '0773-5251':
			$obj->TitleID = 63880;
			break;
			
		// Bonn zoological bulletin
		case '2190-7307':
			$obj->TitleID = 82521;
			break;
			
		
		// Bulletin de la Société philomathique de Paris
		case '0366-3515':
			$obj->TitleID = 9580;
			break;
			
		// Bulletin Du Museum Paris
		case '1148-8425':
			$obj->TitleID = 5943;
			break;
			
		// Bulletin of the Natural History Museum. Zoology series
		case '0968-0470':
			$obj->TitleID = 62642;
			break;
			
		// Bulletin of the Southern California Academy of Sciences
		case '0038-3872':
			$obj->TitleID = 4949;
			break;

		// Gayana
		case '0016-531X':
			$obj->TitleID = 39684;
			break;
			
		// Manila Phillipine J Sci D
		case '0031-7683':
			$obj->TitleID = 50545;
			break;
			
		// Novon
		case '1055-3177':
			$obj->TitleID = 744;
			break;
	
		// Stuttgarter Beiträge zur Naturkunde
		case '0341-0145':
			$obj->TitleID = 49174;
			break;
			
		// Transactions American Microscopical Society 
		case '0003-0023':
			$obj->TitleID = 37912;
			break;
			
		// Transactions of the Linnean Society
		case '1945-9432':
			$obj->TitleID = 2203;
			break;

		case '0771-0488':
			$obj->TitleID = 10603;
			break;
			
		case '1019-8563':
			$obj->TitleID =  15675;
			break;
			
		case '0177-7424':
			$obj->TitleID=42670;
			break;
			
		// Scientific papers of the Natural History Museum, University of Kansas
		case '1094-0782':
			$hits = bhl_title_lookup($atitle);
			if (count($hits) > 0)
			{
				if ($hits[0]['sl'] > 90)
				{
					$obj->TitleID = $hits[0]['TitleID'];
				}
			}
			break;
			
		case '0375-099X':
			$obj->TitleID = 10294;
			break;
		
		// Spixiana
		case '0341-8391':
			$obj->TitleID = 40214;
			break;
			
		// Iheringia. Série zoologia
		case '0073-4721':
			$obj->TitleID=50228;
			break;
			
		case '0084-5620':
			$obj->TitleID=45493;
			break;
			
		// Transactions of the Linnean Society of London. Zoology.
		case '1945-9440':
			$obj->TitleID=51416;
			break;
			
		// Zoological Science (Tokyo)
		case '0289-0003':
			$obj->TitleID = 61647;
			break;
			
		// Fieldiana
		case '0015-0754':
			$obj->TitleID = 5132;
			break;
		
		// Nature
		case '0028-0836':
			$obj->TitleID = 21368;
			break;

		default:
			break;
	}
	
	if ($debug)
	{
		echo __FILE__ . ' line ' . __LINE__ . ' TitleID = ' . $obj->TitleID . "\n";
	}
	
	/*$obj->ISSN = '0150-9322';
	$obj->TitleID = 4647;*/
	
	// Non-trivial cases just specifiy a match
	if ($obj->TitleID == 0)
	{
		switch ($title)
		{
			case 'Annales du Musee Zoologique Academie des Sciences St Peterburg':
			case 'Annales du Musee Zoologique St Peterburg':
			case 'Ann Mus St Petersburg':
			case 'St Petersburg Ann mus zool':
				$obj->TitleID = 8097;
				break;
				
			default:
				break;
		}
	}
	
	if ($debug)
	{
		echo __FILE__ . ' line ' . __LINE__ . ' TitleID = ' . $obj->TitleID . "\n";
	}
	
	
	
	// If no ISSN, or no mapping available via ISSN, so try string matching
	if ($obj->TitleID == 0)
	{
		$hits = bhl_title_lookup($title);
		
		if ($debug)
		{
			echo __FILE__ . ' line ' . __LINE__ . "\n";
			echo '<pre>';
			print_r($hits);
			echo '</pre>';
		}
		
		
		if (count($hits) > 0)
		{
			$obj->TitleID = $hits[0]['TitleID'];
		}		
	}
	
	if ($debug)
	{
		echo __FILE__ . ' line ' . __LINE__ . ' TitleID = ' . $obj->TitleID . "\n";
	}
	
	
	// Special cases where title is in VolumeInfo (e.g., article is treated as a monograph)
	if ($obj->TitleID == 0)	
	{
		if (isset($obj->ISSN))
		{
			switch ($obj->ISSN)
			{
				case '0015-0754':
					//echo $title . "\n";
					//echo "Handle Fieldiana...\n";
					$obj->ItemIDs = bhl_itemid_from_pattern ('Fieldiana% Zoology%', '/^Fieldiana\.? Zoology/', $volume);
					break;
				
				default:
					break;
			}
		}
	}
	
	// At this point if we have a title we then want to find items for this title
	if($obj->TitleID != 0)
	{		
		bhl_title_retrieve ($obj->TitleID, $obj);
				
		// Problem -- volume info varies across titles (and sometimes within...)
		
		if ($debug)
		{
			echo __LINE__ . "<br/>TitleID:" . $obj->TitleID . "<br/>Volume: " . $volume . "<br/>Series: " . $series . "<br/>";
		}
		$volume_offset = 0;
		$obj->ItemIDs = bhl_itemid_from_volume($obj->TitleID, $volume, $series);

		if ($debug)
		{
			echo __LINE__ . " ItemIDs<br/>\n";
			print_r($obj->ItemIDs);
			echo '<br />';
		}
		
		// Special cases where VolumeInfo is year, not volume
		if ((count($obj->ItemIDs) == 0) && ($date != ''))
		{
			$year = substr($date, 0, 4);
			switch ($obj->TitleID)
			{
				case 11516:
					$obj->ItemIDs = bhl_itemid_from_year($obj->TitleID, $year);
					break;
					
				default:
					break;
			}
		}
		
		// Special cases where we know there are problems. For example, there may be multiple titles
		// that correspond to the same journal. In these cases we clear the item list, and add to it 
		// items from all titles that match our query
		$title_list = array();
		switch ($obj->TitleID)
		{
			// Abhandlungen und Berichte des Koniglichen Zoologischen und Anthropologisch-Ethnographischen Museums zur Dresden
			case 49442:
			case 96150:
				$title_list = array(49442, 96150);
				break;
		
			// Acta Societatis pro Fauna et Flora Fennica
			case 5558:
			case 13345:
				$title_list = array(5558, 13345);
				break;
		
			// Actes de la Société linnéenne de Bordeaux
			case 4199:
			case 16235:
				$title_list = array(4199, 16235);
				break;
						
			// 	American journal of conchology
			case 15900:
			case 7077:
				$title_list = array(7077, 15900);
				break;

			// The American journal of science
			case 14965:
			case 60982:
				$title_list = array(14965, 60982);
				break;
				
				
		
			// Annali del Museo civico di storia naturale di Genova
			case 7929:
			case 9576:
			case 43408:
				$title_list = array(7929, 9576, 43408);
				break;
				
			// Annales de la Société entomologique de Belgique
			case 11933:
			case 11938:
				$title_list = array(11933, 11938);
				break;
				
			// Annales de la Société royale malacologique de Belgique
			case 6301:
			case 6205:
			case 7031:
				$title_list = array(6301,6205,7031);
				break;
				
			// Atti del Reale Istituto Veneto di Scienze, Lettere ed Arti
			case 7926:
			case 8237:
				$title_list = array(7926, 8237);
				break;
				
			// Atti della Società italiana di scienze
			case 9586:
			case 16255:
			case 16213:
				$title_list = array(9586,16255,16213);
				break;
				
			// Annales de la Société entomologique de Belgique.
			case 51679:
			case 11933:
				$title_list = array(51679, 11933);
				break;
				
			// Annales des Sciences naturelles
			case 6343:
			case 2205:
			case 13266:
			case 4647:
			case 5010:
				$title_list = array(6343, 2205, 13266, 4647, 5010);
				break;

			// Ann. Mag. Nat. Hist.
			case 2195:
			case 15774:
				$title_list = array(2195, 15774);
				break;
				
			// Anales de la Sociedad Científica Argentina
			case 44792:
			case 51644:
			case 3630:
				$title_list = array(44792, 51644, 3630);
				break;
				
			// Anales del Museo Nacional de Historia Natural de Buenos Aires
			case 5595:
			case 5597:
				$title_list = array(5595, 5597);
				break;
				
			// Annals of the Lyceum of Natural History of New York
			case 4219 :
			case 15987:
				$title_list = array(4219, 15987);
				break;
				
			// Annals of the New York Academy of Sciences
			case 4382 :
			case 51004:
				$title_list = array(4382, 51004);
				break;
			
			// Annals of the South African Museum
			case 62815:
			case 6928:
				$title_list = array(62815, 6928);
				break;
			
			// 	Annales des sciences naturelles, Zoologie
			case 13266:
			case 4647:
				$title_list = array(4647,13266);
				break;
		
			// Archiv für Naturgeschichte
			case 6638:
			case 7051:
			case 2371:
			case 5923:
			case 12937:
			case 12938:
				$title_list = array(6638,7051,2371,5923,12937,12938);
				break;
				
			// Archives de zoologie expérimentale et générale
			case 5559:
			case 7065:
			case 79165:
				$title_list = array(5559,7065, 79165);
				break;
				
			// Atti della Società italiana di scienze naturali
			case 9582:
			case 60455:
				$title_list = array(9582,60455);
				break;

			// Beiträge zur Kenntnis der Meeresfauna Westafrikas
			case 11824:
			case 7400:
				$title_list = array(11824,7400);
				break;
				
			// Bericht der Senckenbergischen Naturforschenden Gesellschaft in Frankfurt am Main
			case 14292:
			case 8745:
				$title_list = array(14292,8745);
				break;
				
			// Berliner entomologische Zeitschrift
			case 46204:
			case 46202:
			case 8267:
				$title_list = array(8267,46202,46204);
				break;
				
			// Boletin de la Sociedad de Biología de Concepción
			case 45409:
			case 51419:
				$title_list = array(45409,51419);
				break;
				
			// Boletín de la Sociedad Española de Historia Natural
			case 5929:
			case 6171:
				$title_list = array(5929,6171);
				break;
				
			// Bonner zoologische Beiträge
			case 82521:
			case 82240:
				$title_list = array(82521,82240);
				break;
				
			// Bonner zoologische Monographien
			case 98946:
			case 82295:
				$title_list = array(98946,82295);
				break;
			
				
			// Botanical Magazine Tokyo
			case 6214:
			case 63894:
				$title_list = array(6214,63894);
				break;
		
				
				
			// Botanische Jahrbucher fur Systematik, Pflanzengeschichte und Pflanzengeographie
			case 60:
			case 68683:
				$title_list = array(60,68683);
				break;
				
			// Brotéria
			case 5931:
			case 7952:
			case 7861:
				$title_list = array(5931,7952,7861);
				break;
				
			// Bulletins of American paleontology
			case 9663:
			case 39837:
				$title_list = array(9663,39837);
				break;
						
			// Bulletin de l'Académie impériale des sciences de St.-Pétersbourg
			case 42575:
			case 49351:
			case 10614:
				$title_list = array(42575,49351,10614);
				break;
				
			// Bollettino dei musei di zoologia ed anatomia comparata della R. Università di Torino
			case 14698:
			case 10776:
				$title_list = array(14698,10776);
				break;
				
			// Bulletin de la Société botanique de France
			case 359:
			case 5948:
			case 9580:
				$title_list = array(359,5948,9580);
				break;
				
			// Bulletin de la Société philomathique de Paris
			case 9580:
			case 5064:
				$title_list = array(9580,5064);
				break;
			
			// Bulletin de la Société zoologique de France
			case 51699:
			case 7415:
			case 10614:
				$title_list = array(51699,7415,10614);
				break;
				
			// Bulletin du Muséum National d'Histoire Naturelle
			case 14109:
			case 5943:	
			case 14964:
			case 12908:
			case 13855:
				$title_list = array(14109,5943,14964,12908,13855);
				break;
				
			// Bulletin international de l'Académie des sciences de Cracovie
			case 5605:
			case 5607:
			case 13192:
				$title_list = array(51699,5605,5607);
				break;
				
			// Bulletin of the British Ornithologists' Club.
			case 8260:
			case 46639:
			case 62378:
			case 102724:
				$title_list = array(8260,46639, 62378, 102724);
				break;
				
			// Bulletin of the Illinois State Laboratory of Natural History
			case 5041:
			case 8196:
				$title_list = array(5041,8196);
				break;
				
			// Bulletin of the Natural History Museum (Entomology)
			case 2192:
			case 2201:
			case 53882:
			case 62492: // supplement
				$title_list = array(2192, 2201, 53882, 62492);
				break;
				
			// Bulletin of the Southern California Academy of Sciences
			case 4949:
			case 50446:
			case 60457:
				$title_list = array(4949, 50446, 60457);
				break;
			
				
			// Bulletin of zoological nomenclature
			case 11541:
			case 51603:
				$title_list = array(11541, 51603);
				break;
				
			// The Canadian field-naturalist
			case 1929:
			case 39970:
				$title_list = array(1929, 39970);
				break;
				
			// Comptes rendus hebdomadaires des séances de l'Académie des scien...
			case 7014:
			case 4466:
			case 1590:
				$title_list = array(4466, 7014, 1590);
				break;
				
			// Comptes rendus des séances de la Société de biologie et de ses filiales.  
			case 5068:
			case 8070:
			case 66681:
				$title_list = array(5068, 8070,66681);
				break;
				
			// Current Herpetology
			case 66716:
			case 70709:
				$title_list = array(66716,70709);
				break;
				
			// Discovery Reports
			case 6168:
			case 15981:
				$title_list = array(6168, 15981);
				break;

			// Entomological News
			case 34360:
			case 2356:
			case 2359:
				$title_list = array(34360, 2356,2359);
				break;
				
			// Fieldiana
			case 5132:
			case 42256:
				$title_list = array(5132, 42256);
				break;
				
				
				
				
			// Gayana
			case 39684:
			case 39988:
				$title_list=array(39988,39684);
				break;
				
			// Jahrbuch der Hamburgischen Wissenschaftlichen Anstalten
			case 7925:
			case 9594:
				$title_list=array(7925,9594);
				break;
				
				
			// Journal für Ornithologie.
			case 11280 :
			case 14025:
			case 47027:
				$title_list=array(11280 ,14025, 47027);
				break;
				
			// Journal of botany, British and foreign
			case 8066 :
			case 15787:
				$title_list=array(8066 ,15787);
				break;
				
				
			// Journal of the Asiatic Society of Bengal
			// Journal and proceedings of the Asiatic Society of Bengal
			case 51678 :
			case 47024:
				$title_list=array(47024 ,51678);
				break;
			
			// The journal of the College of Agriculture, Tohoku Imperial University, Sapporo, Japan
			case 8338 :
			case 98298:
				$title_list=array(8338,98298);
				break;
			
				
			// Journal of the Washington Academy of Sciences
			case 2087:
			case 60244:
				$title_list=array(2087,60244);
				break;
				
			// Kungliga Svenska Vetenskaps-Akademiens handlingar
			case 2512:
			case 12549:
			case 50688:
				$title_list=array(2512,12549, 50688);
				break;
				
			// London and Edinburgh Philosophical Magazine and Journal of Science
			case 2440:
			case 58332:
			case 3735:
				$title_list=array(2440,58332, 3735);
				break;
				
			// Malakozoologische Blätter
			case 51643:
			case 15800:
				$title_list=array(51643,15800);
				break;
				
				
			// Memoirs And Proceedings of The Manchester Literary And Philosophical Society
			case 50366:
			case 9535:
				$title_list=array(50366,9535);
				break;
				
				
			// Mem. Calif. Acad. Sci.
			case 3955:
			case 3949:
				$title_list=array(3955,3949);
				break;
				
			// Mémoires de l'Académie royale des sciences, des lettres et des beaux-arts de Belgique
			case 2743 :
			case 2732:
			case 5551:
				$title_list = array(2743,2732,5551);
				break;
			
			
			// Memoirs of the Queensland Museum
			case 12912 :
			case 60751:
			case 61449:
			case 101455:
				$title_list = array(12912,60751,61449,101455);
				break;
					
			// Misc Pub Kansas (some of these are treated as individual titles)
			case 4050:
			case 16171:
			case 16222:
			case 5474:
				$title_list = array(4050, 16171, 16222, 5474);
				break;	
				
			// Mitteilungen aus dem Naturhistorischen Museum in Hamburg
			case 8009:
			case 9579:
			case 42281:
				$title_list = array(8009, 9579, 42281);
				break;	
				
			// Mitteilungen aus dem Zoologischen Museum in Berlin.
			case 11448:
			case 42540:
				$title_list = array(11448,42540);
				break;
				
			// Mitteilungen des Naturwissenschaftlichen Vereines für Steiermark
			case 12305:
			case 42384:
				$title_list = array(12305,42384);
				break;
				
			// Meddelanden af Societatis pro Fauna et Flora Fennica
			case 3613:
			case 13470:
				$title_list = array(3613, 13470);
				break;	
				
			// Mémoires du Muséum d'histoire naturelle.
			case 4501:
			case 50067:
				$title_list = array(4501, 50067);
				break;	

			// Memoirs of the National Museum of Victoria
			// Memoirs of the National Museum, Melbourne
			// Memoirs of the National Museum, Melbourne
			case 58640:
			case 13041:
			case 57949:
			case 59883:
				$title_list = array(58640, 13041, 57949, 59883);
				break;	
				
			// Monatsberichte der Königlichen Preussische Akademie des Wissenschaften zu Berlin
			case 48522:
			case 51443:
				$title_list = array(48522, 51443);
				break;	
				
			// Natural history report.
			case 1163:
			case 42665:
				$title_list = array(1163, 42665);
				break;	
				
			// Nova acta Academiae Caesareae Leopoldino-Carolinae Germanicae Naturae Curiosorum
			case 12266:
			case 86329:
				$title_list = array(12266, 86329);
				break;	
			
				
			// Occasional Papers of the Boston Society of Natural History
			case 7539:
			case 50720:
				$title_list = array(7539, 50720);
				break;	
				
			// Occasional papers of the California Academy of Sciences
			case 4672:
			case 5584:
				$title_list = array(4672, 5584);
				break;	
				
			// Occasional papers of the Museum of Natural History, the University of Kansas.
			case 7410:
			case 15798:
				$title_list = array(7410, 15798);
				break;	
				
			// Öfversigt af Kongl. Vetenskaps-akademiens forhandlingar
			case 2515:
			case 15534:
				$title_list = array(2515, 15534);
				break;	
				
			// Ornis
			case 16093:
			case 8629:
				$title_list = array(16093, 8629);
				break;	
				
				
			// Ornithologische Monatsberichte
			case 8609:
			case 46941:
				$title_list = array(8609, 46941);
				break;	
				
				
			// Nota lepidopterologica.
			case 79076:
			case 63275:
				$title_list = array(79076, 63275);
				break;	
				
			// Notes from the Leyden Museum
			case 8740:
			case 12935:
				$title_list = array(8740, 12935);
				break;	
				
			// The Philippine journal of science
			case 69:
			case 50545:
				$title_list = array(69, 50545);
				break;
				
			// The Philosophical magazine
			case 982:
			case 58331:
			case 58328:
				$title_list = array(982, 58331, 58328);
				break;
			
				
			// Pom
			case 9567:
			case 8154:
			case 12098:
				$title_list = array(9567, 8154,12098);
				break;
				
			// Proceedings of the American Academy of Arts and Sciences
			case 3640:
			case 3934:
				$title_list = array(3934, 3640);
				break;
				
			// Proceedings of the Biological Society of Washington
			case 2211:
			case 3622:
			case 50687:
				$title_list = array(2211, 3622, 50687);
				break;

			// Proceedings of the California Academy of Sciences
			case 3952:
			case 7411:
			case 15816:
			case 3966:
			case 4274:
			case 3943:
			case 12931:
			case 45400:
				$title_list = array(3952, 7411, 15816, 3966, 4274, 3943, 12931, 45400);
				break;
				
			// Proceedings of The Linnean Society of New South Wales
			case 2375:
			case 50818:
				$title_list = array(2375,50818);
				break;
				
			// Proceedings of the Royal Irish Academy
			case 2375:
			case 60468:
				$title_list = array(2375,60468);
				break;
				
				
			// Proceedings of the Zoological Society of London
			case 1594:
			case 44963:
				$title_list = array(1594,44963);
				break;
				
			// Quarterly journal of microscopical science
			case 13831:
			case 14014:
				$title_list = array(13831,14014);
				break;
				
				
			// Records of the Indian Museum
			case 53477:
			case 10294:
				$title_list=array(53477,10294);
				break;
				
			// Records of the South Australian Museum
			case 14053:
			case 42375:
			case 61893:
				$title_list=array(14053,42375,61893);
				break;
				
			// Reise der oesterreichischen Fregatte Novara
			case 5376:
			case 1597:
			case 9173:
				$title_list=array(5376,1597,9173);
				break;
				
			// Report of the Commissioner for ...
			case 6927:
			case 15220:
				$title_list=array(6927,15220);
				break;
			
			// Revue d'entomologie
			case 10428:
			case 11902:
			case 14588:
				$title_list=array(10428,11902, 14588);
				break;
				
			// Revue et magasin de zoologie pure et appliquée
			case 51560:
			case 51630:
			case 2744:
			case 2212:
				$title_list = array(2744, 51630, 51560, 2212);
				break;
				
			// Revue suisse de zoologie
			case 8981:
			case 62174:
			case 69643:
				$title_list=array(8981,62174,69643);
				break;
				
			// Russkoe entomologicheskoe obozrenie = Revue russe d'entomologie.
			case 11807:
			case 11489:
				$title_list=array(11807,11489);
				break;
				
			// The Scientific proceedings of the Royal Dublin Society
			case 14895:
			case 44062:
				$title_list = array(14895, 44062);
				break;
				
			// Scopus
			case 64405:
			case 99906:
				$title_list=array(64405,99906);
				break;
				
			// Sitzungsberichte der Gesellschaft Naturforschender Freunde zu Berlin
			case 9584:
			case 7922:
				$title_list = array(7922, 9584);
				break;
				
			// Sitzungsberichte der Kaiserlichen Akademie der Wissenschaften. Mathematisch-Naturwissenschaftliche Classe
			case 6884:
			case 8219:
			case 6888:
			case 6776:
			case 8100:
			case 7337:
				$title_list = array(6884, 8219, 6888, 6776, 8100,7337);
				break;
				
			// Stettiner Entomologische Zeitung
			case 8630:
			case 8641:
				$title_list = array(8630, 8641);
				break;
			
			
			
			// Stuttgarter Beiträge zur Naturkunde
			case 49392:
			case 51723:
			case 49174:
			case 43750:
				$title_list = array(49392, 51723, 49174, 43750);
				break;
			
			
			// Tijdschrift voor entomologie.
			case 10088:
			case 39564:
				$title_list = array(10088, 39564);
				break;
				
			// Trabajos del Museo de Ciencias Naturales
			case 13508:
			case 15242:
				$title_list = array(13508, 15242);
				break;
				
			// Transactions and proceedings and report of the Royal Society of South Australia (Incorporated)
			case 51476:
			case 16197:
			case 16190:
			case 51127:
			case 51669:
				$title_list = array(51476, 16197,16190, 51127, 51669);
				break;
			
				
			// Transactions and proceedings of the New Zealand Institute
			case 48984:
			case 4095:
				$title_list = array(48984, 4095);
				break;
			
			// Transactions of Kansas Academy of Sciences
			case 8255:
			case 8256:
				$title_list = array(8255, 8256);
				break;
				
			// Transactions of the Academy of Science of Saint Louis
			case 3634:
			case 6336:
			case 42204:
				$title_list = array(3634, 6336, 42204);
				break;
				
				
				
			// Transactions of the American Entomological Society
			case 7830:
			case 7549:
			case 5795:
				$title_list = array(5795, 7549, 7830);
				break;
			
				
			// Transactions of the Connecticut Academy of Arts and Sciences
			case 7541:
			case 5604:
			case 13505:
				$title_list = array(7541, 5604, 13505);
				break;
				
			// Transactions of The Linnean Society of London
			case 2203: //-> 683
			case 8257: //-> -> 51416
			case 683:
			case 51416:
				$title_list=array(683,51416);
				break;
				
			// Transactions of the Royal Society of South Australia
			case 16197:
			case 16186:
			case 51127:
			case 16190:
			case 62638:
			case 63905:
			case 63906:
				$title_list=array(16197, 16186,51127, 16190, 62638, 63905, 63906);
				break;
				
			// Transactions of the San Diego Society of Natural History
			case 51441:
			case 3144:
				$title_list = array(51441, 3144);
				break;
				
			// Transactions of the Zoological Society of London
			case 12650:
			case 45493:
				$title_list = array(12650, 45493);
				break;
				
			// The Kansas University science bulletin
			case 3179:
			case 15415:
				$title_list = array(3179, 15415);
				break;
				
			// Tulane
			case 3119:
			case 5361:
				$title_list = array(3119, 5361);
				break;
				
			// Veliger
			case 66841:
			case 69283:
			case 67217:
				$title_list = array(66841, 69283, 67217);
				break;
								
			// Verhandlungen der Naturforschenden Gesellschaft in Basel.
			case 8939:
			case 46540:
				$title_list = array(8939, 46540);
				break;
				
			// Verhandlungen des Zoologisch-Botanischen Vereins in Wien
			case 11285:
			case 13275:
			case 44800:
				$title_list = array(11285, 13275, 44800);
				break;
				
			// Veröffentlichungen der Zoologischen Staatssammlung München
			case 39971:
			case 51449:
				$title_list = array(39971, 51449);
				break;
				
			// The Victorian Naturalist
			case 5027:
			case 8941:
			case 60605:
			case 43746:
				$title_list = array(5027, 8941, 60605, 43746);
				break;
				
			// Videnskabelige meddelelser fra den Naturhistoriske forening
			case 7547:
			case 52368:
			case 2226:
				$title_list = array(7547, 52368, 2226);
				break;

			// Zeitschrift für wissenschaftliche Zoologie
			case 2334:
			case 9197:
				$title_list = array(2334, 9197);
				break;
				
			// Zoological results of the fishing experiments carried on by F.I.S. "Endeavour,"
			case 6512:
			case 11387:
				$title_list = array(6512, 11387);
				break;
			
			// Zoologische Jahrbücher
			case 8980:
			case 13352:
				$title_list = array(8980, 13352);
				break;
			
				

			default:
				break;
		}
		if (count($title_list) != 0)
		{
			$obj->ItemIDs = array();
			foreach ($title_list as $id)
			{
				//echo "<h2>Title list $id</h2>";
				$obj->ItemIDs = array_merge(bhl_itemid_from_volume($id, $volume, $series), $obj->ItemIDs);
			}
		}
	}
	
	//echo __LINE__;
	
	if ($debug)
	{
		echo "Line " . __LINE__ . "<br/>";
		echo '<h2>Title list</h2>';
		print_r($title_list);
		print_r($obj->ItemIDs);
	}
		
	// At this point if we have any items then we have a potential hit. For each item in the list we
	// query the BHL database and look for pages with PageNumber matching our query
	$num_items = count($obj->ItemIDs);
	if ($num_items != 0)
	{
		for ($i = 0; $i < $num_items; $i++)
		{
			$sql = 'SELECT * FROM bhl_page 
			INNER JOIN page USING(PageID)
			WHERE (bhl_page.ItemID = ' . $obj->ItemIDs[$i]->ItemID . ') 
			AND (PageNumber = ' . $db->qstr($page) . ')
			OR (PageNumber = ' . $db->qstr('%[' . $page . ']') . ')
			ORDER BY SequenceOrder';

			$sql = 'SELECT * FROM bhl_page 
			INNER JOIN page USING(PageID)
			WHERE (bhl_page.ItemID = ' . $obj->ItemIDs[$i]->ItemID . ') 
			AND (PageNumber = ' . $db->qstr($page) . ')
			ORDER BY SequenceOrder';
			
			//echo $sql;
			
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
			
			/*
			
			// if no hit try difefrent formatting
			if ($result->NumRows() == 0)
			{
				$sql = 'SELECT * FROM bhl_page 
				INNER JOIN page USING(PageID)
				WHERE (bhl_page.ItemID = ' . $obj->ItemIDs[$i]->ItemID . ') 
				AND (PageNumber = ' . $db->qstr('[' . $page . ']') . ')
				ORDER BY SequenceOrder';

				$result = $db->Execute($sql);
				if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
			
			}
			*/
			
			$obj->ItemIDs[$i]->pages = array();			

			switch ($result->NumRows())
			{
				case 0:
					//no hit :(
					
					// Try and handle case where page one is a title page
					$guess = bhl_step_back(
						$obj->ItemIDs[$i]->ItemID, 
						$page, 
						$obj->ItemIDs[$i]->volume_offset);
					if ($guess != 0)
					{
						$obj->ItemIDs[$i]->pages[] = $guess;
						$obj->hits[] = $guess;
						$obj->ItemIDs[$i]->PageID = $guess;						
					}					
					break;
					
				case 1:
					// unique hit
					$obj->ItemIDs[$i]->pages[] = $result->fields['PageID'];
					$obj->hits[] = $result->fields['PageID'];
					$obj->ItemIDs[$i]->PageID = $result->fields['PageID'];
					break;
					
				default:
					// More than one hit, for example if Item has multiple volumes, hence potentially
					// more than one page with this number.
										
					while (!$result->EOF) 
					{
						$obj->ItemIDs[$i]->pages[] = $result->fields['PageID'];
						$obj->hits[] = $result->fields['PageID'];
						$result->MoveNext();
					}
					// Assume page in nth volume is nth page in SequenceOrder
					// (but actually we store all hits and use string matching on title to filter)
					$obj->ItemIDs[$i]->PageID = $obj->ItemIDs[$i]->pages[$obj->ItemIDs[$i]->volume_offset];
					break;
			}
		}
	}
		
	if ($debug)
	{
		echo __LINE__;
		echo '<pre>';
		print_r($obj);
		echo '</pre>';
	}	

	// Summarise results as array of hits 	
	$search_results = array();
	
	// Post process hits, filtering by title match...
	$n = count($obj->ItemIDs);
	for ($i = 0; $i < $n; $i++)
	{
		foreach ($obj->ItemIDs[$i]->pages as $page)
		{
			$search_hit = bhl_score_page($page, $atitle);
			
/*			new stdclass;
			$search_hit->PageID = $page;
			$search_hit->score = -1;
			$search_hit->snippet = '';
			
			// Score title match
			if ($atitle != '')
			{
				$search_hit->score = bhl_score_string(
					$search_hit->PageID, 
					$atitle, 
					$search_hit->snippet
					);
			} */
			
			$search_results[] = $search_hit;
		}	
	}
	
	//return $obj;
	return $search_results;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Score a page in BHL
 *
 * @param PageID BHL PageID
 * @param title Title of reference being sought
 *
 * @return Search hit
 *
 */
function bhl_score_page($PageID, $title)
{
	$search_hit = new stdclass;

	$search_hit->score = -1;
	$search_hit->snippet = '';	
	$search_hit->PageID = $PageID;

	if ($title != '')
	{
		$search_hit->score = bhl_score_string(
			$search_hit->PageID, 
			$title, 
			$search_hit->snippet
			);
	}
	
	return $search_hit;
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Step through BHL database to find article
 *
 * We step forwards through pages until we find a numbered page, then compute the position of
 * the target page. 
 *
 * For example, for "The identities of the Colombian Frogs confused with Eleutherodactulys latidiscus
 * (Boulenger) (Amphibia: Anura: Leptodactylidae)" page 1 is http://biodiversitylibrary.org/page/4466887
 * but this is labelled "Title Page", not "Page 1". The next page is labelled "Page 2". If we were
 * searching for "Page 1" we increment page numbers until we hit a numbered page (in this case we get
 * hit "Page 2" http://biodiversitylibrary.org/page/4466857 . Using the SequenceOrder for this item,
 * we get the predecessor of "Page 2" in the item (http://biodiversitylibrary.org/page/4466887) and
 * return that as our hit.
 *
 * For multiple volume items the success of this approach depends on the Item having all volumes, 
 * which is often not the case.
 *
 * @param ItemID BHL ItemID
 * @param page Target page of article
 * @param offset Offset for target volume if Item has multiple volumes, default is 0 (one volume in item)
 * @param window How far we want to look ahead for a numbered page
 *
 * @return
 *
 */
// It may be that first page is a cover/title page, especially if spage = 1
function bhl_step_back($ItemID, $page, $offset = 0, $window = 3)
{
	global $db;
	
	$hit = 0;
	
	// 0. All pages in item in SequenceOrder
	$item_sequence = array();
	$sql = 'SELECT PageID, SequenceOrder FROM page 
			WHERE (ItemID=' . $ItemID . ')
			ORDER By SequenceOrder';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


	
	while (!$result->EOF) 
	{
		$item_sequence[$result->fields['SequenceOrder']] = $result->fields['PageID'];
		$result->MoveNext();
	}
	
	
	// 1. Go forward from target page until we hit a numbered page
	$pages 			= array();
	$page2sequence 	= array();
	$page_type 		= array();
	$page_number 	= $page;
	$sequence2page 	= array();
	$found = false;

	// Step through pages until we get a numbered page
	while (($page_number < $page + $window) && !$found)
	{
		$page_number++;
		
		$sql = 'SELECT * FROM bhl_page 
	INNER JOIN page USING(PageID)
	WHERE (bhl_page.ItemID = ' . $ItemID . ') 
	AND (PageNumber = ' . $db->qstr($page_number) . ') 
	ORDER BY SequenceOrder';
	

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


		
		while (!$result->EOF) 
		{
			array_push($pages, $result->fields['PageID']);
			$page2sequence[$result->fields['PageID']] = $result->fields['SequenceOrder'];
			$sequence2page[$result->fields['SequenceOrder']] = $result->fields['PageID'];
			$page_type[$result->fields['PageID']] = $result->fields['PageTypeName'];
			$found = true;
			
			$result->MoveNext();			
		}
	}
	
	// If we got a hit step back to actual target page. For example, if looking for Page 1 and
	// we find Page 2, then we step back one (in sequence order) to get Page 1
	if (count($pages) > 0)
	{
		/*echo '<pre>';
		print_r($pages);
		print_r($page2sequence);
		print_r($page_type);
		echo '</pre>';
		*/
		$found_page = $pages[$offset];
		$found_sequence = $page2sequence[$found_page];
		
		$hit = $item_sequence[$found_sequence - ($page_number - $page)];
	}
	
	return $hit;

}


//--------------------------------------------------------------------------------------------------
function bhl_find_article_from_article_title($atitle, $title, $volume, $page, $series = '')
{
	global $db;
	
	// Data structure to hold search result
	$obj = new stdclass;
	$obj->TitleID = 0;
	$obj->ISSN = '';
	$obj->ItemIDs = array();
	$obj->hits = array();
	
	// Do we have this title?
	$hits = bhl_title_lookup($atitle);
	
	if (count($hits) > 0)
	{
		if ($hits[0]['sl'] > 90)
		{
			$obj->TitleID = $hits[0]['TitleID'];
		}
	}
	
	if ($obj->TitleID != 0)
	{
		bhl_title_retrieve ($obj->TitleID, $obj);
		
		//echo $obj->TitleID;
		
		$volume_offset = 0;
		$obj->ItemIDs = bhl_itemid_from_volume($obj->TitleID, $volume, $series);
		{
			//print_r($obj->ItemIDs);
		}
		
		if (count($obj->ItemIDs) != 0)
		{
		
			$sql = 'SELECT * FROM bhl_page 
			INNER JOIN page USING(PageID)
			WHERE (bhl_page.ItemID = ' . $obj->ItemIDs[0]->ItemID . ') 
			AND (PageNumber = ' . $db->qstr($page) . ') 
			ORDER BY SequenceOrder';
					
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
			switch ($result->NumRows())
			{
				case 0:
					//no hit :(
					
					// Try and handle case where page one is a title page
					if ($page == 1)
					{
						$guess = bhl_step_back($obj->ItemIDs[0]->ItemID, $page, $obj->ItemIDs[0]->volume_offset);
						if ($guess != 0)
						{
							array_push($obj->hits, $guess);
							$obj->ItemIDs[0]->PageID = $guess;						
						}
					}
					break;
					
				case 1:
					// unique hit
					array_push($obj->hits, $result->fields['PageID']);
					$obj->ItemIDs[0]->PageID = $result->fields['PageID'];
					break;
			
				default:
					break;
					
			}
		
		}
	
	}

//	return $obj;

	// Summarise results as array of hits 	
	$search_results = array();
	
	foreach ($obj->hits as $hit)
	{
		// Post process hits, filtering by title match...
		$search_hit = new stdclass;
		$search_hit->PageID = $hit;
		$search_hit->score = -1;
		$search_hit->snippet = '';
		
		// Score title match
		if ($atitle != '')
		{
			$search_hit->score = bhl_score_string(
				$search_hit->PageID, 
				$atitle, 
				$search_hit->snippet
				);
		}
		$search_results[] = $search_hit;
	}
	
	return $search_results;
}




function test_bhl_find()
{
	$tests = array();
	
	//----------------------------------------------------------------------------------------------
	// Journal of Hymenoptera Research
	array_push($tests, array(
		'title' => 'Journal of Hymenoptera Research', 
		'volume' => 6,
		'spage'=> 256,
		'PageID' => 4491707)
		);
		
	// Multiple pages in same item (multiple volumes)
	array_push($tests, array(
		'title' => 'Journal of Hymenoptera Research', 
		'volume' => 8,
		'spage'=> 1,
		'PageID' => 4491014)
		);
	
	//----------------------------------------------------------------------------------------------
	// Fieldiana
	array_push($tests, array(
		'title' => 'Fieldiana, Zoology', 
		'volume' => 31,
		'spage'=> 149,
		'PageID' => 2763486)
		);
	array_push($tests, array(
		'title' => 'Fieldiana, Zoology', 
		'volume' => 39,
		'spage'=> 577,
		'PageID' => 2866715)
		);
		
	// Two hits
	array_push($tests, array(
		'title' => 'Fieldiana, Zoology', 
		'volume' => 73,
		'spage'=> 49,
		'PageID' => 2759622)
		);
	array_push($tests, array(
		'title' => 'Fieldiana, Zoology', 
		'volume' => 77,
		'spage'=> 1,
		'PageID' => 2866529)
		);
		
		
	//----------------------------------------------------------------------------------------------
	// University of Kansas Science Bulletin
	array_push($tests, array(
		'title' => 'University of Kansas Science Bulletin', 
		'volume' => 35,
		'spage'=> 577,
		'PageID' => 4413503)
		);
		
	//----------------------------------------------------------------------------------------------
	// Bulletin of Zoological Nomenclature
	array_push($tests, array(
		'title' => 'Bulletin of Zoological Nomenclature', 
		'volume' => 23,
		'spage'=> 169,
		'PageID' => 12222978)
		);
		
	//----------------------------------------------------------------------------------------------
	// Proceedings of the California Academy of Sciences
	array_push($tests, array(
		'title' => 'Proceedings of the California Academy of Sciences', 
		'volume' => 47,
		'spage'=> 47,
		'PageID' => 15776069)
		);		

	//----------------------------------------------------------------------------------------------
	// Ann Mag Nat Hist
	array_push($tests, array(
		'title' => 'Ann Mag Nat Hist', 
		'volume' => 20,
		'spage'=> 413,
		'series' => 8,
		'PageID' => 15611435)
		);	
		
	//----------------------------------------------------------------------------------------------
	// Bulletin of the British Museum (Natural History). Zoology

	array_push($tests, array(
		'title' => 'Bulletin of the British Museum (Natural History). Zoology', 
		'volume' => 34,
		'spage'=> 65,
		'PageID' => 2261841)
		);	
	array_push($tests, array(
		'title' => 'Bulletin of the British Museum (Natural History). Zoology', 
		'volume' => 27,
		'spage'=> 65,
		'PageID' => 2261309)
		);	
	array_push($tests, array(
		'title' => 'Bulletin of the British Museum (Natural History). Zoology', 
		'volume' => 27,
		'spage'=> 59,
		'PageID' => 2261319)
		);	
		
	//----------------------------------------------------------------------------------------------
	// Bulletin of the British Museum (Natural History). Entomology

	array_push($tests, array(
		'title' => 'Bulletin of the British Museum (Natural History): Entomology', 
		'volume' => 12,
		'spage'=> 247,
		'PageID' => 2298342)
		);	
		
	//----------------------------------------------------------------------------------------------
	// Memoirs of the Museum of Comparative Zoölogy
	
	array_push($tests, array(
		'title' => 'Memoirs of the Museum of Comparative Zoölogy', 
		'volume' => 50,
		'spage'=> 85,
		'PageID' => 15776069)
		);	
	
	//----------------------------------------------------------------------------------------------
	// Proc. ent. Soc. Wash.
	// Banks, N. (1899b). Some spiders from northern Louisiana. Proc. ent. Soc. Wash. 4: 188-195.

	array_push($tests, array(
		'title' => 'Proc. ent. Soc. Wash.', 
		'volume' => 4,
		'spage'=> 188,
		'PageID' => 2299619)
		);	

		
	//----------------------------------------------------------------------------------------------
	// Ann. Soc. ent. Fr.
	// Simon, E. (1885c). Etudes arachnologiques. 17e Mémoire. XXIV. Arachnides recuellis dans la 
	// vallée de Tempé et sur le mont Ossa (Thessalie). Ann. Soc. ent. Fr. (6) 5: 209-218.

	array_push($tests, array(
		'title' => 'Ann. Soc. ent. Fr.', 
		'volume' => 5,
		'spage'=> 209,
		'series' => 6,
		'PageID' => 10171703)
		);
		
	//----------------------------------------------------------------------------------------------
	// Mitteilungen der Schweizerischen Entomologischen Gesellschaft
	// Forel A (1887) Fourmis récoltées à Madagascar par le Dr. Conrad Keller. Mitteilungen der Schweizerischen Entomologischen Gesellschaft 7: 381–389. 
		
	array_push($tests, array(
		'title' => 'Mitteilungen der Schweizerischen Entomologischen Gesellschaft', 
		'volume' => 7,
		'spage'=> 381,
		'PageID' => 10395996)
		);

	
	//----------------------------------------------------------------------------------------------
	// Revue zoologique africaine
	array_push($tests, array(
		'title' => 'Revue zoologique africaine', 
		'volume' => 9,
		'spage'=> 1,
		'PageID' => 4491707)
		);
		
		
	echo '<pre>';
	$ok = 0;
	$failed = array();
	foreach ($tests as $test)
	{
		echo $test['title'] . ' ' . $test['volume'] . ' ' . $test['spage'] . ' ...';
	
		$search_hits = bhl_find_article(
			$test['title'], 
			$test['volume'],
			$test['spage'],
			(isset($test['series']) ? $test['series'] : '')
			);
		$hits = $search_hits;
		
		$matched = in_array($test['PageID'], $hits->hits); 
		
		if ($matched)
		{
			$ok++;
			echo " [" . count($hits->hits) . "] ok\n";
		}
		else
		{
			echo " not found\n";
			array_push($failed, array($test, $hits));
		}
	}
	
	// Report
	echo count($tests) . ' references, ' . (count($tests) - $ok) . ' failed' . "\n";
	print_r($failed);
	
	echo '</pre>';
	
	
}



// test parsers
//test_bhl_find();


/*




// Display hits...
echo '<b>Page hit</b>';
echo '<table border="0" cellpadding="10">';
foreach ($search_hits->hits as $h)
{
	echo '<tr>';
	echo '<td>';
	echo '<img style="border:1px solid rgb(128,128,128);" src="thumbnail.php?PageID=' . $h . '"/><br/>';
	echo '</td>';
	
	echo '<td valign="top">';	
	$text = get('http://iphylo.org/~rpage/bhl/ocr.php?PageID=' . $h);
	$snippet = trim_text($text, 40);
	echo '<span style="color:green;">' . $snippet . '</span><br/>';
	
	// direct link to BHL page
	echo '<a href="http://www.biodiversitylibrary.org/page/' . $h . '">' . $h . '</a><br/>';
	
	// OpenURL
	
	
	// COinS
	echo '</td>';
	
	
	
	echo '</tr>';
}
echo '</table>';

// can we offer a fallback to item?
if (count($search_hits->hits) == 0)
{
	// No hits to page level, but maybe we have just one item
	
	if (count($search_hits->ItemIDs) == 1)
	{
		// display title page of item
		$pages = bhl_title_page($search_hits->ItemIDs[0]->ItemID);
		
		echo "<b>Title hit (didn't get page hit)</b>";
		echo '<table border="0" cellpadding="10">';
		foreach ($pages as $PageID)
		{
			echo '<tr>';
			echo '<td>';
			echo '<img style="border:1px solid rgb(128,128,128);" src="thumbnail.php?PageID=' . $PageID . '"/><br/>';
			echo '</td>';
			
			echo '<td valign="top">';
			$text = get('http://iphylo.org/~rpage/bhl/ocr.php?PageID=' . $PageID);
			$snippet = trim_text($text, 40);
			echo '<span style="color:green;">' . $snippet . '</span><br/>';
			
			// direct link to BHL page
			echo '<a href="http://www.biodiversitylibrary.org/page/' . $PageID . '">' . $PageID . '</a><br/>';
			
			// OpenURL
			
			
			// COinS
			echo '</td>';
			echo '</tr>';
			
		}
		echo '</table>';
	}
		
	
	
	
}



*/



?>