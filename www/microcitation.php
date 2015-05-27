<?php

require_once(dirname(dirname(__FILE__)) . '/db.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');

require_once(dirname(dirname(__FILE__)) . '/bhl_text.php');
require_once(dirname(__FILE__) . '/agrep.php');

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
	
	//print_r($matches);
	//echo $publication;
	
	
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
function display_form()
{
	echo '<html>';
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	echo '<title>Microcitation Search - BioStor</title>';
	echo '<link rel="shortcut icon" href="http://biostor.org//images/biostor-shadow32x32.ico" />';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";	
	echo '<link type="text/css" href="http://biostor.org/css/main.css" rel="stylesheet" />';
	echo '   <style type="text/css">'  . "\n";
	echo '
	label {
		float:left;
		width:80px;
		text-align:right;
		padding-right:4px;
		padding-top:4px;
	}
	textarea {
		width:200px;
	}
	input {
		width: 200px;
	}
	</style>'  . "\n";
	
	echo '<script>
function reportErrors(errors)
{
 var msg = "The form contains errors...\n";
 for (var i = 0; i<errors.length; i++) {
 var numError = i + 1;
  msg += "\n" + numError + ". " + errors[i];
}
 alert(msg);
}	

function validate_form(form)
{
	var errors = [];

	if (form.publication.value == "")  
	{
		errors[errors.length] = "Please supply a citation";
	}	
	if (form.year.value == "")  
	{
		errors[errors.length] = "Please supply a year";
	}	
	if (form.name.value == "")  
	{
		errors[errors.length] = "Please supply a taxonomic name";
	}	

	if (errors.length > 0)
	{
		reportErrors(errors);
		return false;
	}
	return true;
}

</script>'; 	
	
	echo '</head>';
	echo '<body>';

?>

<a href=".">Home</a>
<h1>BioStor Microcitation Search</h1>

<form method="get" action="microcitation.php" onsubmit="return validate_form(this)">

<div><label for="genus">Genus</label><input name="name" id="name" type="text" placeholder="Abana" value="Abana"></div>
<div><label for="author">Author</label><input name="author" id="author" type="text" placeholder="Distant 1908" value="Distant 1908"></div>
<div><label for="publication">Publication</label><textarea id="publication" name="publication" rows="4" placeholder="Ann. Mag. nat. Hist., (8) 2, 72.">Ann. Mag. nat. Hist., (8) 2, 72.</textarea></div>
<div><label for="year">Year</label><input name="year" id="year" type="text" placeholder="1908" value="1908"></div>

<div><label for="format">Format</label><select name="format">
<option value="html" selected="html">HTML</option>
<option value="json" >JSON</option>
</select></div>


<div><label></label><button type="submit">Go</button></div>



</form>




<?php
	echo '</body>';
	echo '</html>';	



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
		display_form();
		exit(0);
	}	
	
	$format='html';
	if (isset($_GET['format']))
	{
		switch ($_GET['format'])
		{
			case 'html':
				$format = 'html';
				break;

			case 'json':
				$format = 'json';
				break;

			default:
				$format = 'html';
				break;
		}
	}
	
	$callback = '';
	if (isset($_GET['callback']))
	{
		$callback = $_GET['callback'];
	}
	
	$debug = false;
	if (isset($_GET['debug']))
	{	
		$debug = true;
	}
	
	
   	$result = new stdclass;
   	$result->name 			= $_GET['name'];
   	$result->author 		= $_GET['author'];
   	$result->publication 	= trim($_GET['publication']);
   	$result->year 			= $_GET['year'];
   		
	$result->pages = matching_pages($result->publication, $result->year);
	
	if ($format=='html')
	{
		echo '<html>';
		echo '<head>';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo '<title>Microcitation Search - BioStor</title>';
		echo '<link rel="shortcut icon" href="http://biostor.org//images/biostor-shadow32x32.ico" />';
		echo '<link type="text/css" href="http://biostor.org/css/main.css" rel="stylesheet" />';
		echo '</head>';
		echo '<body>';
		echo '<a href="microcitation.php">Back</a>';	
		echo '<h1>Microcitation search results for "' . $result->name . '"</h1>';
	}

	if (count($result->pages) == 0)
	{
		// Not found
		if ($format == 'html')
		{
			echo '<span style="background-color:red;color:white;">Couldn\'t find anything matching the citation "' . $result->publication . '"</span>';
		}
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
	
	if ($format=='html')
	{	
		echo '<p>Published in "' . $result->publication . '"</p>';
		echo '<div>';
		foreach ($result->pages as $PageID)
		{
			echo '<div style="float:left;padding:20px;margin:2px;height:260px;width:180px;';
			
			if (isset($result->matches[$PageID]))
			{
				switch ($result->matches[$PageID]->score)
				{
					case 0:
						echo 'background-color:#0F6';
						break;
					case 1:
						echo 'background-color:yellow;';
						break;
					case 2:
						echo 'background-color:orange;';
						break;
					default:
						break;
				}
						
			}
			
			echo '">';
			echo '<div style="width:100%;text-align:center;">';
			echo '<a href="http://www.biodiversitylibrary.org/page/' . $PageID . '" target="_new">';
			echo '<img style="-webkit-box-shadow: 4px 4px 10px rgba(64,64,64,0.6);-moz-box-shadow: 4px 4px 10px rgba(64,64,64,0.6);border:1px solid rgb(192,192,192);"  height="180" src="bhl_image.php?PageID=' . $PageID . '&amp;thumbnail" />';
			echo '</a>';
			echo '</div>';
			echo '<div>';
			echo '<p/>';
			echo '<div>BHL PageID <a href="http://www.biodiversitylibrary.org/page/' . $PageID . '" target="_new">' . $PageID . '</a></div>';
			
			
			if (isset($result->matches[$PageID]))
			{
				echo '<div>';
				echo 'Found by uBio? ';
				if ($result->matches[$PageID]->ubio)
				{
					echo '✔';
				}
				else
				{
					echo '✘';
				}
				echo '</div>';
				echo '<div>';
				echo '<b>' . $result->matches[$PageID]->string . ' [' . $result->matches[$PageID]->score . ']</b>';
				echo '</div>';
				
				if (isset($result->references[$PageID]))
				{
					echo '<div>BioStor <a href="http://biostor.org/reference/' . $result->references[$PageID] . '" target="_new">' . $result->references[$PageID] . '</a></div>';
				}
				
			}
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';
		echo '<div style="clear:both;"></div>';
		echo '<div>';
		
		echo '<div>';
		echo '<div style="margin-right:8px;width:32px;height:32px;float:left;background-color:#0F6;"></div>';
		echo '<p>Exact match found</p>';
		echo '</div>';
		echo '<div style="clear:both;"></div>';
		echo '<div>';
		echo '<div style="margin-right:8px;width:32px;height:32px;float:left;background-color:yellow;"></div>';
		echo '<p>Matches string with one difference [1]</p>';
		echo '</div>';
		echo '<div style="clear:both;"></div>';
		echo '<div>';
		echo '<div style="margin-right:8px;width:32px;height:32px;float:left;background-color:orange;"></div>';
		echo '<p>Matches string with two differences [2]</p>';
		echo '</div>';
		echo '<div style="clear:both;"></div>';
		echo '<div>';
		echo '<div style="margin-right:8px;width:32px;height:32px;float:left;text-align:center;">✔</div>';
		echo '<p>uBio found name on this page</p>';
		echo '</div>';
		echo '<div style="clear:both;"></div>';
		echo '<div>';
		echo '<div style="margin-right:8px;width:32px;height:32px;float:left;text-align:center;">✘</div>';
		echo '<p>uBio didn\'t find on this page</p>';
		echo '</div>';
		
		
		echo '</div>';
	
		echo '</body>';
		echo '</html>';
	}
}


main();

?>