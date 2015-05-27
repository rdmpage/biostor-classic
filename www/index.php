<?php

/**
 * @file index.php
 *
 * Home page
 *
 */
 
require_once ('../config.inc.php');
require_once ('../db.php');
require_once (dirname(__FILE__) . '/html.php');
require_once (dirname(__FILE__) . '/sparklines.php');


global $config;

header("Content-type: text/html; charset=utf-8\n\n");
echo html_html_open();
echo html_head_open();

echo '<meta name="google-site-verification" content="G0IJlAyehsKTOUGWSc-1V2RMtYQLnqXs440NUSxbYgA" />' . "\n";

echo '<META name="y_key" content="77957dfab6d01a40" />' . "\n";

//echo html_include_link('application/rdf+xml', 'RSS 1.0', 'rss.php?format=rss1', 'alternate');
echo html_include_link('application/atom+xml', 'ATOM', 'rss.php?format=atom', 'alternate');

echo html_title($config['site_name']);

echo html_include_script('js/prototype.js');

?>
<script type="text/javascript">

function treemap_mouse_over(id) 
{
	var e = document.getElementById(id);
//    	e.style.opacity= "1.0";
//	    e.style.filter = "alpha(opacity=100)";
	e.style.opacity=0.6;
   e.style.filter = "alpha(opacity=60)";

}

function treemap_mouse_out(id) 
{
	var e = document.getElementById(id);
//    	e.style.opacity=0.6;
//        e.style.filter = "alpha(opacity=60)";
	e.style.opacity=1.0;
	e.style.filter = "alpha(opacity=100)";
} 

function treemap_drill_down(id, width, height)
{
	url = 'http://biostor.org/tm.php';
	pars = 'node=' + id + '&width=' + width + '&height=' + height;
	
	var success	= function(t){redrawSuccess(t);}
	var failure	= function(t){redrawFailure(t);}

	var myAjax = new Ajax.Request( url, 
		{method: 'get', parameters: pars, onSuccess:success, onFailure:failure});
}

function redrawSuccess (t) 
{
	var s = t.responseText.evalJSON();
	tm(s);
}

function redrawFailure(t)
{
	alert("Failed: " + t);
}

function lineage_click(id, width, height)
{
	var h = $('node_' + id);
	
	if (id == 0)
	{
		h.innerHTML = '<span style="background-image: url(images/notlast16.gif);background-repeat: no-repeat;" onClick="lineage_click(0,400,400);">Life<\/span>';
	}
	else
	{
		h.remove();
	}
	
	treemap_drill_down(id, width,height);
}

function tm(obj) 
{
	// history
	
	// Find parent node and insert current node below it
	if (obj.id != 0)
	{
		var h = $('node_' + obj.parent_id);
		h.insert( '<div style="margin-left:8px;" id="node_' + obj.id + '"><span style="padding-left:8px;background-image: url(images/last16.gif);background-repeat: no-repeat;" onClick="lineage_click(\'' + obj.id + '\', \'' + obj.width + '\',\'' + obj.height + '\');">' + obj.label + '<\/span><\/div>');
	}
	
	var d = $('treemap');
	d.innerHTML= '';
	
	var html = '';
	
	for (i = 0; i < obj.panels.length; i++)
	{	
		html += '<div id="div' + obj.panels[i].id + '" class="cell"';
		html += 'style="position: absolute;overflow:hidden;text-align:center;';
//		html += 'background-color:rgb(242,242,242);';
		html += 'background-color:#' + obj.panels[i].colour + ';';
		html += ' left:' + obj.panels[i].bounds.x + 'px;';
		html +=' top:' + obj.panels[i].bounds.y + 'px;';
		html += ' width:' + obj.panels[i].bounds.w + 'px;';
		html += ' height:' + obj.panels[i].bounds.h + 'px;';
		html +=' border:2px solid rgb(228,228,228);';
		html += '" ';
		
    	html += ' onMouseOver="treemap_mouse_over(\'div' + obj.panels[i].id + '\');" ';
    	html += ' onMouseOut="treemap_mouse_out(\'div' + obj.panels[i].id + '\');" ';
    	
    	// Link to drill down
		if (!obj.panels[i].isLeaf)
		{
			html += ' onClick="treemap_drill_down(\'' + obj.panels[i].id + '\', \'' + obj.width + '\',\'' + obj.height + '\');"';
		}
		html += ' >';
		
		var tag = obj.panels[i].label; 
		
		var n = obj.panels[i].size;
		
		max_length = 0;
		var words = tag.split(' ');
		for (j = 0; j < words.length; j++)
		{
			max_length = Math.max(max_length, words[j].length);
		}
		font_height = obj.panels[i].bounds.w / max_length;
		font_height *= 1.2;
		font_height = Math.max(10, font_height);
		
		html +='<span style="font-size:' + font_height + 'px;">' + tag + '</span>';
		
		html +='</div>';
	}

	d.innerHTML = html;
 
}
</script>
<?php

