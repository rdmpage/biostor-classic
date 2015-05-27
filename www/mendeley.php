<?php

/**
 * @file mendeley.php
 *
 * Explain how to use with Mendeley
 *
 */
 
require_once ('../config.inc.php');
require_once (dirname(__FILE__) . '/html.php');

global $config;

header("Content-type: text/html; charset=utf-8\n\n");
echo html_html_open();
echo html_head_open();
echo html_title('Mendeley - ' . $config['site_name']);
echo html_head_close();
echo html_body_open();
echo html_page_header(false);

?>
<div style="float:right;padding:10px;"><img src="static/mendeleybox_bigger.png"  width="48" alt="mendeley logo"/></div>
<h1>Using BioStor with Mendeley</h1>

<p><a href="http://www.mendeley.com/">Mendeley</a> is social software for managing research papers. Because BioStor supports <a href="http://ocoins.info/">COinS</a>, the <a href="http://www.mendeley.com/import/">Mendeley Web Importer</a> can import bibliographic data from BioStor pages. If you are using Firefox and install the <a href="referrer.php">OpenURL Referrer Add-on</a> then if a reference in Mendeley doesn't have a URL, Mendeley will display a link (labelled "BioStor") next to each reference. Clicking on that link will take you to BioStor's OpenURL resolver.</p>

<?php
echo html_body_close();
echo html_html_close();	
?>