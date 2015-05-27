<?php

/**
 * @file globalnames.php
 *
 * Query globalnames, see http://wiki.github.com/dimus/gni/api
 *
 */

require_once(dirname(__FILE__) . '/lib.php');


//--------------------------------------------------------------------------------------------------
function find_in_global_names($name)
{
	$guids = array();
	
	$url = 'http://globalnames.org/name_strings.json?search_term=' . urlencode($name);
	
	$json = get($url);
	
	if ($json != '')
	{
		$j = json_decode($json);
		//print_r($j);
				
		foreach ($j->name_strings as $ns)
		{
			$url = $ns->resource_uri;
			
			$xml = get($url);
			//echo $xml;
			
			// Extract id(s)
			
			// Create a DOM object from the XML
			$dom= new DOMDocument;
			$dom->loadXML($xml);
			$xpath = new DOMXPath($dom);
					
			$name_index_id = array();
					
			// name_index_id
			$nodeCollection = $xpath->query ('//name_index_id[@type="integer"]');
			foreach($nodeCollection as $node)
			{
				$name_index_id[] = $node->firstChild->nodeValue;
			}	
			
			//print_r($name_index_id);
			
			foreach ($name_index_id as $n)
			{
				$html = get('http://globalnames.org/name_indices/' . $n . '/name_index_records');
				
				// uBio (maybe multiple hits across results)
				if (preg_match('/>(?<lsid>urn:lsid:ubio.org:namebank:\d+)<\/a>/', $html, $matches))
				{
					if (!isset($guids['ubio']))
					{
						$guids['ubio'] = array();
					}
					$guids['ubio'][] = $matches['lsid'];
				}
				
				// CoL
				if (preg_match('/>(?<lsid>urn:lsid:catalogueoflife.org:taxon:(.*):ac2009)<\/a>/', $html, $matches))
				{
					if (!isset($guids['col']))
					{
						$guids['col'] = array();
					}
					$guids['col'][] = $matches['lsid'];
				}
				
				// IF
				if (preg_match('/>(?<lsid>urn:lsid:indexfungorum.org:names:\d+)<\/a>/', $html, $matches))
				{
					if (!isset($guids['if']))
					{
						$guids['if'] = array();
					}
					$guids['if'][] = $matches['lsid'];
				}
				
				// Zoobank
				if (preg_match('/>(?<lsid>urn:lsid:zoobank.org:act:(.*))<\/a>/', $html, $matches))
				{
					if (!isset($guids['zoobank']))
					{
						$guids['zoobank'] = array();
					}
					$guids['zoobank'][] = $matches['lsid'];
				}
				
				// ION
				if (preg_match('/"http:\/\/www.organismnames.com\/details.htm\?lsid=(?<ion>\d+)">/', $html, $matches))
				{
					if (!isset($guids['ion']))
					{
						$guids['ion'] = array();
					}
					$guids['ion'][] = $matches['ion'];
				}
				
				// Wikipedia
				if (preg_match('/"http:\/\/en.wikipedia.org\/w\/index.php\?title=(?<wikipedia>(.*))&amp;oldid=\d+">/', $html, $matches))
				{
					if (!isset($guids['wikipedia']))
					{
						$guids['wikipedia'] = array();
					}
					$guids['wikipedia'][] = $matches['wikipedia'];
				}
	
				// IPNI (may be multiple records)
				if (preg_match_all('/"http:\/\/www.ipni.org\/ipni\/idPlantNameSearch.do\?&amp;output_format=lsid-metadata&amp;show_history=true&amp;id=(?<id>\d+\-[1|2|3])">/', $html, $matches))
				{
					if (!isset($guids['ipni']))
					{
						$guids['ipni'] = array();
					}
					foreach($matches['id'] as $id)
					{
						$guids['ipni'][] = $id;
					}
					
				}
	
			}
			
		}
		
		
	}
	return $guids;
}

if (0)
{
// test
$name = 'Cryptops mirus'; // page 5473800
//$name = 'Dendrobates amazonicus';
//$name = 'Anthurium dendrobates'; // IPNI
//$name = 'Absidia caerulea'; // index fungorum

$name = 'Chromis megalopsis'; // Zoobank
$name = 'Chromis woodsi'; // Zoobank, and in BioStor

print_r(find_in_global_names($name));
}

?>

