<?php

/**
 * @file reference.php
 *
 * Operations on a reference object
 *
 */
 
require_once(dirname(__FILE__) . '/config.inc.php'); 
require_once(dirname(__FILE__) . '/nameparse.php'); 
require_once(dirname(__FILE__) . '/bhl_text.php'); 
 
//--------------------------------------------------------------------------------------------------
/**
 * @brief Add an author to a reference from a string containing the author's name
 *
 * @param reference Reference object
 * @param author Author name as a string
 *
 */
function reference_add_author_from_string(&$reference, $author)
{
	if (!isset($reference->authors))
	{
		$reference->authors = array();
	}
	
	$parts = parse_name($author);
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
	$reference->authors[] = $author;	

}
 
//--------------------------------------------------------------------------------------------------
/**
 * @brief Create a simple text string citation for a reference
 *
 * For an article this is of the form journal volume(issue): spage-epage
 *
 * @param reference Reference object
 *
 * @return Citation string
 */
function reference_to_citation_text_string($reference)
{
	$text = '';
	switch ($reference->genre)
	{
		case 'article':
			$text .= $reference->secondary_title;
			$text .= ' ';
			if (isset($reference->volume))
			{
				$text .= $reference->volume ;
			}
			if (isset($reference->issue))
			{
				$text .= '(' . $reference->issue . ')';
			}		
			$text .= ':';
			$text .= ' ';
			$text .= $reference->spage;
			if (isset($reference->epage))
			{
				$text .= '-' . $reference->epage;
			}
			/*if (isset($reference->year))
			{
				$text .= ' [' . $reference->year . ']';
			}*/
			break;
			
		default:
			break;
	}
	
	return $text;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Create a simple text string citation for a authors of a reference
 *
 * @param reference Reference object
 *
 * @return Author string 
 */
function reference_authors_to_text_string($reference)
{
	$text = '';
	
	$count = 0;
	$num_authors = count($reference->authors);
	if ($num_authors > 0)
	{
		foreach ($reference->authors as $author)
		{
			$text .= $author->forename . ' ' . $author->lastname;
			if (isset($author->suffix))
			{
				$text .= ' ' . $author->suffix;
			}
			$count++;
			
			if ($count == 2 && $num_authors > 3)
			{
				$text .= ' et al.';
				break;
			}
			if ($count < $num_authors -1)
			{
				$text .= ', ';
			}
			else if ($count < $num_authors)
			{
				$text .= ' and ';
			}			
		}
	}
	
	return $text;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Create <meta> tags for a reference
 *
 *
 * @param reference Reference object to be encoded
 *
 * @return HTML <meta> tags
 */
function reference_to_meta_tags($reference)
{
	global $config;
	
	$html = '';
	
	/*
	// Embed first page of OCR text
	if (db_reference_from_bhl($reference->reference_id))
	{
		$pages = bhl_retrieve_reference_pages($reference->reference_id);
		$page_ids = array($pages[0]->PageID);
		$text = bhl_fetch_text_for_pages($page_ids);
		$text = str_replace ('\n', '' , $text);
		$text = str_replace ('- ', '-' , $text);
		$text = str_replace ('- ', '-' , $text);
		
		$html .= "\n<!-- First page of OCR text -->\n";
		$html .= '<meta name="description" content="' . htmlentities($text, ENT_COMPAT, "UTF-8") . '" />' . "\n";
	}
	*/
	
	// Dublin core
	$html .= "\n<!-- Dublin Core metadata -->\n";
	$html .= '<link title="schema(DC)" rel="schema.dc" href="http://purl.org/dc/elements/1.1/" />' . "\n";
	$html .= '<meta name="dc.publisher" content="BioStor" />' . "\n";
	$html .= '<meta name="dc.title" content="' . htmlentities($reference->title, ENT_COMPAT, "UTF-8") . '" />' . "\n";
	$html .= '<meta name="dc.source" content="' . htmlentities(reference_to_citation_text_string($reference), ENT_COMPAT, "UTF-8") . '" />' . "\n";
	foreach ($reference->authors as $author)
	{
		$html .= '<meta name="dc.creator" content="' . htmlentities($author->forename . ' ' . $author->lastname, ENT_COMPAT, "UTF-8"). '" />' . "\n";
	}
	$html .= '<meta name="dc.date" content="' . $reference->date . '" />' . "\n";
	$html .= '<meta name="dc.identifier" content="' . $config['web_root'] . 'reference/' . $reference->reference_id . '" />' . "\n";
	

	// Google Scholar
	$html .= "\n<!-- Google Scholar metadata -->\n";
	$html .= '<meta name="citation_publisher" content="BioStor" />' . "\n";
	$html .= '<meta name="citation_title" content="' . htmlentities($reference->title, ENT_COMPAT, "UTF-8") . '" />' . "\n";
	$html .= '<meta name="citation_date" content="' . $reference->date . '" />' . "\n";
	
	$author_names = array();
	foreach ($reference->authors as $author)
	{
		$author_names[] = $author->lastname . ', ' . $author->forename;
	}
	$html .= '<meta name="citation_authors" content="' . join(";", $author_names) . '" />' . "\n";
	
	if ($reference->genre == 'article')
	{
		$html .= '<meta name="citation_journal_title" content="' . htmlentities($reference->secondary_title, ENT_COMPAT, "UTF-8") . '" />' . "\n";
		$html .= '<meta name="citation_volume" content="' . $reference->volume . '" />' . "\n";
		if (isset($reference->issue))
		{
			$html .= '<meta name="citation_issue" content="' . $reference->issue . '" />' . "\n";
		}
		$html .= '<meta name="citation_firstpage" content="' . $reference->spage . '" />' . "\n";
		$html .= '<meta name="citation_lastpage" content="' . $reference->epage . '" />' . "\n";
	}
	$html .= '<meta name="citation_abstract_html_url" content="' . $config['web_root'] . 'reference/' . $reference->reference_id . '" />' . "\n";
	$html .= '<meta name="citation_fulltext_html_url" content="' . $config['web_root'] . 'reference/' . $reference->reference_id . '" />' . "\n";
	$html .= '<meta name="citation_pdf_url" content="' . $config['web_root'] . 'reference/' . $reference->reference_id . '.pdf" />' . "\n";
	
	// Twitter
	$html .= '<meta name="twitter:card" content="summary" />' . "\n";
	$html .= '<meta name="twitter:title" content="' . htmlentities($reference->title, ENT_COMPAT, "UTF-8") . '" />' . "\n";
	$html .= '<meta name="twitter:site" content="BioStor_org" />' . "\n";

	$description = reference_to_citation_text_string($reference);
	$html .= '<meta name="twitter:description" content="' . htmlentities($reference->title, ENT_COMPAT, "UTF-8") . '" />' . "\n";

	$pages = bhl_retrieve_reference_pages($reference->reference_id);
	if (count($pages) > 0)
	{
		$html .= '<meta name="twitter:image" content="' . $config['web_root'] . 'bhl_image.php?PageID=' . $pages[0]->PageID  . '&thumbnail" />' . "\n";
	}


	$html .= "\n";
	
	return $html;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Create a COinS (ContextObjects in Spans) for a reference
 *
 * COinS encodes an OpenURL in a <span> tag. See http://ocoins.info/.
 *
 * @param reference Reference object to be encoded
 *
 * @return HTML <span> tag containing a COinS
 */
function reference_to_coins($reference)
{
	global $config;
	
	$coins = '';
	
	switch ($reference->genre)
	{
		case 'article':
			$coins .= '<span class="Z3988" title="';
			$coins .= reference_to_openurl($reference); 
			$coins .= '">';
			$coins .= '</span>';
			break;
			
		default:
			break;
	}
	
	return $coins;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Create an OpenURL for a reference
 * *
 * @param reference Reference object to be encoded
 *
 * @return OpenURL
 */
function reference_to_openurl($reference)
{
	global $config;
	
	$openurl = '';
	
	switch ($reference->genre)	
	{
		case 'article':
			$openurl .= 'ctx_ver=Z39.88-2004&amp;rft_val_fmt=info:ofi/fmt:kev:mtx:journal';
			$openurl .= '&amp;genre=article';
			if (count($reference->authors) > 0)
			{
				$openurl .= '&amp;rft.aulast=' . urlencode($reference->authors[0]->lastname);
				$openurl .= '&amp;rft.aufirst=' . urlencode($reference->authors[0]->forename);
			}
			foreach ($reference->authors as $author)
			{
				$openurl .= '&amp;rft.au=' . urlencode($author->forename . ' ' . $author->lastname);
			}
			$openurl .= '&amp;rft.atitle=' . urlencode($reference->title);
			$openurl .= '&amp;rft.jtitle=' . urlencode($reference->secondary_title);
			if (isset($reference->series))
			{
				$openurl .= '&amp;rft.series/' . urlencode($reference->series);
			}
			if (isset($reference->issn))
			{
				$openurl .= '&amp;rft.issn=' . $reference->issn;
			}
			$openurl .= '&amp;rft.volume=' . $reference->volume;
			$openurl .= '&amp;rft.spage=' . $reference->spage;
			if (isset($reference->epage))
			{
				$openurl .= '&amp;rft.epage=' . $reference->epage;
			}
			$openurl .= '&amp;rft.date=' . $reference->year;
			
			if (isset($reference->sici))
			{
				$openurl .= '&amp;rft.sici=' . urlencode($reference->sici);
			}
						
			if (isset($reference->doi))
			{
				$openurl .= '&amp;rft_id=info:doi/' . urlencode($reference->doi);
			}
			else if (isset($reference->hdl))
			{
				$openurl .= '&amp;rft_id=info:hdl/' . urlencode($reference->hdl);
			}
			else if (isset($reference->url))
			{
				$openurl .= '&amp;rft_id='. urlencode($reference->url);
			}
			break;
			
		default:
			break;
	}
	
	return $openurl;
}

//--------------------------------------------------------------------------------------------------
function reference_to_bibtex($reference)
{
	global $config;
	
	$bibtex = '';
	
	switch ($reference->genre)
	{
		case 'article':
			$bibtex .= "@article{";
			
			// Citekey
			$citekey = '';
			if (isset($reference->authors[0]->lastname))
			{
				$citekey = $reference->authors[0]->lastname;
				if (isset($reference->year))
				{
					$citekey .= $reference->year;
				}
				else
				{
					$citekey = ':' . $reference->reference_id;
				}
			}
			else
			{
				$citekey = 'biostor:' . $reference->reference_id;
			}
			$bibtex .= $citekey . ",\n";	
			
			
			$num_authors = count($reference->authors);
			if (count($num_authors) > 0)
			{
				$bibtex .= "   author = {" . latex_safe($reference->authors[0]->forename) . ' ' . latex_safe($reference->authors[0]->lastname);
				
				for ($i = 1; $i < $num_authors; $i++)
				{
					$bibtex .= " and " . latex_safe($reference->authors[$i]->forename) . ' ' . latex_safe($reference->authors[$i]->lastname);
				}
				$bibtex .= "},\n";
			}
			$bibtex .= "   title = {" . latex_safe($reference->title) . "},\n";
			$bibtex .= "   journal = {" . latex_safe($reference->secondary_title) . "},\n";
			$bibtex .= "   volume = {" . $reference->volume . "},\n";
			if (isset($reference->issue) && ($reference->issue != ''))
			{
				$bibtex .= "   number = {" . $reference->issue . "},\n";
			}
			$bibtex .= "   pages = {" . $reference->spage; 
			if (isset($reference->epage))
			{
				$bibtex .= "--" . $reference->epage;
			}
			$bibtex .=  "},\n";
			$bibtex .= "   year = {" . $reference->year . "},\n";

			$bibtex .= "   url = {" .  $config['web_root'] . "reference/" . $reference->reference_id . "}\n";
			$bibtex .=  "}\n";
			break;
			
		default:
			break;
	}
	
	return $bibtex;
}



//--------------------------------------------------------------------------------------------------
/**
 * @brief Export reference in RIS format
 *
 * @param reference Reference object to be encoded
 *
 * @return RIS format text
 */
function reference_to_ris($reference)
{
	global $config;
	
	$ris = '';
	
	switch ($reference->genre)
	{
		case 'article':
			$ris .= "TY  - JOUR\n";
			foreach ($reference->authors as $author)
			{
				$ris .= "AU  - " . $author->lastname . ", " . $author->forename . "\n";
			}
			$ris .= "ID  - " . $reference->reference_id . "\n";
			$ris .= "TI  - " . $reference->title . "\n";
			$ris .= "JF  - " . $reference->secondary_title . "\n";
			if (isset($reference->issn))
			{
				$ris .= "SN  - " . $reference->issn . "\n";
			}			
			$ris .= "VL  - " . $reference->volume . "\n";
			if (isset($reference->issue))
			{
				$ris .= "IS  - " . $reference->issue . "\n";
			}	
			$ris .= "SP  - " . $reference->spage . "\n";
			if (isset($reference->epage))
			{
				$ris .= "EP  - " . $reference->epage . "\n";
			}	
			$ris .= "Y1  - " . $reference->year . "\n";
			$ris .= "UR  - " .  $config['web_root'] . "reference/" . $reference->reference_id . "\n";

			// PDF
			if (isset($reference->pdf))
			{
				$ris .= "L1  - " . $reference->pdf . "\n";
			}	
			// BHL
			if (isset($reference->PageID))
			{
				$ris .= "L2  - http://www.biodiversitylibrary.org/page/" . $reference->PageID . "\n";
			}	


			$ris .= "ER  - \n\n";
			
			break;
			
		default:
			break;
	}
	
	return $ris;
}

//--------------------------------------------------------------------------------------------------
function reference_to_atom($reference, &$feed, &$rss)
{
	global $config;
	
	$entry = $feed->createElement('entry');
	$entry = $rss->appendChild($entry);
	
	// title
	$title = $entry->appendChild($feed->createElement('title'));
	$title->appendChild($feed->createTextNode($reference->title));

	// link
	$link = $entry->appendChild($feed->createElement('link'));
	$link->setAttribute('rel', 'alternate');
	$link->setAttribute('type', 'text/html');
	$link->setAttribute('href', $config['web_root'] . 'reference/' . $reference->reference_id);

	// dates
	$updated = $entry->appendChild($feed->createElement('updated'));
	$updated->appendChild($feed->createTextNode(date(DATE_ATOM, strtotime($reference->updated))));
	
	$created = $entry->appendChild($feed->createElement('published'));
	$created->appendChild($feed->createTextNode(date(DATE_ATOM, strtotime($reference->created))));

	// id
	$id = $entry->appendChild($feed->createElement('id'));
//	$id->appendChild($feed->createTextNode('urn:uuid:' . uuid()));
	$id->appendChild($feed->createTextNode( $config['web_root'] . 'reference/' . $reference->reference_id ));

	
	$description = '';
	$description .= '<div>';
	
	$description .= reference_authors_to_text_string($reference) . '<br/>';
	$description .= reference_to_citation_text_string($reference);
	$pages = bhl_retrieve_reference_pages($reference->reference_id);
	if (count($pages) > 0)
	{
		$description .= '<div><img src="http://biostor.org/bhl_image.php?PageID=' . $pages[0]->PageID . '&thumbnail" /></div>';
	}
	$description .= '</div>';
	
	// content
	$content = $entry->appendChild($feed->createElement('content'));
	$content->setAttribute('type', 'html');
	$content->appendChild($feed->createTextNode($description));

	// summary
	$summary = $entry->appendChild($feed->createElement('summary'));
	$summary->setAttribute('type', 'html');
	$summary->appendChild($feed->createTextNode($description));

}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Write reference in Endnote XML
 *
 * @param reference Reference object
 *
 * @return XML 
 */
function reference_to_endnote_xml($reference, &$doc, &$records)
{
	global $config;
	
	// record
	$record = $records->appendChild($doc->createElement('record'));
	
	// ref-type
	$ref_type = $record->appendChild($doc->createElement('ref-type'));
	
	switch ($reference->genre)
	{
		case 'article':
			$ref_type->setAttribute('name', 'Journal Article');
			break;
	}
	// contributors
	$contributors = $record->appendChild($doc->createElement('contributors'));
	$authors = $contributors->appendChild($doc->createElement('authors'));
	foreach ($reference->authors as $a)
	{
		$author = $authors->appendChild($doc->createElement('author'));
		$author->setAttribute('first-name', $a->forename);
		$author->setAttribute('last-name', $a->lastname);
		if (isset($a->suffix))
		{			
			$author->setAttribute('suffix', $a->suffix);
		}
		$author->appendChild($doc->createTextNode($a->forename . ' ' . $a->lastname));
	}
	
	// titles		
	$titles = $record->appendChild($doc->createElement('titles'));

	$title = $titles->appendChild($doc->createElement('title'));
	$title->appendChild($doc->createTextNode($reference->title));

	$secondary_title = $titles->appendChild($doc->createElement('secondary-title'));
	$secondary_title->appendChild($doc->createTextNode($reference->secondary_title));
	
	if ($reference->genre == 'article')
	{
		$periodical = $record->appendChild($doc->createElement('periodical'));
	
		$full_title = $periodical->appendChild($doc->createElement('full-title'));
		$full_title->appendChild($doc->createTextNode($reference->secondary_title));
		
		if (isset($reference->issn))
		{
			$isbn = $record->appendChild($doc->createElement('isbn'));
			$isbn->appendChild($doc->createTextNode($reference->issn));		
		}
	
		$volume = $record->appendChild($doc->createElement('volume'));
		$volume->appendChild($doc->createTextNode($reference->volume));
		
		if (isset($reference->issue))
		{
			$number = $record->appendChild($doc->createElement('number'));
			$number->appendChild($doc->createTextNode($reference->issue));			
		}

		$pages = $record->appendChild($doc->createElement('pages'));
		$pages->setAttribute('start', $reference->spage);
		
		$page_string = $reference->spage;
		if (isset($reference->epage))
		{
			$page_string .= '-' . $reference->epage;
			$pages->setAttribute('end', $reference->epage);
		}
		$pages->appendChild($doc->createTextNode($page_string));
		
		$urls = $record->appendChild($doc->createElement('urls'));
		$related_urls = $urls->appendChild($doc->createElement('related-urls'));
		$url = $related_urls->appendChild($doc->createElement('url'));
		$url->appendChild($doc->createTextNode($config['web_root'] . 'reference/' . $reference->reference_id));

	}
	// Dates
	$dates = $record->appendChild($doc->createElement('dates'));
	$year = $dates->appendChild($doc->createElement('year'));
	$year->appendChild($doc->createTextNode($reference->year));
}

//--------------------------------------------------------------------------------------------------
function reference_to_mendeley($reference)
{
	global $config;
	
	$obj = new stdclass;
	$obj->authors = array();
	foreach ($reference->authors as $a)
	{
		$author = new stdclass;
		$author->forename = $a->forename;
		$author->surname = $a->lastname;
		
		$obj->authors[] = $author;
	}
	
	if (isset($reference->date))
	{
		$obj->date = $reference->date;
	}	
	
	// Identifiers
	$obj->identifiers = new stdclass;
	$obj->identifiers->biostor = (Integer)$reference->reference_id;
	if (isset($reference->doi))
	{
		$obj->identifiers->doi = $reference->doi;
	}
	if (isset($reference->hdl))
	{
		$obj->identifiers->hdl = $reference->hdl;
	}
	if (isset($reference->isbn))
	{
		$obj->identifiers->isbn = $reference->isbn;
	}
	if (isset($reference->issn))
	{
		$obj->identifiers->issn = $reference->issn;
	}
	if (isset($reference->oclc))
	{
		if ($reference->oclc != 0)
		{
			$obj->identifiers->oclc = (Integer)$reference->oclc;
		}
	}
	if (isset($reference->pmid))
	{
		$obj->identifiers->pmid = (Integer)$reference->pmid;
	}
	if (isset($reference->lsid))
	{
		if (preg_match('/urn:lsid:zoobank.org:pub:(?<id>.*)/', $reference->lsid, $m))
		{
			$obj->identifiers->zoobank = $m['id'];
		}
	}
	
	if (isset($reference->issue))
	{
		$obj->issue = $reference->issue;
	}
	
	if (isset($reference->spage))
	{
		$obj->pages = $reference->spage;
	}
	if (isset($reference->epage))
	{
		$obj->pages .= '-' . $reference->epage;
	}
	
	$obj->publication_outlet = $reference->secondary_title;
	$obj->title = $reference->title;
	
	
	$obj->type = '';
	switch ($reference->genre)
	{
		case 'article':
			$obj->type = 'Journal Article';
			break;

		case 'book':
			$obj->type = 'Book';
			break;

		default:
			$obj->type = 'Unknown';
			break;
	}			

	if (isset($reference->volume))
	{
		$obj->volume = $reference->volume;
	}
	
	if (isset($reference->year))
	{
		$obj->year = $reference->year;
	}
	
	
	
	return $obj;
}

//--------------------------------------------------------------------------------------------------
function reference_to_solr($reference)
{
	$obj  = reference_to_mendeley($reference);
	

	$item = array();
	$item['id'] 				= 'reference/' . $reference->reference_id;
	$item['title'] 				= $obj->title;
	$item['publication_outlet'] = $obj->publication_outlet;
	$item['year'] 				= $obj->year;
	
	$authors = array();
	foreach ($obj->authors as $a)
	{
		$authors[] = $a->forename . ' ' . $a->surname;
	}
	$item['authors'] = $authors;
	$item['citation'] = reference_authors_to_text_string($reference)
		. ' ' . $reference->year 
		. ' ' . $reference->title
		. ' ' . reference_to_citation_text_string($reference);

	return $item;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Export reference in Wikispecies format
 *
 * @param reference Reference object to be encoded
 *
 * @return Wikispecies
 */
function reference_to_wikispecies($reference)
{
	global $config;
	
	$wikispecies = '';
	
	// * {{aut|Baptista, A.R.P.}}; {{aut|Mathis, W.N.}} 1996: A new species of ''Cyamops'' Melander (Diptera: Periscelididae) from Brazil, with distributional notes on another species. ''Proceedings of the Entomological Society of Washington'', '''98''': 245-248.
	
	$count = 0;
	$num_authors = count($reference->authors);
	foreach ($reference->authors as $author)
	{
		$wikispecies .= "{{aut| " . $author->lastname . ", " . $author->forename . "}}";
		$count++;
		if ($count < $num_authors )
		{
			$wikispecies .= '; ';
		}
	}
	$wikispecies .= ' ';
	$wikispecies .= $reference->year . ': ';
	$wikispecies .= $reference->title . '. ';
	$wikispecies .= "''" . $reference->secondary_title . "'', ";
	$wikispecies .= "'''" . $reference->volume . "''': ";
	$wikispecies .= $reference->spage;
	if (isset($reference->epage))
	{
		$wikispecies .= "-" . $reference->epage;
	}	
	$wikispecies .= ".";
	
	$wikispecies .= " [" . $config['web_root'] . 'reference/' . $reference->reference_id. " BioStor]";
	
	if (isset($reference->doi))
	{
		$wikispecies .= " {{doi|" . $reference->doi . "}}";
	}
	
	return $wikispecies;
}

//--------------------------------------------------------------------------------------------------
function reference_to_bibjson($reference, $include_text = true)
{
	global $config;
	
	$obj = new stdclass;
	$obj->author = array();
	foreach ($reference->authors as $a)
	{
		$author = new stdclass;
		
		if (($a->forename == '') || ($a->lastname == ''))
		{
		}
		else
		{		
			$author->firstname = $a->forename;
			$author->lastname = $a->lastname;
		}
		$author->name = trim($a->forename . ' ' . $a->lastname);
		
		$obj->author[] = $author;
	}
	
	switch ($reference->genre)
	{
		case 'article':
		case 'book':
		case 'chapter':
		case 'letter':
			$obj->type = $reference->genre;
			break;

		default:
			$obj->type = 'generic';
			break;
	}			
	
	$obj->title = $reference->title;
	
	if ($reference->genre == 'book')
	{
		if (isset($reference->publisher))
		{
			$obj->publisher = new stdclass;
			$obj->publisher->name = $reference->publisher;
			
			if (isset($reference->publoc))
			{
				$obj->publisher->address = $reference->publoc;
			}
		}

		if (isset($reference->oclc))
		{
			if ($reference->oclc != 0)
			{
				$identifier = new stdclass;
				$identifier->type = 'oclc';
				$identifier->id = (Integer)$reference->oclc; 
				$obj->book->identifier[] = $identifier;
			}
		}
		
		if (isset($reference->secondary_authors))
		{		
			$obj->book->editor = array();
			foreach ($reference->secondary_authors as $a)
			{
				$author = new stdclass;
				
				if (($a->forename == '') || ($a->lastname == ''))
				{
				}
				else
				{		
					$author->firstname = $a->forename;
					$author->lastname = $a->lastname;
				}
				$author->name = trim($a->forename . ' ' . $a->lastname);
				
				$obj->book->editor[] = $author;
			}
		}
		
		if (isset($reference->spage))
		{
			$obj->pages = $reference->spage;
		}
		if (isset($reference->epage))
		{
			$obj->pages .= '--' . $reference->epage;
		}		
				
	}
	
	
	if ($reference->genre == 'chapter')
	{
		$obj->book = new stdclass;
		$obj->book->title = $reference->secondary_title;
		
		if (isset($reference->secondary_authors))
		{		
			$obj->book->editor = array();
			foreach ($reference->secondary_authors as $a)
			{
				$author = new stdclass;
				
				if (($a->forename == '') || ($a->lastname == ''))
				{
				}
				else
				{		
					$author->firstname = $a->forename;
					$author->lastname = $a->lastname;
				}
				$author->name = trim($a->forename . ' ' . $a->lastname);
				
				$obj->book->editor[] = $author;
			}
		}
		
		if (isset($reference->publisher))
		{
			$obj->book->publisher = new stdclass;
			$obj->book->publisher->name = $reference->publisher;
			
			if (isset($reference->publoc))
			{
				$obj->book->publisher->address = $reference->publoc;
			}
		}
		
		
		if (isset($reference->oclc))
		{
			if ($reference->oclc != 0)
			{
				$identifier = new stdclass;
				$identifier->type = 'oclc';
				$identifier->id = (Integer)$reference->oclc; 
				$obj->book->identifier[] = $identifier;
			}
		}
		
		
		if (isset($reference->spage))
		{
			$obj->pages = $reference->spage;
		}
		if (isset($reference->epage))
		{
			$obj->pages .= '--' . $reference->epage;
		}		
		
		
	}
	

	if (($reference->genre == 'article') || ($reference->genre == 'letter'))
	{
		$obj->journal = new stdclass;
		$obj->journal->name = $reference->secondary_title;
		
		if (isset($reference->series))
		{
			$obj->journal->series = $reference->series;
		}
		
		$obj->journal->volume = $reference->volume;
		
		if (isset($reference->issue))
		{
			$obj->journal->issue = $reference->issue;
		}
	
		if (isset($reference->spage))
		{
			$obj->journal->pages = $reference->spage;
		}
		if (isset($reference->epage))
		{
			$obj->journal->pages .= '--' . $reference->epage;
		}
		if (isset($reference->issn))
		{
			$identifier = new stdclass;
			$identifier->type = 'issn';
			$identifier->id = $reference->issn; 
			$obj->journal->identifier[] = $identifier;
		}
		if (isset($reference->oclc))
		{
			if ($reference->oclc != 0)
			{
				$identifier = new stdclass;
				$identifier->type = 'oclc';
				$identifier->id = (Integer)$reference->oclc; 
				$obj->journal->identifier[] = $identifier;
			}
		}
	}
	
	/*
	if ($reference->genre == 'letter')
	{
	
		if (isset($reference->oclc))
		{
			if ($reference->oclc != 0)
			{
				$identifier = new stdclass;
				$identifier->type = 'oclc';
				$identifier->id = (Integer)$reference->oclc; 
				$obj->identifier[] = $identifier;
			}
		}	
	
		if (isset($reference->spage))
		{
			$obj->pages = $reference->spage;
		}
		if (isset($reference->epage))
		{
			$obj->pages .= '--' . $reference->epage;
		}
	}
	*/
	
	if (isset($reference->year))
	{
		$obj->year = $reference->year;
	}
	
	$link = new stdclass;
	$link->anchor = 'LINK';
	$link->url = $config['web_root'] . 'reference/' . $reference->reference_id;
	$link->url = 'https://biostor.org/' . 'reference/' . $reference->reference_id;
	$obj->link[] = $link;
	
	if (isset($reference->PageID))
	{
		$link = new stdclass;
		$link->anchor = 'LINK';
		$link->url = 'http://www.biodiversitylibrary.org/page/' . $reference->PageID;
		$obj->link[] = $link;
	}
	
	
	
	// Identifiers
	if (!isset($obj->identifier))
	{
		$obj->identifier = array();
	}

	$identifier = new stdclass;
	$identifier->type = 'biostor';
	$identifier->id = (Integer)$reference->reference_id; 
	$obj->identifier[] = $identifier;
	
	if (isset($reference->PageID))
	{
		$identifier = new stdclass;
		$identifier->type = 'bhl';
		$identifier->id = (Integer)$reference->PageID; 
		$obj->identifier[] = $identifier;
	}
	
	if (isset($reference->doi))
	{
		$identifier = new stdclass;
		$identifier->type = 'doi';
		$identifier->id = $reference->doi; 
		$obj->identifier[] = $identifier;
	}
	if (isset($reference->hdl))
	{
		$identifier = new stdclass;
		$identifier->type = 'handle';
		$identifier->id = $reference->hdl; 
		$obj->identifier[] = $identifier;
	}
	if (isset($reference->isbn))
	{
		$identifier = new stdclass;
		$identifier->type = 'isbn';
		$identifier->id = $reference->isbn; 
		$obj->identifier[] = $identifier;
	}
	if (isset($reference->isbn13))
	{
		$identifier = new stdclass;
		$identifier->type = 'isbn13';
		$identifier->id = $reference->isbn13; 
		$obj->identifier[] = $identifier;
	}

	if (isset($reference->lsid))
	{
		if (preg_match('/urn:lsid:zoobank.org:pub:(?<id>.*)/', $reference->lsid, $m))
		{
			$identifier = new stdclass;
			$identifier->type = 'lsid';
			$identifier->id = $reference->lsid; 
			$obj->identifier[] = $identifier;			
		}
	}

	if (isset($reference->pmid))
	{
		$identifier = new stdclass;
		$identifier->type = 'pmid';
		$identifier->id = (Integer)$reference->pmid; 
		$obj->identifier[] = $identifier;			
	}
	
	if (isset($reference->jstor))
	{
		$identifier = new stdclass;
		$identifier->type = 'jstor';
		$identifier->id = (Integer)$reference->jstor; 
		$obj->identifier[] = $identifier;			
	}
	
	
	if (0)
	{
		// espensive as it hits MySQL bad
		// names as tags
		$names = bhl_names_in_reference($reference->reference_id);
		if (count($names->tags) > 0)
		{
			$obj->tag = $names->tags;
		}
	}
	

	if (!bhl_has_been_geocoded($reference->reference_id))
	{
		bhl_geocode_reference($reference->reference_id);
	}
	$reference->localities = bhl_localities_for_reference($reference->reference_id);
	
	if (count($reference->localities) > 0)
	{
		$obj->geometry = new stdclass;
		$obj->geometry->type = "MultiPoint";
		$obj->geometry->coordinates = array();
		foreach ($reference->localities as $loc)
		{
			$obj->geometry->coordinates[] = array((Double)$loc->longitude, (Double)$loc->latitude);
		}
	}
	
	// BHL pages...
	
	// if 0 we don't upload text
	if (1)
	{
	
	
		$obj->bhl_pages = array();
		$pages = bhl_retrieve_reference_pages($reference->reference_id);
		foreach ($pages as $page)
		{		
			if ($page->PagePrefix != '')
			{
				$label = $page->PagePrefix;
			
				if ($page->PageTypeName != '')
				{
					if ($page->PageTypeName != 'Text')
					{
						$label .= ' (' . $page->PageTypeName . ')';
					}
				}
						
			
				$label .= ' ' . $page->PageNumber;
			}
			else
			{
				$label = $page->page_order ;
			}
			$obj->bhl_pages[$label] = (Integer)$page->PageID;
		}	
		
		// text
		if ($include_text)
		{
		
			$obj->text = array();
	
			if (db_reference_from_bhl($reference->reference_id))
			{
				$pages = bhl_retrieve_reference_pages($reference->reference_id);
				foreach ($pages as $p)
				{
					$obj->text[] = bhl_fetch_ocr_text($p->PageID);
				}
			}
		}
		
		
	}	
	
	
	return $obj;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Export reference in Wikipedia format
 *
 * @param reference Reference object to be encoded
 *
 * @return Wikipedia
 */
function reference_to_wikipedia($reference)
{
	global $config;
	
	$wikipedia = '{{cite ';
	
	switch ($reference->genre)
	{
		case 'article':
			$wikipedia .= 'journal ';
			break;

		case 'book':
			$wikipedia .= 'book ';
			break;
			
		default:
			break;
	}
	
	/*
{{cite journal | last = Zhou | first = K. | coauthors = Li, Y., Nishiwaki, M., Kataoka, T. | year = 1982 | title = A brief report on observations of the baiji (''Lipotes vexillifer'') in the lower reaches of the Yangtze River between Nanjing and Guichi | journal = Acta Theriologica Sinica | volume = 2 | pages = 253–254}}
	*/
	
	$count = 0;
	$num_authors = count($reference->authors);
	
	$wikipedia .= "\n" . ' | last = ' . $reference->authors[0]->lastname;
	$wikipedia .= "\n" . ' | first = ' . $reference->authors[0]->forename;
	
	if ($num_authors > 1)
	{
		$wikipedia .= "\n" . ' | coauthors = ';
		for ($i = 1; $i < $num_authors; $i++)
		{
			$wikipedia .= $reference->authors[$i]->lastname . ', ' . $reference->authors[$i]->forename . ';';
		}
	}
	$wikipedia .= ' ';
	$wikipedia .= "\n" . ' | year = ' . $reference->year;
	
	$wikipedia .= "\n" . ' | title = ' . $reference->title;
	$wikipedia .= "\n" . ' | journal = ' . $reference->secondary_title;
	$wikipedia .= "\n" . ' | volume = ' . $reference->volume ;
	if (isset($reference->issue))
	{
		$wikipedia .= "\n" . ' | issue = ' . $reference->issue;
	}	
	$wikipedia .= "\n" . ' | pages = ' . $reference->spage;
	if (isset($reference->epage))
	{
		$wikipedia .= "-" . $reference->epage;
	}	
	
	$wikipedia .= "\n" . ' | url = ' .  $config['web_root'] . 'reference/' . $reference->reference_id;
	
	if (isset($reference->doi))
	{
		$wikipedia .= "\n" . ' | doi = ' . $reference->doi;
	}
	
	$wikipedia .= "\n" . '}}';
	
	return $wikipedia;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Convert to citeproc-js object
 *
 * @param reference Reference object to be converted
 * @param id Local id for citeproc-js object 
 *
 * @return citeproc-js object
 */
function reference_to_citeprocjs($reference, $id = 'ITEM-1')
{
	global $config;

	$citeproc_obj = array();
	$citeproc_obj['id'] = $id;
	$citeproc_obj['title'] = $reference->title;
	
	switch ($reference->genre)
	{
		case 'article':
			$citeproc_obj['type'] = 'article-journal';
			break;

		case 'book':
			$citeproc_obj['type'] = 'book ';
			break;
			
		default:
			break;
	}
		
	
	$citeproc_obj['issued']['date-parts'][] = array($reference->year);
	
	if (isset($reference->authors))
	{
		$citeproc_obj['author'] = array();
		foreach ($reference->authors as $author)
		{
			// ICZN
			if (isset($author->forename) && ($author->forename == 'International Commission On Zoological'))
			{
				$a = new stdclass;
				$a->literal = $author->forename . ' ' . $author->lastname;
				$a->literal = str_replace(' On ', ' on ', $a->literal);
				$citeproc_obj['author'][] = $a;
			}
			else
			{
				if (isset($author->lastname))
				{
					$a = new stdclass;
					if (isset($author->forename))
					{
						$a->given = $author->forename;
						$a->family = $author->lastname;
					}
					else
					{
						$a->literal = $author->name;
					}
					$citeproc_obj['author'][] = $a;
				}
			}
		}
	}
	
	if (isset($reference->secondary_title))
	{
		$citeproc_obj['container-title'] = $reference->secondary_title;
		$citeproc_obj['volume'] = $reference->volume;
		if (isset($reference->issue))
		{
			$citeproc_obj['issue'] = $reference->issue;
		}
		$citeproc_obj['page'] = $reference->spage . '-' . $reference->epage;
	}
	
	if (isset($reference->doi))
	{
		$citeproc_obj['DOI'] = $reference->doi;
	}
	
	$citeproc_obj['URL'] = $config['web_root'] . 'reference/' . $reference->reference_id;
	
	return $citeproc_obj;
}

//--------------------------------------------------------------------------------------------------
// NLM JATS metadata and page scans
function reference_to_jats($reference)
{
	$reference->bhl_pages = array();
	$reference->text = array();
	
	if (db_reference_from_bhl($reference->reference_id))
	{
		$pages = bhl_retrieve_reference_pages($reference->reference_id);
		foreach ($pages as $p)
		{
			$reference->bhl_pages[] = $p->PageID;
			$reference->text[] = bhl_fetch_ocr_text($p->PageID);
		}
	}	


	$doc = DOMImplementation::createDocument(null, '',
		DOMImplementation::createDocumentType("article", 
			"SYSTEM", 
			"jats-archiving-dtd-1.0/JATS-archivearticle1.dtd"));
	
	// http://stackoverflow.com/questions/8615422/php-xml-how-to-output-nice-format
	$doc->preserveWhiteSpace = false;
	$doc->formatOutput = true;	
	
	// root element is <records>
	$article = $doc->appendChild($doc->createElement('article'));
	
	$article->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
	
	$front = $article->appendChild($doc->createElement('front'));
	
	$journal_meta = $front->appendChild($doc->createElement('journal-meta'));
	$journal_title_group = $journal_meta->appendChild($doc->createElement('journal-title-group'));
	$journal_title = $journal_title_group->appendChild($doc->createElement('journal-title'));
	$journal_title->appendChild($doc->createTextNode($reference->secondary_title));
	
	if (isset($reference->issn))
	{
		$issn = $journal_meta->appendChild($doc->createElement('issn'));
		$issn->appendChild($doc->createTextNode($reference->issn));
	}
	
	$article_meta = $front->appendChild($doc->createElement('article-meta'));
	
	$article_id = $article_meta->appendChild($doc->createElement('article-id'));
	$article_id->setAttribute('pub-id-type', 'biostor');
	$article_id->appendChild($doc->createTextNode($reference->reference_id));

	if (isset($reference->doi))
	{
		$article_id = $article_meta->appendChild($doc->createElement('article-id'));
		$article_id->setAttribute('pub-id-type', 'doi');
		$article_id->appendChild($doc->createTextNode($reference->doi));
	}
	
	$title_group = $article_meta->appendChild($doc->createElement('title-group'));
	$article_title = $title_group->appendChild($doc->createElement('article-title'));
	$article_title->appendChild($doc->createTextNode($reference->title));
	
	if (count($reference->authors) > 0)
	{
		$contrib_group = $article_meta->appendChild($doc->createElement('contrib-group'));
		
		foreach ($reference->authors as $author)
		{
			$contrib = $contrib_group->appendChild($doc->createElement('contrib'));
			$contrib->setAttribute('contrib-type', 'author');
			
			$name = $contrib->appendChild($doc->createElement('name'));
			$surname = $name->appendChild($doc->createElement('surname'));
			$surname->appendChild($doc->createTextNode($author->lastname));
			if (isset($author->forename))
			{
				$given_name = $name->appendChild($doc->createElement('given-names'));
				$given_name->appendChild($doc->createTextNode($author->forename));
			}
		}
	}
	
	if (isset($reference->date))
	{
		$pub_date = $article_meta->appendChild($doc->createElement('pub-date'));
		$pub_date->setAttribute('pub-type', 'ppub');
		
		if (preg_match('/(?<year>[0-9]{4})-(?<month>\d+)-(?<day>\d+)/', $reference->date, $m))
		{
			if ($m['day'] != '00')
			{
				$day = $pub_date->appendChild($doc->createElement('day'));
				$day->appendChild($doc->createTextNode(str_replace('0','', $m['day'])));			
			}
			
			if ($m['month'] != '00')
			{
				$month = $pub_date->appendChild($doc->createElement('month'));
				$month->appendChild($doc->createTextNode(str_replace('0','', $m['month'])));
			}
		
			$year = $pub_date->appendChild($doc->createElement('year'));
			$year->appendChild($doc->createTextNode($m['year']));
		}	
	}
	else
	{
		$pub_date = $article_meta->appendChild($doc->createElement('pub-date'));
		$pub_date->setAttribute('pub-type', 'ppub');
		$year = $pub_date->appendChild($doc->createElement('year'));
		$year->appendChild($reference->year);
	}
	
	if (isset($reference->volume))
	{
		$volume = $article_meta->appendChild($doc->createElement('volume'));
		$volume->appendChild($doc->createTextNode($reference->volume));
	}
	if (isset($reference->issue))
	{
		$issue = $article_meta->appendChild($doc->createElement('issue'));
		$issue->appendChild($doc->createTextNode($reference->issue));
	}
	
	
	if (isset($reference->spage))
	{
		$fpage = $article_meta->appendChild($doc->createElement('fpage'));
		$fpage->appendChild($doc->createTextNode($reference->spage));		
	}
	
	if (isset($reference->epage))
	{
		$fpage = $article_meta->appendChild($doc->createElement('lpage'));
		$fpage->appendChild($doc->createTextNode($reference->epage));		
	}
	
	
	if (isset($reference->abstract))
	{
		$abstract = $article_meta->appendChild($doc->createElement('abstract'));
		$p = $abstract->appendChild($doc->createElement('p'));
		$p->appendChild($doc->createTextNode($reference->abstract));		
	}
	
	$body = $article->appendChild($doc->createElement('body'));
	
	if (count($reference->text) > 0)
	{
		foreach ($reference->text as $text)
		{
			$preformat = $body->appendChild($doc->createElement('preformat'));
			$preformat->appendChild($doc->createTextNode($text));
		}
	}
	
	$supplementary_material = $body->appendChild($doc->createElement('supplementary-material'));
	$supplementary_material->setAttribute('content-type', 'scanned-pages');
	
	$n = count($reference->bhl_pages);
	for($i = 0; $i < $n; $i++)
	{
		$graphic = $supplementary_material->appendChild($doc->createElement('graphic'));
		$graphic->setAttribute('xlink:href', 'http://www.biodiversitylibrary.org/pagethumb/' . $reference->bhl_pages[$i]);
		$graphic->setAttribute('xlink:role', $reference->bhl_pages[$i]);
		$graphic->setAttribute('xlink:title', 'scanned-page');
	}
	
	return $doc->saveXML();
}




?>