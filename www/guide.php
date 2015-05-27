<?php

/**
 * @file guide.php
 *
 * Tutorial page
 *
 */
 
require_once ('../config.inc.php');
require_once (dirname(__FILE__) . '/html.php');


global $config;

header("Content-type: text/html; charset=utf-8\n\n");
echo html_html_open();
echo html_head_open();
echo html_title('Guide to using BioStor - ' . $config['site_name']);
echo html_head_close();
echo html_body_open();
echo html_page_header(false);

echo '<h1>Guide to using Biostor</h1>
<p>This page provides a guide to using BioStor to find a reference. At its heart BioStor is an OpenURL resolver, wich you can use either from within bibliographic software and web sites, or through BioStor\'s web interface.</p>

<h2>Using BioStor as a service</h2>
<p>You can use BioStor to find references from within <a href="endnote.php">EndNote</a> and <a href="zotero.php">Zotero</a>. If you use the Firefox web browser you could install the <a href="referrer.php">OpenURL Referrer add on</a>, which will add the same functionality to sites that support support COinS, such as <a href="mendeley.php">Mendeley</a>.</p>
<p>If you are using BioStor in this way, you can skip the next ahead to...</p>

<h2>Using the web interface</h2>
<p>BioStor\'s OpenURL resolver provides a <a href="openurl.php">query form</a> for entering the bibliographic details of the reference you are searching for. You can enter these details directly, or have BioStor parse the citation for you.</p>

<h3>Citation parser</h3>
<p>BioStor has a simple citation parser:</p>
<div style="text-align:center;"><img src="static/citation.png" alt="citation"/></div>
<div>
<p>If you paste a citation (such as the one below) and click on the <b>Parse</b> button, BioStor will attempt to parse it and, if successful, it will populate the bibliographic details form.</p>
<div style="text-align:center;"><textarea rows="4" cols="60" readonly="readonly">Voss, G. L. and W. G. Pearcy (1990) Deep-water octopods (Mollusca; Cephalopoda) of the northeastern Pacific. Proceedings of the California Academy of Sciences, 47(3): 47-94.</textarea></div>
</div>

<p>If the parser succeeds you will see a tick beside the <b>Parse</b> button:</p>
<div style="text-align:center;"><img src="static/citation_success.png" alt="success"/></div>


<h3>Find from bibliographic details</h3>
<p>Below the citation parser is a form where you can enter bibliographic details of the reference you are searching for:</p>

<div style="text-align:center;"><img src="static/details_populated.png" alt="populated"/></div>

<p>If you click on the <b>Find</b> button, BioStor will search for the reference.</p>';

echo '<h2>Search results</h2>
<p>If the reference already exists in BioStor then you will be taken to a page displaying information about that reference. If the reference doesn\'t already exist in BioStor, but BioStor has found it then you will see a display like this:</p>
<div style="text-align:center;"><img src="static/openurl_hit.png" alt="hit"/></div>

<p>The page displays one or more possible matches in the Biodiversity Heritage Library. If you have supplied the title of the reference you are looking for, BioStor will compute a "Title Score" based on how well the title matches the text on the corresponding page in the Biodiversity Heritage Library. A score of 1 is a perfect match. The search results also display a thumbnail of the Biodiversity Heritage Library page. You can click on the thumbnail to enlarge it.</p>
<p>If you want to accept the match you must first fill in the CAPTCHA, then click on the <b>Click here to accept this match</b> button. BioStor will then load this reference into its database. Note that if BioStor hasn\'t encountered references from journal before, it may take some time to display the reference while it downloads the page images from BHL.</p>


';



echo html_body_close();
echo html_html_close();	


?>