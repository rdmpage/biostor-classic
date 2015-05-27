<?php

require_once (dirname(dirname(__FILE__)) . '/db.php');
require_once (dirname(dirname(__FILE__)) . '/reference.php');


$image_width = 800;
$thumbnail_width = 100;

$jatsdir = dirname(__FILE__) . '/jats';


// Generate JATS XML and associted files in a package we can use to generate nice PDFs, etc.

//--------------------------------------------------------------------------------------------------
function jats_xml($reference)
{
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
	
	$supplementary_material = $body->appendChild($doc->createElement('supplementary-material'));
	$supplementary_material->setAttribute('content-type', 'scanned-pages');
	
	$n = count($reference->bhl_pages);
	for($i = 0; $i < $n; $i++)
	{
		$graphic = $supplementary_material->appendChild($doc->createElement('graphic'));
		$graphic->setAttribute('xlink:href', 'bw_images/' . $reference->bhl_pages[$i] . '.png');
		$graphic->setAttribute('xlink:role', $reference->bhl_pages[$i]);
		$graphic->setAttribute('xlink:title', 'scanned-page');
	}
	
	return $doc->saveXML();
}


//--------------------------------------------------------------------------------------------------
function write_jats($reference)
{
	global $config;
	global $jatsdir;
	global $image_width;
	global $thumbnail_width;
	
	$basedir = $jatsdir . '/' . $reference->reference_id;
	
	// Ensure cache subfolder exists for this item
	if (!file_exists($basedir))
	{
		$oldumask = umask(0); 
		mkdir($basedir, 0777);
		umask($oldumask);
	}
	
	$subdirectories_names = array('abbyy', 'bw_images', 'djvu', 'html', 'images');
	
	foreach ($subdirectories_names as $dir)
	{
		$subdir = $basedir . '/' . $dir;
		
		if (!file_exists($subdir))
		{
			$oldumask = umask(0); 
			mkdir($subdir, 0777);
			umask($oldumask);
		}
		
		$subdirectories_names[$dir] = $subdir;
	}
	
	// Readme's
	$text = 
"Article
=======

Biostor article " . $reference->reference_id . " in JATS format with page scans.
";

	file_put_contents($basedir . '/' . 'README.md', $text);
	
	
	
	// Dump stuff we need...
	
	// Metadata
	$xml = jats_xml($reference);
	$xml_filename = $basedir . '/' . $reference->reference_id . '.xml';
	file_put_contents ($xml_filename, $xml);
	
	// Images and XML
	$file_details = bhl_file_from_pageid ($reference->bhl_pages[0]);
	if ($file_details)
	{
		$reference->bhl_ItemID = $file_details->ItemID;
		$reference->bhl_prefix = $file_details->prefix;
	}
	
	print_r($reference);
	
	// Where the DjVu and image files live 
	$cache_namespace = $config['cache_dir']. "/" . $reference->bhl_ItemID;

	$djvu_filename = $cache_namespace . '/' . $reference->bhl_prefix . '.djvu';
	
	$djvu_xml_dir = $basedir . '/djvu';
	
	$text = 
"DjVu files
==========

DjVu files for each page and XML extracted each file.
";

	file_put_contents($djvu_xml_dir . '/' . 'README.md', $text);
	
	$bw_image_dir =  $basedir . '/bw_images';

	$text = 
"Black and white page images
===========================

Black and white page images for each page, extracted from DjVu file in foreground mode. 

Images were extracted in TIFF format, converted to PNG (width $image_width pixels), and GIF thumbnails
($thumbnail_width pixels wide) created.
";

	file_put_contents($bw_image_dir . '/' . 'README.md', $text);
	
	
	$image_dir =  $basedir . '/images';

	$text = 
"Page images
===========

Images for each page, extracted from DjVu file. 

Images were extracted in TIFF format, converted to JPEG (width $image_width pixels), and GIF thumbnails
($thumbnail_width pixels wide) created.
";

	file_put_contents($image_dir . '/' . 'README.md', $text);
	
	
	
	foreach ($reference->bhl_pages as $PageID)
	{
		$sequence_order = bhl_sequence_order_from_pageid($PageID);
		
		$djvu_page_filename = $djvu_xml_dir . '/' . $PageID . '.djvu';
	
		// XML
		// a) Extract individual DjVu pages
		$command = 'djvused ' . $djvu_filename . ' -e "select ' . $sequence_order 
			. ';save-page ' . $djvu_page_filename . '"';
		//echo $command . "\n";
		system($command, $return_var);
		//echo $return_var . "\n";
			
		
		// b) convert to XML
		$command = 'djvutoxml ' .$djvu_page_filename . ' ' . $djvu_xml_dir . '/' . $PageID . '.xml';
		echo $command . "\n";
		system($command, $return_var);
		echo $return_var . "\n";
		
		// c) B & W images (foreground mode)
		$tiff_filename = $bw_image_dir . '/' . $PageID . '.tiff';
	
		$command = "ddjvu -format=tiff -page=" . $sequence_order;
		$command .= " -mode=foreground ";
		$command .= " -size=" . $image_width . "x2000 " . $djvu_page_filename  . " " . $tiff_filename;
		echo $command . "\n";
		system($command, $return_var);
		echo $return_var . "\n";
		
		// Convert to PNGs to save space
		$png_filename = $bw_image_dir . '/' . $PageID . '.png';
						
		$command = "convert";			
		$command .= " -type Grayscale  -depth 8 ";

		$command .= $tiff_filename . "  " . $png_filename;  	
		echo $command . "\n";
		system($command, $return_var);
								
		// thumbnails 
		$gif_filename = $bw_image_dir . '/' . $PageID . '.gif';
		$command = "convert -thumbnail " . $thumbnail_width . ' ' . $png_filename . ' ' . $gif_filename;
		echo $command . "\n";
		system($command, $return_var);
		//echo $return_var . "\n";			

		// Clean up
		// Delete TIFF
		unlink ($tiff_filename);
	
	
		// d) Original images
		$tiff_filename = $image_dir . '/' . $PageID . '.tiff';
	
		$command = "ddjvu -format=tiff -page=" . $sequence_order;
		$command .= " -size=" . $image_width . "x2000 " . $djvu_page_filename  . " " . $tiff_filename;
		echo $command . "\n";
		system($command, $return_var);
		echo $return_var . "\n";
		
		// Convert to JPEG to save space
		$jpeg_filename = $image_dir . '/' . $PageID . '.jpeg';
						
		$command = "convert ";			

		$command .= $tiff_filename . "  " . $jpeg_filename;  	
		echo $command . "\n";
		system($command, $return_var);
								
		// thumbnails 
		$gif_filename = $image_dir . '/' . $PageID . '.gif';
		$command = "convert -thumbnail " . $thumbnail_width . ' ' . $jpeg_filename . ' ' . $gif_filename;
		echo $command . "\n";
		system($command, $return_var);
		//echo $return_var . "\n";			
		
		// Clean up
		// Delete TIFF
		unlink ($tiff_filename);
	}
	
	
	// ABBYY
	$abbyy_filename = $cache_namespace . '/' . $reference->bhl_prefix . '_abbyy';
	
	$abbyy_xml_dir = $basedir . '/abbyy';
	
	$text = 
"ABBYY files
===========

ABBYY XML files for each page.
";

	file_put_contents($abbyy_xml_dir . '/' . 'README.md', $text);
	

	// ABBYY XML is one giant file, we need to go through and get matching pages
	
	$page_set = array(); // list of pages in the order in which they occur in the file
	foreach ($reference->bhl_pages as $PageID)
	{
		$sequence_order = bhl_sequence_order_from_pageid($PageID);
		$page_set[$sequence_order] = $PageID;
	}
	
	if (file_exists($abbyy_filename))
	{
		// scan file
		$xml = '';
		$in_page = false;
		$page_counter = 0;
		$pages_found = 0;
		$num_pages = count($page_set);
		
		$file_handle = fopen($abbyy_filename, "r");
		while (!feof($file_handle) && ($pages_found < $num_pages)) 
		{
			$line = fgets($file_handle);
			
			if (preg_match('/<page width="(?<w>\d+)" height="(?<h>\d+)"/', $line))
			{
				$in_page = true;
			}
			
			
			if ($in_page)
			{
				$xml .= $line;
			}

			// We've got one page, if it matches one we need, output it
			if (preg_match('/<\/page>/', $line))
			{
				if (isset($page_set[$page_counter]))
				{
					echo $page_counter . " " . $page_set[$page_counter] . "\n";
				
					$abbyy_page_filename = $abbyy_xml_dir . '/' . $page_set[$page_counter] . '.xml';
					file_put_contents($abbyy_page_filename, $xml);
					$pages_found++;
				}
				
				$page_counter++;
				$in_page = false;
				$xml = '';
				
			}
		}
	}
	
	// Package up
	$command = "tar cvf " . $jatsdir . '/' . $reference->reference_id . ".tar.gz -C " . $jatsdir . ' ' . $reference->reference_id;
	echo $command . "\n";
	system($command, $return_var);
	echo $return_var . "\n";
	
	
}






