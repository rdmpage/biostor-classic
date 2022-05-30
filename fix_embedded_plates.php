<?php

// Find references where the last page is not numbered the same as the last page
// in the metadata. This is a candidate for having plates embedded in the text.



require_once(dirname(__FILE__) . '/db.php');

$start 	= 0;
$end 	= 0;

$ids=array(
61055,
61060,

);

echo '<html>';
echo '<ul>';

foreach ($ids as $reference_id)
//for ($reference_id=$start;$reference_id<=$end;$reference_id++)
{

	// echo "-- reference_id = $reference_id\n";
	
	$article = db_retrieve_reference($reference_id);
	
	$last_page_meta = $article->spage;
	
	// How many pages do we expect from the metadata?
	$num_pages = 1;	
	if (isset($article->spage) && isset($article->epage))
	{
		$num_pages = $article->epage - $article->spage + 1;
		$last_page_meta = $article->epage;
	}

	// pages we actually have
	$pages = bhl_retrieve_item_pages_for_reference($reference_id);
	
	$num_actual_pages = count($pages);
	
	//print_r($pages);
	
	$check = true;
	
	$d = $num_actual_pages - $num_pages;
	
	// echo "$d = $num_actual_pages - $num_pages\n";
	
	if ($d == 0)
	{
		// same number of pages
		$check = true;
	}
	
	if ($d > 0)
	{
		// more pages in item than in metadata, probably edited
		$check = false;
	}

	if ($d < 0)
	{
		// something bad has hapepned, or special case
		$check = false; // for now
	}
	
	if ($check)
	{
		//$last_page_meta != $pages[$num_actual_pages-1]->PageNumber;
		
		if ($last_page_meta != $pages[$num_actual_pages-1]->PageNumber)
		{
			echo '<li>';
			echo '<a href="http://direct.biostor.org/reference/' .  $reference_id . '" targte="_new">' . $article->title . '</a><br />';
			echo 'Last page in article ' . $last_page_meta . ', last page in BHL ' . $pages[$num_actual_pages-1]->PageNumber . ' ';
			echo '<a href="http://direct.biostor.org/page_range_editor.php?reference_id=' . $reference_id . '" target="_new">Fix</a>';			
			echo '</li>';
		
		
		}
	
	
	}
	

}

echo '</ul>';
echo '</html>';

?>