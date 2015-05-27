<?php

/**
 * @file journals.php
 *
 * Search 
 *
 */


require_once ('../config.inc.php');
require_once ('../db.php');
require_once (dirname(__FILE__) . '/html.php');

echo html_html_open();
echo html_head_open();
echo html_title ('Journals - BioStor');
echo html_head_close();
echo html_body_open();
echo html_page_header(false);	
echo '<h1>Journals</h1>';

	global $db;
	
	$journal_titles = array();
	
	// Journals for which we have articles
	$sql = 'SELECT secondary_title, issn, COUNT(reference_id) AS c
FROM rdmp_reference
WHERE (PageID <> 0)
GROUP BY issn
ORDER BY secondary_title';

	$journals = array();

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		if (!isset($journals[$result->fields['issn']]))
		{
			$journal = new stdclass;
			
			$journal->title = $result->fields['secondary_title'];
			$journal->issn = $result->fields['issn'];
			
			$journals[$result->fields['issn']] = $journal;
			
			$journal_titles[$journal->title] = $journal->issn;
		}
		$result->MoveNext();		
	}
	
	// Journals in BHL
	$sql = 'SELECT IdentifierValue, FullTitle FROM bhl_title_identifier
INNER JOIN bhl_title USING(TitleID)
WHERE (IdentifierName="ISSN") AND (IdentifierValue NOT LIKE "% (Internet)")';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		if (!isset($journals[$result->fields['IdentifierValue']]))
		{
			$journal = new stdclass;
			
			$journal->title = $result->fields['FullTitle'];
			$journal->issn = $result->fields['IdentifierValue'];
			
			$journals[$result->fields['IdentifierValue']] = $journal;

			$journal_titles[$journal->title] = $journal->issn;
		}
		$result->MoveNext();		
	}	
	
	ksort($journal_titles);

	$char = 'A';
	
	echo '<div>' . "\n";
	
	echo '<div style="clear:both;border-top:1px solid rgb(228,228,228);">' . "\n";
	echo '<h3><a name="' . $char . '">' . $char . '</a></h3>' . "\n";
	
	foreach ($journal_titles as $issn)
	{
		$journal = $journals[$issn];
		
		$first_char = mb_substr($journal->title, 0, 1, 'utf-8');
		if ($first_char != $char)
		{
			//echo $journal->title;
			echo '</div>';
			//echo '</li>';
			
			echo '<div style="clear:both;border-top:1px solid rgb(228,228,228);">' . "\n";
			$char = $first_char;
			echo '<h3><a name="' . $char . '">' . $char . '</a></h3>' . "\n";
		}
/*		echo '<li style="border-top:1px dotted rgb(128,128,128);">';
		echo '<a href="' . $config['web_root'] . 'issn/' . $result->fields['issn'] .'">' . $result->fields['secondary_title'] . '</a> [' . $result->fields['c'] . ']<br/>';
		echo '<span>' . $result->fields['issn'] . '</span><br/>';
		echo '<a href="' . $config['web_root'] . 'issn/' . $result->fields['issn'] .'"><img src="http://bioguid.info/issn/image.php?issn=' . $result->fields['issn']  . '" alt="cover" style="border:1px solid rgb(228,228,228);height:100px;" /></a>';
		echo '</li>';
*/

		echo '<div style="padding:4px;float:left;text-align:center;font-size:10px;height:200px;">' . "\n";
		echo '<a href="' . $config['web_root'] . 'issn/' . $journal->issn .'">' . "\n";
		echo '<img src="http://bioguid.info/issn/image.php?issn=' . $journal->issn . '" alt="cover" style="border:1px solid rgb(228,228,228);height:100px;" />' . "\n";
		echo '</a>' . "\n";
		echo '<p style="width:120px;">' . $journal->issn . '<br />';
		echo '<a href="' . $config['web_root'] . 'issn/' . $journal->issn .'">' . $journal->title . '</a>';
		echo '</p>'. "\n";
		
		echo '</div>' . "\n";
		
	}
	echo '</div>' . "\n";

echo html_body_close();
echo html_html_close();


?>