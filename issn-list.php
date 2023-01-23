<?php

/**
 * @file rss.php
 *
 */

require_once (dirname(__FILE__) . '/config.inc.php');
require_once (dirname(__FILE__) . '/db.php');

//----------------------------------------------------------------------------------------
function get_wiki($url, $user_agent='', $content_type = '')
{	
	$data = null;

	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE,
	  
		CURLOPT_SSL_VERIFYHOST=> FALSE,
		CURLOPT_SSL_VERIFYPEER=> FALSE,
	  
	);

	if ($content_type != '')
	{
		
		$opts[CURLOPT_HTTPHEADER] = array(
			"Accept: " . $content_type, 
			"User-agent: Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405" 
		);
		
	}
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	return $data;
}

//----------------------------------------------------------------------------------------
// Do we have a journal with this ISSN?
function wikidata_item_from_issn($issn)
{

	$item = '';
	
	if (isset($cached_issn[$issn]))
	{
		$item = $cached_issn[$issn];
	}
	else
	{
	
		$sparql = 'SELECT * WHERE { ?work wdt:P236 "' . strtoupper($issn) . '" }';
	
		$url = 'https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=' . urlencode($sparql);
		$json = get_wiki($url, '', 'application/json');
	
		if ($json != '')
		{
			$obj = json_decode($json);
			if (isset($obj->results->bindings))
			{
				if (count($obj->results->bindings) != 0)	
				{
					$item = $obj->results->bindings[0]->work->value;
					$item = preg_replace('/https?:\/\/www.wikidata.org\/entity\//', '', $item);
				}
			}
		}
	}
		
	return $item;
}


//----------------------------------------------------------------------------------------// Do we have a journal with this ISSN?
function wikipedia_from_wikidata($item)
{
	$wikipedia = '';

	$sparql = 'SELECT ?article WHERE {
    ?article schema:about wd:' . $item . '.
    ?article schema:isPartOf <https://en.wikipedia.org/>.

    SERVICE wikibase:label {
       bd:serviceParam wikibase:language "en"
    }
}';

	$url = 'https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=' . urlencode($sparql);
	$json = get_wiki($url, '', 'application/json');
	
	//echo $json;

	if ($json != '')
	{
		$obj = json_decode($json);
		if (isset($obj->results->bindings))
		{
			if (count($obj->results->bindings) != 0)	
			{
				$wikipedia = $obj->results->bindings[0]->article->value;
				$wikipedia = preg_replace('/https:\/\/en.wikipedia.org\/wiki\//', '', $wikipedia);
			}
		}
	}
		
	return $wikipedia;
}

//----------------------------------------------------------------------------------------// Do we have a journal with this ISSN?
function dbpedia_from_wikipedia($wikipedia)
{
	$text = array();
	
	$dbpedia = 'http://dbpedia.org/resource/' . $wikipedia;
	
	
	$url = 'http://dbpedia.org/sparql?default-graph-uri=http://dbpedia.org&query='
		. urlencode('DESCRIBE <' . $dbpedia . '>')
		. '&format=application/json-ld';

	$json = get_wiki($url);
	
	$obj = json_decode($json);
	
	//print_r($obj);
	
	$dbpedia = str_replace('%27', "'", $dbpedia);
	
	if ($obj)
	{	
		if (isset($obj->{$dbpedia}->{'http://www.w3.org/2000/01/rdf-schema#comment'}))
		{
			foreach ($obj->{$dbpedia}->{'http://www.w3.org/2000/01/rdf-schema#comment'} as $comment)
			{
				$string = new stdclass;
				$string->{'@language'} = $comment->lang;
				$string->{'@value'} = $comment->value;
		
				$text[] = $string;
			}
		}
	}
	
	return $text;
}


//----------------------------------------------------------------------------------------


$journals = array();

$letters = array();

$sql = 'select  secondary_title, issn from rdmp_reference 
where issn is not null and issn <> "" and PageID IS NOT NULL and PageID <> "" and PageID <> 0
group by issn, secondary_title';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{	
	if (!isset($journals[$result->fields['issn']]))
	{
		$journals[$result->fields['issn']] = array();		
	}
	
	$journals[$result->fields['issn']][] =  $result->fields['secondary_title'];

	$result->MoveNext();		
}

//print_r($journals);

foreach ($journals as $issn => $names)
{
	$filename = 'about/' . $issn . '.json';
	
	if (!file_exists($filename))
	{
		$obj = new stdclass;
		$obj->{'@context'} = "http://schema.org/";
		$obj->{'@type'} = "Periodical";
	
		$obj->{'@id'} = "https://biostor.org/issn/" . $issn;	
	
		$obj->issn = array($issn);
		$obj->sameAs = array('http://issn.org/resource/ISSN/' . $issn);
	
		$obj->name = $names[0];
	
		$item = wikidata_item_from_issn($issn);
		
		if ($item != '')
		{
			$obj->sameAs[] = 'http://www.wikidata.org/entity/' . $item;
	
			$wikipedia = wikipedia_from_wikidata($item);
		
			if ($wikipedia != '')
			{
				$obj->sameAs[] = 'https://en.wikipedia.org/wiki/' . $wikipedia;
				$text = dbpedia_from_wikipedia($wikipedia);
			
				$obj->description = $text;
				$obj->name = str_replace('_', ' ', $wikipedia);
			}
		}
	
		file_put_contents($filename, json_encode($obj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	}
}

print_r($letters);



?>
