<?php

require_once('../db.php');
require_once ('../bhl_utilities.php');

$xml = 
'<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.1">
    <Document>
        <name>BioStor</name>
        <open>1</open>
        <description></description>';

$xml .= '<Style id="whiteBall">	       
<IconStyle>
	<Icon>
		<href>http://bioguid.info/images/whiteBall.png</href>
	</Icon>
</IconStyle>
</Style>';


$sql = 'SELECT reference_id, title, secondary_title, volume, spage, epage, date, year, rdmp_reference.PageID AS first_page, latitude, longitude
FROM rdmp_reference
INNER JOIN rdmp_reference_page_joiner USING(reference_id)
INNER JOIN rdmp_locality_page_joiner ON rdmp_locality_page_joiner.PageID = rdmp_reference_page_joiner.PageID
INNER JOIN rdmp_locality USING(locality_id)
WHERE locality_id <> 0';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql . $db->ErrorMsg());

while (!$result->EOF) 
{	
	$xml .= '<Placemark>';
	$xml .= '<styleUrl>#whiteBall</styleUrl>';
	
	$xml .= '<TimeStamp>';
	$xml .= '<when>' . $result->fields['date'] . '</when>';
	$xml .= '</TimeStamp>';
	
	$xml .= '<name>' . $result->fields['reference_id'] . '</name>';
	$xml .= '<description>';	
	$xml .= '<strong>' . str_replace('&', '&amp;', $result->fields['title'])  . '</strong><br/>';
	$xml .= '<i>' . $result->fields['secondary_title'] . '</i> ' . $result->fields['volume'] . ': ' . $result->fields['spage'] . '-' . $result->fields['epage'] .' (' . $result->fields['year'] . ')<br/>';
	
	$xml .= '<a href="http://biostor.org/reference/' . $result->fields['reference_id'] . '">http://biostor.org/reference/' . $result->fields['reference_id'] . '</a><br/>';

	$image = bhl_fetch_page_image($result->fields['first_page']);
	$xml .= '<img src="' . $image->thumbnail->url . '" width="' . $image->thumbnail->width . '" height="' . $image->thumbnail->height . '"/><br/>';	
	
	$xml .= '</description>';
	$xml .= '<Point><extrude>0</extrude><altitudeMode>absolute</altitudeMode>';
	$xml .= '<coordinates>';
	$xml .= $result->fields['longitude'] . ',' . $result->fields['latitude'];
	$xml .= '</coordinates>';
	
	
	$xml .= '</Point>
	</Placemark>';
	$result->MoveNext();
}

$xml .= '</Document>
</kml>';

	
header('Content-type: application/vnd.google-earth.kml+xml');
header("Content-Disposition: attachment; filename=biostor.kml");

echo $xml;

?>
