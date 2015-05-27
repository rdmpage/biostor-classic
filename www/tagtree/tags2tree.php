<?php

require_once(dirname(__FILE__) . '/paths.php');
require_once(dirname(__FILE__) . '/tree.php');


if (count($_POST) == 0)
{
	header("Content-type: text/html; charset=utf-8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Tag tree</title>
	<meta name="generator" content="BBEdit 9.0" />
</head>
<body>
<form action="tags2tree.php" method="post">
<textarea name="tags" rows="20" cols="40"></textarea><br/>
<!--<select name="format">
	<option value="html">HTML</option>
	<option value="json">JSON</option>
</select> -->
<input type="submit" value="Go">
</form>
</body>
</html>
<?php
}
else
{
	$debug = 0;
	
	// Extract tags
	$tags = explode ("\n", trim($_POST['tags']));
	
	$url = '';
	if (isset($_POST['url']))
	{
		$url = $_POST['url'];
	}
	
	$ids = array();
	
	// Clean up tags and extract identifier (delimited by "|")
	for ($i = 0; $i < count($tags); $i++)
	{
		$parts = explode("|", trim($tags[$i]));
		$tags[$i] = $parts[0];
		$ids[$tags[$i]] = $parts[1];
	}

	// Format	
	$format = 'html';
	if (isset($_POST['format']))
	{
		$format = $_POST['format'];
	}

	// Get paths in a given classification (in this case CoL2008)
	$paths = get_paths($tags);
	
	if ($debug)
	{
		echo '<pre>';
		echo "Raw paths for tags:\n";
		print_r($paths);
		echo '</pre>';
	}
	
	// What names haven't we found? Keep these in case user wants to see them
	$found = array_keys($paths);
	$not_found = array_diff ($tags, $found);

	if ($debug)
	{
		echo '<pre>';
		echo "Not found\n";
		print_r($not_found);
		echo "Found\n";
		print_r($found);
	}
	
	// Add to our list of nodes any intermediate nodes in the paths
	// For example, if we have a node for a mouse and for a human, make sure we have a node for
	// mammals
	$x = array();
	foreach ($paths as $label => $path)
	{
		$nodes = explode("/", $path);
		$n = count($nodes);
		$p = '';
		for ($i = 1; $i < $n; $i++)
		{
			$p .= '/' . $nodes[$i];
			array_push($x, $p);
		}
	}
	$x = array_unique($x);
	$paths = array_merge($paths, get_names($x));
	
	if ($debug)
	{
		echo '<pre>';
		echo "Paths for all tags and intermediate nodes:\n";
		print_r($paths);
		echo '</pre>';
	}	
	
	
	// Construct tree from paths
	$t = new Tree();
	$t->PathsToTree($paths);
		
	echo '<div>';
	echo $t->WriteTree($tags, $ids, $url);
	
	// Tags not found in classification
	if (count($not_found) != 0)
	{
		echo '<p><b>Tags not found</b></p>';
		echo '<ul>';
		foreach ($not_found as $f)
		{
//			echo '<li><a href="' . $config['web_root'] . $url . $ids[$f] . '">' . $f . '</a></li>';
			echo '<li><a href="' . $config['web_root'] . $url . $f . '">' . $f . '</a></li>';
		}
		echo '</ul>';
	}
	echo '</div>';



}

?>