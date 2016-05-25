<?php

// Add external DOIs to BioStor articles
require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/reference.php');

/*
SELECT * FROM rdmp_reference WHERE issn='1055-3177' AND doi IS NULL

*/

$issn = '1055-3177'; // Novon

$sql = "SELECT reference_id FROM rdmp_reference WHERE issn='" . $issn . "' AND doi IS NULL ORDER BY year LIMIT 1000";

$ids = array();

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$ids[] = $result->fields['reference_id'];
	
	$result->MoveNext();			
}

// Lookup

$update = array();

$sql = '';

foreach ($ids as $reference_id)
{
	$reference = db_retrieve_reference($reference_id);
	
	//print_r($reference);
	
	if (isset($reference->issn)
		&& isset($reference->volume)
		&& isset($reference->spage)
		)
	{
	
		$parameters = array(
			'pid' => 'r.page@bio.gla.ac.uk',
			'noredirect' => 'true',
			'format' => 'unixref',
			'issn' => $reference->issn,
			'volume' => $reference->volume,
			'spage' => $reference->spage
		);
	
		// CrossRef
	
		$url = 'http://www.crossref.org/openurl?' . http_build_query($parameters);
	
		//echo $url . "\n";
		
		$xml = get($url);
		
		if ($xml != '')
		{
			$dom= new DOMDocument;
			$dom->loadXML($xml);
			$xpath = new DOMXPath($dom);
			
			$xpath_query = '//journal_article[@publication_type="full_text"]/doi_data/doi';
			$nodeCollection = $xpath->query ($xpath_query);
		
			foreach($nodeCollection as $node)
			{
				$doi = $node->firstChild->nodeValue;
				
				$update[] = $reference_id;
				
				$sql .= 'UPDATE rdmp_reference SET doi="' . $doi . '" WHERE reference_id=' . $reference_id . ';' . "\n";
			}
			
		}
	}
}


echo join(",\n", $update);
echo "\n";

file_put_contents(dirname(__FILE__) . '/' . $issn . '.txt', join(",\n", $update));


echo $sql;
file_put_contents(dirname(__FILE__) . '/' . $issn . '.sql', $sql);
?>
