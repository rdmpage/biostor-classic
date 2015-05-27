<?php

// Dump all item articles that we have

/*

 SELECT DISTINCT ItemID INTO OUTFILE "/tmp/items.txt"
    -> FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\n'
    -> FROM rdmp_reference INNER JOIN page USING(PageID);
Query OK, 6243 rows affected (15 min 33.32 sec)


*/

require_once ('itemutilities.php');

$filename = "items.txt";
$file_handle = fopen($filename, "r");

$html = '<html>
<head>
<title>Items</title>
</head>
<body>';

$count = 0;


while (!feof($file_handle) && ($count < 10000000))
{
	$ItemID = trim(fgets($file_handle));
	
	$obj = articles_for_item($ItemID);	
	
	$json_filename = "items/$ItemID.json";
	file_put_contents($json_filename, json_format(json_encode($obj)));

	$html .= '<a href="' . 	$ItemID . '.json">' . $ItemID . '</a><br/>' . "\n";
	
	$count++;
}

$html .= '</body>
</html>';

file_put_contents("items/index.html", $html);


?>