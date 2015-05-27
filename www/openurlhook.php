<?php

// OpenURL handler that can be embedded in other sites, or as a popup

/**
 * @file eopenurl.php
 *
 * OpenURL handler
 *
 */
 
$debug 	= true;

require_once(dirname(dirname(__FILE__)) . '/ISBN-ISSN.php');
require_once(dirname(dirname(__FILE__)) . '/nameparse.php');

//--------------------------------------------------------------------------------------------------
/**
 * @brief Parse OpenURL parameters and return context object
 *
 * @param params Array of OpenURL parameters
 * @param context_object Context object to populate
 *
 */
function parse_openurl($params, &$context_object)
{
	global $debug;
	
	$context_object->referring_entity = new stdClass;
	$context_object->referent = new stdClass;
	
	$context_object->referent->authors = array();
	$context_object->referent->identifiers = new stdClass;
	
	foreach ($params as $key => $value)
	{
		switch ($key)
		{
			case 'ctx_ver':
				$context_object->version = $value[0];
				break;
				
			case 'rfe_id':
				$context_object->referring_entity->id = $value[0];
				break;
		
			case 'rft_val_fmt':
				switch ($value)
				{
					case 'info:ofi/fmt:kev:mtx:journal':
						$context_object->referent->type = 'Journal Article';
						break;

					case 'info:ofi/fmt:kev:mtx:book':
						$context_object->referent->type = 'Book';
						break;
						
					default:
						if (!isset($context_object->referent->type))
						{
							$context_object->referent->type = 'Unknown';
						}
						break;
				}
				break;
			
			// Article title
			case 'rft.atitle':
			case 'atitle':
				$title = $value[0];
				$title = preg_replace('/\.$/', '', $title);
				$title = strip_tags($title);
				$title = html_entity_decode($title, ENT_NOQUOTES, 'UTF-8');
				$context_object->referent->title = $title;
				$context_object->referent->type = 'Journal Article';
				break;

			// Book title
			case 'rft.btitle':
			case 'btitle':
				$context_object->referent->title = $value[0];
				$context_object->referent->type = 'Book';
				break;
				
			// Journal title
			case 'rft.jtitle':
			case 'rft.title':
			case 'title':
				$publication_outlet = trim($value[0]);
				$publication_outlet = preg_replace('/^\[\[/', '', $publication_outlet);
				$publication_outlet = preg_replace('/\]\]$/', '', $publication_outlet);
				$context_object->referent->publication_outlet = $publication_outlet;
				$context_object->referent->type = 'Journal Article';
				break;
				
			case 'rft.issn':
			case 'issn':
				$ISSN_proto = $value[0];			
				$clean = ISN_clean($ISSN_proto);
				$class = ISSN_classifier($clean);
				if ($class == "checksumOK")
				{
					$context_object->referent->identifiers->issn = canonical_ISSN($ISSN_proto);
					$context_object->referent->type = 'Journal Article';
				}
				break;

			// Identifiers
			case 'rft_id':
			case 'id':
				foreach ($value as $v)
				{		
					// DOI
					if (preg_match('/^(info:doi\/|doi:)(?<doi>.*)/', $v, $match))
					{
						$context_object->referent->identifiers->doi = $match['doi'];
					}
					// Handle
					if (preg_match('/^(info:hdl\/|hdl:)(?<hdl>.*)/', $v, $match))
					{
						$context_object->referent->identifiers->hdl = $match['hdl'];
					}
					// PMID
					if (preg_match('/^(info:pmid\/|pmid:)(?<pmid>.*)/', $v, $match))
					{
						$context_object->referent->identifiers->pmid = $match['pmid'];
					}
					
					// Without INFO-URI prefix
					// LSID
					if (preg_match('/^urn:lsid:/', $v))
					{
						$context_object->referent->identifiers->lsid = $v;
					}
					// URL (including PDFs)
					if (preg_match('/^http:\/\//', $v))
					{
						$matched = false;
						// PDF
						if (!$matched)
						{
							if (preg_match('/\.pdf/', $v))
							{
								$matched = true;
								$context_object->referent->pdf = $v;
							}
						}
						// BioStor
						if (!$matched)
						{
							if (preg_match('/http:\/\/biostor.org\/reference\/(?<id>\d+)$/', $v, $match))
							{
								$matched = true;
								$context_object->referent->identifiers->biostor = $match['id'];
							}
						}
						if (!$matched)
						{
							$context_object->referent->url = $v;
						}						
					}					
				}
				break;

			// Authors 
			case 'rft.au':
			case 'au':
				foreach ($value as $v)
				{
					$parts = parse_name($v);					
					$author = new stdClass();
					if (isset($parts['last']))
					{
						$author->surname = $parts['last'];
					}
					if (isset($parts['suffix']))
					{
						$author->suffix = $parts['suffix'];
					}
					if (isset($parts['first']))
					{
						$author->forename = $parts['first'];
						
						if (array_key_exists('middle', $parts))
						{
							$author->forename .= ' ' . $parts['middle'];
						}
					}
					$context_object->referent->authors[] = $author;					
				}
				break;
						
			default:
				$k = str_replace("rft.", '', $key);
				$context_object->referent->$k = $value[0];				
				break;
		} 
	}
	
	// Clean
	
	
	// Dates
	if (isset($context_object->referent->date))
	{
		if (preg_match('/^[0-9]{4}$/', $context_object->referent->date))
		{
			$context_object->referent->year = $context_object->referent->date;
			$context_object->referent->date = $context_object->referent->date . '-00-00';
		}
		if (preg_match('/^(?<year>[0-9]{4})-(?<month>[0-9]{2})-(?<day>[0-9]{2})$/', $context_object->referent->date, $match))
		{
			$context_object->referent->year = $match['year'];
			$context_object->referent->date = $match['year'] . '-' . $match['month'] . '-' . $match['day'];
		}
	}	
	
	// Zotero
	if (isset($context_object->referent->pages))
	{
		// Note "u" option in regular expression, so that we match UTF-8 characters such as Ð
		if (preg_match('/(?<spage>[0-9]+)[\-|Ð](?<epage>[0-9]+)/u', $context_object->referent->pages, $match))
		{
			$context_object->referent->spage = $match['spage'];
			$context_object->referent->epage = $match['epage'];
			unset($context_object->referent->pages);
		}
	}
	
	// Endnote epage may have leading "-" as it splits spage-epage to generate OpenURL
	if (isset($context_object->referent->epage))
	{
		$context_object->referent->epage = preg_replace('/^\-/', '', $context_object->referent->epage);
	}
	
	// Journal titles with series numbers are split into title,series fields
	if (preg_match('/(?<title>.*),?\s+series\s+(?<series>[0-9]+)$/i', $context_object->referent->publication_outlet, $match))
	{
		$context_object->referent->publication_outlet= $match['title'];
		$context_object->referent->series= $match['series'];
	}		

	// Volume might have series information
	if (preg_match('/^series\s+(?<series>[0-9]+),\s*(?<volume>[0-9]+)$/i', $context_object->referent->volume, $match))
	{
		$context_object->referent->volume= $match['volume'];
		$context_object->referent->series= $match['series'];
	}		
	
	// Author array might not be populated, in which case add author from aulast and aufirst fields
	if ((count($context_object->referent->authors) == 0) && (isset($context_object->referent->aulast) && isset($context_object->referent->aufirst)))
	{
		$author = new stdClass();
		$author->surname = $context_object->referent->aulast;
		$author->forename = $context_object->referent->aufirst;
		$context_object->referent->authors[] = $author;
	}	
	
	// Use aulast and aufirst to ensure first author name properly parsed
	if (isset($context_object->referent->aulast) && isset($context_object->referent->aufirst))
	{
		$author = new stdClass();
		$author->surname = $context_object->referent->aulast;
		$author->forename = $context_object->referent->aufirst;
		$context_object->referent->authors[0] = $author;
	}	
	
	// EndNote encodes accented characters, which break journal names
	if (isset($context_object->referent->publication_outlet))
	{
		$context_object->referent->publication_outlet = preg_replace('/%9F/', 'Ÿ', $context_object->referent->publication_outlet);
	}
	
	
}

