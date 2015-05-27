<?php

/**
 * @file display_specimen.php
 *
 * Display specimen 
 *
 */

require_once (dirname(__FILE__) . '/display_object.php');
require_once ('../reference.php');
require_once ('../specimens.php');

//--------------------------------------------------------------------------------------------------
class DisplaySpecimen extends DisplayObject
{
	public $code = '';
	
	//----------------------------------------------------------------------------------------------
	function __construct()
	{
		$this->object = new stdclass;
		
		$this->GetId();
		$this->GetFormat();
	}
	

	//----------------------------------------------------------------------------------------------
	function GetId()
	{
		if (isset($_GET['code']))
		{
			$this->code = $_GET['code'];
		}
		parent::GetId();		
	}	
		

	//----------------------------------------------------------------------------------------------
	function DisplayHtmlContent()
	{
		global $config;
		
		echo html_page_header(true, '', 'name');
		
		echo '<h1>' . $this->GetTitle() . '</h1>';
		
		if (count($this->object->specimens) == 0)
		{
			echo '<p>No specimens with this code</p>';
		}
		else
		{
			// What articles have this specimen?
			echo '<h2>References in BioStor with this specimen</h2>';
			
			$refs = specimens_references_with_code($this->code);
			
			//print_r($refs);
			
			foreach ($refs as $occurrenceID => $ref_list)
			{
				if ($occurrenceID != 0)
				{
					echo '<p>GBIF occurrence <a class="gbif" href="http://data.gbif.org/occurrences/'
						. $occurrenceID . '/" target="_new">' . $occurrenceID . '</a>';
						
					$datasetID = specimens_dataset_from_occurrence($occurrenceID);
					$dataset = specimens_dataset($datasetID);
					echo ' from dataset <a href="gbif_dataset.php?datasetID=' . $dataset->datasetID  . '">' . $dataset->dataResourceName . ' (' . $dataset->providerName . ')' . '</a>';
					
					echo '</p>';
					
					$occurrence = specimens_from_occurrenceID($occurrenceID);
					echo '<p>' . $occurrence->scientificName . ' (' . join(':', $occurrence->lineage) . ')' . '</p>';
				}
				else
				{
					echo '<p>(specimen not known in GBIF)</p>';
				}
			
				echo '<table cellspacing="0" cellpadding="2" width="100%">';

				foreach ($ref_list as $reference_id)
				{
					$reference = db_retrieve_reference ($reference_id);
					echo '<tr';
					
					if (in_array($reference_id, $act_refs))
					{
						echo ' style="background-color:#D8F3C9;"';
					}
					echo '>';
					
					if ($reference->PageID != 0)
					{
						$image = bhl_fetch_page_image($reference->PageID);
						$imageURL = $image->thumbnail->url;
					}
					else
					{
						// if it's an article we could use journal image
						$imageURL = 'http://bioguid.info/issn/image.php?issn=' . $reference->issn;
					}
					
					echo '<td valign="top"><img style="border:1px solid rgb(192,192,192);" src="' . $imageURL . '" width="40" />';
					
					echo '</td>';
					
					echo '<td valign="top">';
					
					
					echo '<a href="' . $config['web_root'] . 'reference/' . $reference_id . '">' . $reference->title . '</a><br/>';
					echo '<span>' . reference_authors_to_text_string($reference);
					if (isset($reference->year))
					{
						echo ' (' . $reference->year . ')';
					}
					echo ' ' . reference_to_citation_text_string($reference) . '</span>';
					echo ' ' . reference_to_coins($reference);
					
					if (0)
					{
						echo '<div>';
						echo bhl_pages_with_name_thumbnails($reference_id,$this->object->NameBankID);	
						echo '</div>';
					}
					echo '</td>';
					echo '</tr>';
				}
				echo '</table>';
			}		
			
			
		}
	}
	
	//----------------------------------------------------------------------------------------------
	function GetTitle()
	{
		return $this->code;
	}

	//----------------------------------------------------------------------------------------------
	function Retrieve()
	{
		$this->object->specimens = specimens_with_code($this->code);
		return $this->object;
	} 

}

$d = new DisplaySpecimen();
$d->Display();


?>