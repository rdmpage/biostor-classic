<?php

/**
 * @file bhl_viewer.php
 *
 * Javascript viewer for BHL content
 *
 */

require_once(dirname(__FILE__) . '/bhl_utilities.php');


//--------------------------------------------------------------------------------------------------
/**
 * @brief Display a set of BHL pages for a reference
 *
 * @param reference_id Primary key of reference in local database
 * @page_to_display Specific page to display (default is 0, which means display first page in reference)
 *
 * @return HTML for viewer
 */
function bhl_reference_viewer($reference_id, $page_to_display = 0)
{
	$pages = bhl_retrieve_reference_pages($reference_id);
	
	//print_r($pages);
	echo bhl_viewer($pages, $page_to_display);
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Display a set of BHL pages for a BHL item
 *
 * @param ItemID BHL item
 * @page_to_display Specific page to display (default is 0, which means display first page in Item)
 *
 * @return HTML for viewer
 */
function bhl_item_viewer($ItemID, $page_to_display = 0)
{
	$pages = bhl_retrieve_item_pages($ItemID);
	echo bhl_viewer($pages, $page_to_display);
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Display a set of BHL pages
 *
 * @param pages Array of pages
 *
 * @return HTML for viewer
 */
function bhl_viewer ($pages, $page_to_display = 0)
{
	global $config;
	
	$html = '';
	
	// If we don't have a page range then we can't display article
	if (count($pages) == 0)
	{
		$html = '<p>Can not display article as we don\'t have a page range (missing epage?)</p>';
		return $html;	
	}
	
	$viewer_height = 600;
	$viewer_width = 600;
	
	// The viewer
	$html .= '<div style="position:relative;height:' . $viewer_height . 'px;width:' 
	. $viewer_width . 'px;border: 1px solid rgb(128,128,128);">'; // background-color:rgb(146,146,146)">';
	
	// The page itself
	$image = bhl_fetch_page_image($pages[0]->PageID);
		
	// Fit to viewing rectangle 
	$img_height = $image->height;
	$img_width = $image->width;
	
	if ($img_height > $img_width)
	{
		// Portrait mode
		if ($img_height > $viewer_height)
		{
			$scale =  $viewer_height/$img_height;
			$img_height *= $scale;
			$img_width *= $scale;
		}
	}
	else
	{
		// Landscape
		if ($img_width > $viewer_width)
		{
			$scale =  $viewer_width/$img_width;
			$img_height *= $scale;
			$img_width *= $scale;			
		}
	}
	// Inset to enable padding
	$img_width -= 20;
	
	
	$html .=  "<!-- Page -->\n";
	$html .=  '<div id="page">' . "\n";
	$html .=  '<img id="page_image"  src="' . $image->thumbnail->url . '" width="' . $img_width . '" />' . "\n";
	$html .=  '</div>' . "\n";
	
	$html .=  "<!-- Page thumbnails -->\n";
	$html .=  '<div id="thumbnail_list" >';
	foreach ($pages as $page)
	{
		$image = bhl_fetch_page_image($page->PageID);
	
		$html .=  "<!-- Thumbnail -->\n";
		$html .=  '<div class="thumbnail_item" id="thumbnail_' . $page->PageID . '" onclick="show_page(\'' . $image->url . '\', \'' . $image->thumbnail->url . '\', \'' . $page->PageID . '\');">' . "\n";
		$html .=  '<a name="' . $page->PageID . '"></a>' . "\n";
		$html .=  '<img class="thumbnail" id="thumbnail_image_' . $page->PageID . '" src="' . $image->thumbnail->url . '" width="' . $image->thumbnail->width . '" height="' . $image->thumbnail->height . '"/>';
		
		$html .= "<!-- Page info -->\n";
		$html .= '<span class="thumbnail_page_info" >';
		if ($page->PagePrefix == '')
		{
			$html .= '[' . $page->page_order . ']';
		}
		else
		{
			$html .= $page->PagePrefix . ' ' . $page->PageNumber;
		}
		$html .=  '</span>' . "\n";
		
		$html .=  '</div>' . "\n";		
	}
	$html .=  '</div>' . "\n";
	
	$html .=  '</div>' . "\n";
		
	if ($page_to_display == 0)
	{
		$page_to_display = $pages[0]->PageID;
	}
	$image = bhl_fetch_page_image($page_to_display);
		
	$html .=  '<!-- initialise -->' . "\n";
	$html .=  '<script type="text/javascript">';
	$html .=  	'show_page(\'' . $image->url . '\', \'' . $image->thumbnail->url . '\', \'' . $page_to_display . '\');';	
	$html .=  '</script>';
	
	
	return $html;
}

?>