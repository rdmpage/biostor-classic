<?php

// get specimens from articles

require_once (dirname(__FILE__) . '/specimens.php');





$sql = 'SELECT * FROM rdmp_reference WHERE';

//$sql .=  ' issn="0015-0754"';

//$sql .= '  year > 1940';

//$sql .= '  reference_id =133199';
$sql .= '  reference_id = 3323';


$ids = array();

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{	
	$ids[] = $result->fields['reference_id'];

	$result->MoveNext();		
}

foreach ($ids as $reference_id)
{
	echo $reference_id . "\n";
	
	specimens_delete($reference_id);

	if (!specimens_has_been_parsed($reference_id))
	{
		specimens_from_reference($reference_id);
	}
	$specimens = specimens_from_db($reference_id);
	
	print_r($specimens);
}

?>

