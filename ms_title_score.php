<?php

require_once('bhl_search.php');
require_once('bhl_text.php');

$refs = array();

$sql = 'SELECT reference_id, title, PageID FROM rdmp_reference WHERE PageID <> 0 AND(issn="0374-5481") LIMIT 1000';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

$hist = array();

while (!$result->EOF) 
{

	$s = bhl_score_page($result->fields['PageID'], $result->fields['title']);
	
	echo $result->fields['reference_id'] . "\t" . $s->score ;
	
	$cell = round($s->score * 10);
	
	echo "\t" . $cell;
	
	echo  "\n";
	
	//if ($cell < 2) exit();
	
	if (!isset($hist[$cell]))
	{
		$hist[$cell] = 0;
	}
	$hist[$cell]++;
	
	$result->MoveNext();
	
}
ksort($hist);
print_r($hist);

?>