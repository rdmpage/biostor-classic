<?php

/**
 * @file zotero.php
 *
 * Explain how to use with Zotero
 *
 */
 
require_once ('../config.inc.php');
require_once (dirname(__FILE__) . '/html.php');

global $config;

header("Content-type: text/html; charset=utf-8\n\n");
echo html_html_open();
echo html_head_open();
echo html_title('Zotero - ' . $config['site_name']);
echo html_head_close();
echo html_body_open();
echo html_page_header(false);

?>
<div style="float:right;padding:10px;"><img src="static/zotero_z_32px.png" alt="zotero logo"/></div>
<h1>Using BioStor with Zotero</h1>


<p><a href="http://www.zotero.org">Zotero</a> is a Firefox extension for collecting and managing bibliographic references. You can use Zotero to capture citations from BioStor, and you can also link to references in BioStor from within Zotero using OpenURL. Note that the instructions below assume that you are using version 2.0 or Zotero.</p>

<h2>Storing citations</h2>
<p>Zotero can detect when a BioStor page contains bibliograpic information, and you can import that information into you Zotero library in the normal way (see the Zotero page <a href="http://www.zotero.org/support/getting_stuff_into_your_library">Getting stuff into your library</a>).</p>

<h2>Set up OpenURL linking</h2>
<p>To enable OpenURL linking go the <b>Actions</b> button in Zotero and click 
on the <b>Preferences</b> command:</p>

<div style="text-align:center;"><img src="static/zotero_menu.png" width="200" alt="zotero menu"/></div>

<p>Click on the <b>Advanced</b> tab in the preferences dialog box, which should look something like this:</p>

<div style="text-align:center;"><img src="static/zotero_preferences.png" width="400" alt="zotero preferences"/></div>

<p>In the section labelled <b>OpenURL</b> set <b>Resolver</b> to http://biostor.org/openurl.php.</p>

<h2>Use OpenURL linking</h2>

<p>To use OpenURL linking within Zotero, view a reference and then click on the <b>Locate</b> button.</p>

<div style="text-align:center;"><img src="static/zotero_locate.png" alt="zotero locate"/></div>

<p>Zotero will display the result in your Firefox browser window.</p>



<?php
echo html_body_close();
echo html_html_close();	
?>