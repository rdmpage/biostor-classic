<?php

/**
 * @file form.php
 *
 */
 
require_once ('../config.inc.php');

//--------------------------------------------------------------------------------------------------
// Hidden form used on openurl.php 
function reference_hidden_form($reference)
{
	$html = '<div style="display:none;">';
	
	$html .= '<textarea name="title" rows="5" cols="40">' . $reference->title . '</textarea>';
	// Authors
	$authors = '';
	foreach ($reference->authors as $author)
	{
		$authors .= $author->forename;
		$authors .= ' ' . $author->lastname;
		if (isset($author->suffix))
		{
			$authors .= ' ' .  $author->suffix;
		}
		$authors .= "\n";
	}
	$html .= '<textarea name="authors" rows="5" cols="40">' . trim($authors) . '</textarea>';
	
	foreach ($reference as $k => $v)
	{
		switch ($k)
		{
			case 'secondary_title':
				$html .= '<textarea name="' . $k . '" rows="2" cols="40">' . $v . '</textarea>';
				break;
		
			case 'series':
			case 'volume':
			case 'issue':
			case 'spage':
			case 'epage':
			case 'date':
			case 'year':
			case 'issn':
			case 'url':
			case 'doi':
			case 'lsid':
			case 'oclc':
				$html .= '<input type="text" name="' . $k . '" value="' . $v . '"></input>' . "\n";
				break;
				
			default:
				break;
		}
	}
	$html .= '</div>';
	
	return $html;
}

//--------------------------------------------------------------------------------------------------
//form for editing metadata
function reference_form($reference, $recaptcha = true)
{
	global $config;
	
	// field names
	$field_names = array(
		'issn' => 'ISSN',
		'oclc' => 'OCLC',
		'series' => 'Series',
		'volume' => 'Volume',
		'issue' => 'Issue',
		'spage' => 'Starting page',
		'epage' => 'Ending page',
		'date' => 'Date',
		'year' => 'Year',
		'url' => 'URL',
		'doi' => 'DOI',
		);
		
	//print_r($reference);
	
	$html = '';
	
	$html .= '<form id="metadata_form" action="#">' . "\n";
	$html .= '<table width="100%">' . "\n";
	
	// Reference type
	// for now
	$html .= '<tr><td></td><td><input type="hidden" name="genre" value="article" ></td></tr>' . "\n";

	// reference id
	$html .= '<tr><td></td><td><input type="hidden" name="reference_id" value="' . $reference->reference_id . '" ></td></tr>' . "\n";

/*	$html .= '<tr><td class="field_name">Type:</td><td>';
	$html .= '<select id="genre">';
		
	$html .= '<option value="book"';
	if ($reference->genre == 'book')
	{
		$html .= ' selected="selected"';
	}
	$html .= '>Book</option>';
		$html .= '<option value="chapter"';
	if ($reference->genre == 'chapter')
	{
		$html .= ' selected="selected"';
	}
	$html .= '>Book chapter</option>';
	$html .= '<option value="article"';
	if ($reference->genre == 'article')
	{
		$html .= ' selected="selected"';
	}
	$html .= '>Journal article</option>';
	$html .= '</select>';
	$html .= '</td></tr>'; */
	
	// Title
	$html .= '<tr><td class="field_name">Title</td><td><textarea id="title" name="title" rows="5" cols="40">' . $reference->title . '</textarea></td></tr>';
	
	$html .= '<tr><td></td><td>';
	
	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'á\');">á</span>';
	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'à\');">à</span>';
	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'å\');">å</span>';
	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'ä\');">ä</span>';

	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'ç\');">ç</span>';

	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'é\');">é</span>';
	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'è\');">è</span>';
	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'É\');">É</span>';

	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'ö\');">ö</span>';
	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'ø\');">ø</span>';

	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'ü\');">ü</span>';

	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'æ\');">æ</span>';
	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'œ\');">œ</span>';

	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'ß\');">ß</span>';

	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'—\');">—</span>';
	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'„\');">„</span>';
	$html .= '<span style="padding:4px;background-color:rgb(240,240,240);" onclick="SetSC(\'title\', \'‟\');">‟</span>';


	
	$html .= '</td></tr>';
	

	
	// Authors
	$authors = '';
	foreach ($reference->authors as $author)
	{
		$authors .= $author->forename;
		$authors .= ' ' . $author->lastname;
		if (isset($author->suffix))
		{
			$authors .= ' ' .  $author->suffix;
		}
		$authors .= "\n";
	}
	$html .= '<tr><td class="field_name">Authors</td><td><textarea name="authors" rows="5" cols="40">' . trim($authors) . '</textarea><br/><span style="font-size:12px;color:rgb(192,192,192);">One author per line, "First name Last name" or "Last name, First name"</span></td></tr>';

	$journal_fields = array('secondary_title', 'issn', 'oclc', 'series', 'volume', 'issue', 'spage', 'epage', 'date', 'year', 'url', 'doi');

	foreach ($journal_fields as $k)
	{
		switch ($k)
		{
			case 'secondary_title':
				$html .= '<tr><td class="field_name">' . 'Journal' . '</td><td><textarea name="' . $k . '" rows="2" cols="40">' . $reference->{$k} . '</textarea></td></tr>' . "\n";
				break;
		
			default:
				$html .= '<tr><td class="field_name">' . $field_names[$k] . '</td><td><input class="field_value" type="text" name="' . $k . '" value="';
				if (isset($reference->{$k}))
				{
					$html .= $reference->{$k};
				}
				$html .= '"';

				// Identifier fields need to be bigger
				if (($k == 'url') || ($k == 'doi'))
				{
					$html .= ' size="40"';
				}
				
				// Vital to suppress enter key in input boxes
				$html .= ' onkeypress="onMyTextKeypress(event);"></td></tr>' . "\n";
				break;
		}
	}
	
	// Recaptcha 
	if ($recaptcha)
	{
		$html .= '<tr><td></td><td>';
		$html .= '<script type="text/javascript">';
		$html .= 'var RecaptchaOptions = ';
		$html .= '{';
		$html .=     'theme: \'white\',';
		$html .=     'tabindex: 2';
		$html .=  '};';
		$html .= '</script>';
		$html .= '<div id="recaptcha_div">';
		$html .= '<script type="text/javascript" src="http://api.recaptcha.net/challenge?k=' . $config['recaptcha_publickey'] . '"></script>';
		$html .= '</div>';
		$html .= '</td></tr>';
	}

	$html .= '<tr><td></td><td><span style="padding:2px;cursor:pointer;background-color:#2D7BB2;color:white;font-size:18px;font-family:Arial;text-align:center;" onclick="store(\'metadata_form\', ' . $reference->PageID . ');">&nbsp;Update&nbsp;</span></td></tr>' . "\n";

	$html .= '</table>
</form>';

	return $html;
}

?>