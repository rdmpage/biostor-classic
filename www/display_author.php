<?php

/**
 * @file display_author.php
 *
 * Display information about a person
 *
 * 12/01/2010 13:43 use Simile Exhibit code http://www.simile-widgets.org/exhibit/ to provide faceted
 * browsing of author publications.
 *
 */

require_once (dirname(__FILE__) . '/display_object.php');
require_once (dirname(__FILE__) . '/sparklines.php');

//--------------------------------------------------------------------------------------------------
class DisplayAuthor extends DisplayObject
{
	public $name = '';
	
	//----------------------------------------------------------------------------------------------
	function GetId()
	{
		if (isset($_GET['id']))
		{
			$this->id = $_GET['id'];
		}
		if (isset($_GET['name']))
		{
			$this->name = $_GET['name'];
		}
	}	

	//----------------------------------------------------------------------------------------------
	// Extra <HEAD> items
	function DisplayHtmlHead()
	{
		// My original coauthor widget
		//echo html_include_script('js/coauthors.js');
		
		// Exhibit
		echo html_include_link('application/json','','exhibit_author.php?id=' . $this->id, 'exhibit/data');
//		echo html_include_script('http://static.simile.mit.edu/exhibit/api-2.0/exhibit-api.js');

		// From email from Ted Benson Ted Benson <eob@csail.mit.edu>
		echo html_include_script('http://trunk.simile-widgets.org/ajax/api/simile-ajax-api.js');
		echo html_include_script('http://trunk.simile-widgets.org/exhibit/api/exhibit-api.js?log=true');
	}

	//----------------------------------------------------------------------------------------------
	function DisplayHtmlContent()
	{
		global $config;
		
		echo html_page_header(true, '', 'author');
		
		echo '<h1>' . $this->GetTitle() . '</h1>';
		
		// Name variations
		$names = db_get_all_author_names($this->id);
		if (count($names) > 1)
		{
/*			echo '<h2>Name variants</h2>'; 
			echo '<ul>';
			foreach ($names as $name)
			{
				echo '<li><a href="' . $config['web_root'] . 'author/' . $name['author_id'] . '">' . $name['name'] . '</a></li>';
			}
			echo '</ul>';
			echo '<h2>Name variants</h2>'; 
			echo '<ul>';
*/
			$count = 0;
			echo '<p>(';
			foreach ($names as $name)
			{
				if ($count++ > 0) { echo ', '; };
				echo '<a href="' . $config['web_root'] . 'author/' . $name['author_id'] . '">' . $name['name'] . '</a>';
			}
			echo ')</p>';
		}
		
		echo '<a href="http://twitter.com/share" class="twitter-share-button" data-count="vertical" data-via="rdmpage" data-related="biostor_org">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';

		
		echo '<div>';
		echo '<img src="' . sparkline_author_articles($this->id) . '" alt="sparkline" align="top"/>';
		echo '</div>';
		
		echo '<div>';
		
		// Ideally make this SVG, but then we have to deal with clicks on nodes going to embedded object,
		// not parent window (see issue with phylomash project)
		if (0)
		{
			$graph_url = $config['web_root'] . 'cgi-bin/neato.cgi/' .  $config['web_root'] . 'coauthor/' . $this->id . '.svg';
			
			echo '
			<!--[if IE]>
			<embed width="360" height="180" src="' . $graph_url. '">
			</embed>
			<![endif]-->
			<![if !IE]>
			<object id="mysvg" type="image/svg+xml" width="360" height="180" data="' . $graph_url. '">
			<p>Error, browser must support "SVG"</p>
			</object>
			<![endif]>';	
		}
		else
		{
			/* Turn off
			$graph_url = $config['web_root'] . 'cgi-bin/neato.cgi/' .  $config['web_root'] . 'coauthor/' . $this->id . '.png';
			echo '<img src="' . $graph_url . '" alt="graph" align="top"/>';
			*/
		}
		echo '</div>';

		// My original code
		/*
		// List of papers authored
		echo '<h2>Publications</h2>';
		$refs = db_retrieve_authored_references($this->id);
		echo '<ul>';
		foreach($refs as $reference_id)
		{
			$reference = db_retrieve_reference ($reference_id);
			echo '<li><a href="' . $config['web_root'] . 'reference/' . $reference_id . '">' . $reference->title . '</a></li>';
		}
		echo '</ul>';

		// Timeline 
		echo '<h2>Publication Timeline</h2>';
		echo '<div>';
		$timeline = db_retrieve_author_timeline($this->id);
		
		$max_count = 0;
		foreach ($timeline as $k => $v)
		{
			$max_count = max ($max_count, $v);
		}
		
		// CSS display from http://www.alistapart.com/articles/accessibledatavisualization/
		echo '<ul class="timeline">';
		foreach ($timeline as $k => $v)
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
		echo '</div>';
		//print_r($timeline);
		
		// Coauthorships (LinkedIn-style)
		$coauthors = db_retrieve_coauthors($this->id);
		//print_r($coauthors);
		echo '<h2>Coauthors</h2>';
		echo '<div>';
		echo '<div id="contact_index" style="float:right;padding:6px;text-align:center;"></div>';
		echo '<div id="contact_list" style="overflow:auto;height:400px;border:1px solid rgb(190,190,190);"></div>';
		echo '</div>';
		
		echo '<script type="text/javascript">' . "\n" . 'display_coauthors(\'' .  json_encode($coauthors) . '\');</script>' . "\n";
		*/
		
		// Exhibit
		
		echo '<h2>Publications</h2>';
		
		echo '
    <table width="100%">
        <tr valign="top">
            <td ex:role="viewPanel">
                <div ex:role="view">                
				  <table ex:role="lens" class="reference">
					   <tr>
						   <td><img style="border:1px solid rgb(192,192,192);" ex:src-content=".imageURL" width="40"/></td>
						   <td>
								<div>
								<a ex:href-content=".uri">
							   <span ex:content=".label"></span></a></div>
							   <div>
								   <span ex:content=".citation"></span>
							   </div>
						   </td>
					   </tr>
				   </table>                                
                </div>
            </td>
            <td width="25%">
               <!-- browsing controls here... -->
                <div ex:role="facet" ex:facetClass="TextSearch" ex:facetLabel="Search within results"></div>
				<div ex:role="facet" ex:expression=".type" ex:facetLabel="Reference type"></div>
				<div ex:role="facet" ex:expression=".journal" ex:facetLabel="Journal"></div>
 				<div ex:role="facet" ex:expression=".year" ex:facetLabel="Year"></div>
 				<div ex:role="facet" ex:expression=".coauthors" ex:facetLabel="Coauthors"></div>
            </td>
        </tr>
    </table>';
		
	}
	
	//----------------------------------------------------------------------------------------------
	// hCard 
	function DisplayMicroformat()
	{
		echo '<div style="visibility:hidden;height:0px;">';	
		echo '<div class="vcard">';
		echo '<span class="fn n">';
		echo '<span class="given-name">' . $this->object->forename . '</span>';
		echo '&nbsp;';
		echo '<span class="family-name">' . $this->object->lastname . '</span>';
		echo '</span>';
		echo '</div>';
		echo '</div>';
	}

	//----------------------------------------------------------------------------------------------
	function GetTitle()
	{
		return $this->object->forename . ' ' . $this->object->lastname;
	}

	//----------------------------------------------------------------------------------------------
	function Retrieve()
	{
		$this->object = db_retrieve_author ($this->id);
		return $this->object;
	} 

}

$d = new DisplayAuthor();
$d->Display();


?>