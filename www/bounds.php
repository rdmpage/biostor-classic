<?php

/**
 * @file bounds.php
 *
 * Retrieve set of references contained within a polygon, and return as JSON
 *
 */


require_once('../db.php');

$bounds = explode(",", $_GET['bounds']);

function references_in_polygon($mysql_polygon, &$items)
{
	global $db;
	
	$sql = 'SELECT DISTINCT(reference_id), title FROM rdmp_locality
	INNER JOIN rdmp_locality_page_joiner USING(locality_id)
	INNER JOIN rdmp_reference_page_joiner ON rdmp_locality_page_joiner.PageID = rdmp_reference_page_joiner.PageID
	INNER JOIN rdmp_reference USING(reference_id)
	WHERE Intersects(loc, GeomFromText(\'' . $mysql_polygon . '\'))
	LIMIT 20';
	
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
		while (!$result->EOF) 
		{
			$hit = new stdclass;
			$hit->id = $result->fields['reference_id'];
			$hit->title = $result->fields['title'];
			
			$items->list[] = $hit;
			$result->MoveNext();
		}
}

$items = new stdclass;
$items->list = array();

// Have we crossed the dateline?

if ($bounds[0] - $bounds[2] > 180)
{
	// two boxes	
	$polygon1 = 'POLYGON((' . $bounds[0] . ' ' . $bounds[1] 
		. ',' . $bounds[0] . ' ' . $bounds[3]
		. ',' . '180' . ' ' . $bounds[3]
		. ',' . '180' . ' ' . $bounds[1]
		. ',' . $bounds[0] . ' ' . $bounds[1]
		. '))';
	
	references_in_polygon($polygon1, $items);
	
	$polygon2 = 'POLYGON((' . '-180' . ' ' . $bounds[1] 
		. ',' . '-180' . ' ' . $bounds[3]
		. ',' . $bounds[2] . ' ' . $bounds[3]
		. ',' . $bounds[2] . ' ' . $bounds[1]
		. ',' . '-180' . ' ' . $bounds[1]
		. '))';
	
	references_in_polygon($polygon2, $items);
	
	
}
else
{
	
	
	$polygon = 'POLYGON((' . $bounds[0] . ' ' . $bounds[1] 
		. ',' . $bounds[0] . ' ' . $bounds[3]
		. ',' . $bounds[2] . ' ' . $bounds[3]
		. ',' . $bounds[2] . ' ' . $bounds[1]
		. ',' . $bounds[0] . ' ' . $bounds[1]
		. '))';
	
	//echo $polygon;
	
	
	references_in_polygon($polygon, $items);
}

echo json_encode($items);
?>