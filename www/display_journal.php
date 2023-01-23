<?php

/**
 * @file display_journal.php
 *
 * Display journal 
 *
 */

// journal info
require_once (dirname(__FILE__) . '/display_object.php');
require_once ('../bhl_journal.php');
require_once ('../reference.php');
require_once (dirname(__FILE__) . '/sparklines.php');

//--------------------------------------------------------------------------------------------------
class DisplayJournal extends DisplayObject
{
	public $issn = '';
	public $oclc = '';
	
	
	//----------------------------------------------------------------------------------------------
	function GetId()
	{
		if (isset($_GET['id']))
		{
			$this->id = $_GET['id'];
		}
		if (isset($_GET['issn']))
		{
			$this->issn = $_GET['issn'];
		}
		if (isset($_GET['oclc']))
		{
			$this->oclc = $_GET['oclc'];
		}
	}	
	
	//----------------------------------------------------------------------------------------------
	function GetFormat()
	{
		if (isset($_GET['format']))
		{
			switch ($_GET['format'])
			{
				case 'xml':
					$this->format = 'xml';
					break;

				case 'ris':
					$this->format = 'ris';
					break;

				case 'bib':
					$this->format = 'bib';
					break;

				case 'text':
					$this->format = 'text';
					break;
					
				case 'tsv':
					$this->format = 'tsv';
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
			case 'xml':
				$this->DisplayXml();
				break;

			case 'ris':
				$this->DisplayRis();
				break;

			case 'bib':
				$this->DisplayBibtex();
				break;
				
			case 'text':
				$this->DisplayText();
				break;
				
			case 'tsv':
				$this->DisplayTsv();
				break;
				

			default:
				parent::DisplayFormattedObject();
				break;
		}
	}	
	
	//----------------------------------------------------------------------------------------------
	function DisplayText()
	{
		header("Content-type: text/plain; charset=utf-8\n\n");
	
		$articles = db_retrieve_articles_from_journal($this->issn, $this->oclc);
		foreach ($articles as $k => $v)
		{
			foreach ($v as $ref)
			{
				echo $ref->id . "\n";
			}
		}
	}	
	
	
	//----------------------------------------------------------------------------------------------
	function DisplayRis()
	{
		$ris = '';
		
		$articles = db_retrieve_articles_from_journal($this->issn, $this->oclc);
		foreach ($articles as $k => $v)
		{
			foreach ($v as $ref)
			{
				$reference = db_retrieve_reference ($ref->id);
				$ris .= reference_to_ris($reference);
			}
		}
	
		header("Content-type: text/plain; charset=utf-8\n\n");
		echo $ris;
	}	
	
	//----------------------------------------------------------------------------------------------
	function DisplayBibtex()
	{
		$bibtex = '';
		
		$articles = db_retrieve_articles_from_journal($this->issn, $this->oclc);
		foreach ($articles as $k => $v)
		{
			foreach ($v as $ref)
			{
				$reference = db_retrieve_reference ($ref->id);
				$bibtex .= reference_to_bibtex($reference);
			}
		}
		
		header("Content-type: application/x-bibtex; charset=utf-8\n\n");
		echo $bibtex;
	}	

	//----------------------------------------------------------------------------------------------
	// Endnote XML export format
	function DisplayXml()
	{
		// Create XML document
		$doc = new DomDocument('1.0', 'UTF-8');
		$xml = $doc->appendChild($doc->createElement('xml'));

		// root element is <records>
		$records = $xml->appendChild($doc->createElement('records'));
		
		$articles = db_retrieve_articles_from_journal($this->issn, $this->oclc);
		foreach ($articles as $k => $v)
		{
			foreach ($v as $ref)
			{
				$reference = db_retrieve_reference ($ref->id);
				reference_to_endnote_xml($reference, $doc, $records);
			}
		}
		
		// Dump XML
		header("Content-type: text/xml; charset=utf-8\n\n");
		echo $doc->saveXML();
	}	
	
	//----------------------------------------------------------------------------------------------
	// TSV export format
	function DisplayTsv()
	{
		$keys = array('reference_id', 'title', 'authors', 'secondary_title', 'issn', 
		'volume', 'issue', 'spage', 'epage', 'year', 'date', 'doi', 'jstor', 'PageID');
		
		$tsv = '';
		
		$tsv .= join("\t", $keys) . "\n";
		
		$articles = db_retrieve_articles_from_journal($this->issn, $this->oclc);
		foreach ($articles as $k => $v)
		{
			foreach ($v as $ref)
			{
				$reference = db_retrieve_reference ($ref->id);
				$tsv .= reference_to_tsv($reference, $keys) . "\n";
			}
		}
		
		
		// Dump TSV
		header("Content-type: text/plain; charset=utf-8\n\n");
		header('Content-Disposition: attachment; filename=' . $this->issn . '.tsv');
		echo $tsv;
	}		
	


	//----------------------------------------------------------------------------------------------
	function DisplayHtmlContent()
	{
		global $config;
		
		echo html_page_header(true, '', 'name');
		
		echo '<div style="float:right;background-color:rgb(230,242,250);padding:6px">' . "\n";
			
		if ($this->issn != '')
		{
			echo '<h2>Identifiers</h2>' . "\n";
			echo '<ul class="guid-list">' . "\n";
			echo '<li class="permalink"><a href="' . $config['web_root'] . 'issn/' . $this->issn . '" title="Permalink">' . $config['web_root'] . 'issn/' . $this->issn . '</a></li>' . "\n";	
			echo '<li class="worldcat"><a href="http://www.worldcat.org/issn/' . $this->issn . '" title="ISSN">' .  $this->issn . '</a></li>' . "\n";	

			echo '<h2>Export</h2>' . "\n";
			echo '<ul class="export-list">' . "\n";
			echo '<li class="xml"><a href="' . $config['web_root'] . 'issn/' . $this->issn . '.xml" title="Endnote XML">Endnote XML</a></li>';
			echo '<li class="ris"><a href="' . $config['web_root'] . 'issn/' . $this->issn . '.ris" title="RIS">Reference manager</a></li>';		
			echo '<li class="bibtex"><a href="' . $config['web_root'] . 'issn/' . $this->issn . '.bib" title="BibTex">BibTex</a></li>';	
			echo '<li class="text"><a href="' . $config['web_root'] . 'issn/' . $this->issn . '.text" title="text">Text</a></li>';	
			echo '<li class="text"><a href="' . $config['web_root'] . 'issn/' . $this->issn . '.tsv" title="tsv">TSV</a></li>';	
			echo '</ul>' . "\n";
		}
		if ($this->oclc != '')
		{
			echo '<h2>Identifiers</h2>' . "\n";
			echo '<ul class="guid-list">' . "\n";
			echo '<li class="permalink"><a href="' . $config['web_root'] . 'oclc/' . $this->oclc . '" title="Permalink">' . $config['web_root'] . 'oclc/' . $this->oclc . '</a></li>' . "\n";	
			echo '<li class="worldcat"><a href="http://www.worldcat.org/oclc/' . $this->oclc . '" title="OCLC">' .  $this->oclc . '</a></li>' . "\n";	

			echo '<h2>Export</h2>' . "\n";
			echo '<ul class="export-list">' . "\n";
			echo '<li class="xml"><a href="' . $config['web_root'] . 'oclc/' . $this->oclc . '.xml" title="Endnote XML">Endnote XML</a></li>';
			echo '<li class="ris"><a href="' . $config['web_root'] . 'oclc/' . $this->oclc . '.ris" title="RIS">Reference manager</a></li>';		
			echo '<li class="bibtex"><a href="' . $config['web_root'] . 'oclc/' . $this->oclc . '.bib" title="BibTex">BibTex</a></li>';	
			echo '<li class="text"><a href="' . $config['web_root'] . 'oclc/' . $this->oclc . '.text" title="text">Text</a></li>';	
			echo '</ul>' . "\n";
		}

		echo '</div>' . "\n";
		
		
		echo '<h1>' . $this->GetTitle() . '</h1>';
		
		// Image
		if (isset($this->issn))
		{
			echo '<div>';
			//echo '<img src="http://bioguid.info/issn/image.php?issn=' . $this->issn . '" alt="cover" style="border:1px solid rgb(228,228,228);height:100px;" />';
			echo '<img src="' . $config['web_root'] . 'issn_image.php?issn=' . $this->issn . '" alt="cover" style="border:1px solid rgb(228,228,228);height:100px;" />';
			echo '</div>';
		}
		
		// Stats
/*		echo '<div>';
		echo '<img src="' . sparkline_articles_added_for_issn($this->issn) . '" alt="sparkline" />';
		echo '</div>';*/
		
		//echo '<a href="http://twitter.com/share" class="twitter-share-button" data-count="vertical" data-via="rdmpage" data-related="biostor_org">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
		
		
		echo '<h2>Coverage</h2>' . "\n";

		echo '<p>';
		if ($this->issn != '')
		{
			echo bhl_articles_for_issn($this->issn);
		}
		if ($this->oclc != '')
		{
			echo bhl_articles_for_oclc($this->oclc);
		}
		echo ' articles in database.</p>' . "\n";

		echo '<h3>Distribution of identified articles over time</h3>' . "\n";

		echo '<div>' . "\n";
		echo '   <img src="' . sparkline_references($this->issn, $this->oclc, 360,100) . '" alt="sparkline" />' . "\n";
		echo '</div>' . "\n";
		
		
		$titles = array();
		if ($this->issn != '')
		{
			$titles = bhl_titles_for_issn($this->issn);
		}
		if ($this->oclc != '')
		{
			$titles = bhl_titles_for_oclc($this->oclc);
		}		
		
		if (count($titles) > 0)
		{
			echo '<h3>Distribution of identified articles across BHL items</h3>' . "\n";
			
			echo '<div>';
			echo '<div style="display:inline;background-color:rgb(230,242,250);width:20px;height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;</div>';
			echo '&nbsp;Scanned pages&nbsp;';
			echo '<div style="display:inline;background-color:rgb(0,119,204);width:10px;height:10px;">&nbsp;&nbsp;&nbsp;&nbsp;</div>';
			echo '&nbsp;Articles&nbsp;';
			echo '</div>';
			echo '<p></p>';
			
			
	
			$items = array();
			$volumes = array();		
			items_from_titles($titles, $items, $volumes);
			$html = '<div style="height:400px;border:1px solid rgb(192,192,192);overflow:auto;">' . "\n";
			$html .= '<table>' . "\n";
			$html .= '<tbody style="font-size:10px;">' . "\n";
			
			foreach ($volumes as $volume)
			{
				$item = $items[$volume];
				
				// How many pages in this item?
				$num_pages = bhl_num_pages_in_item($item->ItemID);
				
				// Coverage
				$coverage = bhl_item_page_coverage($item->ItemID);	
				
				$row_height = 10;
				
				// Draw as DIV
				
				$html .= '<tr>' . "\n";
				$html .= '<td>';
				$html .= '<a href="http://www.biodiversitylibrary.org/item/' . $item->ItemID .'" target="_new">';
				$html .= $item->VolumeInfo;
				$html .= '</a>';
				$html .= '</td>' . "\n";
				$html .= '<td>' . "\n";
				$html .= '<div style="position:relative">' . "\n";
				$html .= '   <div style="background-color:rgb(230,242,250);border-bottom:1px solid rgb(192,192,192);border-right:1px solid rgb(192,192,192);position:absolute;left:0px;top:0px;width:' . $num_pages . 'px;height:' . $row_height . 'px;">' . "\n";
				
				foreach ($coverage as $c)
				{   
					$html .= '      <div style="background-color:rgb(0,119,204);position:absolute;left:' . $c->start . 'px;top:0px;width:' . ($c->end - $c->start) . 'px;height:' . $row_height . 'px;">' . "\n";
					$html .= '      </div>' . "\n";
				}   
				
				
				$html .= '   </div>' . "\n";
				$html .= '</div>' . "\n";
				$html .= '</td>' . "\n";
				$html .= '</tr>' . "\n";
			
			}
			
			$html .= '</tbody>' . "\n";
			$html .= '</table>' . "\n";
			$html .= '</div>' . "\n";
			echo $html;
		}
		
		$institutions = institutions_from_titles($titles);
		if (count($institutions) != 0)
		{
			echo '<h3>BHL source(s)</h3>' . "\n";
			echo '<table>' . "\n";
			foreach ($institutions as $k => $v)
			{
				echo '<tr>' . "\n";
				echo '<td>' . "\n";
				switch ($k)
				{
					case 'American Museum of Natural History Library':
						echo '<img src="' . $config['web_root'] . 'images/institutions/' . 'AMNH_logo_--_blue_rectangle.jpg' . '" width="48" />';
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
				echo '</td>' . "\n";	
				echo '<td>' . "\n";	
				echo $k . '<br />' . $v . ' items';	
				echo '</td>' . "\n";	
				echo '</tr>' . "\n";	
	
			}
			echo '</table>' . "\n";	
		}
		
		// How does journal relate to BHL Titles and Items?
		$titles = array();
		if ($this->issn != '')
		{
			$titles = db_retrieve_journal_names_from_issn($this->issn);
		}
		if ($this->oclc != '')
		{
			$titles = db_retrieve_journal_names_from_oclc($this->oclc);
		}
		if (count($titles) > 1)
		{	
			echo '<h2>Alternative titles</h2>';
			echo '<ul>';
			foreach ($titles as $title)
			{
				echo '<li>' . $title . '</li>';
			}
			echo '</ul>';
		}
		
		echo '<h2>Articles</h2>';
		
		if ($this->issn == '0374-5481')
		{
			// Special treatment for Ann mag Nat Hist
			
			echo '<ul>';
			for ($series=1;$series <= 9; $series++)
			{
				echo '<li><a href="' . $config['web_root'] . 'issn/' . $this->issn . '#series' . $series . '">Series ' . $series . '</a></li>';
			}
			echo '</ul>';
			
			for ($series=1;$series <= 9; $series++)
			{
				echo '<h3><a name="series' . $series . '"></a>Series ' . $series . '</h3>' . "\n";
				$articles = db_retrieve_articles_from_journal_series($this->issn, ($series == 1 ? '' : $series));
				echo '<ul>';
				foreach ($articles as $k => $v)
				{
					echo '<li style="display:block;border-top:1px solid #EEE; ">' . $k;
					echo '<ul>';
					
					if (is_array($v))
					{
						foreach ($v as $ref)
						{
							echo '<li><a href="' . $config['web_root'] . 'reference/' . $ref->id . '">' . $ref->title . '</a></li>';
						}
					}
					echo '</ul>';
					echo '</li>';
				}
				echo '</ul>';
			}
		}
		else
		{
			
			$articles = db_retrieve_articles_from_journal($this->issn, $this->oclc);
			
			//print_r($articles);
			
			echo '<ul>';
			foreach ($articles as $k => $v)
			{
				echo '<li style="display:block;border-top:1px solid #EEE; ">' . $k;
				echo '<ul>';
				
				if (is_array($v))
				{
					foreach ($v as $ref)
					{
						if (0)
						{
							// fast
							echo '<li><a href="' . $config['web_root'] . 'reference/' . $ref->id . '">' . $ref->title . '</a></li>';
						}
						else
						{
							// slower, but useful for debugging
							$reference = db_retrieve_reference ($ref->id);
							echo '<li style="border-bottom:1px dotted rgb(128,128,128);padding:4px;">';
							echo '<a href="' . $config['web_root'] . 'reference/' . $ref->id . '">' . $reference->title . '</a><br/>';
							echo '<span style="color:green;">' . reference_authors_to_text_string($reference);
							if (isset($reference->year))
							{
								echo ' (' . $reference->year . ')';
							}
							echo ' ' . reference_to_citation_text_string($reference) . '</span>';
							echo ' ' . reference_to_coins($reference);
		
							// Thumbail, useful for debugging
							if (0)
							{
								echo '<div>';					
								$pages = bhl_retrieve_reference_pages($ref->id);
								$image = bhl_fetch_page_image($pages[0]->PageID);
								echo '<a href="' . $config['web_root'] . 'reference/' . $ref->id . '">';
								echo '<img style="padding:2px;border:1px solid blue;margin:2px;" id="thumbnail_image_' . $page->PageID . '" src="' . $image->thumbnail->url . '" width="' . $image->thumbnail->width . '" height="' . $image->thumbnail->height . '" alt="thumbnail"/>';	
								echo '</a>';
								echo '</div>'; 
							}
							echo '</li>';
						}
					}
				}
				else
				{
					// Article lacks volume
					if (isset($v->id))
					{
							$reference = db_retrieve_reference ($v->id);
							echo '<li style="border-bottom:1px dotted rgb(128,128,128);padding:4px;">';
							echo '<a href="' . $config['web_root'] . 'reference/' . $v->id . '">' . $reference->title . '</a><br/>';
							echo '<span style="color:green;">' . reference_authors_to_text_string($reference);
							if (isset($reference->year))
							{
								echo ' (' . $reference->year . ')';
							}
							echo ' ' . reference_to_citation_text_string($reference) . '</span>';
							echo ' ' . reference_to_coins($reference);
		
					
					}
				}
				echo '</ul>';
				echo '</li>';
			}
			echo '</ul>';
		}
	}

	//----------------------------------------------------------------------------------------------
	function GetTitle()
	{
		return $this->object->title;
	}


	//----------------------------------------------------------------------------------------------
	function Retrieve()
	{
		if ($this->issn != '')
		{
			$this->object = db_retrieve_journal_from_issn ($this->issn);
		}
		if ($this->oclc != '')
		{
			$this->object = db_retrieve_journal_from_oclc ($this->oclc);
		}
		
		return $this->object;
	} 

}

$d = new DisplayJournal();
$d->Display();


?>