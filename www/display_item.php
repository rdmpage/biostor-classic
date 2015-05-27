<?php

/**
 * @file display_item.php
 *
 * Display a single BHL item (may be a book, or a journal volume, for example) 
 *
 */

require_once (dirname(__FILE__) . '/display_object.php');
require_once ('../bhl_text.php');
require_once ('../bhl_viewer.php');
require_once ('../identifier.php');
require_once ('../geocoding.php');

//--------------------------------------------------------------------------------------------------
class DisplayBHLItem extends DisplayObject
{
	public $localities = array();
	public $page = 0;
	
	//----------------------------------------------------------------------------------------------
	function GetId()
	{
		$this->page = 0;
		
		if (isset($_GET['id']))
		{
			$this->id = $_GET['id'];
		}
		if (isset($_GET['page']))
		{
			$this->page = $_GET['page'];
		}
	}	
	
	//----------------------------------------------------------------------------------------------
	function GetFormat()
	{
		if (isset($_GET['format']))
		{
			switch ($_GET['format'])
			{

				case 'text':
					$this->format = 'text';
					break;					
		
				default:
					parent::GetFormat();
					break;
			}
		}
	}	
	
	//----------------------------------------------------------------------------------------------
	function DisplayFormattedObject()
	{
		switch ($this->format)
		{
			case 'text':
				$this->DisplayText();
				break;

			default:
				parent::DisplayFormattedObject();
				break;
		}
	}		
	
	

	//----------------------------------------------------------------------------------------------
	// Extra <HEAD> items
	function DisplayHtmlHead()
	{
		global $config;
		
		echo html_include_css('css/viewer.css');
		echo html_include_script('js/fadeup.js');
		echo html_include_script('js/prototype.js');
		echo html_include_script('js/lazierLoad.js'); // not working for some reason...
		echo html_include_script('js/viewer.js');
		
		// Recaptcha
		echo html_include_script('http://api.recaptcha.net/js/recaptcha_ajax.js');
	}
		
	//----------------------------------------------------------------------------------------------
	function DisplayHtmlContent()
	{
		global $config;

		echo html_page_header(true, '', 'name');
		
		echo '<h1>' . $this->GetTitle() . '</h1>';
		
		/*
		//------------------------------------------------------------------------------------------
		// Export options
		echo '<h2>Export</h2>';
		echo '<div>';
		echo '<span><a href="' . $config['web_root'] . 'reference/' . $this->id . '.text" title="Text">Text</a></span>';		
		echo '</div>';
		*/
		//------------------------------------------------------------------------------------------
		// Identifiers
		echo '<h2>Identifiers</h2>';
		echo '<ul>';
		
		if ($this->in_bhl)
		{
			// BHL reference
			echo '<li><a href="http://www.biodiversitylibrary.org/item/' . $this->id . '" target="_new">BHL ItemID:' . $this->id . '</a></li>';

			$identifiers = bhl_retrieve_identifiers($this->object->TitleID);
			foreach ($identifiers as $k => $v)
			{
				switch ($k)
				{
					case 'oclc':
						echo '<li><a href="http://www.worldcat.org/oclc/' . $v . '" target="_new">OCLC:' . $v . '</a></li>';
						break;
						
					case 'isbn':
						echo '<li><a href="http://www.worldcat.org/isbn/' . $v . '" target="_new">ISBN:' . $v . '</a></li>';
						break;
					
					default:
						break;
				}
					
			}
		
		}
		echo '</ul>';
		
		//------------------------------------------------------------------------------------------
		if ($this->in_bhl)
		{
			//--------------------------------------------------------------------------------------
			echo '<h2>Viewer</h2>';
			
			echo '<table width="100%" >';
			echo '<tr  valign="top"><td>';
			echo bhl_item_viewer($this->id, $this->page);
			echo '</td>';
			echo '<td>';
			
			//echo $this->DisplayEditForm();
			
			echo '</td></tr>';
			echo '</table>';
			

			
			//--------------------------------------------------------------------------------------
			if (count($this->localities) != 0)
			{
				echo '<h2>Localities</h2>';
				echo '<p class="explanation">Localities extracted from OCR text.</p>';
				echo '<div id="map_canvas" style="width: 600px; height: 300px"></div>';
			}
			
			
		}		
	}

	
	
	//----------------------------------------------------------------------------------------------
	function DisplayText()
	{
		$text = '';
		if (db_reference_from_bhl($this->id))
		{
			$pages = bhl_retrieve_reference_pages($this->id);
			$page_ids = array();
			foreach ($pages as $p)
			{
				$page_ids[] = $p->PageID;
			}
			
	
			$text = bhl_fetch_text_for_pages($page_ids);
			
			$text = str_replace ('\n', "\n" , $text);
			$text = str_replace ("\n ", "\n" , $text);
			
			// wiki experiments
			/*
			foreach ($page_ids as $page)
			{
				$names = names_in_page($page);
				print_r($names);
			}
			*/
		}
		
		header("Content-type: text/plain; charset=utf-8\n\n");
		echo $text;
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
			$this->object = new stdclass;
			$this->object->ItemID = $this->id;
			$this->object->TitleID = bhl_titleid_from_item_id($this->object->ItemID);
			$this->object->title = bhl_retrieve_title_from_ItemID($this->object->ItemID);
			$this->in_bhl = true;
		}
		
		return $this->object;
	} 

}

$d = new DisplayBHLItem();
$d->Display();


?>