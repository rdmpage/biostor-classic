<?php

require_once ('../db.php');


$PageID = 27080430;

$reference_id = 26; 574; // 26; // 574;

if (isset($_GET['reference_id']))
{
	$reference_id = $_GET['reference_id'];
}

//echo $reference_id;	

//$ItemID = bhl_item_from_pageid($PageID);

$ItemID = bhl_item_from_reference_id($reference_id);


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
	
	<base href="http://biostor.org/" />
	
	<script src="js/jquery-1.9.1.js"></script>
	<script src="js/jquery-ui.js"></script>
	

	<style>
	#selectable .ui-selecting { background: #FECA40; }
	#selectable .ui-selected { background: #F39814; color: white; }
	#selectable { font-family:sans-serif;font-size:12px; }
	</style>
	<script>
	$(function() {
		$( "#selectable" ).selectable();
	});
	
	
	function save_pages()
	{
		var sql = 'DELETE FROM rdmp_reference_page_joiner WHERE reference_id=<REFERENCEID>;<br />';
		var page_order = 0;
		$('.ui-selected').each(function(i, obj) {
			if (obj.id)
			{
			    if (page_order == 0)
			    {
			    	sql += 'UPDATE rdmp_reference SET PageID=' + obj.id.replace(/pageid/, '') + ' WHERE reference_id=' + <REFERENCEID> + ';' + '<br />';
			    }
			
    			sql += 'INSERT INTO rdmp_reference_page_joiner(reference_id, PageID, page_order) VALUES (' 
    			+ <REFERENCEID> + ',' + obj.id.replace(/pageid/, '') + ',' + (page_order++) + ');' + '<br />';
    		}
		});
		$('#sql').html(sql);
	}
	
	function show_page(page_id)
	{
		$('#page').attr('src', 'bhl_image.php?PageID=' + page_id);
	}
	</script>
</head>
<body onload="$(window).resize();">
EOT;

	$template = str_replace('<REFERENCEID>', $reference_id, $template);

	echo $template;
	
	// Get reference 
	$reference = db_retrieve_reference($reference_id);
	
	echo '<div>';
	echo $reference->title;
	echo '</div>';

	echo '<div>';
	echo '<button>Revert</button> <button onclick="save_pages()">Save</button>';
	echo '</div>';
	
	echo '<div style="position:relative;border:1px dotted blue;">';

	echo '<div id="thumbnails" style="position:absolute;left:0px;top:0px;width:400px;border:1px solid blue;height:500px;overflow:scroll;">';

	echo '<div id="selectable">';
	foreach ($pages as $page)
	{
		echo '<div ';
		
		echo 'id="pageid' . $page->PageID . '" ';
		
		if (in_array($page->PageID, $article))
		{
			echo 'class="ui-selected" ';
		}
		
		echo 'style="margin:2px;float:left;width:auto;height:auto;border:1px solid red;"><img height="130" src="bhl_image.php?PageID=' . $page->PageID . '&thumbnail" />';
		
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
	
	echo '<div style="top:0px;left:450px;width:400px;position:absolute;border:1px solid black;">';
	echo '<img id="page" style="width:auto;" src="bhl_image.php?PageID=' . $reference->PageID . '" />';
	echo '</div>';
	
	echo '<div id="sql"style="font-size:10px;font-family:fixed;top:0px;left:850px;width:800px;position:absolute;border:1px solid black;"></div>';
	
	echo '</div>';
	
	echo '<script type="text/javascript">
			
		// http://stackoverflow.com/questions/6762564/setting-div-width-according-to-the-screen-size-of-user
		$(window).resize(function() { 
			var windowWidth = $(window).width();
			var windowHeight =$(window).height() -10;
			$(\'#thumbnails\').css({ \'height\':windowHeight });
			$(\'#page\').css({ \'height\':windowHeight });
		});
		
		</script>';
	
	echo '</body>
	</html>';

}



?>