//--------------------------------------------------------------------------------------------------
function display_input($context_object, $key, $label, $placeholder)
{
	echo '<div>';
	echo '<label for="' . $key . '">' . $label . '</label>';
	echo '<input id="' . $key . '" name="' . $key . '" value="';
	if (isset($context_object->referent->$key))
	{
		echo $context_object->referent->$key;
	}
	echo '"';
	
	if ($placeholder != '')
	{
		echo ' placeholder="' . $placeholder . '"';
	}
	
	echo '>';
	echo '</div>' . "\n";
}

//--------------------------------------------------------------------------------------------------
function display_form($context_object, $webhook = '')
{
	echo '<html>';
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";	
	echo '<script src="js/jquery-1.4.4.min.js"></script>' . "\n";
	//echo '<script src="js/json2.js"></script>' . "\n";
	echo '<script src="js/openurl.js"></script>' . "\n";
	echo '   <style type="text/css">'  . "\n";
	echo '   body {
		font-family:sans-serif;
		font-size:12px;
	}
	label {
		float:left;
		width:80px;
		font-size:12px;
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
	
	echo '</head>';
	
	echo '<div style="position:relative;height:700px;width:800px;border:1px solid rgb(128,128,128);">' . "\n";	
	echo '<div id="page" style="position:absolute;left:400px;top:0px;height:700px;width:400px;overflow:hide;" onclick="hidePage();"></div>' . "\n";
	echo '<div id="hits" style="position:absolute;left:400px;top:0px;height:700px;width:400px;overflow:auto;"></div>' . "\n";
	
	echo '<div style="border-right:1px solid rgb(128,128,128);height:700px;width:400px;">' . "\n";
	echo '<form id="openurl">' . "\n";
	
	if (isset($context_object->referring_entity))
	{
		if (isset($context_object->referring_entity->id))
		{
			echo '<div><label for="rfe_id">Id</label><input readonly id="rfe_id" name="rfe_id" value="' . $context_object->referring_entity->id . '">' . "\n";
		}
	}
	
	switch ($context_object->referent->type)
	{
		case 'Journal Article':

			echo '<input type="hidden" id="rft_val_fmt" name="rft_val_fmt" value="info:ofi/fmt:kev:mtx:journal">' . "\n";	

			echo '<div><label for="title">Title</label><textarea id="title" name="title" rows="4">' . $context_object->referent->title . '</textarea></div>' . "\n";
			
			echo '<div><label for="authors">Authors</label><textarea id="authors" name="authors" rows="4" placeholder="one name per line">' . "\n";
			if (isset($context_object->referent->authors))
			{
				foreach ($context_object->referent->authors as $author)
				{
					echo $author->forename . ' ' . $author->surname . "\n";
				}
			}
			echo '</textarea></div>' . "\n";
			
			display_input($context_object, 'publication_outlet', 'Journal', 'journal');
			display_input($context_object, 'series', 'Series', 'series');
			display_input($context_object, 'volume', 'Volume', 'volume');
			display_input($context_object, 'issue', 'Issue', 'issue');
			display_input($context_object, 'spage', 'Starting page', 'first page in article');
			display_input($context_object, 'epage', 'Ending page', 'last page in article');
			display_input($context_object, 'year', 'Year', 'year article published');
			
			// Search
			echo '<div><label>Search</label>';
			echo '<button type="button" onclick="findFromMetadata();">bioGUID</button>';
			echo '<button type="button" onclick="findInBiostor();">BioStor</button>';
			echo '<img id="bioguid_progress" src="images/blank16x16.png">';
			echo '</div>';
			
			// Identifiers
			
			// DOI
			echo '<div><label for="doi">DOI</label>';
			echo '<input id="doi" name="doi" placeholder="DOI" value="';
			if (isset($context_object->referent->identifiers->doi))
			{
				echo $context_object->referent->identifiers->doi;
			}
			echo '" onchange="doi_enable();">';
			echo '<button type="button" id="doi_link" onclick="doi_go();">Go</button>';
			echo '<button type="button" id="doi_lookup" onclick="findFromDOI();">Lookup</button>';
			echo '</div>' . "\n";	
			
			// Handle
			echo '<div><label for="hdl">Handle</label><input id="hdl" name="hdl" placeholder="Handle" value="';
			if (isset($context_object->referent->identifiers->hdl))
			{
				echo $context_object->referent->identifiers->hdl;
			}
			echo '" onchange="hdl_enable();">';
			echo '<button type="button" id="hdl_link" onclick="handle_go();">Go</button>';
			echo '</div>' . "\n";	
			
			// ISSN
			echo '<div><label for="issn">ISSN</label><input id="issn" name="issn" placeholder="ISSN for journal" value="';
			if (isset($context_object->referent->identifiers->issn))
			{
				echo $context_object->referent->identifiers->issn;
			}
			echo '" >';
			echo '</div>' . "\n";	
			
			// BioStor 
			echo '<div><label for="biostor">BioStor</label><input id="biostor" name="biostor" placeholder="BioStor reference number" value="';
			if (isset($context_object->referent->identifiers->biostor))
			{
				echo $context_object->referent->identifiers->biostor;
			}
			echo '" onchange="biostor_enable();">';
			echo '<button type="button" id="biostor_link" onclick="biostor_go();">Go</button>';
			echo '</div>' . "\n";	
			

			// PMID with link to NCBI
			echo '<div><label for="pmid">PMID</label><input id="pmid" name="pmid" placeholder="PMID" value="';
			if (isset($context_object->referent->identifiers->pmid))
			{
				echo $context_object->referent->identifiers->pmid;
			}
			echo '" onchange="pmid_enable();">';
			echo '<button type="button" id="pmid_link" onclick="pmid_go();">Go</button>';
			echo '</div>' . "\n";	

			// URL, could be anything :(
			echo '<div><label for="url">URL</label><input id="url" name="url" placeholder="URL" value="';
			if (isset($context_object->referent->url))
			{
				echo $context_object->referent->url;
			}
			echo '" onchange="url_enable();">';
			echo '<button type="button" id="url_link" onclick="url_go();">Go</button>';
			echo '</div>' . "\n";	
			
			// PDF
			echo '<div><label for="pdf">PDF</label><input id="pdf" name="pdf" placeholder="PDF" value="';
			if (isset($context_object->referent->pdf))
			{
				echo $context_object->referent->pdf;
			}
			echo '" onchange="pdf_enable();">';
			echo '<button type="button" id="pdf_link" onclick="pdf_go();">Go</button>';
			echo '</div>' . "\n";	
			
						

			break;
			
		default:
			break;
			
	}
	
	// webhook
	if ($webhook != '')
	{
		if (preg_match ('/^(http:\/\/(.*))/', $webhook))
		{
			echo '<input type="hidden" id="webhook" name="webhook" value="' . $webhook . '">' . "\n";	
//			echo '<input type="hidden" id="webhook" name="webhook" value="http://www.postbin.org/1b61x6c">' . "\n";	
			echo '<div><label>Save</label><button type="button" onclick="save();">Save</button></div>' . "\n";
		}
	}
	
	echo '</form>';
	echo '</div>';
	
	echo '</div>' . "\n";
	
	
	// Form defaults
	echo '<script>
		doi_enable();
		hdl_enable();
		biostor_enable();
		pmid_enable();
		url_enable();
		pdf_enable();
	</script>
	';
	
	echo '</body>';
	echo '</html>';	
}




