<?php

/**
 * @file bhl_text.php
 *
 * Retrieve, store, and manipulate BHL OCR text
 *
 */

require_once (dirname(__FILE__) . '/config.inc.php');
require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/swa.php');


//--------------------------------------------------------------------------------------------------
/**
 * @brief Use sequence alignment to score page text for presence of a string 
 *
 * For example, string might be a article title and we want to see whether this string (or a string
 * like it) is present in the OCR text for this page. We use Smith-Waterman alignment on words to
 * find and score the string
 *
 * @param PageID BHL PageID of page
 * @param string String we are searching for
 * @param snippet If string is found we enclose it in a snippet for display
 *
 * @return Score for string alignment
 *
 */
 function bhl_score_string($PageID, $string, &$snippet)
{
	$score = -1.0;
	$text = bhl_fetch_ocr_text($PageID);
	$text = bhl_unescape_newlines ($text);
	$snippet = '';
	$score = smith_waterman($text, $string, $snippet);
	return $score;
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Fetch BHL OCR text 
 *
 * Fetch OCT text from BHL pagesummaryservice.aspx service, clean it, then store in local
 * database. If we have already stored text for this page we retrieve local copy.
 *
 * @param PageID BHL PageID of page
 *
 * @return Cleaned text
 *
 */
function bhl_fetch_ocr_text($PageID)
{
	global $db;
	
	$refresh = true;
	$refresh = false;
	
	$text = '';
	
	// Do we have this already in database?
	
	$sql = 'SELECT * FROM rdmp_text
		WHERE (PageID=' . $PageID . ') LIMIT 1';
				
	$result = $db->Execute($sql);
	if ($result == false) die("failed $sql"); 
	
	if (($result->NumRows() == 1) && !$refresh)
	{
		$text = $result->fields['ocr_text'];
	}
	else
	{
		$url = 'http://www.biodiversitylibrary.org/services/pagesummaryservice.ashx?op=FetchPageUrl&pageID=' . $PageID;		
		
		$url = 'http://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetPageOcrText&pageid=' . $PageID . '&apikey=' . '0d4f0303-712e-49e0-92c5-2113a5959159' . '&format=json';

		
		$json = get($url);
		if ($json != '')
		{
			$j = json_decode($json);		
			
			//$text = $j[4];
			
			$text = $j->Result;
			//$text = utf8_decode($text);
			
			$text = bhl_clean_ocr_text($text);
			
			bhl_store_ocr_text ($PageID, $text);
			
		}
	}
	
	
	// clean
	$text = preg_replace('/\x1F/', "", $text);
	$text = preg_replace('/\x1D/', "", $text);
	
	

	return $text;

}




//--------------------------------------------------------------------------------------------------
/**
 * @brief Store BHL OCR text 
 *
 * @param PageID BHL PageID of page
 * @param text Text to store
 *
 */
function bhl_store_ocr_text($PageID, $text)
{
	global $db;
		
	$sql = 'INSERT INTO rdmp_text (PageID, ocr_text) VALUES (' . $PageID . ',' . $db->qstr($text) . ')
  ON DUPLICATE KEY UPDATE ocr_text = ' . $db->qstr($text);
				
	$result = $db->Execute($sql);
	if ($result == false) die("failed $sql"); 
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Clean BHL OCR text 
 *
 * Clean BHL text by replacing paired HTML break tags by a single break tag, then replacing 
 * break tags by new lines (escaped with "\").
 *
 * @param text Text to store
 * @param html True if text is to be displayed as HTML
 *
 * @return Cleaned text
 */
function bhl_clean_ocr_text($text, $html = false)
{
	// clean up for display
	$text = preg_replace('/â€”/U', '—', $text);
	
	
	$text = preg_replace('/<br \/>(<br \/>)+/', "<br />", $text);
	$text = preg_replace('/<br \/>/', "\\n", $text);
	//$text = preg_replace('/\s\s+/', ' ', $text);
	$text = preg_replace('/\\n\\n/', "\n", $text);
	
	if ($html)
	{
		$text = preg_replace('/</', '&lt;', $text);
		$text = preg_replace('/>/', '&gt;', $text);
	}

	return $text;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Unescape new lines from BHL text 
 *
 * @param text Text
 *
 * @return Text with \\n replaced by \n
 */
function bhl_unescape_newlines ($text)
{
	return str_replace("\\n", "\n", $text);
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Fetch BHL OCR text for a set of pages as a single block of text
 *
 * @param pages Array of BHL PageIDs
 *
 * @return Text of BHL pages 
 *
 */
function bhl_fetch_text_for_pages($pages)
{
	$text = '';
	foreach ($pages as $PageID)
	{
		$text .= bhl_fetch_ocr_text($PageID) . "\f";
	}
	return $text;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Fetch BHL OCR text as an array of pages indexed 0,1,...,n-1
 *
 * @param pages Array of BHL PageIDs
 *
 * @return Text of BHL pages indexed 0,1,...,n-1
 *
 */
function bhl_fetch_pages($pages)
{
	$text = array();
	foreach ($pages as $PageID)
	{
		$text[] = fetch_ocr_text($PageID);
	}
	return $text;
}


//--------------------------------------------------------------------------------------------------
function bhl_geocode_reference($reference_id)
{
	$pages = bhl_retrieve_reference_pages($reference_id);
	
	foreach ($pages as $page)
	{
		$text = bhl_fetch_ocr_text($page->PageID);
		
		//echo $text;
		
		$pts = points_from_text($text);
		
		//echo $page->PageID;
		//print_r($pts);
		
		if (count($pts) > 0)
		{
			foreach ($pts as $pt)
			{
				$loc = new stdclass;
				
				$loc->name = ''; 
				$loc->latitude = $pt->latitude;
				$loc->longitude = $pt->longitude;
			
				$locality_id = db_store_locality ($loc);
				bhl_store_locality_link($page->PageID, $locality_id);
			}
		}
		else
		{
			// No localities
			bhl_store_locality_link($page->PageID, 0);
		}
	
	
	}


}

// test
/*
echo '<pre>';
echo str_replace("\\n", "\n", fetch_ocr_text(12229302));
echo '</pre>';
*/

if (0)
{
	$PageID = 5221087;
	
			$url = 'http://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetPageOcrText&pageid=' . $PageID . '&apikey=' . '0d4f0303-712e-49e0-92c5-2113a5959159' . '&format=json';

		
		$json = get($url);
		if ($json != '')
		{
			$j = json_decode($json);		
			
			//$text = $j[4];
			
			//print_r($j);
			
			$text = $j->Result;
			
			$text = utf8_decode($text);
			
	
			$text = preg_replace('/â€”/U', '—', $text);
	
			echo $text;
			
			$text = bhl_clean_ocr_text($text);
			
			echo $text;
			
		}
}


?>
