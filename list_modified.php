<?php

require_once('db.php');


// get list of modified records

// Get last 50 references added to database
$sql = 'SELECT * FROM rdmp_reference
ORDER BY `updated` DESC
LIMIT 50';

$reference_ids = array();

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$reference_ids[] = $result->fields['reference_id'];
	
	$result->MoveNext();			
}


echo '$ids=array(' . "\n";

echo join(",\n", $reference_ids);

echo "\n);\n\n";

?>

