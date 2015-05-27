<?php

/**
 * @file display_name.php
 *
 * Display taxonomic name string 
 *
 */

require_once (dirname(__FILE__) . '/display_object.php');
require_once ('../bhl_names.php');
require_once ('../col.php');
require_once ('../nomenclator.php');
require_once ('../reference.php');
require_once ('../taxon.php');
require_once (dirname(__FILE__) . '/sparklines.php');

//print_r( $_GET);

//--------------------------------------------------------------------------------------------------
class DisplayName extends DisplayObject
{
	public $namestring = '';
	public $namebankid = 0;
//	public $identifiers = array();
	
	//----------------------------------------------------------------------------------------------
	function __construct()
	{
		$this->object = new NameString;
		
		$this->GetId();
		$this->GetFormat();
	}
	

	//----------------------------------------------------------------------------------------------
	function GetId()
	{
		if (isset($_GET['namebankid']))
		{
			$this->namebankid = $_GET['namebankid'];
		}
		if (isset($_GET['namestring']))
		{
			$this->namestring = $_GET['namestring'];
		}
		parent::GetId();		
/*		if (isset($_GET['lsid']))
		{
			$this->identifiers[] = $_GET['lsid'];
		}*/
	}	
		

	//----------------------------------------------------------------------------------------------
	function DisplayHtmlContent()
	{
		global $config;
		
		echo html_page_header(true, '', 'name');
		
		echo '<h1>' . $this->GetTitle() . '</h1>';
		
		echo '<h2>Identifiers</h2>';
		echo '<ul>';		
		if ($this->object->NameBankID != 0)
		{
			echo '<li>' . 'urn:lsid:ubio.org:namebank:' .  $this->object->NameBankID .'</li>';
		}
		echo '</ul>';
		
		
		// Taxon name (nomenclators)
		$acts = acts_for_namestring($this->GetTitle());
		$act_refs = references_for_acts_for_namestring($this->GetTitle());
		if (count($acts) > 0)
		{
			echo '<h2>Taxonomic names</h2>';
			echo '<ul>';
			foreach ($acts as $tn)
			{
				echo '<li>' . $tn->global_id . '</li>';
			}
			echo '</ul>';
		}

		$col = col_accepted_name_for($this->GetTitle());
		if (isset($col->name))
		{
			echo '<h2>Catalogue of Life accepted name</h2>' . "\n";
			echo '<p>';
			echo '<span><a href="' . $config['web_root'] . 'name/' . $col->name . '">' . $col->name . '</a>' . ' ' . $col->author . '</span>';
			echo '</p>' . "\n";
			
			// Synonyms
			$col_synonyms = col_synonyms_for_namecode($col->name_code);
			if (count($col_synonyms) != 0)
			{
				echo '<h3>Synonyms</h3>' . "\n";
				echo '<ul>' . "\n";
				foreach ($col_synonyms as $s)
				{
					echo '<li><a href="' . $config['web_root'] . 'name/' . $s->name . '">' . $s->name . '</a> ' . $s->author . '</li>' . "\n";
				}
				echo '</ul>' . "\n";
			}
		}
		
		if (1) // 0 to turn off name search
		{
			/*
			// What pages have this name? (BHL timeline)
			$hits = bhl_name_search($this->object->NameBankID);
			if (count($hits) > 0)
			{
				echo '<h2>BHL</h2>' . "\n";		
				echo '<h3>Distribution of name in BHL</h3>';		
				echo '<div>' . "\n";
				echo '   <img src="' . sparkline_bhl_name($hits, 360,100) . '" alt="sparkline" />' . "\n";
				echo '</div>' . "\n";
			}
			else
			{
				//echo '<p>Name not found in BHL</p>';
			}
			*/
			// What articles have this name?
			echo '<hr />';
			echo '<h2>References in BioStor</h2>';
	//		echo '<p><img src="/images/star.png"> indicates reference that publishes a "nomenclatural act", such as publishing the name.</p>';
	
			echo '<div>';
			echo '<div style="display:inline;border:1px solid rgb(192,192,192);background-color:#D8F3C9;width:20px;height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;</div>';
			echo '&nbsp;Reference contains nomenclatural act, such as publishing the name';
			echo '</div>';
			
			echo '<p />';
	
			
			// Find using BHL bhl_page_name index
			//$refs = bhl_references_with_name($this->object->NameBankID);
			$refs = bhl_references_with_namestring($this->GetTitle());
			
			// Merge with references from nomenclators
			$refs = array_merge($refs, $act_refs);
			$refs = array_unique($refs);
			//print_r($refs);
			
			if (count($refs) == 0)
			{
				echo '<p>[No references]</p>';
			}
				
	/*		
			echo '<ul class="reference-list">';
			foreach($refs as $reference_id)
			{
				$reference = db_retrieve_reference ($reference_id);
				echo '<li ';
				
				if (in_array($reference_id, $act_refs))
				{
					echo 'class="act"';
				}
				else
				{
					echo 'class="default"';
				}
				echo '>';
				echo '<a href="' . $config['web_root'] . 'reference/' . $reference_id . '">' . $reference->title . '</a><br/>';
				echo '<span style="color:green;">' . reference_authors_to_text_string($reference);
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
				echo '</li>';
			}
			echo '</ul>';
	*/
	
			echo '<table cellspacing="0" cellpadding="2" width="100%">';
			foreach($refs as $reference_id)
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
	
	
	
			/*
			$refs = col_references_for_name($this->GetTitle());
			if (count($refs) != 0)
			{
				echo '<h2>Catalogue of Life Bibliography</h2>';
				echo '<ol>';
				foreach($refs as $ref)
				{
					echo '<li style="border-bottom:1px dotted rgb(128,128,128);padding:4px;">';
					echo '<span>';
					echo '[' . $ref->record_id . '] ';
					if (isset($ref->reference_type))
					{
						echo '[' . $ref->reference_type . '] ';
					}
					echo $ref->author;
					echo ' ';
					echo $ref->year;
					echo ' ';
					echo $ref->title;
					echo '. ';
					echo $ref->source;
					echo '</span>';
					echo '</li>';
				}
				echo '</ol>';
			}
			*/
		}
	}
	