//echo '<meta name="google-site-verification" content="G0IJlAyehsKTOUGWSc-1V2RMtYQLnqXs440NUSxbYgA" />';

echo html_head_close();
echo html_body_open();
echo html_page_header(true);


// How many pages?

// How many articles? [in BHL]
$sql = 'SELECT COUNT(reference_id) AS c FROM rdmp_reference WHERE (PageID <> 0)';
$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

$num_references = $result->fields['c'];


// How many authors?
$sql = 'SELECT COUNT(DISTINCT(author_id))	 AS c
FROM rdmp_author
INNER JOIN rdmp_author_reference_joiner USING(author_id)
INNER JOIN rdmp_reference USING(reference_id)
WHERE (lastname <> "") AND (forename <> "") AND (rdmp_reference.PageID <> 0)';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

$num_authors = $result->fields['c'];

// How many journals?
$sql = 'SELECT COUNT(DISTINCT(issn)) AS c FROM rdmp_reference WHERE (PageID <> 0)';


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

$num_journals = $result->fields['c'];

/*
// How many editors (IP)?
$sql = 'SELECT COUNT(DISTINCT(INET_NTOA(ip))) as c FROM rdmp_reference_version';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

$num_editors = $result->fields['c'];

*/
echo '<div style="float:right;padding:10px;width:300px;">' . "\n";

/*
echo '<a class="twitter-timeline" href="https://twitter.com/biostor_org" data-widget-id="377779191166431232">Tweets by @biostor_org</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';
*/
echo "<a class=\"twitter-timeline\" href=\"https://twitter.com/biostor_org\" data-widget-id=\"567310691699552256\">Tweets by @biostor_org</a> <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\"://platform.twitter.com/widgets.js\";fjs.parentNode.insertBefore(js,fjs);}}(document,\"script\",\"twitter-wjs\");</script>";
/*

echo "<script src=\"http://widgets.twimg.com/j/2/widget.js\"></script>
<script>
new TWTR.Widget({
  version: 2,
  type: 'search',
  search: 'biostor_org',
  interval: 6000,
  title: '',
  subject: 'BioStor on Twitter',
  width: 250,
  height: 300,
  theme: {
    shell: {
      background: '#8ec1da',
      color: '#ffffff'
    },
    tweets: {
      background: '#ffffff',
      color: '#444444',
      links: '#1985b5'
    }
  },
  features: {
    scrollbar: false,
    loop: true,
    live: true,
    hashtags: true,
    timestamp: true,
    avatars: true,
    toptweets: true,
    behavior: 'default'
  }
}).render().start();
</script>";
*/

echo '</div>' . "\n";

echo '<h1>What is BioStor?</h1>' . "\n";


echo '<p>BioStor provides tools for extracting, annotating, and visualising literature from the <a href="http://www.biodiversitylibrary.org/">Biodiversity Heritage Library</a> (and other sources). For background and further details please see:</p>' . "\n";

