<?php

/**
 * @file display_reference.php
 *
 * Display reference 
 *
 */

require_once (dirname(__FILE__) . '/display_object.php');
require_once (dirname(__FILE__) . '/display_pdf.php');
require_once (dirname(__FILE__) . '/form.php');
require_once ('../bhl_names.php');
require_once ('../bhl_text.php');
require_once ('../bhl_viewer.php');
require_once ('../cites.php');
require_once ('../identifier.php');
require_once ('../reference.php');
require_once ('../geocoding.php');
require_once ('../nomenclator.php');
require_once ('../user.php');

require_once (dirname(__FILE__) . '/tagtree/paths.php');

require_once ('../specimens.php');


//--------------------------------------------------------------------------------------------------
class DisplayReference extends DisplayObject
{
	public $specimens = array();
	public $localities = array();
	public $page = 0;
	public $taxon_names = NULL;
	public $in_bhl = false;
	public $refresh = false;
	
	//----------------------------------------------------------------------------------------------
	function GetId()
	{
		if (isset($_GET['id']))
		{
			$this->id = $_GET['id'];
		}
		if (isset($_GET['page']))
		{
			$this->page = $_GET['page'];
		}
		if (isset($_GET['callback']))
		{
			$this->callback = $_GET['callback'];
		}
		if (isset($_GET['refresh']))
		{
			$this->refresh = true;
		}
	}	
	
