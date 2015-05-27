<?php

/**
 * @file exhibit_author.php
 *
 */

require_once ('../bhl_utilities.php');
require_once ('../lib.php');
require_once ('../reference.php');

/*
    "items" : [
        {   type :                  "Nobelist",
            label :                 "Burton Richter",
            discipline :            "Physics",
            shared :                "yes",
            "last-name" :           "Richter",
            "nobel-year" :          "1976",
            relationship :          "alumni",
            "co-winner" :           "Samuel C.C. Ting",
            "relationship-detail" : "MIT S.B. 1952, Ph.D. 1956",
            imageURL :              "http://nobelprize.org/nobel_prizes/physics/laureates/1976/richter_thumb.jpg"
*/

// Exhibit object
$exhibit = new stdclass;

// Set plurals label
$articleType = new stdclass;
$articleType->pluralLabel = "articles";

$exhibit->types = array();
$exhibit->types['article'] = $articleType;

// Items will hold the references
$exhibit->items = array();

$id = 0;

if (isset($_GET['id']))
{
	$id = $_GET['id'];
}

if ($id != 0)
{
	$refs = db_retrieve_authored_references($id);
	
	$coauthors = db_retrieve_coauthors($id);
	
	
	$c = array();
	foreach($coauthors->coauthors as $co)
	{
		$c[] = $co->id;
	}
	
	$journal_names = array();
	
	foreach($refs as $reference_id)
	{
		global $config;
		
		$reference = db_retrieve_reference ($reference_id);
		
		$item = new stdclass;
		$item->uri = $config['web_root'] . 'reference/' . $reference_id;
		$item->type = $reference->genre;
		$item->label = $reference->title;
		
		// Group journals
		if (!isset($journal_names[$reference->issn]))
		{
			$journal_names[$reference->issn] = $reference->secondary_title;
		}
		$item->journal = $journal_names[$reference->issn];

		$item->citation = reference_authors_to_text_string($reference)
			. ' (' . $reference->year . ') ' . reference_to_citation_text_string($reference);
		$item->year = $reference->year;
		
		if ($reference->PageID != 0)
		{
			$image = bhl_fetch_page_image($reference->PageID);
			$item->imageURL = $image->thumbnail->url;
		}
		else
		{
			// if it's an article we could use journal image
			$item->imageURL = 'http://bioguid.info/issn/image.php?issn=' . $reference->issn;
		}
		
		$item->coauthors = array(); 
		foreach ($reference->authors as $author)
		{
			if (in_array($author->id, $c))
			{
				$item->coauthors[] = $author->forename . ' ' . $author->lastname;
			}
		}
	
		$exhibit->items[] = $item;	
	}
}
echo json_format(json_encode($exhibit));

?>

