<?php

/**
 * @file display_title.php
 *
 * Display information about a BHL title 
 *
 */

require_once (dirname(__FILE__) . '/display_object.php');

//--------------------------------------------------------------------------------------------------
class DisplayTitle extends DisplayObject
{
	//----------------------------------------------------------------------------------------------
	function DisplayHtmlContent()
	{
		global $config;
		
		echo '<h1>' . $this->GetTitle() . '</h1>';
		
		echo '<p>' . $this->object->FullTitle . '</p>';
		echo '<p>' . $this->object->PublicationDetails . '</p>';
		echo '<p>' . $this->object->TL2Author . '</p>';
		
		echo '<p>';
		if ($this->object->StartYear != 0)
		{
			echo $this->object->StartYear . '-';
		}
		if ($this->object->EndYear != 0)
		{
			echo $this->object->EndYear;
		}
		
		$institutions = bhl_institutions_with_title($this->id);
		echo '<ul>';
		foreach ($institutions as $i)
		{
			echo '<li>';
			echo $i->name . ' [' . $i->count . ']';	
			switch ($i->name)
			{
				case 'American Museum of Natural History Library':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'AMNH_logo_--_blue_rectangle.jpg' . '" width="48" />';
					break;

				case 'Harvard University, MCZ, Ernst Mayr Library':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'Mod_Color_Harvard_Shield_small_bigger.jpg' . '" width="48" />';
					break;


					
				case 'Missouri Botanical Garden':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'twitter_icon_MBG.jpg' . '" width="48" />';
					break;
					
				case 'New York Botanical Garden':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'NYBGDOMEHEADERWEB.jpg' . '" />';
					break;

				case 'Smithsonian Institution Libraries':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'SILCatesbyMagnolia.jpg' . '"  width="48" />';
					break;
				
				case 'The Field Museum':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'field.jpg' . '" width="48" />';
					break;
				
				case 'BHL-Europe':
					echo '<br /><div style="background-color:green;width:120px;text-align:center"><img src="' . $config['web_root'] . 'images/institutions/' . 'BHL_logo_wg.png' . '" height="48" /></div>';			
					break;
					
				case 'Boston Public Library':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'BPLcards.jpg' . '" width="48" />';			
					break;
					
				case 'Harvard University Herbarium':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'huh_logo_bw_100.png' . '" width="48" />';
					break;
				
				case 'MBLWHOI Library':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'library_logo2_bigger.jpg' . '" width="48" />';
					break;
					
				case 'Natural History Museum, London':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'natural_history_museum-01.jpg' . '" width="48" />';
					break;
				
				case 'University of Illinois Urbana Champaign':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'ilogo_horz_bold.gif' . '" height="48" />';
					break;
							
				default:
					break;
			}
			echo '</li>';	
			
		}
		echo '</ul>';
		
		echo '<h2>Identifiers</h2>';
		
		echo '<ul>';
		
		echo '<li><a href="http://www.biodiversitylibrary.org/title/' . $this->id . '" target="_new">' . $this->id . '</a></li>';
		
		foreach ($this->object->identifiers as $identifier)
		{
			echo '<li>' . $identifier['namespace'] . ':' . $identifier['identifier'] . '</li>';
		}
		echo '</ul>';
		
		echo '<h2>Coverage</h2>';
		
		$max_count = 0;
		foreach ($this->object->years as $k => $v)
		{
			$max_count = max ($max_count, $v);
		}
		
		// CSS display from http://www.alistapart.com/articles/accessibledatavisualization/
		echo '<ul class="timeline">';
		foreach ($this->object->years as $k => $v)
		{
			echo '<li>';
			echo '<a>';
			echo '<span class="label">' . $k . '</span>';
			
			$percentage = round(100 * $v/$max_count, 2);
			
			echo '<span class="count" style="height: ' . $percentage . '%">(' . $v . ')</span>';
			echo '</a>';
			echo '</li>';
		}
		echo '</ul>';

	}
	//----------------------------------------------------------------------------------------------
	function GetTitle()
	{
		return $this->object->ShortTitle;
	}


	//----------------------------------------------------------------------------------------------
	function Retrieve()
	{
		$this->object = bhl_retrieve_title ($this->id);
		return $this->object;
	} 

}

$d = new DisplayTitle();
$d->Display();


?>

	