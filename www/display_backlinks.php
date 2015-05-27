<?php

/**
 * @file display_backlinks.php
 *
 * Display "backlinks" (references cited by a reference) 
 *
 */

require_once (dirname(__FILE__) . '/display_object.php');
require_once ('../cites.php');
require_once ('../reference.php');

//--------------------------------------------------------------------------------------------------
class DisplayBacklinks extends DisplayObject
{

	//----------------------------------------------------------------------------------------------
	function DisplayHtmlContent()
	{
		global $config;
		global $db;
		
		$this->display_disqus = false;

		echo html_page_header(true, '', 'name');
		
		echo '<h1><a href=' . $config['web_root'] . 'reference/' . $this->id . '">' .  $this->GetTitle() . '</a></h1>';

		echo '<h2>' . num_cites($this->id) . " references cited" . '</h2>';
		echo '<p class="explanation">References cited by this reference</p>' . "\n";
		
		echo '<div>';
		echo '<div style="display:inline;border:1px solid rgb(192,192,192);background-color:#D8F3C9;width:20px;height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;</div>';
		echo '&nbsp;Reference exists in BioStor&nbsp;';
		echo '<div style="display:inline;border:1px solid rgb(192,192,192);background-color:#FCCB08;width:10px;height:10px;">&nbsp;&nbsp;&nbsp;&nbsp;</div>';
		echo '&nbsp;Reference parsed but not found online&nbsp;';
		echo '<div style="display:inline;border:1px solid rgb(192,192,192);background-color:white;width:10px;height:10px;">&nbsp;&nbsp;&nbsp;&nbsp;</div>';
		echo '&nbsp;Reference not parsed&nbsp;';
		echo '</div>';
		echo '<hr />' . "\n";
		
		$sql = 'SELECT * FROM rdmp_reference_cites
INNER JOIN rdmp_citation_string USING(citation_string_id)
LEFT JOIN rdmp_reference_citation_string_joiner USING(citation_string_id)
WHERE rdmp_reference_cites.reference_id = ' . $this->id .
' ORDER BY rdmp_reference_cites.citation_order';

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
		echo '<ul>' . "\n";
		while (!$result->EOF) 
		{
			$style = 'padding:4px;border-bottom:1px solid rgb(192,192,192);';
			if ($result->fields['reference_id'] != '')
			{
				$style .= 'background-color:#D8F3C9;';
			}
			elseif ($result->fields['citation_object'] != '')
			{
				$style .= 'background-color:#FCCB08;';
			}
		
		
			echo '<li style="' . $style . '">';
			if ($result->fields['reference_id'] != '')
			{
				echo '<a href="' . $config['web_root'] . 'reference/' . $result->fields['reference_id'] . '">';
			}
			echo $result->fields['citation_string'];
			if ($result->fields['reference_id'] != '')
			{
				echo '</a>';
			}
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

$d = new DisplayBacklinks();
$d->Display();


?>