echo '<blockquote>Page, R. D. (2011). Extracting scientific articles from a large digital archive: BioStor and the Biodiversity Heritage Library. BMC Bioinformatics. Springer Science + Business Media. <a href="http://dx.doi.org/10.1186/1471-2105-12-187" target="_new">doi:10.1186/1471-2105-12-187</a></blockquote>';

/*
echo '<ul>';
echo '<li>Find references using <a href="openurl.php">Reference finder</a></li>';
echo '<li>Start browsing <a href="reference/1">references</a>, <a href="author/1">authors</a>, or <a href="name/2706186">taxon names</a></li>';
echo '</ul>';

echo '<h2>Finding references using BioStor</h2>
*/

echo '<p>The main purpose of BioStor is to find articles in the <a href="http://www.biodiversitylibrary.org/">Biodiversity Heritage Library</a>. To get started you can read the <a href="guide.php">guide to using BioStor</a>, or go directly to the <a href="openurl.php">Reference Finder</a>. You can also use BioStor to find references from within <a href="endnote.php">EndNote</a> and <a href="zotero.php">Zotero</a>. If you use the Firefox web browser you could install the <a href="referrer.php">OpenURL Referrer add on</a>, which will add the same functionality to sites that support support COinS, such as <a href="mendeley.php">Mendeley</a>.</p>
' . "\n";

echo '<p>BioStor is a project by <a href="http://iphylo.blogspot.com">Rod Page</a>. For data sources see <a href="credits.php">Credits</a>.</p>
' . "\n";

echo '<h1>Progress</h1>';

echo '<table cellpadding="4">' . "\n";
echo '<tr><td>References</td><td style="text-align:right;">';
echo $num_references .'</td></tr>' . "\n";
echo '<tr><td>Authors</td><td style="text-align:right;">' . $num_authors . '</td></tr>';
echo '<tr><td>Journals</td><td style="text-align:right;">' . $num_journals . '</td></tr>' . "\n";
echo '</table>';


echo '<p>Distribution of articles over time</p>' . "\n";
echo '<img src="' . sparkline_references('', '', 360,100) . '" alt="sparkline" align="top"/>' . "\n";


//echo '<img src="' . sparkline_cummulative_articles_added() . '" alt="sparkline" />' . "\n";

echo '
<h2>Examples</h2>
<table>
<tr><th>Reference</th><th>Feature reference demonstrates</th></tr>
<tr>
<td width="50%">Achille P Raselimanana, Christopher J Raxworthy and Ronald A Nussbaum (2000) A revision of the dwarf Zonosaurus Boulenger (Reptilia: Squamata: Cordylidae) from Madagascar, including descriptions of three new species Scientific Papers Natural History Museum University of Kansas 18, 1-16 <a href="reference/50335">50335</td>
<td>Automatically extracted map</td>
</tr>
<tr>
<td>Beiträge zur nähem naturhistorischen Kenntniß des Unterdonaukreises in Bayern,J Waltl, Isis von Oken 31: 250-261 (1838) <a href="reference/50357">50357</a></td>
<td>Single physical page has two columns of text, each of which has a separate page number, i.e., one page corresponds to two "pages" of text.</td>
</tr>
<tr>
<td>Description of a new species of subterraean isopod, W P Hay, 
Proceedings of the United States National Museum 21(1176): 871-872 (1921) <a href="reference/50287">50287</a></td>
<td>Haplophthalmus puteus n. sp. (not picked up by OCR)</td>
</tr>
</table>';

echo '<h1>Coverage</h1>';

