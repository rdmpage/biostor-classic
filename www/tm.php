<?php


require_once('../db.php');
require_once(dirname(__FILE__) . '/treemap.php');

function num_documents($id)
{
	global $db;
	
	$sql = 'SELECT * FROM col_tree 
		WHERE col_tree.record_id = ' . $id . ' LIMIT 1';		
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);	

	$path = $result->fields['path'];
	
	$sql = 'SELECT COUNT(DISTINCT(reference_id)) AS c FROM rdmp_tree_index WHERE path';
	if ($result->fields['weight'] > 1)
	{
		$sql .= ' LIKE ' . $db->qstr($path . '/%');
	}
	else
	{
		$sql .= ' = ' . $db->qstr($path);
	}
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);	
	
	$count = 0;
	
	if ($result->NumRows() == 1)
	{
		$count = $result->fields['c'];
	}
	return $count;
}
	
	
function n2colour($n)
{
	$colour = 'ffffff';
	
	if ($n > 0)
	{
		$colour = 'ffebcc';
	}
	if ($n > 5)
	{
		$colour = 'ffdfaf';
	}
	if ($n > 10)
	{
		$colour = 'ffd492';
	}
	if ($n > 100)
	{
		$colour = 'ffc875';
	}
	if ($n > 500)
	{
		$colour = 'ffbc57';
	}
	if ($n > 1000)
	{
		$colour = 'ffb13a';
	}
	
	return $colour;
}

function main($node = 0, $width = 300, $height = 300, $callback='')
{
	global $db;
	
	// Create some data for us to plot
	$items = array();
	
	// 0 = life
	$subtree_root = $node;
	

	if ($node == 0)
	{
		$label = 'Life';
		$parent_id = 0;
		$path = "/";
	}
	else
	{
		// get parent and label for this node
		$sql = 'SELECT parent_id, path FROM col_tree 
			WHERE col_tree.record_id = ' . $subtree_root . ' LIMIT 1';		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);	

		$parent_id = $result->fields['parent_id'];
		$path = $result->fields['path'];
	
		$sql = 'SELECT `name` FROM taxa 
			WHERE record_id = ' . $subtree_root . ' LIMIT 1';		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);	

		$label = $result->fields['name'];
	}
	
	
	// Get children of this node
	$sql = 'SELECT * FROM col_tree 
		INNER JOIN taxa USING(record_id)
		WHERE col_tree.parent_id = ' . $subtree_root .
		' AND (col_tree.record_id <> 0)
		ORDER BY taxa.name';
		
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$c = num_documents($result->fields['record_id']);
/*		if ($c > 0)
		{
			$c = log10($c)+1;
		}*/
	
		$i = new Item(
			log10($result->fields['weight'] + 1), 
	//		$result->fields['weight'], 
			$result->fields['name'] . ' ' . $c, 
			$result->fields['record_id'],
			($result->fields['right_id'] - $result->fields['left_id'] == 1),
//			n2colour(floor(log10(num_documents($result->fields['record_id']) + 1)))
//			floor(log10(num_documents($result->fields['record_id'])+1)) 
			n2colour($c)
			);
		array_push($items, $i);
		$result->MoveNext();	
	}
	
	// Treemap bounds

	$tm = new TreeMap(0,0,$width,$height,$items);
	$tm->compute();	
	
	$obj = new stdclass;
	$obj->panels = $tm->drawme;
	$obj->width = $width;
	$obj->height = $height;
	$obj->id = $node;
	$obj->parent_id = $parent_id;
	$obj->label = $label;
	$obj->path = $path;
	$json = json_encode($obj);
	
	if ($callback != '')
	{
		echo $callback . '(';
	}
	echo $json;
	if ($callback != '')
	{
		echo ')';
	}
}

$node = 0;
$callback='';
$width = 300;
$height = 300;

if (isset($_GET['node']))
{
	$node = $_GET['node'];
}
if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}
if (isset($_GET['width']))
{
	$width = $_GET['width'];
}
if (isset($_GET['height']))
{
	$height = $_GET['height'];
}

main($node, $width, $height, $callback);

?>

