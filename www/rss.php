<?php

/**
 *
 * @file rss.php
 *
 * RSS feed of recently added references
 *
 */
 
require_once('../db.php');
require_once('../reference.php');

// Conditional get
$sql = 'SELECT `created` FROM rdmp_reference ORDER BY `created` DESC LIMIT 1';
$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

$last_modified = date(DATE_RFC822, strtotime($result->fields['created']));
$etag = '"' . md5($result->fields['created']) . '"';

$send = true;
$headers = getallheaders();
foreach ($headers as $k => $v)
{
	switch(strtolower($k))
	{
		case 'if-modified-since':
			if (strcasecmp($last_modified, $v) == 0)
			{
				$send = false;
			}
			break;

		case 'if-none-match':
			if (strcasecmp($etag, $v) == 0)
			{
				$send = false;
			}
			break;
			
	}
}

if ($send == false)
{
	// RSS feed hasn't been modified since last time client asked for it
	header("HTTP/1.1 304 Not Modified\n\n");
	$_SERVER['REDIRECT_STATUS'] = 304;	
	exit();
}
 
$format = 'atom';

if (isset($_GET['format']))
{
	switch ($_GET['format'])
	{
		case 'atom':
			$format = 'atom';
			break;

		case 'rss1':
			$format = 'rss1';
			break;

		default:
			$format = 'atom';
			break;
	}
}


// Get last 50 references added to database
$sql = 'SELECT * FROM rdmp_reference
ORDER BY `created` DESC
LIMIT 50';

$reference_ids = array();

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$reference_ids[] = $result->fields['reference_id'];
	
	$result->MoveNext();			
}



// Create feed

$feed = new DomDocument('1.0', 'UTF-8');

switch ($format)
{
	case 'atom':
		$rss = $feed->createElement('feed');
		$rss->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
		$rss->setAttribute('xmlns:geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
		$rss->setAttribute('xmlns:georss', 'http://www.georss.org/georss');
		$rss = $feed->appendChild($rss);
		
		// feed
		
		// title
		$title = $feed->createElement('title');
		$title = $rss->appendChild($title);
		$value = $feed->createTextNode($config['site_name']);
		$value = $title->appendChild($value);
		
		// link
		$link = $feed->createElement('link');
		$link->setAttribute('href', $config['web_root']);
		$link = $rss->appendChild($link);
		
		$link = $feed->createElement('link');
		$link->setAttribute('rel', 'self');
		$link->setAttribute('type', 'application/atom+xml');
		$link->setAttribute('href', $config['web_root'] . 'rss.php?format=atom');
		$link = $rss->appendChild($link);
				
		// updated
		$updated = $feed->createElement('updated');
		$updated = $rss->appendChild($updated);
		$value = $feed->createTextNode(date(DATE_ATOM));
		$value = $updated->appendChild($value);
		
		// id
		$id = $feed->createElement('id');
		$id = $rss->appendChild($id);
		$id->appendChild($feed->createTextNode('urn:uuid:' . uuid()));

		// author
		$author = $feed->createElement('author');
		$author = $rss->appendChild($author);
		
		$name = $feed->createElement('name');
		$name = $author->appendChild($name);
		$name->appendChild($feed->createTextNode('BioStor'));
		
		// items
		
		foreach ($reference_ids as $reference_id)
		{
			$ref = db_retrieve_reference($reference_id);
			
			reference_to_atom ($ref, $feed, $rss);
		}
		
		break;
		
	case 'rss1':
		$rss = $feed->createElement('rdf:RDF');
		$rss->setAttribute('xmlns', 'http://purl.org/rss/1.0/');
		$rss->setAttribute('xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
		$rss->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
		$rss->setAttribute('xmlns:prism', 'http://prismstandard.org/namespaces/1.2/basic/');
		$rss->setAttribute('xmlns:geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
		$rss->setAttribute('xmlns:georss', 'http://www.georss.org/georss');
		$rss = $feed->appendChild($rss);

		// channel
		$channel = $feed->createElement('channel');
		$channel->setAttribute('rdf:about', $config['web_root']);
		$channel = $rss->appendChild($channel);
		
		// title
		$title = $channel->appendChild($feed->createElement('title'));
		$title->appendChild($feed->createTextNode($config['site_name']));

		// link
		$link = $channel->appendChild($feed->createElement('link'));
		$link->appendChild($feed->createTextNode($config['web_root']));

		// description
		$description = $channel->appendChild($feed->createElement('description'));
		$description->appendChild($feed->createTextNode($config['site_name']));

		// items
		$items = $channel->appendChild($feed->createElement('items'));
		$seq = $items->appendChild($feed->createElement('rdf:Seq'));
	
		break;
		
	default:
		break;
}

header("Content-type: text/xml;charset=utf-8");
header("Last-modified: $last_modified");
header("ETag: $etag");
echo $feed->saveXML();

?>

