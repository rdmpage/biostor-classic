<?php

/**
 * @file nomenclator.php
 *
 * Add names from nomenclators
 *
 */
 
 
// Need to build table linking names to references

/*
INSERT INTO rdmp_reference_taxon_name_joiner(taxon_name_id, reference_id) 
SELECT rdmp_taxon_name.taxon_name_id, rdmp_reference.reference_id FROM rdmp_taxon_name 
INNER JOIN rdmp_reference WHERE rdmp_taxon_name.publishedInCitation = rdmp_reference.lsid;
*/
 
require_once(dirname(__FILE__) .'/taxon.php');
require_once(dirname(__FILE__) .'/taxon_name.php');


//--------------------------------------------------------------------------------------------------
function db_retrieve_taxon_name($taxon_name_id)
{
	global $db;
	
	$tn = new TaxonName;
	
	$sql = 'SELECT * FROM rdmp_taxon_name WHERE taxon_name_id=' . $taxon_name_id . ' LIMIT 1'; 

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		// Populate object
		foreach ($result->fields as $k => $v)
		{
			switch ($k)
			{
					
				default:
					if ($v != '')
					{
						$tn->$k = $v;
					}
			}
		}
	}
	
	return $tn;
}

//--------------------------------------------------------------------------------------------------
function db_store_taxon_name($uri, $taxon_name)
{
	global $db;
	
	$taxon_name_id = 0;
	
	$sql = 'SELECT * FROM rdmp_taxon_name WHERE (global_id = ' . $db->qstr($uri) . ') LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$taxon_name_id = $result->fields['taxon_name_id'];
	}
	else
	{
		$taxon_name->global_id = $uri;
		
		// Store name string
		$taxon_name->namestring_id = find_namestring($taxon_name->nameComplete);
		
		if (preg_match('/urn:lsid:organismnames.com:name:(?<id>\d+)$/', $uri, $m))
		{
			$taxon_name->local_id = $m['id'];
		}
		if (preg_match('/urn:lsid:indexfungorum.org:names:(?<id>\d+)$/', $uri, $m))
		{
			$taxon_name->local_id = $m['id'];
		}
	
		// Store taxon name
		$keys = array();
		$values = array();
		
		// Article metadata
		foreach ($taxon_name as $k => $v)
		{
			switch ($k)
			{
				
				// Things we store as is
				case 'namestring_id':
				case 'local_id':
				case 'global_id':
				case 'versionInfo':
				case 'nameComplete':
				case 'uninomial':
				case 'genusPart':
				case 'specificEpithet':
				case 'nomenclaturalCode':
				case 'rank':
				case 'rankString':
				case 'authorship':
				case 'year':
				case 'publishedInCitation':
				case 'publishedIn':
				case 'microreference':
					$keys[] = $k;
					$values[] = $db->qstr($v);
					break;		
					
				case 'publication':
					$keys[] = $k;
					$values[] = $db->qstr(json_encode($v));					
					break;
				
				// Things we ignore
				default:
					break;
			}
		}
		
		$sql = 'INSERT INTO rdmp_taxon_name (' . implode (",", $keys) . ') VALUES (' . implode (",", $values) . ')';
	
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

		$taxon_name_id = $db->Insert_ID();
		
	}
	return $taxon_name_id;
}

	

