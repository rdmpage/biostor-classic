<?php

/**
 * @file openurl.php
 *
 * OpenURL handler
 *
 */

require_once('../bhl_search.php');
require_once('../bhl_text.php');
require_once(dirname(__FILE__) . '/form.php');
require_once(dirname(__FILE__) . '/html.php');
require_once('../ISBN-ISSN.php');
require_once('../nameparse.php');
require_once('../swa.php');

$debug = false;

$format = 'html';

//--------------------------------------------------------------------------------------------------
/**
 * @brief Parse OpenURL parameters and return referent
 *
 * @param params Array of OpenURL parameters
 * @param referent Referent object to populate
 *
 */
function parse_openurl($params, &$referent)
{
	global $debug;
	
	$referent->authors = array();
	
	foreach ($params as $key => $value)
	{
		switch ($key)
		{
			case 'rft_val_fmt':
				switch ($value)
				{
					case 'info:ofi/fmt:kev:mtx:journal':
						$referent->genre = 'article';
						break;

					case 'info:ofi/fmt:kev:mtx:book':
						$referent->genre = 'book';
						break;
						
					default:
						if (!isset($referent->genre))
						{
							$referent->genre = 'unknown';
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
				$referent->title = $title;
				$referent->genre = 'article';
				break;

			// Book title
			case 'rft.btitle':
			case 'btitle':
				$referent->title = $value[0];
				$referent->genre = 'book';
				break;
				
			// Journal title
			case 'rft.jtitle':
			case 'rft.title':
			case 'title':
				$secondary_title = trim($value[0]);
				$secondary_title = preg_replace('/^\[\[/', '', $secondary_title);
				$secondary_title = preg_replace('/\]\]$/', '', $secondary_title);
				$referent->secondary_title = $secondary_title;
				$referent->genre = 'article';
				break;
				
			case 'rft.issn':
			case 'issn':
				$ISSN_proto = $value[0];			
				$clean = ISN_clean($ISSN_proto);
				$class = ISSN_classifier($clean);
				if ($class == "checksumOK")
				{
					$referent->issn = canonical_ISSN($ISSN_proto);
					$referent->genre = 'article';
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
						$referent->doi = $match['doi'];
						// CrossRef metadata search may have URL
						$referent->doi = str_replace('http://dx.doi.org/', '', $referent->doi);
					}
					// URL
					if (preg_match('/^http:\/\//', $v, $match))
					{
						$referent->url = $v;
					}
					// LSID
					if (preg_match('/^urn:lsid:/', $v, $match))
					{
						$referent->lsid = $v;
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
						$author->lastname = $parts['last'];
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
					$referent->authors[] = $author;					
				}
				break;

			default:
				$k = str_replace("rft.", '', $key);
				$referent->$k = $value[0];				
				break;
		} 
	}
	
	// Clean
	
	
	// Dates
	if (isset($referent->date))
	{
		if (preg_match('/^[0-9]{4}$/', $referent->date))
		{
			$referent->year = $referent->date;
			$referent->date = $referent->date . '-00-00';
		}
		if (preg_match('/^(?<year>[0-9]{4})-(?<month>[0-9]{2})-(?<day>[0-9]{2})$/', $referent->date, $match))
		{
			$referent->year = $match['year'];
			$referent->date = $match['year'] . '-' . $match['month'] . '-' . $match['day'];
		}
	}	
	
	// Zotero
	if (isset($referent->pages))
	{
		// Note "u" option in regular expression, so that we match UTF-8 characters such as –
		if (preg_match('/(?<spage>[0-9]+)[\-|–](?<epage>[0-9]+)/u', $referent->pages, $match))
		{
			$referent->spage = $match['spage'];
			$referent->epage = $match['epage'];
			unset($referent->pages);
		}
	}
	
	// Endnote epage may have leading "-" as it splits spage-epage to generate OpenURL
	if (isset($referent->epage))
	{
		$referent->epage = preg_replace('/^\-/', '', $referent->epage);
	}
	
	// Single page
	if (isset($referent->pages))
	{
		if (is_numeric($referent->pages))
		{
			$referent->spage = $referent->pages;
			$referent->epage = $referent->pages;
			unset($referent->pages);
		}
	}
	
	
	// Journal titles with series numbers are split into title,series fields
	if (preg_match('/(?<title>.*),?\s+series\s+(?<series>[0-9]+)$/i', $referent->secondary_title, $match))
	{
		$referent->secondary_title= $match['title'];
		$referent->series= $match['series'];
	}		

	// Volume might have series information
	if (preg_match('/^series\s+(?<series>[0-9]+),\s*(?<volume>[0-9]+)$/i', $referent->volume, $match))
	{
		$referent->volume= $match['volume'];
		$referent->series= $match['series'];
	}	
	
	// Volume might have series information s4-3
	if (preg_match('/^s(?<series>[0-9]+)-(?<volume>[0-9]+)$/i', $referent->volume, $match))
	{
		$referent->volume= $match['volume'];
		$referent->series= $match['series'];
	}	
	
	
	// Roman to Arabic volume
	if (!is_numeric($referent->volume))
	{
		if (preg_match('/^[ivxicl]+$/', $referent->volume))
		{
			$referent->volume = arabic($referent->volume);
		}
	}
	
	// Author array might not be populated, in which case add author from aulast and aufirst fields
	if ((count($referent->authors) == 0) && (isset($referent->aulast) && isset($referent->aufirst)))
	{
		$author = new stdClass();
		$author->lastname = $referent->aulast;
		$author->forename = $referent->aufirst;
		$referent->authors[] = $author;
	}	
	
	// Use aulast and aufirst to ensure first author name properly parsed
	if (isset($referent->aulast) && isset($referent->aufirst))
	{
		$author = new stdClass();
		$author->lastname = $referent->aulast;
		$author->forename = $referent->aufirst;
		$referent->authors[0] = $author;
	}	
	
	// EndNote encodes accented characters, which break journal names
	if (isset($referent->secondary_title))
	{
		$referent->secondary_title = preg_replace('/%9F/', 'ü', $referent->secondary_title);
	}
	
	//------------------------------------------------------------------------------------
	// German Wikipedia may have 'Bd.' prefix for volume
	// e.g. https://de.wikipedia.org/wiki/Hans_Hermann_Carl_Ludwig_von_Berlepsch
	if (preg_match('/^Bd\.?\s*(?<volume>.*)$/', $referent->volume, $match))
	{
		$referent->volume = $match['volume'];
	}
	
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Display form where user can enter bibliographic details of item to search for.
 *
 */
function display_form()
{
	global $config;
	
	echo html_html_open();
	echo html_head_open();
	echo html_title ('Reference Finder - ' . $config['site_name']);
	echo html_include_script('js/fadeup.js');
	echo html_include_script('js/prototype.js');

	echo '<script type="text/javascript">
var current_type = \'article\';
function switch_type(reference_type)
{
	$(\'metadata\').innerHTML ="";
	
	var html = "";
	
	switch (reference_type)
	{
		case "article":
			$(\'genre\').selectedIndex = 1;
			html += \'<table>\';
			html += \'<tr><td class="openurl_field_name">Title<\/td>\';
			html += \'<td><textarea class="field_value" id="title" name="atitle" rows="4" cols="60"><\/textarea><\/td>\';
			html += \'<\/tr>\';

			html += \'<tr><td class="openurl_field_name">Journal<\/td>\';
			html += \'<td><textarea class="field_value" id="secondary_title" name="title" rows="4" cols="60"><\/textarea><\/td>\';
			html += \'<\/tr>\';

			html += \'<tr><td class="openurl_field_name">Series<\/td>\';
			html += \'<td><input class="field_value" id="series" name="series" ><\/td>\';
			html += \'<\/tr>\';

			html += \'<tr><td class="openurl_field_name">Volume<\/td>\';
			html += \'<td><input class="field_value" id="volume" name="volume" ><\/td>\';
			html += \'<\/tr>\';

			html += \'<tr><td class="openurl_field_name">Issue<\/td>\';
			html += \'<td><input class="field_value" id="issue" name="issue" ><\/td>\';
			html += \'<\/tr>\';

			html += \'<tr><td class="openurl_field_name">Starting page<\/td>\';
			html += \'<td><input class="field_value" id="spage" name="spage" ><\/td>\';
			html += \'<\/tr>\';

			html += \'<tr><td class="openurl_field_name">Ending page<\/td>\';
			html += \'<td><input class="field_value" id="epage" name="epage" ><\/td>\';
			html += \'<\/tr>\';

			html += \'<tr><td class="openurl_field_name">Year<\/td>\';
			html += \'<td><input class="field_value" id="year" name="year" ><\/td>\';
			html += \'<\/tr>\';

			html += \'<tr><td class="openurl_field_name">DOI<\/td>\';
			html += \'<td><input class="field_value" id="doi" name="doi" size="60"><\/td>\';
			html += \'<\/tr>\';

			html += \'<tr><td class="openurl_field_name">URL<\/td>\';
			html += \'<td><input class="field_value" id="url" name="url" size="60"><\/td>\';
			html += \'<\/tr>\';

			html += \'<\/table>\';
			break;
			
		case "book":
			$(\'genre\').selectedIndex = 0;
			html += \'<table>\';
			html += \'<tr><td class="openurl_field_name">Title<\/td>\';
			html += \'<td><textarea class="field_value" name="btitle" rows="4" cols="60"><\/textarea><\/td>\';
			html += \'<\/tr>\';

			html += \'<tr><td class="openurl_field_name">ISBN<\/td>\';
			html += \'<td><input class="field_value" name="isbn" ><\/td>\';
			html += \'<\/tr>\';

			html += \'<tr><td class="openurl_field_name">OCLC<\/td>\';
			html += \'<td><input class="field_value" name="oclc" ><\/td>\';
			html += \'<\/tr>\';

			html += \'<tr><td class="openurl_field_name">Year<\/td>\';
			html += \'<td><input class="field_value" name="year" ><\/td>\';
			html += \'<\/tr>\';
			html += \'<\/table>\';
		
			break;
			
		default:
			break;
	}


	$(\'metadata\').innerHTML = html;


	current_type = reference_type;
}

//--------------------------------------------------------------------------------------------------
function parse_citation()
{
	var text = $(\'citation\').value;
	
	text = text.replace("&", "and");
	
	var success	= function(t){parseComplete(t);}
	var failure	= function(t){parseFailed(t);}

	var url = "parsecitation.php";
	var pars = "citation="+ encodeURIComponent(text);
	var myAjax = new Ajax.Request(url, {method:"post", postBody:pars, onSuccess:success, onFailure:failure});
}

//--------------------------------------------------------------------------------------------------
function parseFailed(t)
{
	$(\'citation_message\').innerHTML = \'<img align="absmiddle" src="images/cross.png" />Failed to parse citation\';
	fadeUp($(\'citation_row\'),255,255,153);
}

//--------------------------------------------------------------------------------------------------
// Take object from parsed citation string and populate bibliographic metadata form
function parseComplete(t)
{
	$(\'citation_message\').innerHTML = \'<img align="absmiddle" src="images/tick.png" />Parsed citation\';

	var text = t.responseText;
	
	//alert(text);
	
	var citation = text.evalJSON();
	
	switch (citation.genre)
	{
		case "article":
			switch_type("article");
			for(var item in citation) 
			{
				var value = citation[item];
				value = value.replace(/\\\/g, "");
				value = value.replace(/\'\'/g, "");
				switch (item)
				{
					case "title":
					case "secondary_title":
						$(item).innerHTML = value;
						break;
						
					case "series":
					case "volume":
					case "issue":
					case "spage":
					case "epage":
					case "year":
					case "doi":
					case "url":
						$(item).value = value;
						break;

					default:
						// Eat anything else
						break;
				}
   				
  			}
			break;
			
		default:
			break;
	}
	
	fadeUp($(\'openurl\'),255,255,153);
}

var check_year = /^[0-9]{4}$/;

function reportErrors(errors)
{
 var msg = "The form contains errors...\n";
 for (var i = 0; i<errors.length; i++) {
 var numError = i + 1;
  msg += "\n" + numError + ". " + errors[i];
}
 alert(msg);
}	

function validate_openurl_form(form)
{
	var errors = [];
	var genre = form.genre.options[form.genre.selectedIndex].value;
	
	switch (genre)
	{
		case \'article\':
			if (form.secondary_title.value == "")  
			{
				errors[errors.length] = "Please supply a journal name";
			}	
			if (form.volume.value == "")  
			{
				errors[errors.length] = "Please supply a volume";
			}	
			if (form.spage.value == "")  
			{
				errors[errors.length] = "Please supply a starting page";
			}	
			break;
			
		case \'book\':
			break;
			
	}

	if (errors.length > 0)
	{
		reportErrors(errors);
		return false;
	}
	return true;
}

</script>'; 
	echo html_head_close();
	echo html_body_open();
	echo html_page_header(false);
	
	echo '<h1>Reference Finder</h1>';
	
	echo "<!-- citation parsing -->\n";
	echo '<h2>Find from citation</h2>';
	echo '<table>';
	echo '<tr id="citation_row"><td class="openurl_field_name">Citation</td>';
	echo '<td><textarea class="field_value" id="citation" rows="6" cols="60"></textarea></td></tr>';
	
	echo '<tr><td></td><td style="font-size:12px;color:rgb(128,128,128);">Author(s) (Year) Title. Journal. Volume: Starting page-End page, e.g.:<br/>
	J D Lynch (1968) Genera of leptodactylid frogs in México. University of Kansas Publications Museum of Natural History 17: 503-515</td></tr>';
	
	echo '<tr><td></td><td><span style="padding:2px;cursor:pointer;background-color:#2D7BB2;color:white;font-size:18px;font-family:Arial;text-align:center;" onclick="parse_citation();">&nbsp;Parse&nbsp;</span>&nbsp;<span id="citation_message"></span></td></tr>';
	echo '</table>';
	
	
	echo "<!-- bibliographic metadata -->\n";
	echo '<h2>Find from bibliographic details</h2>';
	
	echo '<form id="openurl" method="get" action="openurl.php" onsubmit="return validate_openurl_form(this)">';
	echo "\n";
	
	echo '<table>';
	echo '<tr><td class="openurl_field_name">Type</td>';
	echo '<td>';
	
	echo '<select id="genre" name="genre" onchange="switch_type(genre.options[genre.selectedIndex].value);">';
	echo '<option value="book">Book</option>';
	//echo '<option value="chapter">Chapter</option>';
	echo '<option value="article" selected="selected">Journal article</option>';
	echo '</select>';

	echo '</td>';
	echo '</tr>';
	echo '</table>';
	
	echo '<div id="metadata">';
	echo '</div>';
	
	echo '<table>';
	echo '<tr><td class="openurl_field_name"></td>';
	echo '<td><input type="submit" value="Find" /></td></tr>	';
	echo '</table>';
	echo '</form>';
	
	echo '<script type="text/javascript">switch_type("article");</script>';


	echo html_body_close();
	echo html_html_close();
}


//--------------------------------------------------------------------------------------------------

function display_bhl_result_html($referent, $hits)
{
	global $config;
	
	header("Content-type: text/html; charset=utf-8\n\n");
	echo html_html_open();
	echo html_head_open();
	echo html_title ('Reference Finder - ' . $config['site_name']);
	echo html_include_css('css/main.css');
	echo html_include_css('css/lightbox.css');
	echo html_include_script('js/fadeup.js');
	echo html_include_script('js/prototype.js');
	echo html_include_script('js/scriptaculous.js?load=effects,builder');
	echo html_include_script('js/lightbox.js');
	
	// Handle user accepting a hit
	echo  '<script type="text/javascript">';
	echo 'function store(form_id, page_id)
{
var form = $(form_id);
//alert($(form).serialize());

// Update database
var success	= function(t){updateSuccess(t);}
var failure	= function(t){updateFailure(t);}

var url = "update.php";
var pars = $(form).serialize() + "&PageID=" + page_id;
var myAjax = new Ajax.Request(url, {method:"post", postBody:pars, onSuccess:success, onFailure:failure});
}

function updateSuccess (t)
{
var s = t.responseText.evalJSON();
//alert(t.responseText);
if (s.is_valid)
{
// we\'ve stored reference, so reload page, which will redirect us to page for reference
window.location.reload(true);
}
else
{
// User did not pass recaptcha so refresh it
Recaptcha.create("' . $config['recaptcha_publickey'] . '",
	"recaptcha_div", {
	theme: "clean",
	callback: Recaptcha.focus_response_field
});
//fadeUp($(recaptcha_div),255,255,153);
}
}

function updateFailure (t)
{
	var s = t.responseText.evalJSON();
	alert("Badness happened:\n" + t.responseText);
}'
;

// Based on http://ne0phyte.com/blog/2008/09/02/javascript-keypress-event/
// and http://blog.evandavey.com/2008/02/how-to-capture-return-key-from-field.html
// I want to capture enter key press in recaptcha to avoid submitting the form (user must click
// on button for that). We listen for keypress and eat it. Note that we attach the listener after
// the window has loaded.
echo 'function onMyTextKeypress(event)
{
if (Event.KEY_RETURN == event.keyCode) 
{
// do something usefull
//alert(\'Enter key was pressed.\');		
Event.stop(event);
}
return;
}
Event.observe(window, \'load\', function() {
Event.observe(\'recaptcha_response_field\', \'keypress\', onMyTextKeypress);
});';

	echo  '</script>';
	
	echo html_head_close();
	echo html_body_open();
	echo html_page_header(false);
	
	echo '<h1>Reference Finder results</h1>';
	
	if (count($hits) != 0)
	{
		echo '<form id="metadata_form" action=\'#\'>';
		
		// referent metadata (hidden). By populating form we can pass metadata to
		// update.php via Ajax call
		echo reference_hidden_form($referent);
		
		echo '<table border="0" cellpadding="10">';
		foreach ($hits as $hit)
		{
			echo '<tr>';
			
			// Thumbnail of page
			echo '<td valign="top">';
			echo '<a href="bhl_image.php?PageID=' . $hit->PageID . '" rel="lightbox">';
			echo '<img style="border:1px solid rgb(128,128,128);" src="bhl_image.php?PageID=' . $hit->PageID . '&amp;thumbnail" alt="page thumbnail"/>';
			echo '</a>';
			echo '</td>';
			
			// Details of match
			echo '<td valign="top">';	
			echo '<div style="margin-bottom:4px;">' . $hit->snippet . '</div>';
			echo "<div><span>Title match score = " . $hit->score . "</span></div>";

			echo '<div><span>BHL PageID </span><a href="http://www.biodiversitylibrary.org/page/' . $hit->PageID . '">' . $hit->PageID  . '</a></div>';
			
			// Action
			echo '<br/><span style="padding:2px;cursor:pointer;background-color:#2D7BB2;color:white;font-size:18px;font-family:Arial;text-align:center" onclick="store(\'metadata_form\', \'' . $hit->PageID  . '\');">&nbsp;Click here to accept this match&nbsp;</span>';
	
			echo '</td>';		
			echo '</tr>';
		}
		echo '</table>';	

		// Recaptcha
		$recaptcha = !user_is_logged_in();
		if ($recaptcha)
		{
			echo '<script type="text/javascript">';
			echo 'var RecaptchaOptions = ';
			echo '{';
			echo    'theme: \'white\',';
			echo     'tabindex: 2';
			echo  '};';
			echo '</script>';
			echo '<div id="recaptcha_div">';
//			echo '<script type="text/javascript" src="http://api.recaptcha.net/challenge?k=' . $config['recaptcha_publickey'] . '"></script>';

// https://groups.google.com/forum/?fromgroups#!topic/recaptcha/V7qswqBnA1o
			echo '<script type="text/javascript" src="https://www.google.com/recaptcha/api/challenge?k=' . $config['recaptcha_publickey'] . '"></script>';
			echo '</div>';
		}		
		echo '</form>';
	}
	else
	{
		echo '<p>No matching article found</p>';
		echo '<ul>';
		echo '<li><a href="openurl.php">Return to Reference Finder</a></li>';			
		echo '<li><a href="http://biodiversitylibrary.org/openurl?' . $_SERVER['QUERY_STRING'] . '" target= "_new">Try to find using BHL OpenURL resolver</a></li>';			
		echo '<li><a href="http://bioguid.info/openurl.php?' . $_SERVER['QUERY_STRING'] . '" target= "_new">Try to find using bioGUID OpenURL resolver</a></li>';			
		echo '</ul>';
	}
	echo html_body_close();
	echo html_html_close();	
}


//--------------------------------------------------------------------------------------------------
function display_bhl_result_json($referent, $hits, $callback = '')
{
	if (count($hits) > 0)
	{
		header("Content-type: text/plain; charset=utf-8\n\n");
		if ($callback != '')
		{
			echo $callback . '(';
		}
		echo json_format(json_encode($hits));
		if ($callback != '')
		{
			echo ')';
		}
	}
	else
	{
		header('HTTP/1.1 404 Not Found');
		header('Status: 404 Not Found');
		$_SERVER['REDIRECT_STATUS'] = 404;
		echo 'Referent not found';
	}
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
	global $format;
	
	$id = 0;
	$callback = '';
		
	// If no query parameters 
	if (count($_GET) == 0)
	{
		display_form();
		exit(0);
	}	
	
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
	
	if (isset($_GET['callback']))
	{
		$callback = $_GET['callback'];
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
	$referent = new stdclass;
	parse_openurl($params, $referent);

	
	// Flesh it out 
	// If we are looking for an article we need an ISSN, or at least an OCLC
	// Ask whether have this in our database (assumes we have ISSN)
	if (!isset($referent->issn))
	{
		// Try and get ISSN from bioGUID
		$issn = issn_from_title($referent->secondary_title);
		if ($issn != '')
		{
			$referent->issn = $issn;
		}
		else
		{
			// No luck with ISSN, look for OCLC
			if (!isset($referent->oclc))
			{
				$oclc = oclc_for_title($referent->secondary_title);
				if ($oclc != 0)
				{
					$referent->oclc = $oclc;
				}
			}
		}
	}
	
	if ($debug)
	{
		echo '<h1>Referent</h1>';
		echo '<pre>';
		print_r($referent);
		echo '</pre>';
	}
	
	
	// Handle identifiers
	if (isset($referent->url))
	{
		// BHL URL, for example if we have already mapped article to BHL
		// in Zotero, 
		if (preg_match('/^http:\/\/(www\.)?biodiversitylibrary.org\/page\/(?<pageid>[0-9]+)/', $referent->url, $matches))
		{
			//print_r($matches);
			
			$PageID = $matches['pageid'];
			$references = bhl_reference_from_pageid($PageID);
			
			//print_r($references);
			
			if (count($references) == 0)
			{
				// We don't have an article for this PageID
				$search_hit = bhl_score_page($PageID, $referent->title);
				
				//print_r($search_hit);
				
				//if ($search_hit->score > 0.5)
				{
					// Store
					$id = db_store_article($referent, $PageID);
				}
			}
			else
			{
				// Have a reference with this PageID already
				
				// Will need to handle case where > 1 article on same page, e.g.
				// http://www.biodiversitylibrary.org/page/3336598
				
				$id = $references[0];
			}
			
			// Did we get a hit?
			if ($id != 0)
			{
				// We have this reference in our database
				switch ($format)
				{
					case 'json':
						// Display object
						$reference = db_retrieve_reference($id);
						header("Content-type: text/plain; charset=utf-8\n\n");
						if ($callback != '')
						{
							echo $callback . '(';
						}
						echo json_format(json_encode($reference));
						if ($callback != '')
						{
							echo ')';
						}
						break;
						
					case 'html':
					default:
						// Redirect to reference display
						header('Location: ' . $config['web_root'] . 'reference/' . $id . "\n\n");
						break;
				}
				exit();
			}
		}
	}
	
	// OK, we're not forcing a match to BHL, so do we have this article?
	$id = db_find_article($referent);
	
	//echo "<b>id=$id</b><br/>";
	
	if ($id != 0)
	{
		// We have this reference in our database
		switch ($format)
		{
			case 'json':
				// Display object
				$reference = db_retrieve_reference($id);
				header("Content-type: text/plain; charset=utf-8\n\n");
				if ($callback != '')
				{
					echo $callback . '(';
				}
				echo json_format(json_encode($reference));
				if ($callback != '')
				{
					echo ')';
				}
				break;
				
			case 'html':
			default:
				// Twitter as log
				if ($config['twitter'])
				{
					$tweet_this = false;
					
					$tweet_this = isset($_GET['rfr_id']);
					
					if ($tweet_this)
					{
						$url = $config['web_root'] . 'reference/' . $id . ' '; //  . '#openurl'; // url + hashtag
						
						$url = $id;
						
						$url_len = strlen($url);
						$status = '';
						
						//$text = $_GET['rfr_id'];
						
						$text = '#openurl ' . $_SERVER["HTTP_REFERER"];
						
						//$text .= ' @rdmpage';
						if (isset($article->title))
						{
						}
						$status = $text;
						$status_len = strlen($status);
						$extra = 140 - $status_len - $url_len - 1;
						if ($extra < 0)
						{
							$status_len += $extra;
							$status_len -= 1;
							$status = substr($status, 0, $status_len);
							$status .= '…';
						}
						$status .= ' ' . $url;
						tweet($status);
					}
				}
			
				// Redirect to reference display
				header('Location: reference/' . $id . "\n\n");
				break;
		}
		exit();
	}
	
	// OK, not found, so let's go look for it...
		
	// Search BHL
	$atitle = '';
	if (isset($referent->title))
	{
		$atitle = $referent->title;
	}
	$search_hits = bhl_find_article(
		$atitle,
		$referent->secondary_title,
		$referent->volume,
		(isset($referent->spage) ? $referent->spage : $referent->pages),
		(isset($referent->series) ? $referent->series : ''),
		(isset($referent->date) ? $referent->date : ''),
		(isset($referent->issn) ? $referent->issn : '')
		);

	if (count($search_hits) == 0)
	{
		// try alternative way of searching using article title
		$search_hits = bhl_find_article_from_article_title	(
			$referent->title,
			$referent->secondary_title,
			$referent->volume,
			(isset($referent->spage) ? $referent->spage : $referent->pages),
			(isset($referent->series) ? $referent->series : ''),
			(isset($referent->issn) ? $referent->issn : '')
			);		
	}
	
	// At this point if we haven't found it in BHL we could go elsewhere, e.g. bioGUID,
	// in which case we'd need to take this into account when displaying HTML and JSON
	
	if ($debug)
	{
		echo '<h3>Search hits</h3>';
		echo '<pre>';
		print_r($search_hits);
		echo '</pre>';
	}
	
	if (1)
	{
		// Check whether we already have an article that starts on this 
		foreach ($search_hits as $hit)
		{
			$references = bhl_reference_from_pageid($hit->PageID);
			
			//print_r($references);
			
			if (count($references) != 0)
			{
				// We have this reference in our database
				switch ($format)
				{
					case 'json':
						// Display object
						$reference = db_retrieve_reference($references[0]);
						header("Content-type: text/plain; charset=utf-8\n\n");
						if ($callback != '')
						{
							echo $callback . '(';
						}
						echo json_format(json_encode($reference));
						if ($callback != '')
						{
							echo ')';
						}
						break;
						
					case 'html':
					default:
						// Redirect to reference display
						header('Location: reference/' . $references[0] . "\n\n");
						break;
				}
				exit();
			
			}
		}
		
		
		
		
	}
	
	
	
	
	// Output search results in various formats...
	switch ($format)
	{	
		case 'json':
			display_bhl_result_json($referent, $search_hits, $callback);
			break;
			
		case 'html':
		default:
			display_bhl_result_html($referent, $search_hits);
			break;
	}	

}


main();

?>