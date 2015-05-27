<?php

/**
 * @file bioguid.php
 *
 * Encapsulate http://bioguid.info/ web services
 *
 */
 
require_once (dirname(__FILE__) . '/lib.php'); 
require_once (dirname(__FILE__) . '/reference.php'); 

//--------------------------------------------------------------------------------------------------
/**
 * @brief Lookup journal ISSN from title using bioGUID web service
 *
 * @param title Title of journal
 *
 * @return ISSN if found, empty string if not found, or if lookup fails
 *
 */
function issn_from_title($title)
{
	global $db;
	
	$issn = '';
	
	if (1) // local version
	{
		// local
		$str = $title;
		$str = str_replace(' of ', ' ', $str);
		$str = str_replace(' for ', ' ', $str);
		$str = preg_replace('/^The /', '', $str);
	
		$str = str_replace('(', '', $str);
		$str = str_replace(')', '', $str);
	
		$str = str_replace(',', '', $str);
		$str = str_replace(':', '', $str);
		$str = str_replace('\'', '', $str);
		$str = str_replace('.', '%', $str);
		
		$str = preg_replace('/\s\s*/', ' ', $str);
		
		$str = str_replace (' ', '%', $str);
		$str .= '%';
			
		$sql = 'SELECT * FROM issn
			WHERE title LIKE ' .  $db->Quote($str) .'
			ORDER BY title
			LIMIT 10';
			
		$result = $db->Execute($sql);
		if ($result == false) die("failed"); 
		
		if ($result->NumRows() > 0)
		{
			$issn = $result->fields['issn'];
		}
	}
	else
	{
		// Server
		
		$url = 'http://bioguid.info/services/journalsuggest.php?title=' . urlencode($title);
		$json = get($url);
		
		//echo $url;
			
		//echo $json;
			
		if ($json != '')
		{
		
			$obj = json_decode($json);
			
			//print_r($obj);
			
			if (count($obj->results) > 0)
			{
				$issn = $obj->results[0]->issn;
			}
		}
	}
	return $issn;
} 

//--------------------------------------------------------------------------------------------------
/**
 * @brief Lookup taxon name in uBio using bioGUID web service
 *
 * @param name Name to look for
 *
 * @return NameBankID if found, 0 if name not found
 *
 */
function bioguid_ubio_search($name)
{
	$NameBankID = 0;

	$url = 'http://bioguid.info/ubiosearch.php?name=' . urlencode($name);
	$json = get($url);
	if ($json != '')
	{
		$ids = json_decode($json);
		if (count($ids) == 1)
		{
			$NameBankID = $ids[0];	
		}
	}
	return $NameBankID;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Pass refrence object to bioGUID openURL resolver to find it and add any identifiers found
 *
 * @param reference Reference object, this gets populated with additional fields (such as DOI) if found
 *
 * @return True if found in bioGUID, false otherwise.
 */
function bioguid_openurl_search(&$reference)
{
	$found = false;
	
	$url = 'http://bioguid.info/openurl.php?' . str_replace('&amp;', '&', reference_to_openurl($reference)) . '&display=json';
	
	//echo $url . "\n";
	
	$json = get($url);
		
	if ($json != '')
	{
		$obj = json_decode($json);
				
		$found = ($obj->status == 'ok');
		if ($found)
		{
			// Flesh out
			// Abstract
			if (isset($obj->abstract))
			{
				if (!isset($reference->abstract))
				{
					$reference->abstract = $obj->abstract;
				}
			}
			// epage
			if (isset($obj->epage))
			{
				if (!isset($reference->epage))
				{
					$reference->epage = $obj->epage;
				}
			}
		
			// ISSN
			if (isset($obj->issn))
			{
				if (!isset($reference->issn))
				{
					$reference->issn = $obj->issn;
				}
			}
			// DOI
			if (isset($obj->doi))
			{
				if (!isset($reference->doi))
				{
					$reference->doi = $obj->doi;
				}
			}
			// PMID
			if (isset($obj->pmid))
			{
				if (!isset($reference->pmid))
				{
					$reference->pmid = $obj->pmid;
				}
			}
			// Handle
			if (isset($obj->hdl))
			{
				if (!isset($reference->hdl))
				{
					$reference->hdl = $obj->hdl;
				}
			}
			// URL
			if (isset($obj->url))
			{
				if (!isset($reference->url))
				{
					$reference->url = $obj->url;
				}
			}
			// PDF
			if (isset($obj->pdf))
			{
				if (!isset($reference->pdf))
				{
					$reference->pdf = $obj->pdf;
				}
			}
			// Authors
			if (isset($obj->authors))
			{
				if (count($reference->authors == 0))
				{
					foreach ($obj->authors as $author)
					{
						$reference->authors[] = $author;
					}
				}
			}
			
		}
	}

	return $found;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Call bioGUID to retrieve metadata for a bibliographic identifier (e.g., doi)
 *
 * @param reference Reference object, this gets populated with additional fields (such as DOI) if found
 *
 * @return True if found in bioGUID, false otherwise.
 */
function bioguid_resolve_identifier($namespace, $id)
{
	$reference = NULL;
	
	$url = 'http://bioguid.info/openurl.php?id=';
	
	switch ($namespace)
	{
		case 'hdl':
		case 'doi':
		case 'pmid':
			$url .= $namespace . ':';
			break;
			
		default:
			break;
	
	}
	$url .= urlencode($id) . '&display=json';
	
	//echo $url . "\n";
	
	$json = get($url);

	if ($json != '')
	{
		$obj = json_decode($json);
				
		$found = ($obj->status == 'ok');
		if ($found)
		{
			$reference = new stdclass;
			$reference->authors = array();
			
			foreach ($obj as $k => $v)
			{
				switch ($k)
				{
					case 'doi':
					case 'hdl':
					case 'pmid':
					case 'url':
					case 'pdf':
					case 'volume':
					case 'issue':
					case 'spage':
					case 'epage':
					case 'year':
					case 'date':
					case 'issn':
					case 'genre':
						if (trim($v) != '')
						{
							$reference->{$k} = trim($v);
						}
						break;
						
					case 'title':
						$reference->secondary_title = $v;
						break;

					case 'atitle':
						$reference->title = $v;
						break;
						
					case 'authors':
						foreach ($obj->authors as $author)
						{
							$reference->authors[] = $author;
						}
						break;					
						
					default:
						break;
				}
			}
			
			print_r($reference);

		}
	}
	return $reference;
}


?>