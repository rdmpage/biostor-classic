<?php

/**
 * @file rss.php
 *
 */

require_once (dirname(__FILE__) . '/config.inc.php');
require_once (dirname(__FILE__) . '/db.php');


function fetch_item ($ItemID)
{
	global $db;

	$html = get('http://www.biodiversitylibrary.org/item/' . $ItemID);
	
	$Title = 0;
	$VolumeInfo = '';
	
	$done_pages = false;
	
	$lines = explode("\n", $html);
	
	// PageID for this item...
	$SequenceOrder = 1;
	foreach ($lines as $line)
	{
		if (preg_match('/<option selected="selected" value="' . $ItemID . '">(?<VolumeInfo>.*)<\/option>/', $line, $matches))
		{
			$VolumeInfo = $matches['VolumeInfo'];
		}
	
		if (preg_match('/<a href="\/bibliography\/(?<TitleID>\d+)"/', $line, $matches))
		{
			$TitleID = $matches['TitleID'];
		}

		if (preg_match('/<td class="volume"/', $line, $matches))
		{
			$done_pages = true;
		}
		
		if (!$done_pages)
		{
			if (preg_match('/<option(\s+selected="selected")?\s+value="(?<PageID>\d+)">(Page (?<PageNumber>\d+)|(?<PageTypeName>\w+(\s+\w+)?))<\/option>/', $line, $matches))
			{
				
				$keys = array();
				$values = array();
				
				// PageID
				$keys[] = 'PageID';
				$values[] = $matches['PageID'];
				
				// ItemID
				$keys[] = 'ItemID';
				$values[] = $ItemID;
		
				// Is page numbered
				if ($matches['PageNumber'] != '')
				{
					$keys[] = 'PagePrefix';
					$values[] = '"Page"';
		
					$keys[] = 'PageNumber';
					$values[] = '"' . $matches['PageNumber'] . '"';
					
					if (isset($matches['PageTypeName']))
					{
						if ($matches['PageTypeName'] == '')
						{
							$keys[] = 'PageTypeName';
							$values[] = '"Text"';				
						}
					}
				}
		
				if (isset($matches['PageTypeName']))
				{
					if ($matches['PageTypeName'] != '')
					{
						$keys[] = 'PageTypeName';
						$values[] = '"' . $matches['PageTypeName'] . '"';			
					}
				}
				
				
				$sql = 'INSERT IGNORE INTO bhl_page (' . implode (",", $keys) . ') VALUES (' . implode (",", $values) . ');';
				//echo $sql . "\n";
				$sql_result = $db->Execute($sql);
				if ($sql_result == false) die("failed [" . __LINE__ . "]: " . $sql);
				
				
				$sql = 'INSERT IGNORE INTO page (PageID,ItemID,FileNamePrefix,SequenceOrder) VALUES('
				. $matches['PageID']
				. ',' . $ItemID
				. ',' . $matches['PageID'] // Fake this as we need FileNamePrefix to name images in cache
				. ',' . $SequenceOrder++
				.');';	
				//echo $sql . "\n";
				$sql_result = $db->Execute($sql);
				if ($sql_result == false) die("failed [" . __LINE__ . "]: " . $sql);
				
			}
		}
	}

	// Item	
	$sql = "INSERT IGNORE INTO bhl_item(ItemID,TitleID,VolumeInfo) VALUES("
	. $ItemID . ',' . $TitleID . ', "' . $VolumeInfo . '");';
	
	echo $sql . "\n";
	$sql_result = $db->Execute($sql);
	if ($sql_result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	
	// Do we need to add title?
	$sql = 'SELECT * FROM bhl_title WHERE TitleID=' . $TitleID;
	$sql_result = $db->Execute($sql);
	if ($sql_result == false) die("failed [" . __LINE__ . "]: " . $sql);

	if ($sql_result->NumRows() == 0)
	{
		echo "Fetch Title $TitleID\n";
		
		$url = 'http://www.biodiversitylibrary.org/EndNoteDownload.ashx?id=' . $TitleID;
		$endnote = get($url);
		//echo $endnote;
		
		// Extract title...
		$title = '';
		$lines = explode("\n", $endnote);
		$i = 0;
		$n = count($lines);
		while ($i < $n)
		{
			if (preg_match('/^%T\s+(?<title>.*)(\s*\/\s*)?$/Uu', $lines[$i], $m))
			{
				$title = $m['title'];
				$title = preg_replace('/\s*$/Uu', '', $title);
				break;
			}
			$i++;
		}
		
		$sql = "INSERT IGNORE INTO bhl_title(TitleID,FullTitle,ShortTitle) VALUES("
			. $TitleID . ', ' . $db->qstr($title) . ', ' . $db->qstr($title) . ');';
		echo $sql . "\n";
		$sql_result = $db->Execute($sql);
		if ($sql_result == false) die("failed [" . __LINE__ . "]: " . $sql);
			
	}
	
	
	
}


/**
 *
 * @brief Get contents of RSS feed, optionally checking whether it has been modified.
 *
 * We use HTTP conditional GET to check whether feed has been updated, see 
 * http://fishbowl.pastiche.org/2002/10/21/http_conditional_get_for_rss_hackers.
 * ETag and Last Modified header values are stored in a MySQL database.
 * ETag is a double-quoted string sent by the HTTP server, e.g. "2f4511-8b92-44717fa6"
 * (note the string includes the enclosing double quotes). Last Modified is date,
 * written in the form Mon, 22 May 2006 09:08:54 GMT.
 *
 * @param url Feed URL
 * @param rss Return RSS feed in this variable
 *
 * @return 0 if feed exists and is modified, otherwise an HTTP code or an error
 * code.
 *
 */
function GetRSS ($url, &$rss, $check = false)
{
	global $config;
	global $db;
	
	$sql = 'SELECT last_modified, etag FROM feed WHERE (url = "' . $url . '")';

	$sql_result = $db->Execute($sql);
	if ($sql_result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	$ETag = '';
	$LastModified = '';
	if ($sql_result->RecordCount() == 0)
	{
		$sql = 'INSERT feed (url) VALUES(' . $db->qstr($url) . ')';
		$sql_result = $db->Execute($sql);
		if ($sql_result == false) die("failed [" . __LINE__ . "]: " . $sql);
	}
	else
	{
		$ETag = $sql_result->fields['etag'];
		$LastModified = $sql_result->fields['last_modified'];
	}
	
	// Construct conditional GET header
	$if_header = array();
	
	if ($check)
	{
		if ($LastModified != "''")
		{
			array_push ($if_header, 'If-Modified-Since: ' . $LastModified);
		}
		
		// Only add this header if server returned an ETag value, otherwise
		// Connotea doesn't play nice.
		if ($ETag != "''")
		{
			array_push ($if_header,'If-None-Match: ' . $ETag);
		}
	}
	
	//print_r($if_header);
	 

	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_HEADER,		  1); 
//	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,	1); 
	
	if ($check)
	{
		curl_setopt ($ch, CURLOPT_HTTPHEADER,	  $if_header); 
	}
	
	if ($config['proxy_name'] != '')
	{
		curl_setopt ($ch, CURLOPT_PROXY, $config['proxy_name'] . ":" . $config['proxy_port']);
	}
			
	$curl_result = curl_exec ($ch); 
	
	if(curl_errno ($ch) != 0 )
	{
		// Problems with CURL
		$result = curl_errno ($ch);
	}
	else
	{
		 $info = curl_getinfo($ch);
		 
		 //print_r($info);
		 
		 
		 $header = substr($curl_result, 0, $info['header_size']);
		 
		 $result = $info['http_code'];
		 
		//echo $header;

		if ($result == 200)
		{
			// HTTP 200 means the feed exists and has been modified since we 
			// last visited (or this is the first time we've looked at it)
			// so we grab it, remembering to trim off the header. We store
			// details of the feed in our database.
			$result = 0;
			
			$rss = substr ($curl_result, $info['header_size']);


			if ($check)
			{
				// Retrieve ETag and LastModified
				$rows = split ("\n", $header);
				foreach ($rows as $row)
				{
					$parts = split (":", $row, 2);
					if (count($parts) == 2)
					{
						if (preg_match("/ETag/", $parts[0]))
						{
							$ETag = $parts[1];
						}
						
						if (preg_match("/Last-Modified/", $parts[0]))
						{
							$LastModified = $parts[1];
						}
						
					}
				}
				
				// Store in database
				$sql = 'UPDATE feed SET last_modified=' . $db->qstr($LastModified) . ', etag=' . $db->qstr($ETag) 
					. ' WHERE (url = "' . $url . '")';
	
				$sql_result = $db->Execute($sql);
				if ($sql_result == false) die("failed [" . __LINE__ . "]: " . $sql);
			}

		}
		 
	}
	return $result;
}

if (1)
{

// test


//	$url = "http://localhost/~rpage/ants/rss/Formicidae.rss";
//	$url = 'http://www.connotea.org/rss/tag/phylogeny';
	$url = 'http://names.ubio.org/rss/rss_feed.php?username=rdmpage&rss1=1';
	$url = 'http://www.biodiversitylibrary.org/RecentRss/100';
	
	$rss = '';
	$msg = '';

	$result = GetRSS ($url, &$rss, true);
	if ($result == 0)
	{
		//echo $rss;
		
		// Get item ids...
		$dom = new DOMDocument;
		$dom->loadXML($rss);
		$xpath = new DOMXPath($dom);
		
				
		$nodes = $xpath->query("//item/link");
		foreach($nodes as $node)
		{
			$ItemUrl = $node->firstChild->nodeValue;
			
			//echo $ItemUrl . "\n";
			
			$ItemID = str_replace('http://www.biodiversitylibrary.org/item/', '', $ItemUrl);
			
			// Do we have this already?
			$sql = 'SELECT * FROM bhl_item WHERE ItemID=' . $ItemID;
			//echo $sql . "\n";
			
			$sql_result = $db->Execute($sql);
			if ($sql_result == false) die("failed [" . __LINE__ . "]: " . $sql);

			if ($sql_result->NumRows() == 0)
			{
				//echo "Fetch item $ItemID\n";
				fetch_item($ItemID);
			}
		}
			
		
		if ($result == 0) 
		{ 
			$msg = 'OK';
		}
	}
	else
	{
		switch ($result)
		{
			case 304: $msg = 'Feed has not changed since last fetch (' . $result . ')'; break;
			default: $msg = 'Badness happened (' . $result . ')'; break;
		}	
	}

	echo $msg;

}
?>