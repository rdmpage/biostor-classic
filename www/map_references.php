<?php


/**
 * @file map_references.php
 *
 *
 */
 
require_once('../db.php');

$xml = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns:xlink="http://www.w3.org/1999/xlink" 
xmlns="http://www.w3.org/2000/svg" 
width="360px" height="180px">
   <style type="text/css">
      <![CDATA[     
      .region 
      { 
        fill:blue; 
        opacity:0.4; 
        stroke:blue;
      }
      ]]>
   </style>
  <rect id="dot" x="-3" y="-3" width="6" height="6" style="stroke:black; stroke-width:1; fill:white"/>
 <image x="0" y="0" width="360" height="180" xlink:href="' . $config['webroot'] . 'images/mape.png"/>

 <g transform="translate(180,90) scale(1,-1)">';
 
 
$localities = db_retrieve_localities();

foreach ($localities as $loc)
{
	$xml .= '   <use xlink:href="#dot" transform="translate(' . $loc->longitude . ',' . $loc->latitude . ')" />';
}

$xml .= '
      </g>
	</svg>';
	
	
header("Content-type: image/svg+xml");

echo $xml;

?>

