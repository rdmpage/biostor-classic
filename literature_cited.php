<?php

// Extract literature cited from references 

require_once(dirname(__FILE__) . '/db.php');
require_once(dirname(__FILE__) . '/bhl_find.php');
require_once(dirname(__FILE__) . '/reference.php');


//--------------------------------------------------------------------------------------------------
function store_citation_string($reference)
{
	global $db;
	
	$citation_string_id = 0;
	
	$sql = 'SELECT * FROM rdmp_citation_string WHERE (citation_string=' . $db->qstr($reference->citation) . ') LIMIT 1';
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$citation_string_id = $result->fields['citation_string_id'];
	}
	
	if ($citation_string_id == 0)
	{
		$sql = 'INSERT INTO rdmp_citation_string(citation_string) VALUES(' . $db->qstr($reference->citation) . ')';
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
		$citation_string_id = $db->Insert_ID();
		
		// By default new citation is it's own cluster
		$sql = 'UPDATE rdmp_citation_string SET citation_cluster_id=' . $citation_string_id . ' WHERE citation_string_id=' . $citation_string_id;

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $insert_sql);	
		
		// If citation has been parsed successfully store this
		if (isset($reference->genre))
		{
			$sql = 'UPDATE rdmp_citation_string SET citation_object=' . $db->qstr(json_encode($reference)) . ' WHERE citation_string_id=' . $citation_string_id;
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __LINE__ . "]: " . $insert_sql);	
		}
	}
	
	return $citation_string_id;
}
	