	//----------------------------------------------------------------------------------------------
	function GetFormat()
	{
		if (isset($_GET['format']))
		{
			switch ($_GET['format'])
			{
				case 'bib':
					$this->format = 'bib';
					break;
					
				case 'bibjson':
					$this->format = 'bibjson';
					break;
					
				case 'citeproc':
					$this->format = 'citeproc';
					break;	
					
				case 'jats':
					$this->format = 'jats';
					break;						

				case 'json':
					$this->format = 'json';
					break;

				case 'pdf':
					$this->format = 'pdf';
					break;
					
				case 'ris':
					$this->format = 'ris';
					break;					

				case 'rss':
					$this->format = 'rss';
					break;
			
				case 'text':
					$this->format = 'text';
					break;										
					
				case 'xml':
					$this->format = 'xml';
					break;					

				case 'wikispecies':
					$this->format = 'wikispecies';
					break;					

				case 'wikipedia':
					$this->format = 'wikipedia';
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

			case 'text':
				$this->DisplayText();
				break;

			case 'ris':
				$this->DisplayRis();
				break;

			case 'bib':
				$this->DisplayBibtex();
				break;
				
			case 'pdf':
				$this->DisplayPdf();
				break;

			case 'rss':
				$this->DisplayRSS();
				break;

			case 'json':
				$this->DisplayJSON();
				break;

			case 'bibjson':
				$this->DisplayBibJSON();
				break;

			case 'wikispecies':
				$this->DisplayWikispecies();
				break;

			case 'wikipedia':
				$this->DisplayWikipedia();
				break;
				
			case 'citeproc':
				$this->DisplayCiteproc();
				break;
				
			case 'jats':
				$this->DisplayJats();
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
		
		echo reference_to_meta_tags($this->object);
		
		echo html_include_link('application/rss+xml', 'RSS2.0', 'reference/' . $this->id . '.rss', 'alternate');

		// Canonical link
		//echo html_include_link('', '', 'reference/' . $this->id, 'canonical');
		
		echo '<link rel="canonical" href="http://biostor.org/reference/' . $this->id . '" />' . "\n";
		
		echo html_include_css('css/viewer.css');
		echo html_include_script('js/fadeup.js');
		echo html_include_script('js/prototype.js');
		echo html_include_script('js/lazierLoad.js'); // not working for some reason...
		echo html_include_script('js/viewer.js');
		
		// Google +1
		//echo html_include_script('http://apis.google.com/js/plusone.js');
		
		// Recaptcha
		echo html_include_script('http://api.recaptcha.net/js/recaptcha_ajax.js');

		// Tag tree for names
		$this->taxon_names = bhl_names_in_reference($this->id);
		if ($this->taxon_names != NULL)
		{
			$tags = '';
			foreach ($this->taxon_names->names as $name) 
			{
				$tags .= $name['namestring'] . '|' . $name['NameBankID'] . "\\n";
			}
		
			echo  '<script type="text/javascript">' . "\n";
			echo "function make_tag_tree()
			{
			var success	= function(t){tagtreeComplete(t);}
			var failure	= function(t){tagtreeFailed(t);}
		
			var url = '" . $config['web_root'] . "tagtree/tags2tree.php';
			var pars = 'tags='+ '" . $tags . "';
//			pars += '&url=display_name.php?id%3D'
			pars += '&url=name/'
			var myAjax = new Ajax.Request(url, {method:'post', postBody:pars, onSuccess:success, onFailure:failure});
			}
			
function tagtreeComplete(t)
{
	var s = t.responseText;
	
	$('taxon_names').innerHTML = s;
}

function tagtreeFailed(t)
{
}			
			";
			echo  '</script>' . "\n";
		}
			
		// Form validation
		echo  '<script type="text/javascript">' . "\n";
		echo 'var check_issn = /^[0-9]{4}\-[0-9]{3}([0-9]|X)$/;' . "\n";
		echo 'var check_date = /^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/;' . "\n";
		echo 'var check_year = /^[0-9]{4}$/;' . "\n";
		
		echo  '</script>' . "\n";
		
			
		// Form editing
		echo  '<script type="text/javascript">' . "\n";
		echo '
		
function SetSC(id, sc) {
	// Textarea.
	var obj = $(id);
	obj.value += sc;
	obj.focus();
}		
		
		
function reportErrors(errors)
{
 var msg = "The form contains errors...\n";
 for (var i = 0; i<errors.length; i++) {
 var numError = i + 1;
  msg += "\n" + numError + ". " + errors[i];
}
 alert(msg);
}		
		
function store(form_id, page_id)
{
	var form = $(form_id);
	
	// validate
	var issn = form.issn.value;
	var date = form.date.value;
	var year = form.year.value;

	var secondary_title = form.secondary_title.value;

	var errors = [];
 
 	if (secondary_title == "")  
 	{
  		errors[errors.length] = "Please supply a journal name";
 	}	

 	if ((issn != "") && !check_issn.test(issn)) 
 	{
  		errors[errors.length] = "ISSN " + "\"" + issn + "\" is not valid";
 	}	
 	if ((date != "") && !check_date.test(date)) 
 	{
  		errors[errors.length] = "Date must be in form \"YYYY-MM-DD\"";
 	}	
 	if ((year != "") && !check_year.test(year)) 
 	{
  		errors[errors.length] = "Year \"" + year + "\" is not valid";
 	}	

	if (errors.length > 0)
	{
		reportErrors(errors);';
 
	 if (user_is_logged_in())
 	{
 	}
 	else
 	{
 		echo '
		Recaptcha.create("' . $config['recaptcha_publickey'] . '",
			"recaptcha_div", {
			theme: "clean",
			callback: Recaptcha.focus_response_field
		});';
	}
	echo '
		return false;
	}
		
	//alert($(form).serialize());
	
	// Update database
	var success	= function(t){updateSuccess(t);}
	var failure	= function(t){updateFailure(t);}
	
	var url = "' . $config['web_root'] . 'update.php";
	var pars = $(form).serialize() + "&PageID=" + page_id + "&update=true";
	
	//alert (page_id);
	var myAjax = new Ajax.Request(url, {method:"post", postBody:pars, onSuccess:success, onFailure:failure});
}

function updateSuccess (t)
{
	var s = t.responseText.evalJSON();
	//alert(t.responseText);
	//alert("hi");
	if (s.is_valid)
	{
		// we\'ve updated metadata, so reload page (or do ajax calls, but reload is easier)
		window.location.reload(true);
	}
	else
	{
		// User did not pass recaptcha so refresh it
		Recaptcha.create("' . $config['recaptcha_publickey'] . '",
			"recaptcha_div", {
			theme: "clean",
			callback: Recaptcha.focus_response_field
		});
		//fadeUp($(metadata_form),255,255,153);
	}
}
function updateFailure (t)
{
	alert("Failed: " + t);
}

';

// Based on http://ne0phyte.com/blog/2008/09/02/javascript-keypress-event/
// and http://blog.evandavey.com/2008/02/how-to-capture-return-key-from-field.html
// I want to capture enter key press in recaptcha to avoid submitting the form (user must click
// on button for that). We listen for keypress and eat it. Note that we attach the listener after
// the window has loaded.
echo 'function onMyTextKeypress(event)
{
	if (Event.KEY_RETURN == event.keyCode) 
	{
		// do something usefull
		//alert(\'Enter key was pressed.\');
		
		Event.stop(event);
	}
	return;
}
Event.observe(window, \'load\', function() {
	Event.observe(\'recaptcha_response_field\', \'keypress\', onMyTextKeypress);
});';

		echo  '</script>';
		
		// If we have point localities then we need a map
		if (count($this->localities) != 0)
		{
			echo html_include_script('http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $config['gmap']);
			echo html_include_script('js/gmap.js');
			echo  '<script type="text/javascript">' . "\n";
			
			echo '
				function initialize() 
				{
				  if (GBrowserIsCompatible()) 
				  {
						var map = new GMap2(document.getElementById("map_canvas"));
						map.setCenter(new GLatLng(0, -0), 1);
						map.addControl(new GSmallMapControl());
						map.addControl(new GOverviewMapControl());
						map.addControl(new GMapTypeControl());        
						map.addMapType(G_PHYSICAL_MAP);
						map.setMapType(G_PHYSICAL_MAP);
						';
			
			echo '// Bounding box to contain points, from http://www.svennerberg.com/2008/11/bounding-box-in-google-maps/ ' . "\n";
			echo '// see especially comment by Aiska http://www.svennerberg.com/2008/11/bounding-box-in-google-maps/#comment-1546 ' . "\n";

			echo 'var bounds = new GLatLngBounds();' . "\n";
			
			$n = min(10, count($this->localities));
			for ($i = 0; $i < $n; $i++)
			{
				echo 'var latlng = new GLatLng(';
				echo $this->localities[$i]->latitude;
				echo ',';
				echo $this->localities[$i]->longitude;
				echo ');' . "\n";
				echo 'map.addOverlay(createMarker(latlng), \'\',';
								
				if ($this->localities[$i]->name != '')
				{
					echo "'" . $this->localities[$i] . "'";
				}
				else
				{
					echo "'" . format_decimal_latlon($this->localities[$i]->latitude, $this->localities[$i]->longitude) . "'";
				}
				echo ');' . "\n";
				
				echo 'bounds.extend(latlng);' . "\n";	
			}
			/*
			foreach ($this->localities as $loc)
			{
				echo 'var latlng = new GLatLng(';
				echo $loc->latitude;
				echo ',';
				echo $loc->longitude;
				echo ');' . "\n";
				echo 'map.addOverlay(createMarker(latlng), \'\',';
								
				if ($loc->name != '')
				{
					echo "'" . $loc->name . "'";
				}
				else
				{
					echo "'" . format_decimal_latlon($loc->latitude, $loc->longitude) . "'";
				}
				echo ');' . "\n";
				
				echo 'bounds.extend(latlng);' . "\n";
				
			}
			*/
			echo ' 	}' . "\n";
			
			echo '// Center map in the center of the bounding box' . "\n";
			echo '// and calculate the appropriate zoom level' . "\n"; 
			echo 'map.setCenter(bounds.getCenter(), map.getBoundsZoomLevel(bounds));' . "\n";

			
			echo '}' . "\n";
			echo  '</script>' . "\n";
		}
	}
	
	//----------------------------------------------------------------------------------------------
	function DisplayBodyOpen()
	{
		if ((count($this->localities) != 0) && ($this->id != 140852))
		{
			// Load Google Maps
			echo html_body_open(
				array(
					'onload' => 'initialize()',
					'onunload' => 'GUnload()'
					)
				);
		}
		else
		{
			echo html_body_open();
		}
	}	
	
	//----------------------------------------------------------------------------------------------
	function DisplayEditForm()
	{
		$html = reference_form($this->object, !user_is_logged_in());
		return $html;
	}

	//----------------------------------------------------------------------------------------------
	function DisplayHtmlContent()
	{
		global $config;
		
		log_access($this->id, 'html');

		echo html_page_header(true, '', 'name');
		
		// Embed first page of OCR text, added 2011-12-07
		if ($this->in_bhl)
		{
			$pages = bhl_retrieve_reference_pages($this->id);
			$page_ids = array($pages[0]->PageID);
			$text = bhl_fetch_text_for_pages($page_ids);
			$text = str_replace ('\n', '' , $text);
			$text = str_replace ('- ', '-' , $text);
			$text = str_replace ('- ', '-' , $text);
			
			echo "\n<!-- First page of OCR text -->\n";
			echo '<div style="display:none;">' . "\n";
			echo htmlentities($text, ENT_COMPAT, "UTF-8");
			echo '</div>' . "\n";
		}
		
		
		echo '<div style="float:right;background-color:rgb(230,242,250);padding:6px">' . "\n";
		echo '<h2>Identifiers</h2>' . "\n";
		echo '<ul class="guid-list">' . "\n";
			
		echo '<li class="permalink">' . '<div itemscope itemtype="http://schema.org/ScholarlyArticle">' . '<a href="' . $config['web_root'] . 'reference/' . $this->id . '" title="Permalink">' . $config['web_root'] . 'reference/' . $this->id . '</a>' . '</div>' . '</li>' . "\n";	
		if ($this->in_bhl)
		{
//			echo '<li class="bhl"><a href="http://www.biodiversitylibrary.org/page/' . $this->object->PageID . '" target="_new" title="BHL page"  onClick="_gaq.push([\'_trackEvent\', \'Outbound Links\', \'bhl\', \'' . $this->object->PageID . '\', 0]);">' .  $this->object->PageID . '</a></li>' . "\n";
			echo '<li class="bhl"><a href="http://www.biodiversitylibrary.org/page/' . $this->object->PageID . '" target="_new" title="BHL page">' .  $this->object->PageID . '</a></li>' . "\n";
		}
		
		if (isset($this->object->doi))
		{
			//echo '<li class="doi"><a href="http://dx.doi.org/' . $this->object->doi . '" target="_new" title="DOI" onClick="_gaq.push([\'_trackEvent\', \'Outbound Links\', \'doi\', \'' . $this->object->doi . '\', 0]);">' .  $this->object->doi . '</a></li>' . "\n";
			echo '<li class="doi"><a href="http://dx.doi.org/' . $this->object->doi . '" target="_new" title="DOI">' .  $this->object->doi . '</a></li>' . "\n";
		}
		if (isset($this->object->url))
		{
			echo '<li class="url"><a href="' . $this->object->url . '" target="_new" title="URL">' .  trim_string($this->object->url, 30) . '</a></li>' . "\n";
		}
		if (isset($this->object->pdf))
		{
			echo '<li class="pdf"><a href="' . $this->object->pdf . '" target="_new" title="PDF">' .  trim_string($this->object->pdf, 30) . '</a></li>' . "\n";
		}
		if (isset($this->object->hdl))
		{
			echo '<li class="handle"><a href="http://hdl.handle.net/' . $this->object->hdl . '" target="_new" title="Handle">' .  $this->object->hdl . '</a></li>' . "\n";
		}
		if (isset($this->object->lsid))
		{
			echo '<li class="lsid"><a href="' . $config['web_root'] . $this->object->lsid . '" title="LSID">' . $this->object->lsid . '</a></li>' . "\n";
		}
		if (isset($this->object->pmid))
		{
			echo '<li class="pmid"><a href="http://www.ncbi.nlm.nih.gov/pubmed/' . $this->object->pmid . '" target="_new" title="PMID" >' . $this->object->pmid . '</a></li>' . "\n";
		}
		echo '</ul>' . "\n";

		echo '<h2>Export</h2>' . "\n";
		echo '<ul class="export-list">' . "\n";

		// Mendeley
		//echo '<li class="mendeley"><a href="http://www.mendeley.com/import/?url=' . urlencode($config['web_root'] . 'reference/' . $this->id) . '" title="Add to Mendeley" target="_new" onClick="_gaq.push([\'_trackEvent\', \'Export\', \'Mendeley\', \'' . $this->id . '\', 0]);">Mendeley</a></li>';
		echo '<li class="mendeley"><a href="http://www.mendeley.com/import/?url=' . urlencode($config['web_root'] . 'reference/' . $this->id) . '" title="Add to Mendeley" target="_new" >Mendeley</a></li>';
		
		/*
		if ($this->in_bhl)
		{
			echo '<li class="pdf"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.pdf" title="PDF" onClick="_gaq.push([\'_trackEvent\', \'Export\', \'pdf\', \'' . $this->id . '\', 0]);">PDF</a></li>';
		}
		*/
		/*
		echo '<li class="xml"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.xml" title="Endnote XML" target="_new" onClick="_gaq.push([\'_trackEvent\', \'Export\', \'Endnote\', \'' . $this->id . '\', 0]);">Endnote XML</a></li>';
		echo '<li class="ris"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.ris" title="RIS" target="_new" onClick="_gaq.push([\'_trackEvent\', \'Export\', \'RIS\', \'' . $this->id . '\', 0]);">Reference manager</a></li>';		
		echo '<li class="bibtex"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.bib" title="BibTex" target="_new" onClick="_gaq.push([\'_trackEvent\', \'Export\', \'bibtex\', \'' . $this->id . '\', 0]);">BibTex</a></li>';	
		echo '<li class="bibjson"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.bibjson" title="BibJSON" target="_new" onClick="_gaq.push([\'_trackEvent\', \'Export\', \'bibjson\', \'' . $this->id . '\', 0]);">BibJSON</a></li>';	
		echo '<li class="wikipedia"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.wikipedia" title="Wikipedia" target="_new" onClick="_gaq.push([\'_trackEvent\', \'Export\', \'Wikipedia\', \'' . $this->id . '\', 0]);">Wikipedia</a></li>';	
		*/
		echo '<li class="xml"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.xml" title="Endnote XML" target="_new" >Endnote XML</a></li>';
		echo '<li class="ris"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.ris" title="RIS" target="_new" >Reference manager</a></li>';		
		echo '<li class="bibtex"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.bib" title="BibTex" target="_new" >BibTex</a></li>';	
		echo '<li class="bibjson"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.bibjson" title="BibJSON" target="_new" >BibJSON</a></li>';	
		echo '<li class="wikipedia"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.wikipedia" title="Wikipedia" target="_new" >Wikipedia</a></li>';	
		
		if ($this->in_bhl)
		{
			echo '<li class="text"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.text" title="Text" >Text</a></li>';
		}
		echo '</ul>' . "\n";


		echo '</div>' . "\n";
		
		//------------------------------------------------------------------------------------------
		echo '<div itemscope itemtype="http://schema.org/ScholarlyArticle">';


		echo '<h1>' . '<div itemprop="name">' . $this->GetTitle() . '</div>' . '</h1>' . "\n";
		
		//------------------------------------------------------------------------------------------
		// Authors
		echo '<div>' . "\n";
		$count = 0;
		$num_authors = count($this->object->authors);
		if ($num_authors > 0)
		{
			foreach ($this->object->authors as $author)
			{
				echo '<a href="' . $config['web_root'] . 'author/' . $author->id . '">';
				echo $author->forename . ' ' . $author->lastname;
				if (isset($author->suffix))
				{
					echo ' ' . $author->suffix;
				}
				echo '</a>';
				$count++;
				if ($count < $num_authors -1)
				{
					echo ', ';
				}
				else if ($count < $num_authors)
				{
					echo ' and ';
				}
				
			}
		}
		echo "\n" . '</div>' . "\n";
		
		
		//------------------------------------------------------------------------------------------
		// Metadata and COinS
		echo '<div>' . "\n";
		echo '<div itemprop="description">';
		echo '<span class="journal">';
		
		// Various options for linking journal.
		if (isset($this->object->issn))
		{
			echo '<a href="' . $config['web_root'] . 'issn/' . $this->object->issn . '">';
			echo $this->object->secondary_title;
			echo '</a>';
		}
		elseif (isset($this->object->oclc))
		{
			echo '<a href="' . $config['web_root'] . 'oclc/' . $this->object->oclc . '">';
			echo $this->object->secondary_title;
			echo '</a>';
		}
		else		
		{
			echo $this->object->secondary_title;
		}
		echo '</span>';
		echo ' ';
		if (isset($this->object->series))
		{
			echo ' <span class="volume">(' . $this->object->series . ') </span>';
		}
		echo '<span class="volume">' . $this->object->volume . '</span>';
		if (isset($this->object->issue))
		{
			echo '<span class="issue">' . '(' . $this->object->issue . ')' . '</span>';
		}		
		echo ':';
		echo ' ';
		echo '<span class="pages">' . $this->object->spage . '</span>';
		if (isset($this->object->epage))
		{
			echo '<span class="pages">' . '-' . $this->object->epage . '</span>';
		}
		if (isset($this->object->year))
		{
			echo ' ';
			echo '<span class="year">' . '(' . $this->object->year . ')' . '</span>';
		}
		echo reference_to_coins($this->object);
		echo '</div>' . "\n";
		echo '</div>' . "\n";
		
		echo '</div>'; // schema
		
		//------------------------------------------------------------------------------------------
		// When record added and updated
		echo '<p class="explanation">Reference added ';
		echo distanceOfTimeInWords(strtotime($this->object->created) ,time(),true);
		echo ' ago';		
		echo '</p>' . "\n";
		
		//------------------------------------------------------------------------------------------
		// Social bookmarking
//		echo '<g:plusone size="tall"></g:plusone>';
		echo '<a href="http://twitter.com/share" class="twitter-share-button" data-count="vertical" data-via="rdmpage" data-related="biostor_org">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
		echo '&nbsp';
		echo '<a href="//www.pinterest.com/pin/create/button/" data-pin-do="buttonBookmark" ><img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_gray_20.png" /></a>';

		//------------------------------------------------------------------------------------------
		// Export options
/*		echo '<h2>Export</h2>' . "\n";
		echo '<div>' . "\n";
		echo '<span><a href="' . $config['web_root'] . 'reference/' . $this->id . '.xml" title="Endnote XML">Endnote XML</a></span>';
		echo ' | ';
		echo '<span><a href="' . $config['web_root'] . 'reference/' . $this->id . '.ris" title="RIS">Reference manager</a></span>';		
		echo ' | ';
		echo '<span><a href="' . $config['web_root'] . 'reference/' . $this->id . '.bib" title="BibTex">BibTex</a></span>';	
		
		if ($this->in_bhl)
		{
			echo ' | ';
			echo '<span><a href="' . $config['web_root'] . 'reference/' . $this->id . '.text" title="Text">Text</a></span>';
		}
		echo '</div>' . "\n";
*/		
		
		//------------------------------------------------------------------------------------------
		// Identifiers
/*		echo '<h2>Identifiers</h2>' . "\n";
		echo '<ul>' . "\n";
		if ($this->in_bhl)
		{
			// BHL reference
			echo '<li><a href="http://www.biodiversitylibrary.org/page/' . $this->object->PageID . '" target="_new">BHL PageID:' . $this->object->PageID . '</a></li>' . "\n";
		}
		
		if (isset($this->object->sici))
		{
			echo '<li><a href="' . $config['web_root'] . 'sici/' . $this->object->sici . '">' .  $this->object->sici . '</a></li>' . "\n";
		}
		if (isset($this->object->url))
		{
			echo '<li><a href="' . $this->object->url . '" target="_new">' .  $this->object->url . '</a></li>' . "\n";
		}
		if (isset($this->object->pdf))
		{
			echo '<li><a href="' . $this->object->pdf . '" target="_new">' .  $this->object->pdf . '</a></li>' . "\n";
		}
		if (isset($this->object->doi))
		{
			echo '<li><a href="http://dx.doi.org/' . $this->object->doi . '" target="_new">' .  $this->object->doi . '</a></li>' . "\n";
		}
		if (isset($this->object->hdl))
		{
			echo '<li><a href="http://hdl.handle.net/' . $this->object->hdl . '" target="_new">' .  $this->object->hdl . '</a></li>' . "\n";
		}
		if (isset($this->object->lsid))
		{
			echo '<li><a href="' . $config['web_root'] . $this->object->lsid . '">' . $this->object->lsid . '</a></li>' . "\n";
		}
		if (isset($this->object->pmid))
		{
			echo '<li><a href="http://www.ncbi.nlm.nih.gov/pubmed/' . $this->object->pmid . '" target="_new">' . $this->object->pmid . '</a></li>' . "\n";
		}
		echo '</ul>' . "\n";*/
		
		
		//------------------------------------------------------------------------------------------
		// Linking
		/*
		echo '<div>' . "\n";
		echo '<span><a href="' . $config['web_root'] . 'reference/' . $this->id . '/backlinks" title="References">Cites (' . num_cites($this->id) . ')</a></span>';
		echo ' | ';
		echo '<span><a href="' . $config['web_root'] . 'reference/' . $this->id . '/forwardlinks" title="Forward links">Cited by (' . num_cited_by($this->id) . ')</a></span>';
		echo '</div>' . "\n";
		*/
		
		
		//------------------------------------------------------------------------------------------
		if ($this->in_bhl)
		{
			//--------------------------------------------------------------------------------------
			echo '<h2>Viewer</h2>';
			echo '<p id="viewer_status"></p>' . "\n";
			echo '<table width="100%" >';
			echo '<tr  valign="top"><td>';
			echo bhl_reference_viewer($this->id, $this->page);
			echo '</td>';
			echo '<td>';
			
			echo $this->DisplayEditForm();
			
			echo '</td></tr>';
			echo '</table>';
			
			if (1)
			{
			if (0) // 0 to not display names
			{
				
				//--------------------------------------------------------------------------------------
				$tag_cloud = name_tag_cloud($this->taxon_names);
				if ($tag_cloud != '')
				{
					echo '<h2>Taxon name tag cloud</h2>';
					echo '<p class="explanation">Taxonomic names extracted from OCR text for document using uBio tools.</p>';
					echo $tag_cloud;
	
					echo '<h2>Taxonomic classification</h2>';
					
					if ($config['use_gbif'])
					{
						echo '<p class="explanation">GBIF classification for taxonomic names in document</p>';					
					}
					else
					{
						echo '<p class="explanation">Catalogue of Life classification for taxonomic names in document</p>';
					}
					echo '<div id="taxon_names"></div>';
					
					echo  '<script type="text/javascript">make_tag_tree();</script>';
	
				}
			}
			
			//--------------------------------------------------------------------------------------
			if (count($this->localities) != 0)
			{
				echo '<h2>Localities</h2>';
				echo '<p class="explanation">Localities extracted from OCR text.</p>';
				echo '<div id="map_canvas" style="width: 600px; height: 300px"></div>';
			}


			//--------------------------------------------------------------------------------------
			if (count($this->specimens) != 0)
			{
				echo '<h2>Specimens</h2>';
				echo '<p class="explanation">Specimen codes extracted from OCR text.</p>';
				echo '<ul style="-moz-column-width: 13em; -webkit-column-width: 13em; -moz-column-gap: 1em; -webkit-column-gap: 1em;">';
				foreach ($this->specimens as $occurrence)
				{
					echo '<li';
					
					if (isset($occurrence->occurrenceID))
					{
						//echo $occurrence->occurrenceID;
						echo ' class="gbif"';
					}
					else
					{
						echo ' class="blank"';
					}

					
					echo '>';
					echo '<a href="specimen/' . rawurlencode($occurrence->code) . '">' . $occurrence->code . '</a>';
					
					
					echo '</li>';
				}
				
				echo '</ul>';
			}
			
			
		}
		}
		else
		{
			echo '<table width="100%" >';
			echo '<tr><td valign="top" width="600">';
			
			$have_content = false;
			
			// PDF displayed using Google Docs
			if (!$have_content)
			{
				// If we have a PDF display it using Google Docs Viewer http://docs.google.com/viewer
				if (isset($this->object->url))
				{
					if (preg_match('/\.pdf$/', $this->object->url))
					{
						echo '<iframe src="http://docs.google.com/viewer?url=';
						echo urlencode($this->object->url) . '&embedded=true" width="600" height="700" style="border: none;">' . "\n";
						echo '</iframe>' . "\n";
						
						$have_content = true;
					}
				}
			}
			
			if (!$have_content)
			{
				if (isset($this->object->abstract))
				{
					echo '<h3>Abstract</h3>' . "\n";
					echo '<div>' . $this->object->abstract . '</div>' . "\n";
					$have_content = true;
				}
			}
			
			if (!$have_content)
			{
				echo '<span>[No text or abstract to display]</span>';
			}
			
			
			echo '</td>';
			echo '<td>';
			
			echo $this->DisplayEditForm();
			
			echo '</td></tr>';
			echo '</table>';
		
		}
		
		/*
		//------------------------------------------------------------------------------------------
		// Nomenclature (experimental)
		$acts = acts_in_publication($this->id);
		if (count($acts) > 0)
		{
			echo '<h2>Names published</h2>' . "\n";
			echo '<p class="explanation">New names or combinations published in this reference.</p>' . "\n";
			echo '<table cellspacing="0" cellpadding="2">' . "\n";
			echo '<tr><th>Name</th><th>Identifier</th></tr>' . "\n";
			foreach ($acts as $tn)
			{
				echo '<tr>';
				echo '<td style="border-bottom:1px solid rgb(228,228,228);"><a href="' . $config['web_root'] . 'name/' . urlencode($tn->ToHTML()) . '">' . $tn->ToHTML() . '</td>';
				
				echo '<td style="border-bottom:1px solid rgb(228,228,228);">' . $tn->global_id . '</td>';
				
				echo '</tr>' . "\n";
			}
			echo '</table>' . "\n";
		}
		*/
		
	}
	
	//----------------------------------------------------------------------------------------------
	// JSON format
	function DisplayJson()
	{
		$j = reference_to_mendeley($this->object);
		
		// Array of BHL pages		
		$j->bhl_pages = array();
		$j->thumbnails = array();
		
		$count = 0;
		
		$pages = bhl_retrieve_reference_pages($this->id);
		foreach ($pages as $page)
		{
			$j->bhl_pages[] = (Integer)$page->PageID;
			
			// Store thumbnails of pages (just page 1 for now)
			if ($count == 0)
			{
				if (0)
				{
					$image = bhl_fetch_page_image($page->PageID);
					$file = @fopen($image->thumbnail->file_name, "r") or die("could't open file --\"$image->thumbnail->file_name\"");
					$img = fread($file, filesize($image->thumbnail->file_name));
					fclose($file);
				
					// to do: test for MIME type, don't assume it
				
					$base64 = chunk_split(base64_encode($img));
					$thumbnail = 'data:image/gif;base64,' . $base64;
				
					$j->thumbnails[] = $thumbnail;
				}
				else
				{
					$image = bhl_fetch_page_image($page->PageID);
					
					$img = get($image->thumbnail->url);
					$base64 = chunk_split(base64_encode($img));
					$thumbnail = 'data:image/jpeg;base64,' . $base64;
				
					$j->thumbnails[] = $thumbnail;					
					
					
				
				}
			}
			$count++;
		}
		
		$j->ia = new stdclass;
		$j->ia->pages = array();
		
		$ia_pages = bhl_retrieve_item_pages_for_reference($this->id);
		foreach ($ia_pages as $page)
		{
			$j->ia->pages[] = (Integer)$page->SequenceOrder;
		}
		//$j->ia_pages = $ia_pages;
		$j->ia->FileNamePrefix = preg_replace('/_\d+$/', '', $ia_pages[0]->FileNamePrefix);
		
		
		// don't do names..
		if (1)
		{
			// Names
			$nm = bhl_names_in_reference_by_page($this->id);
			$j->names = $nm->names;
			
			
			$ignore = false;
			
			if ($this->id == 140852)
			{
				$ignore = true;
			}
			
			if (!$ignore)
			{
				// Get majority rule taxon (what paper is about)
				$tags = array();
				foreach ($nm->names as $name)
				{
					$tags[] = $name->namestring;
				}
				
				$paths = get_paths($tags);
				$majority_rule = majority_rule_path($paths);
				$j->expanded = expand_path($majority_rule);
			}			
			
			/*
			// Nomenclatural acts
			$acts = acts_in_publication($this->id);
			if (count($acts) > 0)
			{
				$count = count($nm->names);
				foreach ($acts as $tn)
				{
					$name = $tn->nameComplete;
					
					// Zoobank crap
					$name = preg_replace('/ subsp\. /', ' ', $name);
					$name = preg_replace('/ var\. /', ' ', $name);
					
					// BHL might have missed this name
					if (!isset($nm->found[$name]))
					{
						$n = new stdclass;
						$n->namestring = $name;
						$n->identifiers = new stdclass;
						$n->pages = array();
						$j->names[] = $n;
						$nm->found[$name] = $count++;					
					}
	
					$index = $nm->found[$name];
					
					// ION
					if (preg_match('/urn:lsid:organismnames.com:name:(?<id>\d+)/', $tn->global_id, $m))
					{
						$j->names[$index]->identifiers->ion = (Integer)$m['id'];
					}
					// Zoobank
					if (preg_match('/urn:lsid:zoobank.org:act:(?<id>.*)/', $tn->global_id, $m))
					{
						$j->names[$index]->identifiers->zoobank = $m['id'];
					}
					// IPNI
					if (preg_match('/urn:lsid:ipni.org:names:(?<id>.*)/', $tn->global_id, $m))
					{
						$j->names[$index]->identifiers->ipni = $m['id'];
					}
					// Index Fungorum
					if (preg_match('/urn:lsid:indexfungorum.org:names(?<id>.*)/', $tn->global_id, $m))
					{
						$j->names[$index]->identifiers->indexfungorum = (Integer)$m['id'];
					}
				
				}
				
				//ksort($j->names);
			} */
		}
		
		
		
		// Output localities in text as array of features in GeoJSON format
/*		$j->featurecollection = new stdclass;
		$j->featurecollection->type = "FeatureCollection";
		$j->featurecollection->features = array();
		foreach ($this->localities as $loc)
		{
			$feature = new stdclass;
			$feature->type = "Feature";
			$feature->geometry = new stdclass;
			$feature->geometry->type = "Point";
			$feature->geometry->coordinates = array();
			$feature->geometry->coordinates[] = (Double)$loc->longitude;
			$feature->geometry->coordinates[] = (Double)$loc->latitude;
			
			$j->featurecollection->features[] = $feature;
		}
*/
        if (1)
        {
			if (count($this->localities) > 0)
			{
				$j->geometry = new stdclass;
				$j->geometry->type = "MultiPoint";
				$j->geometry->coordinates = array();
				foreach ($this->localities as $loc)
				{
					$j->geometry->coordinates[] = array((Double)$loc->longitude, (Double)$loc->latitude);
				}
			}
		}
		
		// ?
	
		header("Content-type: text/plain; charset=utf-8\n\n");
		if ($this->callback != '')
		{
			echo $this->callback . '(';
		}
		echo json_format(json_encode($j));
		if ($this->callback != '')
		{
			echo ')';
		}
	}
	
	//----------------------------------------------------------------------------------------------
	// JSON format
	function DisplayBibJson()
	{
		
		$j = reference_to_bibjson($this->object);
	
		header("Content-type: text/plain; charset=utf-8\n\n");
		if ($this->callback != '')
		{
			echo $this->callback . '(';
		}
		echo json_format(json_encode($j));
		if ($this->callback != '')
		{
			echo ')';
		}
	}
	

	//----------------------------------------------------------------------------------------------
	function DisplayRis()
	{
		log_access($this->id, 'ris');
	
		header("Content-type: text/plain; charset=utf-8\n\n");
		echo reference_to_ris($this->object);
	}
	
	//----------------------------------------------------------------------------------------------
	function DisplayBibtex()
	{
		log_access($this->id, 'bibtex');
	
		header("Content-type: application/x-bibtex; charset=utf-8\n\n");
		echo reference_to_bibtex($this->object);
	}	

	//----------------------------------------------------------------------------------------------
	// Endnote XML export format
	function DisplayXml()
	{
		log_access($this->id, 'xml');
	
		// Create XML document
		$doc = new DomDocument('1.0', 'UTF-8');
		$xml = $doc->appendChild($doc->createElement('xml'));

		// root element is <records>
		$records = $xml->appendChild($doc->createElement('records'));

		// add record for this reference
		reference_to_endnote_xml($this->object, $doc, $records);
		
		// Dump XML
		header("Content-type: text/xml; charset=utf-8\n\n");
		echo $doc->saveXML();
	}

	//----------------------------------------------------------------------------------------------
	// Wikispecies reference
	function DisplayWikispecies()
	{
		header("Content-type: text/plain; charset=utf-8\n\n");
		echo reference_to_wikispecies($this->object);
	}
	
	//----------------------------------------------------------------------------------------------
	// Wikipedia reference
	function DisplayWikipedia()
	{
		header("Content-type: text/plain; charset=utf-8\n\n");
		echo reference_to_wikipedia($this->object);
	}


	//----------------------------------------------------------------------------------------------
	// Wikipedia reference
	function DisplayCiteproc()
	{
		header("Content-type: text/plain; charset=utf-8\n\n");
		
		$citeproc_obj = reference_to_citeprocjs($this->object);
		
		// for some reason we need to convert to JSON and back for this to work!
		$json = json_encode($citeproc_obj);
		
		echo $json;
	}
	
	//----------------------------------------------------------------------------------------------
	// JATS XML
	function DisplayJats()
	{
		$xml  = reference_to_jats($this->object);
		
		header("Content-type: text/xml; charset=utf-8\n\n");
		echo $xml;
	}
	
	
	
	
	//----------------------------------------------------------------------------------------------
	function DisplayText()
	{
		log_access($this->id, 'text');
	
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
	function DisplayPdf()
	{
		log_access($this->id, 'pdf');
		pdf_get($this->id, $this->refresh);
	}	
	
	//----------------------------------------------------------------------------------------------
	// Endnote RSS media format of thumbnails
	function DisplayRSS()
	{
		global $config;
		
		log_access($this->id, 'rss');
				
		// Create XML document
		$feed = new DomDocument('1.0', 'UTF-8');
		
		$rss = $feed->createElement('rss');
		$rss->setAttribute('version', '2.0');
		$rss->setAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');
		$rss = $feed->appendChild($rss);
		
		// channel
		$channel = $feed->createElement('channel');
		$channel = $rss->appendChild($channel);
		
		$title = $channel->appendChild($feed->createElement('title'));
		$title->appendChild($feed->createTextNode($this->GetTitle() ));

		$description = $channel->appendChild($feed->createElement('description'));
		$description->appendChild($feed->createTextNode("Page images from " . $this->GetTitle() ));

		$link = $channel->appendChild($feed->createElement('link'));
		$link->appendChild($feed->createTextNode($config['web_server'] . 'reference/' . $this->id ));
		
		$pages = bhl_retrieve_reference_pages($this->id);
		
		foreach ($pages as $page)
		{
			$image = bhl_fetch_page_image($page->PageID);
			
			$item = $channel->appendChild($feed->createElement('item'));
			
			$title = $item->appendChild($feed->createElement('title'));
			$title->appendChild($feed->createTextNode('Page ' . $page->PageID));
			
			$link = $item->appendChild($feed->createElement('link'));
			$link->appendChild($feed->createTextNode($image->url));
				
			$thumbnail = $item->appendChild($feed->createElement('media:thumbnail'));
			$thumbnail->setAttribute('url', $image->thumbnail->url);
	
			$content = $item->appendChild($feed->createElement('media:content'));
			$content->setAttribute('url', $image->url);
		}
			
		// Dump XML
		header("Content-type: text/xml; charset=utf-8\n\n");
		echo $feed->saveXML();
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
			$this->in_bhl = db_reference_from_bhl($this->id);
		}
								
		// Geocoding?
		
		
		if ($this->in_bhl)
		{
			if (!bhl_has_been_geocoded($this->id))
			{
				bhl_geocode_reference($this->id);
			}
			$this->localities = bhl_localities_for_reference($this->id);
		}
		
		
		
		// Specimens?
		if ($this->in_bhl)
		{
			if (!specimens_has_been_parsed($this->id))
			{
				specimens_from_reference($this->id);
			}
			$this->specimens = specimens_from_db($this->id);
		}
		
		
		
		return $this->object;
	} 

}

$d = new DisplayReference();
$d->Display();


?>