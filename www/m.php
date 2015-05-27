<?php

require_once(dirname(dirname(__FILE__)) . '/db.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');

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
function matching_pages($publication, $year)
{
	global $debug;
	$pages = array();
	
	$matches = array();
	$matched = false;

	// Parse citation
	
	if (!$matched)
	{
		//echo $publication;
		if (preg_match('/(?<journal>.*)\s+(?<volume>\d+):\s*(?<page>\d+)\.?$/Uu', $publication, $matches)) 
		{
			$matched = true;
		}
	}
	
	if (!$matched)
	{
		if (preg_match('/(?<journal>.*),\s+(\((?<series>.*)\)\s+)?(?<volume>\d+),(\s+\((?<issue>\d+)\))?\s+(?<page>\d+)\.$/Uu', $publication, $matches))
		{
			$matched = true;
		}
	}
	
	if (!$matched)
	{
		//echo $publication;
		if (preg_match('/(?<journal>.*)\s+(?<volume>\d+):\s*(?<page>\d+)\s+(?<year>[0-9]{4})/Uu', $publication, $matches)) 
		{
			$matched = true;
		}
	}
	
	if (!$matched)
	{
		if (preg_match('/(?<journal>.*),\s+(?<volume>\d+),\s+(?<page>\d+)\.$/Uu', $publication, $matches))
		{
			$matched = true;
		}
	}	
	
	
	if (!$matched)
	{
		if (preg_match('/(?<journal>.*),\s+(?<volume>\d+),\s+(?<page>\d+)/Uu', $publication, $matches))
		{
			$matched = true;
		}
	}	
	
	if (!$matched)
	{
		if (preg_match('/(?<journal>.*),\s+(?<volume>\d+),\s+(?<page>\d+),/Uu', $publication, $matches))
		{
			$matched = true;
		}
	}		
	
	// Proc. U.S. nat. Mus., 99, no. 3247, 475.
	if (!$matched)
	{
		if (preg_match('/(?<journal>.*),\s+(?<volume>\d+), no. (?<issue>\d+),\s+(?<page>\d+)\.$/Uu', $publication, $matches))
		{
			$matched = true;
		}
	}	
	
	if (!$matched)
	{
		if (preg_match('/(?<journal>.*),\s+(?<volume>\d+) \((?<issue>\d+)\),\s+(?<page>\d+)\.$/Uu', $publication, $matches))
		{
			$matched = true;
		}
	}	
	
	// Spixiana 7 (2): 125.
	if (!$matched)
	{
		if (preg_match('/(?<journal>.*)\s+(?<volume>\d+) \((?<issue>\d+)\):\s+(?<page>\d+)\.$/Uu', $publication, $matches))
		{
			$matched = true;
		}
	}		
	
	// Ann. Mag. Nat. Hist. , ser. 8 vol. 13 p. 436
	if (!$matched)
	{
		if (preg_match('/(?<journal>.*)\s*,\s+ser.\s+(?<series>\d+)\s+vol.\s*(?<volume>\d+)\s+p.\s+(?<page>\d+)\s+(?<year>[0-9]{4})/Uu', $publication, $matches))
		{
			$matched = true;
		}
	}		
	
	print_r($matches);
	echo $publication;
	
	
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
	
	print_r($pages);
	
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
/**
 * @brief Handle microcitation request
 *
 */
function main()
{
	global $config;
	global $debug;
		
	// If no query parameters 
	if (count($_GET) == 0)
	{
		//display_form();
		exit(0);
	}	
	
	$callback = '';
	if (isset($_GET['callback']))
	{
		$callback = $_GET['callback'];
	}
	
	
	$format = 'json';
	
	$debug = false;
	if (isset($_GET['debug']))
	{	
		$debug = true;
	}
	
	$q = $_GET['q'];
   		
	$result->pages = matching_pages($q);

	if (count($result->pages) == 0)
	{
		if ($format == 'json')
		{
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');
			$_SERVER['REDIRECT_STATUS'] = 404;
			echo 'Not found';
			exit();
		}
	}
	else
	{		
		// Do we have any references for the pages?
		$result->references = reference_from_page($result->pages);		
	}
	
	
	// Candidate pages
	if ($format == 'json')
	{
		header("Content-type: text/plain; charset=utf-8\n\n");
		if ($callback != '')
		{
			echo $callback . '(';
		}
		echo json_format(json_encode($result));
		if ($callback != '')
		{
			echo ')';
		}	
	}
	

}


main();

?>