//--------------------------------------------------------------------------------------------------
function is_name($str)
{
	$is_name = false;
	
	if (preg_match('/
	[A-Z][a-zA-Z]+,
	\s*
	([A-Z]\.\s*)+
	/x', $str))
	{
		$is_name = true;
	}
	
	return $is_name;
}

//--------------------------------------------------------------------------------------------------
function is_end_of_citation($str, $mean_line_length)
{
	$is_end = false;
	
	//echo __LINE__ . " $str\n";
	
	if (preg_match('/[­|-|—|–](\d+)\.$/u', $str))
	{
		//echo __LINE__ . "\n";
		$is_end = true;
	}

	if (preg_match('/pp\.(\s+\w+\.)?$/', $str))
	{
		$is_end = true;
	}

	// F C Thompson-style references
	if (preg_match('/([0-9]{2}|\?\?)\]$/', $str))
	{
		$is_end = true;
	}


	if (preg_match('/\.\]$/', $str))
	{
		$is_end = true;
	}

	if (preg_match('/\.$/', $str))
	{
		if (strlen($str) < $mean_line_length)
		{
			if (!is_name($str))
			{
				$is_end = true;
			}
		}
	}
	
	return $is_end;
}

//--------------------------------------------------------------------------------------------------
// process a citation
function process_citation($citation)
{
	$debug = false;
	$matched = false;
	$matches = array();
	
	$series = '';
	
	// Clean
	if (preg_match('/\(Ser\. (?<series>\d+)\)/', $citation, $matches))
	{
		$series = $matches['series'];
		$citation = preg_replace('/\(Ser\. \d+\)/', '', $citation);
	}
	if (preg_match('/\Series (?<series>\d+),/', $citation, $matches))
	{
		$series = $matches['series'];
		$citation = preg_replace('/Series \d+,/', '', $citation);
	}
	
	$citation = preg_replace('/^ZOOTAXA/', "", $citation);

	
	// Fix pagination character from ZootaxaPDF
	$citation = preg_replace('/(\d+)([­|-|—|–])(\d+)\.$/u', "$1-$3", $citation);
	
	if (!$matched)
	{
		if (preg_match('/
		(?<authorstring>.*)
		\s+
		\((?<year>[0-9]{4})[a-z]?(\s+"[0-9]{4}")?\)[\.|:]?
		\s+
		(?<title>(([^\.]+|(?R))(\.))+)
		(?<secondary_title>.*),
		\s+
		(?<volume>(\d+|([L|X|I|V]+)))
		(\s*\((?<issue>\d+([-|\/]\d+)?)\))?
		,
		\s+
		(?<spage>[e]?\d+)
		(
		[-|­|-|—|–| ]
		(?<epage>\d+)
		)?
		/xu', $citation, $matches))
		{
			if ($debug) 
			{
				echo __LINE__ . "\n";
				print_r($matches);	
			}
			$matches['genre'] = 'article';
			$matched = true;
		}	
	}
	
	/*
	// Rec Aust Mus
	if (!$matched)
	{
		if (preg_match('/
		(?<authorstring>.*)
		\s+
		(?<year>[0-9]{4})[a-z]?
		\.
		\s+
		(?<title>(([^\.]+|(?R))(\.))+)
		(?<secondary_title>.*)
		\s+
		(?<volume>(\d+|([L|X|I|V]+)))
		(\s*\((?<issue>\d+([-|\/]\d+)?)\))?
		:
		\s+
		(?<spage>[e]?\d+)
		(
		[-|­|-|—|–| ]
		(?<epage>\d+)
		)?
		/xu', $citation, $matches))
		{
			if ($debug) 
			{
				echo __LINE__ . "\n";
				print_r($matches);	
			}
			$matches['genre'] = 'article';
			$matched = true;
		}	
	}	
	*/
	
	if (!$matched)
	{
		// Peters, J.A. 1964. Dictionary of Herpetology: A Brief and Meaningful Definition of Words and Terms Used in Herpetology. Hafner Publishing Company, New York. 393 pp.
		if (preg_match('/
		(?<authorstring>.*)
		\s+
		[\(]?(?<year>[0-9]{4})[a-z]?(\s+"[0-9]{4}")?[\)]?
		\.?
		\s+
		(?<title>(([^\.]+|(?R))(\.))+)
		\s+
		(?<publisher>.*),
		(?<publoc>.*)
		[\.|,]
		\s+
		(?<pages>\d+)
		\s+
		pp.		
		/xu', $citation, $matches))
		{
			if ($debug) 
			{
				echo __LINE__ . "\n";
				print_r($matches);	
			}
			$matches['genre'] = 'book';
			$matched = true;
		}	
	}

	// Post process	
	$ref = new stdclass;
	$ref->citation = $citation;
	
	if (!$matched)
	{
		echo "FAILED TO PARSE\n----------------------------------\n";
	}
	else
	{
		
		if ($series != '')
		{
			$ref->series = $series;
		}
		
		foreach ($matches as $k => $v)
		{
			switch ($k)
			{
				case 'genre':
				case 'secondary_title':
				case 'issue':
				case 'spage':
				case 'epage':
				case 'year':
				case 'publisher':
				case 'publoc':
				case 'pages':
					if ($v != '')
					{
						$ref->{$k} = trim($v);
					}
					break;
					
				case 'title':
					$v = preg_replace('/\.$/', '', trim($v));
					$ref->{$k} = $v;
					break;
					
				case 'volume':
					// Clean up volume (if Roman convert to Arabic)
					
					if (!is_numeric($v))
					{
						if (preg_match('/^[MDCLXVI]+$/', $v))
						{
							$v = arabic($v);
						}
					}
					
					$ref->{$k} = $v;
					break;
					
				case 'authorstring':
					$v = preg_replace('/&/', '|', $v);
					$v = preg_replace('/(Jr\.[,]?\s+)/', "", $v);
					$v = preg_replace('/([A-Z]\.),/', "$1|", $v);
					$v = preg_replace('/\|\s*\|/', "$1|", $v);
					$v = preg_replace('/\|$/', "", $v);
					$authors = explode("|", $v);
					
					//echo "authors=$v\n";
					
					foreach ($authors as $a)
					{
						{
							reference_add_author_from_string($ref, $a);
						}
					}
					break;
				
				default:
					//echo "$k\n";
					break;
			}
		}
		
		unset($ref->{0});
						
		//print_r($ref);
	}
	
	return $ref;
}

//--------------------------------------------------------------------------------------------------
// Finite state machine to extract individual references
function extract_literature_cited($pages, &$citations)
{	
	// Stats
	$sum_line_length = 0;
	$num_lines = 0;
	foreach ($pages as $page)
	{
		$lines = explode("\n", $page);
		$num_lines += count($lines);
	
		foreach ($lines as $line)
		{
			$sum_line_length += strlen($line);
		}
	}
	$mean_line_length = $sum_line_length / $num_lines;
	
	echo "Lines: " . $num_lines . "\n";
	echo "Text length: " . $sum_line_length . "\n";
	echo "Mean length: " . $mean_line_length . "\n";
	
	// Need rules for skipping running head
	//print_r($text);
	
	define ('STATE_START', 			0);
	define ('STATE_IN_REFERENCES', 	1);
	define ('STATE_OUT_REFERENCES',	2);
	define ('STATE_START_CITATION',	3);
	define ('STATE_END_CITATION',	4);
	
	$state = STATE_START;
	
	$citation = '';
		
	foreach ($pages as $page)
	{
		$lines = explode("\n", $page);
				
		$n = count($lines);
		$line_number = 0;

		// Handle hyphenation
		$hyphens = array();
		$hyphens[0] = 0;
	
		// Skip running head
		$line_number++;
		
		// Hyphen
		$last_line_had_hyphen = false;
	
		while (($state != STATE_OUT_REFERENCES) && ($line_number < $n))
		{
			$line = trim($lines[$line_number]);	
				
			// Trim and flag hyphenation
			if (preg_match('/[A-Za-z][-|­]$/', $line))
			{
				$line = preg_replace('/[-|­]$/', '', $line);
				$hyphens[$line_number] = 1;
			}
			else
			{
				$hyphens[$line_number] = 0;
			}
			
			$next_state = $state;
						
			switch ($state)
			{
				case STATE_START:
					// Look for references
					if (preg_match('/^\s*(REFERENCES|LITERATURE CITED|ZOOTAXA References)$/i', $line))
					{
						// Ignore table of contents
						if (preg_match('/\.?\s*[0-9]$/', $line))
						{
						}
						else
						{
							$state = STATE_IN_REFERENCES;
						}
					}
					break;
					
				case STATE_IN_REFERENCES:
					if (preg_match('/^[A-Z]/', $line))
					{
						if (preg_match('/^((Note[s]? added in proof)|(Appendix)|(Buchbesprechungen)|(Figure)|(Index))/i', $line))
						{
							$state = STATE_OUT_REFERENCES;
						}
						else
						{
							$state = STATE_START_CITATION;
							$citation = $line;
							if (is_end_of_citation($line, $mean_line_length))
							{
								$citations[] = process_citation($citation);
								$state = STATE_IN_REFERENCES;
							}
						}
					}
					break;
					
				case STATE_START_CITATION:
					if ($hyphens[$line_number - 1] == 0)
					{
						$citation .= ' ';
					}
					$citation .= $line;
					if (is_end_of_citation($line, $mean_line_length))
					{
						$citations[] = process_citation($citation);
						$state = STATE_IN_REFERENCES;
					}
					break;
					
			}
			$line_number++;

		}
	
	}
}


//--------------------------------------------------------------------------------------------------
// extract from text extracted from PDFs (e.g., Zootaxa)
function extract_literature_cited_from_text($text, &$citations)
{
	$text = utf8_encode($text);
	$pages = explode("\f", $text);
	$citations = array();
	extract_literature_cited($pages, $citations);
}

//--------------------------------------------------------------------------------------------------
// extract from BHL-derived text
function extract_literature_cited_from_bhl_text($text, &$citations)
{

}

//--------------------------------------------------------------------------------------------------
// Process and store citations
function postprocess_citations($reference_id, $citations)
{
	global $db;
	
	$citation_count = 0;
	
	// Avoid duplications
	if ($reference_id != 0)
	{
		$sql = 'DELETE FROM rdmp_reference_cites WHERE (reference_id=' . $reference_id . ')';
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	}	
	
	foreach ($citations as $citation)
	{
		$citation_reference_id = 0;
		
		if (isset($citation->genre))
		{		
			// Lookup articles
			if ($citation->genre == 'article')
			{
				$citation_reference_id = 0;
				
				// Do we have this already in BioStor?
				$citation_reference_id = db_find_article($citation);
				
				if ($citation_reference_id == 0)
				{
					// Try BioStor OpenURL search
					$citation_reference_id = import_from_openurl(reference_to_openurl($citation));				
				}	
				
				if ($citation_reference_id == 0)
				{
					// Try bioGUID
					if (bioguid_openurl_search($citation))
					{
						$citation_reference_id = db_store_article($citation);
					}
				}	
			}
		}
		
		// At this stage if citation_reference_id is 0 we haven't found it
		
		// 1. store citation string (we are building a list of all such strings)
		$citation_string_id = store_citation_string($citation);
		
		// 2. If we've found it in BioStor, record link between citation string and reference id
		if ($citation_reference_id != 0)
		{
			$sql = 'DELETE FROM rdmp_reference_citation_string_joiner WHERE(reference_id=' . $citation_reference_id . ')
			AND (citation_string_id=' . $citation_string_id . ')'; 
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	
			$sql = 'INSERT INTO rdmp_reference_citation_string_joiner (reference_id,citation_string_id)
			VALUES(' . $citation_reference_id . ',' . $citation_string_id . ')';
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
		}		
		
		// 3. Store link between source reference and citation string (which we will use to display literature cited)
		if ($reference_id != 0)
		{
			$sql = 'INSERT INTO rdmp_reference_cites (reference_id, citation_string_id, citation_order)
			VALUES(' . $reference_id . ',' . $citation_string_id . ',' . $citation_count . ')';
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
		
			$citation_count++;
		}
		
	}
}

if (0)
{

$pdfs=array(
//'http://www.mapress.com/zootaxa/2005f/zt00899.pdf'
//'http://www.mapress.com/zootaxa/2005f/zt00898.pdf'
//'http://www.mapress.com/zootaxa/2005f/zt00894.pdf'
//'http://www.mapress.com/zootaxa/2005f/zt00888.pdf'
//'http://www.mapress.com/zootaxa/2005f/zt00855.pdf'
//'http://www.mapress.com/zootaxa/2003f/zt00388.pdf'
//'http://www.mapress.com/zootaxa/2005f/zt00887.pdf',

/*'http://www.mapress.com/zootaxa/2005f/zt00961.pdf',
'http://www.mapress.com/zootaxa/2005f/zt00957.pdf',
'http://www.mapress.com/zootaxa/2005f/zt00945.pdf',*/

'http://www.mapress.com/zootaxa/2005f/zt00941.pdf',
'http://www.mapress.com/zootaxa/2005f/zt00939.pdf',
'http://www.mapress.com/zootaxa/2005f/zt00938.pdf',
'http://www.mapress.com/zootaxa/2005f/zt00936.pdf',
'http://www.mapress.com/zootaxa/2005f/zt00932.pdf',


/*'http://www.mapress.com/zootaxa/2005f/zt00882.pdf',
'http://www.mapress.com/zootaxa/2005f/zt00880.pdf',
'http://www.mapress.com/zootaxa/2005f/zt00872.pdf',
'http://www.mapress.com/zootaxa/2005f/zt00864.pdf',
'http://www.mapress.com/zootaxa/2005f/zt00861.pdf',*/

//'http://www.mapress.com/zootaxa/2009/f/zt02168p068.pdf'
);

foreach ($pdfs as $pdf)
{
	$filename = 'tmp/' . basename($pdf);
	
	if (!file_exists($filename))
	{
		$bits = get($pdf);
		
		$cache_file = @fopen($filename, "w+") or die("could't open file --\"$filename\"");
		@fwrite($cache_file, $bits);
		fclose($cache_file);
	}
	
	// extract text
	
	$command = '/usr/local/bin/pdftotext -raw ' . $filename;
	system($command);
	
	
	// text file name
	$filename = str_replace(".pdf", ".txt", $filename);
	
	// biostor id
	
	$sql = 'SELECT * FROM rdmp_reference WHERE pdf=' . $db->qstr($pdf) . ' LIMIT 1';
	
	echo $sql;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


	if ($result->NumRows() == 1)
	{
	
		$reference_id = $result->fields['reference_id'];
		
		echo "reference_id: $reference_id\n";
		
		echo "$filename\n";
		
		// extract
		// Open file 
		$handle = fopen($filename, "r");
		$text = fread($handle, filesize($filename));
		fclose($handle);
		
		$citations = array();
		
		extract_literature_cited_from_text($text, $citations);
		
		print_r($citations);
		
		postprocess_citations($reference_id, $citations);
	}
	else
	{
		echo "NOT FOUND in biostor\n\n\n";
	}
}


}
else
{

	$refs = array(
	//	19685 => 'zt02201p010.txt'
	
	//19687 => 'zt02201p020.txt'
	
	//19684 => 'zt02200p068.txt'
	
	//17255 => 'zt01418p628.txt'
	
	//17657=> 'zt01572p082.txt'
	
	//18529 => 'zt01756p061.txt'
	
	//20932 => '32_complete.txt'
	
	19307 => 'Zootaxa_Parapanteles.txt'
		);
	
	foreach ($refs as $reference_id => $filename)
	{
		// Open file 
		$handle = fopen($filename, "r");
		$text = fread($handle, filesize($filename));
		fclose($handle);
		
		$citations = array();
		
		extract_literature_cited_from_text($text, $citations);
		
		print_r($citations);
		
		postprocess_citations($reference_id, $citations);
	}
}


?>