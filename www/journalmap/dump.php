<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/db.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/reference.php');


function get_geotagged_articles()
{
	global $db;
	
	$ids = array();
	
	$sql = 'SELECT DISTINCT reference_id 
	FROM rdmp_reference_page_joiner
	INNER JOIN rdmp_locality_page_joiner USING(PageID)
	WHERE locality_id <> 0
	LIMIT 100';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{	
		$ids[] = $result->fields['reference_id'];
		$result->MoveNext();		
	}
	
	
	return $ids;
	
	
}


$ids = get_geotagged_articles();



$html = '<html>
<head>
<title>Geotagged items</title>
</head>
<body style="font-family:sans-serif;">';

$html .= '<h1>Geotagged BioStor articles</h1>';

$html .= '<table cellspacing="2">';

$html .= '<tr><th><i>n</i></th><th>Article (click for BibJSON + GeoJSON)</th><th>View on Biostor</th></tr>';

foreach ($ids as $reference_id)
{
	$reference = db_retrieve_reference ($reference_id);
	
	
	$j = reference_to_bibjson($reference);
	
	$json = json_format(json_encode($j));
	
	$json_filename = $reference_id . ".json";
	file_put_contents($json_filename, $json);
	
	if (isset($reference->title))
	{
		$title = $reference->title;
	}
	else
	{
		$title = $reference_id;
	}
	
	
	
	$html .= '<tr><td align="right">' . count($reference->localities) . '</td><td>' . ' <a href="' . 	 $reference_id . '.json">' .  $title . '</a></td><td align="center"><a href="http://biostor.org/reference/' . $reference_id . '" target="_new">Show</a></td.</tr>' . "\n";
	
	
	
	
}

$html .= '</table>';


$html .= '</body>
</html>';

file_put_contents("index.html", $html);



?>