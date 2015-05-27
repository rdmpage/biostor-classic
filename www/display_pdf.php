<?php

/**
 * @file display_pdf.php
 *
 */

require_once ('../bhl_names.php');
require_once ('../bhl_utilities.php');
require_once ('../fpdf16/fpdf.php');


//--------------------------------------------------------------------------------------------------
// Get names in reference to use as tags for document
function pdf_tags($reference_id, $num_tags = 5)
{
	$tags = array();
	/*
	$names = bhl_names_in_reference($reference_id);
	$name_count = array();
	foreach ($names->names as $n)
	{
		$name_count[$n['namestring']] = $n['count'];
	}
	array_multisort($name_count, SORT_DESC, SORT_NUMERIC, $name_count);
	$t = array_slice($name_count, 0, $num_tags);
	
	
	foreach ($t as $k => $v)
	{
		$tags[] = $k;
	}
	sort($tags);
	*/
	return $tags;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Test whether PDF file already exists for a reference
 *
 * We store PDFs in files with md5-based name and directory structure, based on
 * http://stackoverflow.com/questions/247678/how-does-mediawiki-compose-the-image-paths
 *
 * If PDF for reference does not exist, we create the directory structure where the file will
 * reside.
 *
 * @param reference_id Reference id
 * @param pdf_filename On return this parameter has the full file system path to the PDF
 *
 * @return True if PDF file exists, false otherwise
 */
function pdf_file_exists($reference_id, &$pdf_filename)
{
	global $config;
	$exists = false;
	
	$uri = $config['web_root'] . 'reference/' . $reference_id . '.pdf';
	
	$id = md5($uri);
	
	preg_match('/^(..)(..)(..)/', $id, $matches);
	
	// Do we have PDF already?
	$cache_namespace = $config['cache_dir']. '/pdf';
	
	$pdf_path = $cache_namespace . '/' . $matches[1] . '/' . $matches[2] . '/' . $matches[3];
	$pdf_filename = $pdf_path . '/' . $id . '.pdf';
	
	// If we dont have file, create directory structure for it	
	if (!file_exists($pdf_filename))
	{
		$path = $cache_namespace;
		if (!file_exists($path))
		{
			$oldumask = umask(0); 
			mkdir($path, 0777);
			umask($oldumask);
		}
		$path .= '/' . $matches[1];
		if (!file_exists($path))
		{
			$oldumask = umask(0); 
			mkdir($path, 0777);
			umask($oldumask);
		}
		$path .= '/' . $matches[2];
		if (!file_exists($path))
		{
			$oldumask = umask(0); 
			mkdir($path, 0777);
			umask($oldumask);
		}
		$path .= '/' . $matches[3];
		if (!file_exists($path))
		{
			$oldumask = umask(0); 
			mkdir($path, 0777);
			umask($oldumask);
		}
	}
	else
	{
		$exists = true;
	}

	return $exists;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Display PDF for a reference
 *
 * @param reference_id Reference id
 * @param pdf_filename Full path of PDF file to display
 */
function pdf_get($reference_id, $refresh = false)
{
	global $config;
	
	$pdf_filename = '';
	if (!pdf_file_exists($reference_id, $pdf_filename) || $refresh)
	{
		pdf_create ($reference_id, $pdf_filename);
	}
	
	if (1)
	{
		// Redirect browser to PDF
		$pdf_url = $pdf_filename;
		$pdf_url = str_replace($config['web_dir'] , '', $pdf_url);
				
		header('Location: ' . $pdf_url . "\n\n");		
	}
	else
	{
		// Read PDF into memory and display in browser, this will fail if PDF exceeds memory allocated
		// to PHP
		$file = @fopen($pdf_filename, "r") or die("could't open file --\"$pdf_filename\"");
		$pdf = fread($file, filesize($pdf_filename));
		fclose($file);
	
		header('Content-type: application/pdf');
		echo $pdf;	
	}
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Create PDF for a reference
 *
 * We create a simple cover page for the PDF. This page contains basic bibliographic metadata
 * for the PDF, in order for Mendeley to process the PDF correctly. In response to my discovery
 * that Mendeley doesn't accept all XMP (Ticket#2010040110000015) support@mendeley.com replied that
 * they have some heuristic tests to see if the metadata is valid, such as whether the information 
 * about the title and authors occurs on the first of the PDF.
 *
 *
 * @param reference_id Reference id
 * @param pdf_filename Full path of PDF file to create
 *
 */
function pdf_create($reference_id, $pdf_filename)
{
	global $config;
	
	// Get reference
	$reference = db_retrieve_reference($reference_id);

	// Get tags
	$tags = pdf_tags($reference->reference_id, 10);
	
	// Paper size
	// A4 = 210 x 297
	$paper_width 	= 210; // mm
	$paper_height 	= 297; // mm
	$margin 		= 10;
	
	//----------------------------------------------------------------------------------------------
	// PDF
	$pdf=new FPDF('P', 'mm', 'A4');
	//$pdf = PDF_Rotate('P', 'mm', 'A4');
	
	//----------------------------------------------------------------------------------------------
	// Basic metadata (e.g., that displayed by Mac OS X Preview)
	$pdf->SetTitle($reference->title, true); // true means use UTF-8
	$pdf->SetAuthor(reference_authors_to_text_string($reference), true); // true means use UTF-8
	if (count($tags) > 0)
	{
		$pdf->SetKeywords(join(", ", $tags), true);
	}	
	
	//----------------------------------------------------------------------------------------------
	// Cover page (partly to ensure Mendeley accepts XMP metadata)
	$pdf->AddPage();
	
	// Title
	$pdf->SetFont('Arial','',24);
	$pdf->SetXY($margin, $margin);
	$pdf->Write(10,utf8_decode($reference->title));
	
	// Authors
	$y = $pdf->GetY();
	$pdf->SetXY($margin, $y+16);
	$pdf->SetFont('Arial','B',16);
	$pdf->Write(6,utf8_decode(reference_authors_to_text_string($reference)));
	
	// Citation
	$y = $pdf->GetY();
	$pdf->SetXY($margin, $y+10);
	$pdf->SetFont('Arial','I',12);
	$pdf->Write(6, utf8_decode($reference->secondary_title));
	$pdf->SetFont('Arial','',12);
	$pdf->Write(6,' ' . $reference->volume);
	if (isset($reference->issue))
	{
		$pdf->Write(6,'(' . $reference->issue . ')');
	}
	$pdf->Write(6,':' . $reference->spage);
	if (isset($reference->epage))
	{
		$pdf->Write(6,'-' . $reference->epage);
	}
	$pdf->Write(6,' (' . $reference->year . ')');

	// URL
	$url = $config['web_root'] . 'reference/' . $reference->reference_id;

	$pdf->Write(6,' ');
	$pdf->SetTextColor(0,0,255);
	$pdf->SetFont('','U');
	$pdf->Write(6, $url, $url);
	$pdf->SetFont('','');
	$pdf->SetTextColor(0,0,0);

	//----------------------------------------------------------------------------------------------
	// If we add taxon names as keywords
	if (count($tags) > 0)
	{
		$keywords = "Keywords: " . join("; ", $tags);

		$y = $pdf->GetY();
		$pdf->SetXY($margin, $y+10);
		$pdf->Write(6, $keywords);
	}
	
	//----------------------------------------------------------------------------------------------
	// Footer giving credit to BHL	
	$y = $paper_height;
	$y -= $margin;
	$y -= 40;
	$pdf->Image($config['web_dir'] . '/images/cc/cc.png', 10, $y, 10);
	$pdf->Image($config['web_dir'] . '/images/cc/by.png', 20, $y, 10);
	$pdf->Image($config['web_dir'] . '/images/cc/nc.png', 30, $y, 10);
	
	$pdf->SetXY(10,$y+10);
	$pdf->SetFont('Arial','',10);
	$pdf->SetTextColor(0,0,0);
	$pdf->Write(6,'Page images from the Biodiversity Heritage Library, ');
	
	//Then put a blue underlined link
	$pdf->SetTextColor(0,0,255);
	$pdf->SetFont('','U');
	$pdf->Write(6,'http://www.biodiversitylibrary.org/','http://www.biodiversitylibrary.org/');
	$pdf->SetFont('','');
	$pdf->SetTextColor(0,0,0);
	$pdf->Write(6,', made available under a Creative Commons Attribution-Noncommercial License ');
	$pdf->SetTextColor(0,0,255);
	$pdf->SetFont('','U');
	$pdf->Write(6,'http://creativecommons.org/licenses/by-nc/2.5/','http://creativecommons.org/licenses/by-nc/2.5/');
	
	//----------------------------------------------------------------------------------------------
	// Add BHL page scans
	$pages = bhl_retrieve_reference_pages($reference_id);
	
	foreach ($pages as $page)
	{	
		$image = bhl_fetch_page_image($page->PageID);
		
		$page_width  = $paper_width;
		$page_height = $paper_height;
		
		$page_width -= (2 * $margin);
		$page_height -= (2 * $margin);
				
		// Fit to page 
		$img_height = $image->height;
		$img_width = $image->width;
		
		$w_scale = $page_width/$img_width;
		$h_scale = $page_height/$img_height;
		
		$scale = min($w_scale, $h_scale);
		$img_height *= $scale;
		$img_width *= $scale;

		$pdf->AddPage();
		$x_offset = ($paper_width - $img_width)/2.0;
		$y_offset = ($paper_height - $img_height)/2.0;
		$pdf->Image($image->file_name, $x_offset, $y_offset, $img_width);
		
	}
	
	$pdf->Output($pdf_filename, 'F');
	
	pdf_add_xmp ($reference, $pdf_filename, $tags);

}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Inject XMP metadata into PDF
 *
 * We inject XMP metadata using Exiftools
 *
 * @param reference Reference 
 * @param pdf_filename Full path of PDF file to process
 * @param tags Tags to add to PDF
 *
 */
function pdf_add_xmp ($reference, $pdf_filename, $tags = array())
{
	global $config;
	
	$url = $config['web_root'] . 'reference/' . $reference->reference_id;

	// URL
	$command = "exiftool" .  " -XMP:URL=" . escapeshellarg($url) . " " . $pdf_filename;	
	system($command);

	$command = "exiftool" .  " -XMP:Identifier=" . escapeshellarg($url) . " " . $pdf_filename;
	system($command);
	
	// Mendeley will overwrite XMP-derived metadata with CrossRef metadata if we include this
	/*if (isset($reference->doi))
	{
		$command = "exiftool" .  " -XMP:DOI=" . escapeshellarg($reference->doi) . " " . $pdf_filename;
		system($command);
	}
	*/
	
	// Title and authors
	$command = "exiftool" .  " -XMP:Title=" . escapeshellarg($reference->title) . " " . $pdf_filename;
	system($command);
	
	foreach ($reference->authors as $a)
	{
		$creator = trim($a->forename . ' ' . $a->lastname);
		$command = "exiftool" .  " -XMP:Creator+=" . escapeshellarg($creator) . " " . $pdf_filename;
		system($command);
	}
	
	// Article
	if ($reference->genre == 'article')
	{
		$command = "exiftool" .  " -XMP:AggregationType=journal " . $pdf_filename;
		system($command);
		$command = "exiftool" .  " -XMP:PublicationName=" . escapeshellarg($reference->secondary_title) . " " . $pdf_filename;
		system($command);
		
		if (isset($reference->issn))
		{
			$command = "exiftool" .  " -XMP:ISSN=" . escapeshellarg($reference->issn) . " " . $pdf_filename;
			system($command);
		}
				
		$command = "exiftool" .  " -XMP:Volume=" . escapeshellarg($reference->volume) . " " . $pdf_filename;
		system($command);
		if (isset($reference->issue))
		{
			$command = "exiftool" .  " -XMP:Number=" . escapeshellarg($reference->issue) . " " . $pdf_filename;
			system($command);
		}
		$command = "exiftool" .  " -XMP:StartingPage=" . escapeshellarg($reference->spage) . " " . $pdf_filename;
		system($command);
		if (isset($reference->epage))
		{
			$command = "exiftool" .  " -XMP:EndingPage=" . escapeshellarg($reference->epage) . " " . $pdf_filename;
			system($command);
			$command = "exiftool" .  " -XMP:PageRange+=" . escapeshellarg($reference->spage. '-' . $reference->epage) . " " . $pdf_filename;
			system($command);
		}
	}
	
	$command = "exiftool" .  " -XMP:CoverDate=" . escapeshellarg(str_replace("-", ":", $reference->date)) . " " . $pdf_filename;
	system($command);
	$command = "exiftool" .  " -XMP:Date=" . escapeshellarg(str_replace("-", ":", $reference->date)) . " " . $pdf_filename;
	system($command);
	
	// Tags (Papers reads these)
	if (count($tags) > 0)
	{
		foreach ($tags as $tag)
		{
			$command = "exiftool" .  " -XMP:Subject+=" . escapeshellarg($tag)  . " " . $pdf_filename;
			system($command);
		}
	}
}	

// test

/*
$reference_id = 188;
$reference_id = 443;
$reference_id = 420;
$reference_id = 522;
$reference_id = 565;
$reference_id = 566;

$reference_id = 207;

$reference_id = 420;


pdf_get($reference_id);
*/

?>
