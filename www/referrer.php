<?php

/**
 * @file referrer.php
 *
 * Explain how to use with Firefox
 *
 */
 
require_once ('../config.inc.php');
require_once (dirname(__FILE__) . '/html.php');

global $config;

header("Content-type: text/html; charset=utf-8\n\n");
echo html_html_open();
echo html_head_open();
echo html_title('OpenURL Referrer - ' . $config['site_name']);
echo html_head_close();
echo html_body_open();
echo html_page_header(false);

?>
<div style="float:right;padding:10px;"><img src="static/extensionItem.png" alt="extension logo"/></div>
<h1>Using BioStor with Firefox OpenURL Referrer Add-on</h1>

<p><a href="https://addons.mozilla.org/en-US/firefox/addon/4150">OpenURL Referrer</a> is a Firefox extension that converts bibliographic citations in the form of <a href="http://ocoins.info/">COinS</a> into URLs.</p>

<h2>Set up OpenURL linking</h2>
<p>Once you have installed OpenURL Referrer in Firefox, go to the <b>Tools</b> menu and choose the <b>Add-ons</b> command. This will display the list of installed Add-ons:</p>

<div style="text-align:center;"><img src="static/addons.png" width="400" alt="addons"/></div>

<p>Select <b>OpenURL Referrer</b> and click on the <b>Preferences</b> button to display the Preferences dialog box:</p>

<div style="text-align:center;"><img src="static/referrer_preferences.png" width="400" alt="preferences"/></div>

<p>Do the following:</p>

<ol>
<li>Click on <b>New Profile</b> and in the dialog box that appears set the profile name to "BioStor".</li>
<li>Set the <b>Link Server Base URL</b> to http://biostor.org/openurl.php</li>
<li>In the section <b>Display link as</b> either enter some text in the <b>Text</b> field, for example "BioStor", or enter the URL of an image in the <b>Image</b> field, for example "http://biostor.org/images/findbiostor.png". This image <img src="images/findbiostor.png" alt="find biostor" align="absmiddle"/> will then be displayed on web pages which contain COinS.</li>
</ol>


<h2>Use OpenURL linking</h2>

<p>When you visit a web page that contains COinS the OpenURL referrer will insert a link (labelled with the text or the image you selected above) into the web page. Clicking on that link will take you to BioStor's OpenURL resolver.</p>

<p>If your installation of OpenURL referrer worked, you should see a link immediately below this sentence:</p>

<p><span class="Z3988" title="ctx_ver=Z39.88-2004&amp;rft_val_fmt=info:ofi/fmt:kev:mtx:journal&amp;rft.aulast=Rathbun&amp;rft.aufirst=M+J+&amp;rft.au=M+J++Rathbun&amp;rft.atitle=Descriptions+of+new+decapod+crustaceans+from+the+west+coast+of+North+America&amp;rft.jtitle=Proceedings+of+the+United+States+National+Museum&amp;rft.issn=0096-3801&amp;rft.volume=24&amp;rft.spage=885&amp;rft.epage=905&amp;rft.date=1902&amp;rft.sici=0096-3801%281902%2924%3A1272%3C885%3ADONDCF%3E2.0.CO%3B2-1"></span></p>



<?php
echo html_body_close();
echo html_html_close();	
?>