	//----------------------------------------------------------------------------------------------
	// JSON format
	function DisplayJson()
	{
		$act_refs = array();
		$refs = bhl_references_with_namestring($this->GetTitle());
		
		// Merge with references from nomenclators
		$refs = array_merge($refs, $act_refs);
		$refs = array_unique($refs);

		$obj = new stdclass;
		$obj->references = array();
		foreach($refs as $reference_id)
		{
			$obj->references[] = db_retrieve_reference ($reference_id);
		}		
	
		header("Content-type: text/plain; charset=utf-8\n\n");
		if ($this->callback != '')
		{
			echo $this->callback . '(';
		}
		echo json_format(json_encode($obj));
		if ($this->callback != '')
		{
			echo ')';
		}
	}	

	//----------------------------------------------------------------------------------------------
	function GetTitle()
	{
		return $this->object->namestring;
	}

	//----------------------------------------------------------------------------------------------
	function Retrieve()
	{
		/*
		// Called with just an integer, assume it is NameBankID
		if ($this->namebankid != 0)
		{
			$this->object = bhl_retrieve_name_from_namebankid ($this->namebankid);
		}
		*/
		// Called with a string
		if ($this->namestring != '')
		{
			$this->object = bhl_retrieve_name_from_namestring ($this->namestring);
			
			/*
			if ($this->object == NULL)
			{
				// Do name lookup
				$this->object = db_get_namestring($this->namestring);
				if ($this->object == NULL)
				{
					// Try uBio
					$this->object = ubio_lookup($this->namestring);
				}					
			}
			*/
		}
		
		// CoL?
		
/*		
		if ($this->object != NULL)
		{
			$this->identifiers[] = $this->object->Identifier;
			$this->namebankid = $this->object->NameBankID;
		}
*/		
		return $this->object;
	} 

}

$d = new DisplayName();
$d->Display();


?>