//--------------------------------------------------------------------------------------------------
/**
 * @brief Handle OpenURL request
 *
 * We may have more than one parameter with same name, so need to access QUERY_STRING, not _GET
 * http://stackoverflow.com/questions/353379/how-to-get-multiple-parameters-with-same-name-from-a-url-in-php
 *
 */
function main()
{
	global $config;
	global $debug;
	
	$webhook = '';
			
	// If no query parameters 
	if (count($_GET) == 0)
	{
		//display_form();
		exit(0);
	}	
	
	if (isset($_GET['webhook']))
	{
		$webhook = $_GET['webhook'];
	}
	
	$debug = false;
	if (isset($_GET['debug']))
	{	
		$debug = true;
	}
	
	// Handle query and display results.
	$query = explode('&', html_entity_decode($_SERVER['QUERY_STRING']));
	$params = array();
	foreach( $query as $param )
	{
	  list($key, $value) = explode('=', $param);
	  
	  $key = preg_replace('/^\?/', '', urldecode($key));
	  $params[$key][] = trim(urldecode($value));
	}
	
	if ($debug)
	{
		echo '<h1>Params</h1>';
		echo '<pre>';
		print_r($params);
		echo '</pre>';
	}

	// This is what we got from user
	$context_object = new stdclass;
	parse_openurl($params, $context_object);
	
	if ($debug)
	{
		echo '<h1>Referent</h1>';
		echo '<pre>';
		print_r($context_object);
		echo '</pre>';
	}
	
	// Display form...
	display_form($context_object, $webhook);
	
}


main();

?>