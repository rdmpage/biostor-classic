<?php

// tar -cvzf scripts.tar.gz *.php

require_once(dirname(__FILE__) . '/ris.php');
require_once(dirname(__FILE__) . '/utils.php');

$year_found = array();
$year_actual = array();
$year_roman = array();

$html = array();

function import($reference)
{	
	global $year_found;
	global $year_actual;
	global $year_roman;
	global $html;
	
	print_r($reference);
	
	if (!array_key_exists($reference->year, $year_actual))
	{
		$html[$reference->year] = '<html><body>';
		$html[$reference->year] .= '<h1>' . $reference->year . '</h1>';
	
		$year_actual[$reference->year] = 0;
		$year_found[$reference->year] = 0;
		$year_roman[$reference->year] = 0;
	}

	// Articles published this year
	$year_actual[$reference->year]++;
	
	if (is_numeric($reference->spage))
	{
		$openurl = reference2openurl($reference);
		
		//echo $openurl . "\n";
		
		$biostor_id = import_from_openurl($openurl);
					
		if ($biostor_id != 0)
		{
			$found = true;
			
			// Articles found this year
			$year_found[$reference->year]++;
			$reference->url = 'http://biostor.org/reference/' . $biostor_id;
			
			$url = $reference->url . '.json';

			$json = get($url);
			$j = json_decode($json);
			
			//print_r($j);
			//echo "---\n";
			$PageID = $j->bhl_pages[0];
			
			//echo $PageID . "\n";
			
			$html[$reference->year] .= '<div>';
			$html[$reference->year] .= '<a href="' . $reference->url . '" target="_new"><img src="http://biostor.org/bhl_image.php?PageID=' . $PageID . '&amp;thumbnail" /></a><br/>';
			$html[$reference->year] .= '<a href="http://www.biodiversitylibrary.org/page/' . $PageID . '" target="_new">' . $PageID . '</a><br/>';
			$html[$reference->year] .= $reference->title . '<br/>';
			$html[$reference->year] .= '</div>';
		}	
	}
	else
	{
		$year_roman[$reference->year]++;
	}
	
	
	
}

	
$filename = 'all.ris';
//$filename = 'test.ris';

$file = @fopen($filename, "r") or die("couldn't open $filename");
$ris = @fread($file, filesize($filename));
fclose($file);

import_ris($ris, 'import');

/*
echo "Articles in each year\n";
print_r($year_actual);
echo "Articles in each year found in BioStor\n";
print_r($year_found);
echo "Articles in each year with roman numbers\n";
print_r($year_roman);
*/

foreach ($html as $year => $h)
{
	$h .= '</body></html>';

	$filename = $year . '.html';
	$file = @fopen($filename, "w") or die("couldn't open $filename");
	fwrite($file, $h);
	fclose($file);	
}

$shtml = '<html><body><ul>';
foreach ($html as $year => $h)
{
	$shtml .= '<li><a href="' . $year . '.html">' . $year . '</a></li>' . "\n";
}
$shtml .= '</ul></body></html>';

$filename = 'tve.html';
$file = @fopen($filename, "w") or die("couldn't open $filename");
fwrite($file, $shtml);
fclose($file);	


?>