/*echo '<h2>Taxonomic coverage</h2>';
echo '<p>Numbers of references for each taxonomic group, mapped on to the Catalogue of Life classification. Size of cells in TreeMap corresponds to number of species in Catalogue of Life, intensity of colour corresponds to number of references extracted form BHL. Click on cell to drill down, click on classification on left to step back up.</p>';



echo '<div style="width:600px;">

<div id="history" style="width:180px;float:left;">
<div id="node_0"><span onClick="lineage_click(0,400,400);">Life</span></div>
</div>

<div id="treemap" style="float:right;position:relative;font-family:Arial;font-size:10px;height:400px;width:400px;">
</div>

<div style="clear: both;"></div>

</div>';

echo '<script type="text/javascript" src="http://biostor.org/tm.php?node=0&width=400&height=400&callback=tm"></script>';

echo '<h2>Geography</h2>';
echo '<p>Localities extracted from articles (<a href="kml.php">click here</a> for Google Earth KML file).</p>';

echo '
<!--[if IE]>
<embed width="360" height="180" src="map_references.php">
</embed>
<![endif]-->
<![if !IE]>
<object id="mysvg" type="image/svg+xml" width="360" height="180" data="map_references.php">
<p>Error, browser must support "SVG"</p>
</object>
<![endif]>';

*/

/*

echo '<h2>Articles</h2>' . "\n";
*/

/*

// Journal summary (only if bioGUID is up


echo '<p>Number of articles per journal (<a href="journals.php">more...</a>). Get most recently added articles as a <a href="rss.php?format=atom">RSS feed</a>.</p>' . "\n";

	
$sql = 'SELECT secondary_title, issn, COUNT(reference_id) AS c
FROM rdmp_reference
WHERE PageID <>0
AND issn IS NOT NULL
GROUP BY issn
ORDER BY c DESC
LIMIT 5';

echo '<table border="0">' . "\n";
echo '<tr>';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	echo '<td valign="top" align="center">';
	echo '<div><a href="' . $config['web_root'] . 'issn/' . $result->fields['issn'] .'"><img src="http://bioguid.info/issn/image.php?issn=' . $result->fields['issn']  . '" alt="cover" style="border:1px solid rgb(228,228,228);height:100px;" /></a></div>';
	echo '<div><a href="' . $config['web_root'] . 'issn/' . $result->fields['issn'] .'">' . $result->fields['secondary_title'] . '</a></div>';
	echo $result->fields['c'] . '&nbsp;articles';
	echo '</td>';
	
	$result->MoveNext();		
}
echo '</tr>';

$sql = 'SELECT secondary_title, issn, COUNT(reference_id) AS c
FROM rdmp_reference
WHERE PageID <>0
AND (issn IS NOT NULL)
GROUP BY issn
ORDER BY c DESC
LIMIT 5,5';

echo '<tr>' . "\n";

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	echo '<td valign="top" align="center">';
	echo '<div><a href="' . $config['web_root'] . 'issn/' . $result->fields['issn'] .'"><img src="http://bioguid.info/issn/image.php?issn=' . $result->fields['issn']  . '" alt="cover" style="border:1px solid rgb(228,228,228);height:100px;" /></a></div>';
	echo '<div><a href="' . $config['web_root'] . 'issn/' . $result->fields['issn'] .'">' . $result->fields['secondary_title'] . '</a></div>';
	echo $result->fields['c'] . '&nbsp;articles';
	echo '</td>';
	
	$result->MoveNext();		
}
echo '</tr>' . "\n";
echo '</table>' . "\n";

*/
/*
echo '<h2>Authors</h2>';

// Most prolific authors...

$sql = 'SELECT COUNT(rdmp_reference.reference_id) AS c, rdmp_author.author_id, rdmp_author.forename, rdmp_author.lastname 
FROM rdmp_reference
INNER JOIN rdmp_author_reference_joiner USING (reference_id)
INNER JOIN rdmp_author USING (author_id)
GROUP BY (rdmp_author.author_cluster_id)
ORDER BY c DESC
LIMIT 10';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

echo '<p>Most prolific authors:</p>' . "\n";
echo '<ol>' . "\n";

while (!$result->EOF) 
{
	echo '<li>';
	echo '<a href="' . $config['web_root'] . 'author/' . $result->fields['author_id'] . '">' 
	. $result->fields['forename']  . ' ' .  $result->fields['lastname'] . '</a> ' 
	. $result->fields['c'] . ' articles';
	echo '</li>' . "\n";
	
	$result->MoveNext();		
}

echo '</ol>' . "\n";

*/

