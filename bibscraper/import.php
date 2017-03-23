<?php

require_once(dirname(__FILE__) . '/ris.php');
require_once(dirname(__FILE__) . '/utils.php');

$format = 'ris';
$format = 'sql';

$ids = array();

function biostor_import($reference)
{
global $format;

global $ids;

// pre-process

//print_r($reference);






// filter

//	if (!in_array($reference->volume, array(85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100)))
//	if (!in_array($reference->volume, array(74)))
if (0)
//	if ($reference->title != 'Haplothrips-Studien')
//if (!in_array($reference->volume, array(8)))
{
return;
}

if (($reference->issn == '0045-8511') 
|| ($reference->issn == '0022-2372') 
|| ($reference->issn == '0004-8038')
|| ($reference->issn == '0003-0023')
)
{
if (!isset($reference->volume))
{
$reference->volume = $reference->issue;
unset($reference->issue);
}
$reference->doi = $reference->publisher_id;

}


if ($reference->secondary_title == 'Bulletin of Zoological Nomenclature')
{
if ($reference->volume == 15)
{
$reference->volume .= 'B';
}

}

if (($reference->epage == '') && is_numeric($reference->spage))
{
$reference->epage = $reference->spage;
}	

if (!(is_numeric($reference->spage) && is_numeric($reference->epage)))
{
if ($format == 'ris')
{
echo reference2ris($reference);
}
return;
}

$reference->issue = preg_replace('/^No\.\s+/Uu', '', $reference->issue);	

if (preg_match('/(?<journal>.*), Series (?<series>\d+)$/Uu', $reference->secondary_title,$m))
{
$reference->secondary_title = $m['journal'];
$reference->series = $m['series'];
}

// Annales de la Societe Entomologique de France 4e serie tome
if (preg_match('/^(?<journal>.*) (?<series>\d+)e serie( tome)?$/Uu', $reference->secondary_title,$m))
{
$reference->secondary_title = $m['journal'];
$reference->series = $m['series'];
}
// Annales de la Societe Entomologique de France (5)
if (preg_match('/^(?<journal>.*) \((?<series>\d+)\)$/Uu', $reference->secondary_title,$m))
{
$reference->secondary_title = $m['journal'];
$reference->series = $m['series'];
}

// Annali del Museo Genova Ser 3
if (preg_match('/^(?<journal>.*) Ser (?<series>\d+)$/Uu', $reference->secondary_title,$m))
{
$reference->secondary_title = $m['journal'];
$reference->series = $m['series'];
}

if (preg_match('/^Treubia B/Uu', $reference->secondary_title))
{
$reference->secondary_title = 'Treubia';
}



if (!is_numeric($reference->volume))
{
if (preg_match('/^[ivxicl]+$/', $reference->volume))
{
$reference->volume = arabic($reference->volume);
}
}

// Tropicos
$reference->title = preg_replace('/~/Uu', '', $reference->title);	
$reference->title = preg_replace('/---/Uu', ' ', $reference->title);	


// BibTeX + Mendeley fuck ups

$reference->title = str_replace("{\textquoteleft}", "'", $reference->title);
$reference->title = str_replace("{\textquoteright}", "'", $reference->title);
$reference->title = str_replace("{\textendash}", "–", $reference->title);
$reference->title = str_replace("{\textendash}", "–", $reference->title);

$reference->title = str_replace("{á}", "á", $reference->title);
$reference->title = str_replace("{è}", "è", $reference->title);
$reference->title = str_replace("{é}", "é", $reference->title);
$reference->title = str_replace("{ö}", "ö", $reference->title);
$reference->title = str_replace("{ü}", "ü", $reference->title);
$reference->title = str_replace("{\r a}", "å", $reference->title);
$reference->title = str_replace("{ç}", "ç", $reference->title);

//print_r($reference);
//exit();

$openurl = reference2openurl($reference);

if ($format == 'sql')
{
echo "-- " . $openurl . "\n";

echo "-- " . $reference->title . "\n";
}

$biostor_id = import_from_openurl($openurl, 0.5, true);

//echo $biostor_id ."\n";

if ($biostor_id != 0)
{
$found = true;

$reference->url = 'http://direct.biostor.org/reference/' . $biostor_id;
$reference->biostor = $biostor_id;

if ($format == 'sql')
{
// Add BioStor to ION
//$sql = '-- UPDATE names SET biostor="' . $biostor_id . '" WHERE sici="' . $reference->publisher_id . '";';
//echo $sql . "\n";
echo $biostor_id . "\n";

// Add JSTOR to BioStor
if (preg_match('/http:\/\/www.jstor.org\/stable\/(?<jstor>\d+)/', $reference->publisher_id, $m))
{
$sql = 'UPDATE rdmp_reference SET jstor=' . $m['jstor'] . ' WHERE reference_id="' . $biostor_id . '";';
echo $sql . "\n";
}

if (is_numeric($reference->publisher_id))
{
	$sql = 'UPDATE rdmp_reference SET jstor=' . $reference->publisher_id . ' WHERE reference_id="' . $biostor_id . '";';
echo $sql . "\n";
}


}

}	

//print_r($reference);
if ($format == 'ris')
{
echo reference2ris($reference);
}

}



$filename = '';
$mode = 'biostor';
$format = 'ris';
$format = 'sql';
if ($argc < 2)
{
echo "Usage: import.php <RIS file> <mode>\n";
exit(1);
}
else
{
$filename = $argv[1];
if ($argc >= 3)
{
$mode = $argv[2];
}
if ($argc == 4)
{
$format = $argv[3];
}	
}

$file = @fopen($filename, "r") or die("couldn't open $filename");
fclose($file);

//$ris = @fread($file, filesize($filename));
//fclose($file);


switch ($mode)
{

default:
import_ris_file($filename, 'biostor_import');
break;
}

print_r($ids);

?>