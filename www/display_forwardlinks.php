<?php

/**
 * @file display_forwardlinks.php
 *
 * Display "forward links" (references that cite this reference) 
 *
 */

require_once (dirname(__FILE__) . '/display_object.php');
require_once ('../cites.php');
require_once ('../reference.php');

//--------------------------------------------------------------------------------------------------
class DisplayForwardlinks extends DisplayObject
{


	//----------------------------------------------------------------------------------------------
	function DisplayHtmlContent()
	{
		global $config;
		global $db;
	
		$this->display_disqus = false;
		
		echo html_page_header(true, '', 'name');
		
		echo '<h1>' . $this->GetTitle() . '</h1>';

		echo '<h2>' . num_cited_by($this->id) . " citations" . '</h2>';
		echo '<p class="explanation">References in BioStor that cite this reference</p>' . "\n";
		
		$sql = 'SELECT DISTINCT(rdmp_reference_cites.reference_id) FROM rdmp_reference_citation_string_joiner
INNER JOIN rdmp_reference_cites USING(citation_string_id)
WHERE rdmp_reference_citation_string_joiner.reference_id=' . $this->id;

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
		echo '<ul>' . "\n";
		while (!$result->EOF) 
		{			
			$reference = db_retrieve_reference ($result->fields['reference_id']);
			echo '<li style="border-bottom:1px dotted rgb(128,128,128);padding:4px;">';
			echo '<a href="' . $config['web_root'] . 'reference/' . $result->fields['reference_id'] . '">' . $reference->title . '</a><br/>';
			echo '<span style="color:green;">' . reference_authors_to_text_string($reference);
			if (isset($reference->year))
			{
				echo ' (' . $reference->year . ')';
			}
			echo ' ' . reference_to_citation_text_string($reference) . '</span>';
			echo ' ' . reference_to_coins($reference);
			echo '</li>';
			
			$result->MoveNext();
		}	
		echo '</ul>' . "\n";
		
	}
	
	//----------------------------------------------------------------------------------------------
	function GetTitle()
	{
		return $this->object->title;
	}

	//----------------------------------------------------------------------------------------------
	function Retrieve()
	{
		if ($this->id != 0)
		{
			$this->object = db_retrieve_reference ($this->id);
		}
										
		return $this->object;
	} 
	

}

$d = new DisplayForwardlinks();
$d->Display();


?>