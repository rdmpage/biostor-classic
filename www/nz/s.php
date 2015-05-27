<?php

require_once(dirname(dirname(__FILE__)) . '/db.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');

require_once(dirname(dirname(__FILE__)) . '/bhl_text.php');
require_once(dirname(__FILE__) . '/agrep.php');


/*

Examples


Approximate match in OCR
------------------------
745	Acanthodelta	Hampson 1908	Ann. Mag. nat. Hist., (8) 1, 487.		1908







*/


$debug = false;
$threshold = 2;

$notparsed = array();
$notfound = array();

$filename = 'nz1.txt';
//$filename = 'nz0.txt';
//$filename = 'nz2.txt';

//--------------------------------------------------------------------------------------------------
function reference_from_matches($matches)
{
	$reference = new stdclass;
	
	// authors
	if (isset($matches['authorstring']))
	{
		$authorstring = $matches['authorstring'];
		$authorstring = preg_replace("/,$/u", "", trim($authorstring));
		$authorstring = preg_replace("/&/u", "|", $authorstring);
		$authorstring = preg_replace("/\.,/u", "|", $authorstring);				
		$reference->authors = explode("|", $authorstring);
	}
	
	if (isset($reference->authors))
	{
		for ($i = 0; $i < count($reference->authors); $i++)
		{
			$author_parts = explode(",", $reference->authors[$i]);
			$forename = $author_parts[1];
			$forename = preg_replace('/([A-Z])\.([A-Z])/', '$1. $2', trim($forename));
			$reference->authors[$i] = $forename . ' ' . trim($author_parts[0]);
		}
	}
	
	$reference->genre = 'article';
	
	foreach ($matches as $k => $v)
	{
		switch ($k)
		{
			case 'journal':
				$reference->secondary_title = $v;
				break;
				
			case 'page':
				$reference->spage = $v;
				break;
				
			case 'title':
			case 'volume':
			case 'series':
			case 'issue':			
			case 'year':
				if ($matches[$k] != '')
				{
					$reference->$k = $v;
				}
				break;
				
			default:
				break;
		}
	}

	return $reference;
}

//--------------------------------------------------------------------------------------------------
// Find bext matching word (assumes we want a single word to match, e.g. a genus)
function best_match($pages, $name, $threshold = 2)
{
	$candidates = array();
	
	$best_score = strlen($name);
	
	foreach ($pages as $p)
	{
		$text = bhl_fetch_ocr_text($p);
		$text = bhl_unescape_newlines ($text);
		
		$filename = dirname(__FILE__) . '/tmp/' . $p . '.txt';
		$file = fopen($filename, "w");
		fwrite($file, $text);
		fclose($file);
		
		$lines = array();
		$matches = array();
	
		agrep($filename, $name, $lines, $matches);
		
		foreach ($matches as $m)
		{
			$words = explode(" ", $m);
			foreach ($words as $word)
			{
				$w = preg_replace('/[^a-zA-Z0-9-\s]/', '', $word);
				$score = levenshtein(strtolower($name), strtolower($w));
				if ($score <= $threshold)
				{
					$match = new stdclass;
					
					$match->score = $score;
					$match->string = $w;
					$match->ubio = false;
				
					if ($score < $best_score)
					{
						$candidates = array();
						$best_score = $score;
					}
					$candidates[$p] = $match;
				}
			}
		}		
	}
	
	return $candidates;
}

//--------------------------------------------------------------------------------------------------
// Has uBio TaxonFinder found this name in any of these pages?
function ubio_match($pages, $name)
{
	global $db;
	
	$candidates = array();
	
	// Did uBio find the name on any of these pages?
	$sql = 'SELECT PageID FROM bhl_page_name WHERE NameConfirmed=' . $db->qstr($name) 
		. 'AND PageID IN (' . join(",", $pages) . ')';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		$match = new stdclass;
		$match->score = 0;
		$match->string = $name;
		$match->ubio = true;
		$candidates[$result->fields['PageID']] = $match;
		$result->MoveNext();				
	}	
	
	return $candidates;
}


//--------------------------------------------------------------------------------------------------
function matching_pages($publication, $year)
{
	global $debug;
	$pages = array();
	
	$matches = array();
	$matched = false;

	// Parse citation
	if (!$matched)
	{
		if (preg_match('/(?<journal>.*),\s+(\((?<series>.*)\)\s+)?(?<volume>\d+),(\s+\((?<issue>\d+)\))?\s+(?<page>\d+)\.$/Uu', $publication, $matches))
		{
			$matched = true;
		}
	}
	
	if (!$matched)
	{
	}
	else
	{
		$reference = reference_from_matches($matches);
		
		
		if (!isset($reference->issn))
		{
			// Try and get ISSN from bioGUID
			$issn = issn_from_title($reference->secondary_title);
			if ($issn != '')
			{
				$reference->issn = $issn;
			}
			else
			{
				// No luck with ISSN, look for OCLC
				if (!isset($reference->oclc))
				{
					$oclc = oclc_for_title($reference->secondary_title);
					if ($oclc != 0)
					{
						$reference->oclc = $oclc;
					}
				}
			}
		}	
		//print_r($reference);
		
		$atitle = '';
		if (isset($reference->title))
		{
			$atitle = $reference->title;
		}
		$search_hits = bhl_find_article(
			$atitle,
			$reference->secondary_title,
			$reference->volume,
			$reference->spage,
			(isset($reference->series) ? $reference->series : ''),
			(isset($reference->year) ? $reference->year : '')
			);
	
		if (count($search_hits) == 0)
		{
			// try alternative way of searching using article title
			$search_hits = bhl_find_article_from_article_title	(
				$atitle,
				$reference->secondary_title,
				$reference->volume,
				$reference->spage,
				(isset($reference->series) ? $reference->series : '')
				);		
		}
		
		//print_r($search_hits);
		
		foreach ($search_hits as $hit)
		{
			$pages[] = $hit->PageID;
		}
		
	}
	
	return $pages;
	

}

//--------------------------------------------------------------------------------------------------
// Find reference(s) for page(s)
function reference_from_page($pages)
{
	global $db;
	
	$references = array();
	
	$sql = 'SELECT * from rdmp_reference_page_joiner WHERE PageID IN (' . join(",", $pages) . ')';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		$references[$result->fields['PageID']] = $result->fields['reference_id'];
		$result->MoveNext();				
	}	
	
	$references = array_unique($references);
	
	return $references;
}



//--------------------------------------------------------------------------------------------------
$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$line = fgets($file_handle);
	$line = trim($line);
	
	if ($debug) { echo $line . "\n"; }
   	$parts = explode("\t", $line);
   	
   	$result = new stdclass;
   	$result->id 			= $parts[0];
   	$result->name 			= $parts[1];
   	$result->author 		= $parts[2];
   	$result->publication 	= $parts[3];
   	$result->comment 		= $parts[4];
   	$result->year 			= $parts[5];
   		
	$result->pages = matching_pages($result->publication, $result->year);

	if (count($result->pages) == 0)
	{
		// Not found
	}
	else
	{		
		// Do we have any references for the pages?
		$result->references = reference_from_page($result->pages);
		
		// Do we have the name on these pages?
		$result->matches = ubio_match($result->pages, $result->name);
		
		if (count($result->matches) > 0)
		{
			// uBio has found this name on one or more pages
		}
		else
		{
			$result->matches = best_match($result->pages, $result->name);
		}
	}
	print_r($result);
}



?>