$reference_id = 80825;

$reference_id = 80870; // Case 3407 Drosophila Fallén, 1823 (Insecta, Diptera): proposed conservation of usage

$reference_id = 65746;

$reference_id = 74881; // Pinnotherids (Crustacea, Decapoda) And Leptonaceans (Mollusca, Bivalvia) Associated With Sipunculan Worms In Hong kong

$reference_id = 65706; // Two new species of Eleutherodactylus (Amphibia: Anura: Leptodactylidae) from Bolivia

//$reference_id = 74837;

//$reference_id = 132698;

$reference_id = 132734;

$reference_id = 50335;

$reference_id = 114608; // Shearogovea, a New Genus of Cyphophthalmi (Arachnida, Opiliones) of Uncertain Position from Oaxacan Caves, Mexico

$reference_id = 65767; // fail

//$reference_id = 81137;

//$reference_id = 113526;

$reference_id = 50321;

$reference_id = 132739;

$reference_id = 51774;

$reference = db_retrieve_reference ($reference_id);



// Get PageIDs
$reference->bhl_pages = array();
$pages = bhl_retrieve_reference_pages($reference_id);
foreach ($pages as $page)
{
	$reference->bhl_pages[] = (Integer)$page->PageID;
}

print_r($reference);
//exit();

// store
write_jats($reference);



?>