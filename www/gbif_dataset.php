<?php

// get specimens from articles

require_once (dirname(dirname(__FILE__)) . '/db.php');
require_once (dirname(dirname(__FILE__)) . '/reference.php');
require_once (dirname(dirname(__FILE__)) . '/specimens.php');
require_once (dirname(__FILE__) . '/html.php');

$datasetID = 0;

if (isset($_GET['datasetID']))
{
	$datasetID = $_GET['datasetID'];
}

$sql = 'SELECT DISTINCT reference_id FROM rdmp_reference_specimen_joiner
INNER JOIN rdmp_specimen USING(occurrenceID)
WHERE datasetID = ' . $datasetID;

$ids = array();

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{	
	$ids[] = $result->fields['reference_id'];

	$result->MoveNext();		
}


header("Content-type: text/html; charset=utf-8\n\n");
echo html_html_open();
echo html_head_open();
echo html_title('GBIF dataset ' . $datasetID . ' - ' . $config['site_name']);
echo html_head_close();
echo html_body_open();

echo html_page_header(true);

$dataset = specimens_dataset($datasetID);

echo '<h1>' . count($ids) . ' references citing occurrences from GBIF dataset ' . $dataset->dataResourceName . ' (' . $dataset->providerName . ')'  . '</h1>';

echo '<p><a href="gbif_data.php">View all GBIF datasets in BioStor</a></p>';


echo '<p><a href="http://data.gbif.org/datasets/resource/' . $datasetID . '/" target="_new">View ' . $dataset->dataResourceName . ' dataset at GBIF</a></p>';

echo '<table cellspacing="0" cellpadding="2" width="100%">';

foreach ($ids as $reference_id)
{
	$reference = db_retrieve_reference ($reference_id);
	echo '<tr';
	
	if (in_array($reference_id, $act_refs))
	{
		echo ' style="background-color:#D8F3C9;"';
	}
	echo '>';
	
	if ($reference->PageID != 0)
	{
		$image = bhl_fetch_page_image($reference->PageID);
		$imageURL = $image->thumbnail->url;
	}
	else
	{
		// if it's an article we could use journal image
		$imageURL = 'http://bioguid.info/issn/image.php?issn=' . $reference->issn;
	}
	
	echo '<td valign="top"><img style="border:1px solid rgb(192,192,192);" src="' . $imageURL . '" width="40" />';
	
	echo '</td>';
	
	echo '<td valign="top">';
	
	
	echo '<a href="' . $config['web_root'] . 'reference/' . $reference_id . '">' . $reference->title . '</a><br/>';
	echo '<span>' . reference_authors_to_text_string($reference);
	if (isset($reference->year))
	{
		echo ' (' . $reference->year . ')';
	}
	echo ' ' . reference_to_citation_text_string($reference) . '</span>';
	echo ' ' . reference_to_coins($reference);
	
	if (0)
	{
		echo '<div>';
		echo bhl_pages_with_name_thumbnails($reference_id,$this->object->NameBankID);	
		echo '</div>';
	}
	echo '</td>';
	echo '</tr>';
}
echo '</table>';

echo html_body_close(true); // true to show Disqus comments
echo html_html_close();	


?>

