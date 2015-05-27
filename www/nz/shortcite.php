<?php

require_once(dirname(dirname(__FILE__)) . '/db.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');

require_once(dirname(dirname(__FILE__)) . '/bhl_text.php');
require_once(dirname(__FILE__) . '/agrep.php');

$threshold = 2;

$debug = true;

$notparsed = array();
$notfound = array();

$filename = 'nz1.txt';
$filename = 'nz0.txt';

$html = '';
$html .= '<table border="1">';


$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$line = fgets($file_handle);
	$line = trim($line);
	
	if ($debug)
	{
		echo $line . "\n";
	}
   	$parts = explode("\t", $line);
   
   	$id = $parts[0];
   	$name = $parts[1];
	
	$publication = $parts[3];
	
	$parameters = array();
	// Default parameters
	$parameters['genre'] = 'article';
	$parameters['format'] = 'json';
	
	$matched = false;
	
	$result_obj = new stdclass;
	
	$result_obj->tokens = $parts;
	
	if (!$matched)
	{
		if (preg_match('/(?<title>.*),\s+(\((?<series>.*)\)\s+)?(?<volume>\d+),(\s+\((?<issue>\d+)\))?\s+(?<page>\d+)\.$/Uu', $publication, $matches))
		{
			//print_r($matches);
			$matched = true;
			
			$parameters['title'] = $matches['title'];
			$parameters['series'] = $matches['series'];
			$parameters['volume'] = $matches['volume'];
			$parameters['issue'] = $matches['issue'];
			$parameters['spage'] = $matches['page'];
			
			$parameters['title'] = $matches['title'];
			$parameters['year'] = $parts[5];
			
			//print_r($parameters);
		}
	}
	
	if (!$matched)
	{
		$notparsed[] = $line;
		$result_obj->parsed = false;
	}
	else
	{
		$result_obj->parsed = true;
		// Find in BioStor/BHL
		$url = 'http://biostor.org/openurl.php?' . http_build_query($parameters);
		
		if ($debug)
		{
			echo $url . "\n";
		}
		
		$json = get($url);
		
		if ($debug)
		{
			echo "BioStor result\n";
			echo $json . "\n";
		}
		
		$hits = json_decode($json);
		
		// Check if we've hit the first page of an article
		if (isset($hits->reference_id))
		{
			
			$PageID = $hits->PageID;
			$hits = array();
			$hit = new stdclass;
			$hit->PageID = $PageID;
			$hits[] = $hit;
		}
		
		if ($debug)
		{
			print_r($hits);
		}
		
		if (count($hits) > 0)
		{
			$pages = array();
			foreach ($hits as $hit)
			{
				$pages[] = $hit->PageID;
			}
			
			$candidates = array();
			
			// Did uBio find the name on any of these pages?
			$sql = 'SELECT PageID FROM bhl_page_name WHERE NameConfirmed=' . $db->qstr($name) . 'AND PageID IN (' . join(",", $pages) . ')';
		
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
			
			while (!$result->EOF) 
			{
				$candidates[] = $result->fields['PageID'];
				$result->MoveNext();				
			}			
						
			if (count($candidates) != 0)
			{
				
				// We have one or more hits
				if ($debug)
				{
					echo "uBio found name\n";
				}
				
				$result_obj->page_name = array();
				foreach ($candidates as $PageID)
				{
					$match = new stdclass;
					$match->name = $name;
					$match->score = 0;		
					$match->ubio = true;
					$result_obj->page_name[$PageID] = $match;
				}
			}
			else
			{
				// OK, uBio didn't find this name on the candidate pages, so lets try an approximate match
				// by grabbing OCR text and looking for name
				$best_score = 100;
				$best_scoring_string = '';
				
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
		
					//print_r($matches);	
					
					foreach ($matches as $m)
					{
						$words = explode(" ", $m);
						foreach ($words as $word)
						{
							$w = preg_replace('/[^a-zA-Z0-9-\s]/', '', $word);
							$score = levenshtein(strtolower($name), strtolower($w));
							if ($score <= $threshold)
							{
								if ($score < $best_score)
								{
									$candidates = array();
									$best_score = $score;
									$best_scoring_string = $word;
								}
								$candidates[] = $p;
							}
							//echo $word . ' ' . $score . "\n";
						}
					}
					
				}
				
				$result_obj->page_name = array();
				if (count($candidates) != 0)
				{
					foreach ($candidates as $PageID)
					{
						$match = new stdclass;
						$match->name = $best_scoring_string;
						$match->score = $best_score;	
						$match->ubio = false;
						$result_obj->page_name[$PageID] = $match;
					}
				}
				
				if ($debug)
				{
					echo "Best match \"$best_scoring_string\", score = $best_score\n";
				}
			}
			
			if (count($candidates) == 0)
			{
				$notfound[] = $line;
				$result_obj->found = false;
			}
			else
			{
				$result_obj->found = true;
				$result_obj->pages = $candidates;
				if ($debug)
				{
					echo "Candidates\n";				
					print_r($candidates);
				}
				// Is page part of article in BioStor				
				$result_obj->references = array();
				
				foreach ($candidates as $PageID)
				{
					$rs = bhl_retrieve_reference_id_from_PageID($PageID);
					if (count($rs) > 0)
					{
						foreach ($rs as $reference_id)
						{
							
						
							$r = db_retrieve_reference($reference_id);
							
							$result_obj->references[$PageID] = $r;
							
							if ($debug)
							{
								echo $r->title . ' ' . reference_to_citation_text_string($r) . "\n";
							}
						}
					}
				}
			}
			
		}
	}
	
	print_r($result_obj);
	
	// html
	$html .= '<tr valign="top">';
	$html .= '<td>';
	$html .= join("</td><td>", $result_obj->tokens);
	$html .= '</td>';
	
	$html .= '<td>';
	
	$html .= '<table width="100%">';
	
	foreach ($result_obj->page_name as $PageID => $v)
	{
		$style = "";
		if ($v->ubio)
		{
			$style = 'style="background-color:green;"';
		}
		else
		{
			if ($v->score == 0)
			{
				$style = 'style="background-color:yellow;"';
			}
			else
			{
				$style = 'style="background-color:orange;"';
			}
		}
		$html .= '<tr ' . $style . '>';
		$html .= '<td>';
		$html .= '<img src="bhl_image.php?PageID=' . $PageID . '&amp;thumbnail" /><br/>';
		$html .= '<a href="http://www.biodiversitylibrary.org/page/' . $PageID . '" target="_new">' . $PageID . '</a>';
		$html .= '</td>';
		
		
		if (isset($result_obj->references[$PageID]))
		{
			$html .= '<td valign="top">';
			$html .= $result_obj->references[$PageID]->title . '<br/>';
			$html .= '<i>' . $result_obj->references[$PageID]->secondary_title . '</i>' . '<br/>';
			$html .= $result_obj->references[$PageID]->reference_id . '<br/>';
			$html .= '</td>';
		}
		
		$html .= '</tr>';
	}
	$html .= '</table>';
	$html .= '</tr>';
	
}
	

$html .= '</table>';

echo $html . "\n";


?>
