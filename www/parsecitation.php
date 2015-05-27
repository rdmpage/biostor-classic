<?php

/**
 * @file parsecitation.php
 *
 * JSON web service that takes a citation string and returns a reference object (if
 * successfully parsed)
 *
 */

require_once('../lib.php');
require_once('../reference.php');

//--------------------------------------------------------------------------------------------------
/**
 * @brief Parse a citation string
 *
 * @param citation Citation string to be parsed
 * @param reference Reference object to be populated
 *
 * @return True if citation successfully parsed, false otherwise
 */
function parse_citation($citation, &$reference)
{
	$matched = false;
	
	$citation = str_replace("\n", '', trim($citation));
	
	if (!$matched)
	{
		if (preg_match('/
		
		(?<year>[0-9]{4})
		([a-z])?
		.?
		((?<title>[^\.]+|(?R))*)
		(\.)
		\s*
		(?<journal>[^\d]+|(?R))
		\s*
		(?<volume>[0-9]+)
		\s*
		(\((?<issue>[0-9]+)\))?
		\s*
		[:|,]
		\s*
		(?<spage>[0-9]+)
		[-|–]
		(?<epage>[0-9]+)
		/xu', $citation, $matches))
		{
			//print_r($matches);
			
			$matched = true;
			
			foreach ($matches as $k => $v)
			{
				$matches[$k] = trim($v);
			}
			
			$reference->genre='article';
			$reference->title = $matches['title'];
			$reference->secondary_title = $matches['journal'];
			$reference->secondary_title = preg_replace('/,$/', '', trim($reference->secondary_title));
			$reference->volume = $matches['volume'];
			if (isset($matches['issue']))
			{
				$reference->issue = $matches['issue'];
			}
			$reference->spage = $matches['spage'];
			$reference->epage = $matches['epage'];
			$reference->year = $matches['year'];
		}
	}
	
	
	// Wikipedia cite
	if (!$matched)
	{
		$rows = preg_split("/\|/", $citation);
				
		$count = 0;
		foreach ($rows as $row)
		{
			if ($count > 0)
			{
				if (preg_match('/(?<key>([A-Za-z0-9_]+))\s*=\s*(?<value>(.*))/', $row, $matches))
				{
					$matched = true;
					
					$value = trim($matches['value']);
					$value = preg_replace('/}}$/', '', $value);
					$value = preg_replace('/^\[\[/', '', $value);
					$value = preg_replace('/\]\]$/', '', $value);
					if ($value != '')
					{
						$key = trim($matches['key']);
						switch ($key)
						{
							case 'journal':
								$reference->secondary_title = $value;
								$reference->genre = 'article';
								break;
								
							case 'pages':
								if (preg_match('/(?<spage>[0-9]+)[\-|–](?<epage>[0-9]+)/u', $value, $match))
								{
									$reference->spage = $match['spage'];
									$reference->epage = $match['epage'];
								}
								break;
							
								
							default:
								$reference->{$key} = $value;
								break;
						}
					}
				}
			}
			$count++;
		}
		
	}
	
	// Older Wikipedia-style
	if (!$matched)
	{
		if (preg_match('/^{{aut\|/', $citation))
		{
			
			if (preg_match_all("/
				([^']''([^'']+|(?R))*'')
				/x", $citation, $matches))
			{
				//print_r($matches);	
				
				$reference->secondary_title = $matches[2][count($matches[2]) - 1];
				
				// get volume and pagination
				if (preg_match("/
					'''(?<volume>[0-9]+)'''
					\s*
					(\((?<issue>[0-9]+)\))?
					\s*
					:
					\s*
					(?<spage>[0-9]+)
					[-|–]
					(?<epage>[0-9]+)
					/xu", $citation, $matches))
				{
					//print_r($matches);	
					$reference->volume = $matches['volume'];
					if (isset($matches['issue']))
					{
						$reference->issue = $matches['issue'];
					}
					$reference->spage = $matches['spage'];
					$reference->epage = $matches['epage'];
					
					$reference->genre = 'article';
					$matched = true;
				}
				
				// get title and year
				if (preg_match("/\(?(?<year>[0-9]{4})\)?:?\s*(?<title>.*)''" . $obj->journal . "/", $citation, $matches))
				{
					$reference->year = $matches['year'];
					$reference->title = trim($matches['title']);
					
					// clean up title
					$reference->title = str_replace("'", "", $reference->title);
					$reference->title = str_replace($reference->secondary_title, "", $reference->title);
					$reference->title = preg_replace('/' . $reference->volume . '$/', '', trim($reference->title));
					$reference->title = preg_replace('/\.$/', '', trim($reference->title));
				}
				
				// journal may be a link to a wiki page, which may have a different name...
				if (preg_match('/\[\[((?<link>[A-Za-zÂÊÁÈÉËÍÎÌÏÓÔÒÛÚÙÜâêáèéëíîìïóôòûúùüÖÜÅÔØ0-9\(\)\.,_\- ]+)\|(?<name>[A-Za-zÂÊÁÈÉËÍÎÌÏÓÔÒÛÚÙÜâêáèéëíîìïóôòûúùüÖÜÅÔØ0-9\(\)\.,_\- ]+))([^\]\]]+|(?R))*\]\]/',  $reference->secondary_title, $matches))
				{
					$reference->secondary_title = $matches['name'];
				}
				// ...or the same name
				$reference->secondary_title = preg_replace('/^\[\[(.*)\]\]$/', "$1", $reference->secondary_title);
				
				
				if (preg_match_all("/
					(\{\{aut\|([^\}\}]+|(?R))*\}\})
					/x", $citation, $a))
				{					
					$author_string = $a[2][0];
					$author_string = str_replace(";", "|", $author_string);
					$author_string = str_replace("&", "|", $author_string);
					$authors = explode("|", $author_string);
					
					$reference->authors = array();
					foreach ($authors as $author)
					{
						reference_add_author_from_string($reference,$author);
					}
				}
			}
		}
	}
	
	return $matched;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Web service, returns reference object in JSON, or 404 if parsing failed.
 *
 */
function main()
{
	$citation = '';
	if (isset($_GET['citation']))
	{
		$citation = $_GET['citation'];
	}
	if (isset($_POST['citation']))
	{
		$citation = $_POST['citation'];
	}
	
	$citation = stripcslashes ($citation);
	
	$reference = new stdclass;
	$matched = parse_citation($citation, $reference);

	if ($matched)
	{
		header("Content-type: text/plain; charset=utf-8\n\n");
		echo json_encode($reference);
	}
	else
	{
		header('HTTP/1.1 404 Not Found');
		header('Status: 404 Not Found');
		$_SERVER['REDIRECT_STATUS'] = 404;	
	}
}

$test = false;

if ($test == false)
{
	main();
}
else
{

	$refs = array();
	$failed = array();
	
	$refs[] = '{{cite journal |author=Christoph D. Schubart, S. Cannicci, M. Vannini and S. Fratini |year=2006 |title=Molecular phylogeny of grapsoid crabs (Decapoda, Brachyura) and allies based on two mitochondrial genes and a proposal for refraining from current superfamily classification |journal=[[Journal of Zoological Systematics and Evolutionary Research]] |volume=44 |issue=3 |pages=193–199 |doi=10.1111/j.1439-0469.2006.00354.x}}';

	$refs[] = 'LOVERIDGE, A. 1934. Australian reptiles in the Museum of Comparative Zoology, Cambridge, Massachusetts. Bull. Mus. Comp. Zool., 77(6): 243-383.';

	$refs[] =  'DuELLMAN, W. E. 1974. A systematic review of the marsupial frogs (Hylidae: Gastrotheca) of the Andes of Ecuador. Occas. Papers Mus. Nat. Hist. Univ. Kansas, 22:1-27.';
	
	$refs[] = "{{aut|Coutière, H.}} (1909): The American species of snapping shrimps of the genus ''Synalpheus''. ''Proceedings of the United States National Museum'' '''36'''(1659): 1-93.";
	
	$refs[] = " {{aut|Chace, F.A.}} (1972): The shrimps of the Smithsonian-Bredin Caribbean expeditions with a summary of the West Indian shallow-water species (Crustacea: Decapoda: Natantia). ''Smithsonian Contributions to Zoology'' '''98''': 1-179.";
	
	$refs[] = "{{aut|Duffy, J. Emmett; Morrison, Cheryl L. & Ríos, Rubén}} (2000): Multiple Origins of Eusociality among Sponge-dwelling Shrimps (''Synalpheus''). ''[[Evolution (journal)|Evolution]]'' '''54'''(2): 503-516";


	echo "--------------------------\n";	
	$ok = 0;
	foreach ($refs as $str)
	{
		$reference = new stdclass;
		$matched = parse_citation($str, $reference);
		
		if ($matched)
		{
			$ok++;
			
			print_r($reference);
		}
		else
		{
			array_push($failed, $str);
		}
	}
	
	// report
	
	echo "--------------------------\n";
	echo count($refs) . ' references, ' . (count($refs) - $ok) . ' failed' . "\n";
	print_r($failed);
}


?>