//--------------------------------------------------------------------------------------------------
function resolve_nomenclator_lsid($lsid)
{
	global $config;
	
	$taxon_name_id = 0;
	
//	$url = 'http://bioguid.info/lsid.php?lsid=' . $lsid . '&display=rdf';
	$url = 'http://lsid.tdwg.org/' . $lsid;

	$rdf = get($url);
	
	if ($rdf != '')
	{	
		echo $rdf;
		
		//------------------------------------------------------------------------------------------
		// Create a DOM object from the RDF
		$dom= new DOMDocument;
		$dom->loadXML($rdf);
		$xpath = new DOMXPath($dom);
		
		$xpath->registerNamespace("dc", "http://purl.org/dc/elements/1.1/");
		$xpath->registerNamespace("owl", "http://www.w3.org/2002/07/owl#");	
		$xpath->registerNamespace("rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");

		$xpath->registerNamespace("tcom", "http://rs.tdwg.org/ontology/voc/Common#");
		$xpath->registerNamespace("tpub","http://rs.tdwg.org/ontology/voc/PublicationCitation#");
		$xpath->registerNamespace("tn", "http://rs.tdwg.org/ontology/voc/TaxonName#");	
		
		// Build simple taxon name object
		$taxon_name = new stdclass;
		
		// Version
		$nodeCollection = $xpath->query ('//owl:versionInfo');
		foreach($nodeCollection as $node)
		{
			$taxon_name->versionInfo = $node->firstChild->nodeValue;
		}	
		
		//------------------------------------------------------------------------------------------
		// Name
		$nodeCollection = $xpath->query ('//tn:nameComplete');
		foreach($nodeCollection as $node)
		{
			$taxon_name->nameComplete = $node->firstChild->nodeValue;
		}	
		$nodeCollection = $xpath->query ('//tn:uninomial');
		foreach($nodeCollection as $node)
		{
			$taxon_name->uninomial = $node->firstChild->nodeValue;
		}	
		$nodeCollection = $xpath->query ('//tn:genusPart');
		foreach($nodeCollection as $node)
		{
			$taxon_name->genusPart = $node->firstChild->nodeValue;
		}	
		$nodeCollection = $xpath->query ('//tn:specificEpithet');
		foreach($nodeCollection as $node)
		{
			$taxon_name->specificEpithet = $node->firstChild->nodeValue;
		}	
		
		// Code
		$nodeCollection = $xpath->query ('//tn:nomenclaturalCode/@rdf:resource');
		foreach($nodeCollection as $node)
		{
			$taxon_name->nomenclaturalCode = $node->firstChild->nodeValue;
		}	
		
		// Rank
		$nodeCollection = $xpath->query ('//tn:rank/@rdf:resource');
		foreach($nodeCollection as $node)
		{
			$taxon_name->rank = $node->firstChild->nodeValue;
		}	
		$nodeCollection = $xpath->query ('//tn:rankString');
		foreach($nodeCollection as $node)
		{
			$taxon_name->rankString = $node->firstChild->nodeValue;
		}	
		
		//------------------------------------------------------------------------------------------		
		// Authorship
		$nodeCollection = $xpath->query ('//tn:authorship');
		foreach($nodeCollection as $node)
		{
			$taxon_name->authorship = $node->firstChild->nodeValue;
		}	
		$nodeCollection = $xpath->query ('//tn:year');
		foreach($nodeCollection as $node)
		{
			$taxon_name->year = $node->firstChild->nodeValue;
		}	
		
		//------------------------------------------------------------------------------------------
		// Publication
		
		// Publication as string
		// Note union XPath query to handle different case 
		// see http://stackoverflow.com/questions/953845/how-do-i-perform-a-case-insensitive-search-for-a-node-in-php-xpath
		$nodeCollection = $xpath->query ('//tcom:PublishedIn | //tcom:publishedIn');
		foreach($nodeCollection as $node)
		{
			$taxon_name->publishedIn = $node->firstChild->nodeValue;
		}	

		// ION has this
		$nodeCollection = $xpath->query ('//tcom:microreference');
		foreach($nodeCollection as $node)
		{
			if (isset($node->firstChild)) // ION may have this blank
			{
				$taxon_name->microreference = $node->firstChild->nodeValue;
			}
		}	
			
		//------------------------------------------------------------------------------------------
		// Publication object (store as JSON)
		$taxon_name->publication = new stdclass;
		
		// ZooBank stores publication LSID here
		$nodeCollection = $xpath->query ('//tn:publication/tpub:PublicationCitation/dc:identifier');
		foreach($nodeCollection as $node)
		{
			$taxon_name->publishedInCitation = $node->firstChild->nodeValue;
			$taxon_name->publication->identifier = $node->firstChild->nodeValue;
		}	

		$nodeCollection = $xpath->query ('//tn:publication/tpub:PublicationCitation/tpub:year');
		foreach($nodeCollection as $node)
		{
			$taxon_name->publication->year = $node->firstChild->nodeValue;
		}	
		$nodeCollection = $xpath->query ('//tn:publication/tpub:PublicationCitation/tpub:volume');
		foreach($nodeCollection as $node)
		{
			$taxon_name->publication->volume = $node->firstChild->nodeValue;
		}	
		$nodeCollection = $xpath->query ('//tn:publication/tpub:PublicationCitation/tpub:title');
		foreach($nodeCollection as $node)
		{
			$taxon_name->publication->title = $node->firstChild->nodeValue;
		}	
		$nodeCollection = $xpath->query ('//tn:publication/tpub:PublicationCitation/tpub:pages');
		foreach($nodeCollection as $node)
		{
			$taxon_name->publication->pages = $node->firstChild->nodeValue;
		}	
		$nodeCollection = $xpath->query ('//tn:publication/tpub:PublicationCitation/tpub:authorship');
		foreach($nodeCollection as $node)
		{
			$taxon_name->publication->authorship = $node->firstChild->nodeValue;
		}	
		$nodeCollection = $xpath->query ('//tn:publication/tpub:PublicationCitation/tpub:parentPublicationString');
		foreach($nodeCollection as $node)
		{
			$taxon_name->publication->parentPublicationString = $node->firstChild->nodeValue;
		}	
		$nodeCollection = $xpath->query ('//tn:publication/tpub:PublicationCitation/tpub:parentPublication/@rdf:resource');
		foreach($nodeCollection as $node)
		{
			$taxon_name->publication->parentPublication = $node->firstChild->nodeValue;
		}	
		$nodeCollection = $xpath->query ('//tn:publication/tpub:PublicationCitation/tpub:datePublished');
		foreach($nodeCollection as $node)
		{
			$taxon_name->publication->datePublished = $node->firstChild->nodeValue;
		}			
		
		print_r($taxon_name);
		
		// Store taxon name
		$taxon_name_id = db_store_taxon_name($lsid, $taxon_name);	
	}
	
	return $taxon_name_id;
}


