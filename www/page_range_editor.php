<?php

require_once ('../db.php');
require_once ('html.php');
require_once ('../user.php');


$PageID = 27080430;

$reference_id = 26; 574; // 26; // 574;

if (isset($_GET['reference_id']))
{
	$reference_id = $_GET['reference_id'];
}

//echo $reference_id;	

//$ItemID = bhl_item_from_pageid($PageID);

$ItemID = bhl_item_from_reference_id($reference_id);

//echo $ItemID;

if ($ItemID != 0)
{
	$pages = bhl_retrieve_item_pages ($ItemID);
	
	$reference_pages = bhl_retrieve_reference_pages($reference_id);
	
	$article = array();
	foreach ($reference_pages as $page)
	{
		$article[] = $page->PageID;
	}
	
	//print_r($pages);
	
	$template = <<<EOT
<!doctype html>

<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Page Range Editor</title>
	
	<base href="." />
	
	<link rel="stylesheet" href="css/jquery-ui.css" />
	
	<script src="js/jquery-1.9.1.js"></script>
	<script src="js/jquery-ui.js"></script>
	
	<link rel="stylesheet" href="css/main.css" />
	
	<style>
	body {
		padding:20px;
		font-family: sans-serif;
	
	}
	
	button {
		font-size:1em;
	}
	
	
	
	
	#selectable .ui-selecting { background: #FECA40; }
	#selectable .ui-selected { background: #F39814; color: white; }
	#selectable { font-family:sans-serif;font-size:12px; }
	.ui-state-disabled { opacity:0.7; }
	
	
	
		.header
		{
			height: 100px;
			font-size: 24px;
			/* background-color:rgb(64,64,64); */
			/* color:white; */
		}
		
		.content
		{
			display: flex;
			flex: 1;
		}		
		
		.left
		{
			height: calc(100vh - 100px);
			overflow-y: auto;
			flex: 2 0 0;
			/* background-color:yellow; */
		}	
		
		.right
		{
			height: calc(100vh - 100px);
			flex: 1 0 0;
			/* background-color:cyan; */
		}			
	
	</style>
	
	
	
	
	<script>

	$(function() {
		$( "#selectable" ).selectable();
	});


	
	function save_pages(logged_in)
	{
		var data = {};
		data.reference_id = <REFERENCEID>;
		data.bhl_pages = [];
		
		var sql = 'DELETE FROM rdmp_reference_page_joiner WHERE reference_id=<REFERENCEID>;' + '\\n';
		var page_order = 0;
		$('.ui-selected').each(function(i, obj) {
			if (obj.id)
			{
			    if (page_order == 0)
			    {
			    	sql += 'UPDATE rdmp_reference SET PageID=' + obj.id.replace(/pageid/, '') + ' WHERE reference_id=' + <REFERENCEID> + ';' + '\\n';
			    }
			
    			sql += 'INSERT INTO rdmp_reference_page_joiner(reference_id, PageID, page_order) VALUES (' 
    			+ <REFERENCEID> + ',' + obj.id.replace(/pageid/, '') + ',' + (page_order++) + ');' + '\\n';
    			
    			data.bhl_pages.push(obj.id.replace(/pageid/, ''));
    		}
		});
		
		if (logged_in) {
		//if (0) {
		
			var url = 'savepages.php?json=' + encodeURIComponent(JSON.stringify(data)) + '&callback=?';
			
			 $.getJSON(url, function(d) {
			 	if (d) {
					if (d.ok) {
						alert('Saved!');
						location.reload();
					} else {
						alert('badness');			 	
					}
				} else {
					alert('much badness');
				}
			 });

		
				
		} else {			
			$('#sql').val(sql);		
			$( "#dialog" ).dialog( { minWidth: 400 } );
		}
		
		
	}
	
	function show_page(page_id)
	{
		$('#page').attr('src',"");		
		$('#page').attr('src', 'http://exeg5le.cloudimg.io/s/height/500/https://www.biodiversitylibrary.org/pagethumb/' + page_id + ',500,500');
		
	}
	
	function toggle_selectable(page_id) {
 		 // Get the checkbox
  		var checkBox = document.getElementById("myCheck");

	  if (checkBox.checked == true){
		$( "#selectable" ).selectable({
  			disabled: false
		});
	  } else {
		$( "#selectable" ).selectable({
  			disabled: true
		});
	  }	
		
	
	}
	
	
	</script>
</head>
<body onload="$(window).resize();">
EOT;

	echo '<div class="header">';
	echo html_page_header(true);

	$template = str_replace('<REFERENCEID>', $reference_id, $template);

	echo $template;
	
	// Get reference 
	$reference = db_retrieve_reference($reference_id);
	
	
	//echo '<img src="http://direct.biostor.org//images/biostor-shadow32x32.png" border="0" align="top">';
	//echo " ";
	echo '<b>';
	echo $reference->title;
	echo '</b>';
	
	echo ' [<a href="reference/' . $reference_id . '" target="_new">View article</a>]';
	
	echo '</div>';

	echo '<div>';
	echo '<input type="checkbox" id="myCheck" checked onclick="toggle_selectable()">Can edit ';
	echo '<button onclick="location.reload();">Refresh</button> <button onclick="save_pages(';
	
	if (user_is_logged_in())
	{
		echo 'true';
	}
	else
	{
		echo 'false';
	}
		
	echo ')">Save</button>';
	echo '</div>';

	echo '<div>';
	echo '<span> Click on a page to select it, <b>Cmd</b> click to select multiple pages, <b>Refresh</b> to reload current pages, <b>Save</b>';
	
	if (user_is_logged_in())
	{
		echo ' to save changes.</span>'; 
	}
	else
	{
		echo ' to show SQL to make changes.'; 
	}	
	
	echo 'To view individual pages toogle <b>Can edit</b> off and click on page numbers.';
	
	echo '</span>';
	
	echo '</div>'; // header
	
	echo '<div class="content">';
	
	//echo '<div style="position:relative;border:1px dotted rgb(192,192,192);">';

	//echo '<div id="thumbnails" style="position:absolute;left:0px;top:0px;width:40%;border:1px solid blue;height:450px;overflow:scroll;">';
	
	echo '<div id="thumbnails" style="overflow:scroll;" class="left">';

	echo '<div id="selectable">';
	foreach ($pages as $page)
	{
		echo '<div ';
		
		echo 'id="pageid' . $page->PageID . '" ';
		
		if (in_array($page->PageID, $article))
		{
			echo 'class="ui-selected" ';
		}
		echo 'style="margin:2px;float:left;width:auto;height:auto;border:1px solid red;">';
		
		echo '<img height="130" src="http://exeg5le.cloudimg.io/s/height/100/https://www.biodiversitylibrary.org/pagethumb/' . $page->PageID . ',60,60" />';

		if (isset($page->PageNumber))
		{
			echo '<div style="text-align:center" onclick="show_page(' . $page->PageID . ');">' . $page->PagePrefix . '&nbsp;' . $page->PageNumber . '</div>';
		}
		else
		{
			echo '<div style="text-align:center" onclick="show_page(' . $page->PageID . ');">' . $page->PageID . '</div>';
		}
		echo '</div>';
	}
	echo '</div>';
	echo '</div>';
	
	//echo '<div style="top:0px;left:400px;width:300px;position:absolute;">';
	
	echo '<div class="right">';
	
	//echo '<img id="page" style="width:auto;" src="bhl_image.php?PageID=' . $reference->PageID . '" />';
	echo '<img id="page" style="height:100%;" src="http://exeg5le.cloudimg.io/s/height/500/https://www.biodiversitylibrary.org/pagethumb/' . $reference->PageID . ',500,500" />';
	
	echo '</div>';
	
	// echo '<div id="sql"style="font-size:10px;font-family:fixed;top:0px;left:850px;width:800px;position:absolute;border:1px solid black;"></div>';
	
	echo '</div>';
	
	echo '<div style="display:none;" id="dialog" title="SQL">
	<p>This SQL will update the pages in the database.</p>
	<textarea rows="20" cols="50" id="sql" style="font-size:12px;font-family:monospace;"	></textarea>
	</div>';
 
	
	echo '<script type="text/javascript">
			
		// http://stackoverflow.com/questions/6762564/setting-div-width-according-to-the-screen-size-of-user
		$(window).resize(function() { 
		/*
			var windowWidth = $(window).width();
			var windowHeight =$(window).height() -10;
			$(\'#thumbnails\').css({ \'height\':windowHeight });
			$(\'#page\').css({ \'height\':windowHeight });
			*/
		});
		
		</script>';
		
	
	echo '</body>
	</html>';

}



?>