echo '<p>Some notable authors (images from Wikipedia)</p>';
echo '<table>';
echo '<tr>';

echo '<!-- Nathan Banks -->';
echo '<td>';
echo '<table>';
echo '<tr><td><a href="author/2234">Nathan Banks</a></td></tr>';
echo '<tr><td><img src="images/people/150px-Banks_NathanUSDA-SEL-AcariB.jpg" height="128" /></td></tr>';
echo '<tr><td><a href="http://en.wikipedia.org/wiki/Nathan_Banks">Wikipedia</a></td></tr>';
echo '</table>';
echo '</td>';

echo '<!-- Lipke_Holthuis -->';
echo '<td>';
echo '<table>';
echo '<tr><td><a href="author/489">Lipke Holthuis</a></td></tr>';
echo '<tr><td><div style="height:128px;width:100px;border:1px solid rgb(228,228,228);"><p/></div></td></tr>';
echo '<tr><td><a href="http://en.wikipedia.org/wiki/Lipke_Holthuis">Wikipedia</a></td></tr>';
echo '</table>';
echo '</td>';

echo '<!-- David_Starr_Jordan -->';
echo '<td>';
echo '<table>';
echo '<tr><td><a href="author/10203">David Starr Jordan</a></td></tr>';
echo '<tr><td><img src="images/people/File-Dsjordan.jpeg" height="128" /></td></tr>';
echo '<tr><td><a href="http://en.wikipedia.org/wiki/David_Starr_Jordan">Wikipedia</a></td></tr>';
echo '</table>';	
echo '</td>';



echo '<!-- Mary Rathbun -->';
echo '<td>';
echo '<table>';
echo '<tr><td><a href="author/1">Mary Rathbun</a></td></tr>';
echo '<tr><td><div style="height:128px;width:100px;border:1px solid rgb(228,228,228);"><p/></div></td></tr>';
echo '<tr><td><a href="http://en.wikipedia.org/wiki/Mary_Rathbun">Wikipedia</a></td></tr>';
echo '</table>';
echo '</td>';

echo '<!-- Hobart Smith -->';
echo '<td>';
echo '<table>';
echo '<tr><td><a href="author/830">Hobart M Smith</a></td></tr>';
echo '<tr><td><div style="height:128px;width:100px;border:1px solid rgb(228,228,228);"><p/></div></td></tr>';
echo '<tr><td><a href="http://en.wikipedia.org/wiki/Hobart_Muir_Smith">Wikipedia</a></td></tr>';
echo '</table>';
echo '</td>';

echo '<!-- James_Edward_Smith -->';
echo '<td>';
echo '<table>';
echo '<tr><td><a href="author/2701">James Edward Smith</a></td></tr>';
echo '<tr><td><img src="images/people/180px-James_Edward_Smith.jpg" height="128" /></td></tr>';
echo '<tr><td><a href="http://en.wikipedia.org/wiki/James_Edward_Smith">Wikipedia</a></td></tr>';
echo '</table>';
echo '</td>';



echo '</tr>';
echo '</table>';





echo '<hr />' . "\n";

echo '<div id="recentcomments" class="dsq-widget"><h2 class="dsq-widget-title">Recent Comments</h2><script type="text/javascript" src="http://disqus.com/forums/biostor/recent_comments_widget.js?num_items=5&hide_avatars=0&avatar_size=32&excerpt_length=200"></script></div><a href="http://disqus.com/">Powered by Disqus</a>';


echo html_body_close();
echo html_html_close();	


?>