//--------------------------------------------------------------------------------------------------
// Link a taxon name and a reference
function link_reference_to_act($reference_id, $taxon_name_id)
{
	global $db;
	
	$sql = 'SELECT * FROM rdmp_reference_taxon_name_joiner 
WHERE (reference_id=' . $reference_id . ') AND (taxon_name_id=' . $taxon_name_id . ') LIMIT 1';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 0)
	{
		$sql = 'INSERT INTO rdmp_reference_taxon_name_joiner(reference_id, taxon_name_id) VALUES ('
		. $reference_id . ',' . $taxon_name_id . ')';

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	}
}

/*
//--------------------------------------------------------------------------------------------------
// Return array of taxon name objects published in a publication
function acts_in_publication($guid)
{
	global $db;
	
	$ids = array();
	
	$sql = 'SELECT taxon_name_id FROM rdmp_taxon_name 
WHERE publishedInCitation=' . $db->qstr($guid);

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$ids[] = $result->fields['taxon_name_id'];
		$result->MoveNext();
	}

	$acts = array();
	foreach ($ids as $id)
	{
		$acts[] = db_retrieve_taxon_name($id);	
	}
	
	return $acts;
}
*/
//--------------------------------------------------------------------------------------------------
// Return array of taxon name objects published in a publication
function acts_in_publication($reference_id)
{
	global $db;
	
	$ids = array();
	
	$sql = 'SELECT taxon_name_id FROM rdmp_reference_taxon_name_joiner 
WHERE reference_id=' . $db->qstr($reference_id);

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$ids[] = $result->fields['taxon_name_id'];
		$result->MoveNext();
	}

	$acts = array();
	foreach ($ids as $id)
	{
		$acts[] = db_retrieve_taxon_name($id);	
	}
	
	return $acts;
}

//--------------------------------------------------------------------------------------------------
// Taxonomic names/acts for a given namestring
function acts_for_namestring($namestring)
{
	global $db;
	
	$ids = array();
	
	$sql = 'SELECT rdmp_taxon_name.taxon_name_id FROM rdmp_namestring
INNER JOIN rdmp_taxon_name USING(namestring_id)
WHERE rdmp_namestring.namestring = ' . $db->qstr($namestring);

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$ids[] = $result->fields['taxon_name_id'];
		$result->MoveNext();
	}

	$acts = array();
	foreach ($ids as $id)
	{
		$acts[] = db_retrieve_taxon_name($id);	
	}
	
	return $acts;	
}


//--------------------------------------------------------------------------------------------------
// References with acts for this string
function references_for_acts_for_namestring($namestring)
{
	global $db;
	
	$reference_ids = array();
	
	$sql = 'SELECT rdmp_reference_taxon_name_joiner.reference_id
FROM rdmp_reference_taxon_name_joiner
INNER JOIN rdmp_taxon_name USING (taxon_name_id)
INNER JOIN rdmp_namestring USING(namestring_id)
WHERE rdmp_namestring.namestring = ' . $db->qstr($namestring);

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$reference_ids[] = $result->fields['reference_id'];
		$result->MoveNext();
	}

	return $reference_ids;
}


// test
if (0)
{
	
	//$lsid = 'urn:lsid:zoobank.org:act:8F76494C-EA94-4469-8165-DD59112B5DB7';
	
	$lsid = 'urn:lsid:ipni.org:names:940522-1';
	
	//$lsid = 'urn:lsid:organismnames.com:name:158762';
	
	//$lsid = 'urn:lsid:indexfungorum.org:names:351936';
	
	//$lsid = 'urn:lsid:zoobank.org:act:38AF91E6-2816-431D-94BF-7C21A77A2C9D';
	
	// Harvest ZooBank name LSID
	
	//$lsid = 'urn:lsid:zoobank.org:act:F9F475E6-1AEE-4E66-B18B-820749A0AFD6';
	//$lsid = 'urn:lsid:zoobank.org:act:727D9DDE-AF96-4745-9E86-CCFE04801342';
	
	// Astyanax varzeae in ZooBank and ION (in Zootaxa, not BHL)
	//$lsid = 'urn:lsid:organismnames.com:name:1929848';
	//$lsid = 'urn:lsid:zoobank.org:act:7A11D37F-F8DE-45C5-B87D-C5ED297E9B76';
	
	// Hippocampus zosterae
	// http://dbpedia.org/resource/Dwarf_seahorse
	// dbpedia-owl:Species/binomialAuthority http://dbpedia.org/resource/David_Starr_Jordan
	
	
	$lsid = 'urn:lsid:zoobank.org:act:2305A61F-D1B5-4796-8EE1-E9E4136EB0B0';
	
	$lsid = 'urn:lsid:organismnames.com:name:661830';
	resolve_nomenclator_lsid($lsid);
}


?>