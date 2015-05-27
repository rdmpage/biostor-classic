<?php

// list all GBIF datasets

require_once (dirname(dirname(__FILE__)) . '/db.php');
require_once (dirname(__FILE__) . '/html.php');



header("Content-type: text/html; charset=utf-8\n\n");
echo html_html_open();
echo html_head_open();
echo html_title('GBIF datasets  - ' . $config['site_name']);
echo html_head_close();
echo html_body_open();
echo html_page_header(true);

echo '<h1>GBIF datasets with occurrences in BioStor</h1>';

echo '<table width="50%" cellpadding="2">';

echo '<tr><th>Dataset</th><th>Count</th></tr>';

$sql = 'select count(occurrenceID) as c, datasetID, dataResourceName, providerName 
from rdmp_reference_specimen_joiner 
inner join rdmp_specimen using (occurrenceID) 
inner join rdmp_gbif_dataset USING(datasetID)
group by datasetID 
order by c DESC';

$sql = 'select count(occurrenceID) as c, datasetID
from rdmp_reference_specimen_joiner 
inner join rdmp_specimen using (occurrenceID) 
group by datasetID 
order by c DESC';


$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{	
	echo '<tr>';
	
//	echo '<td>' . '<a href="gbif_dataset.php?datasetID=' . $result->fields['datasetID']  . '">' . $result->fields['providerName'] . ':' . $result->fields['dataResourceName']  . '</a>' . '</td>';
	echo '<td>' . '<a href="gbif_dataset.php?datasetID=' . $result->fields['datasetID']  . '">' . $result->fields['datasetID']  . '</a>' . '</td>';

	echo '<td align="right">' . $result->fields['c']  . '</td>';

	echo '</tr>';

	$result->MoveNext();		
}
echo '</table>';

echo html_body_close(true); // true to show Disqus comments
echo html_html_close();	


?>

