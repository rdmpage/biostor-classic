<?php

date_default_timezone_set('UTC');

require_once(dirname(__FILE__) . '/transtab_unicode_latex.inc.php');


$config['proxy_name'] = 'wwwcache.gla.ac.uk';
$config['proxy_port'] = 8080;

$config['proxy_name'] = '';
$config['proxy_port'] = '';

//$config['proxy_name'] = '222.37.86.209';
//$config['proxy_port'] = 8909;

//--------------------------------------------------------------------------------------------------
function create_sha1_filename($basedir, &$obj)
{

// Store on disk
// We store PDFs in files with sha1-based name and directory structure, based on
// http://stackoverflow.com/questions/247678/how-does-mediawiki-compose-the-image-paths
preg_match('/^(..)(..)(..)/', $obj->sha1, $matches);

$obj->relative_path = $matches[1] . '/' . $matches[2] . '/' . $matches[3] . '/' . $obj->sha1 . '.' . $obj->extension;

$obj->filename = $basedir . '/' . $obj->relative_path;

// If we dont have file, create directory structure for it	
if (!file_exists($obj->filename))
{
$path = $basedir;
if (!file_exists($path))
{
$oldumask = umask(0); 
mkdir($path, 0777);
umask($oldumask);
}
$path .= '/' . $matches[1];
if (!file_exists($path))
{
$oldumask = umask(0); 
mkdir($path, 0777);
umask($oldumask);
}
$path .= '/' . $matches[2];
if (!file_exists($path))
{
$oldumask = umask(0); 
mkdir($path, 0777);
umask($oldumask);
}
$path .= '/' . $matches[3];
if (!file_exists($path))
{
$oldumask = umask(0); 
mkdir($path, 0777);
umask($oldumask);
}
}

}


//--------------------------------------------------------------------------------------------------
// From http://snipplr.com/view/6314/roman-numerals/
// Expand subtractive notation in Roman numerals.
function roman_expand($roman)
{
$roman = str_replace("CM", "DCCCC", $roman);
$roman = str_replace("CD", "CCCC", $roman);
$roman = str_replace("XC", "LXXXX", $roman);
$roman = str_replace("XL", "XXXX", $roman);
$roman = str_replace("IX", "VIIII", $roman);
$roman = str_replace("IV", "IIII", $roman);
return $roman;
}

//--------------------------------------------------------------------------------------------------
// From http://snipplr.com/view/6314/roman-numerals/
// Convert Roman number into Arabic
function arabic($roman)
{
$result = 0;

$roman = strtoupper($roman);

// Remove subtractive notation.
$roman = roman_expand($roman);

// Calculate for each numeral.
$result += substr_count($roman, 'M') * 1000;
$result += substr_count($roman, 'D') * 500;
$result += substr_count($roman, 'C') * 100;
$result += substr_count($roman, 'L') * 50;
$result += substr_count($roman, 'X') * 10;
$result += substr_count($roman, 'V') * 5;
$result += substr_count($roman, 'I');
return $result;
} 

//--------------------------------------------------------------------------------------------------
// safe file name, based on http://snipplr.com/view/5256/filename-safe/
function filename_safe($filename) 
{
$temp = $filename;
// Lower case
$temp = strtolower($temp);

// Replace spaces with a '_'
$temp = str_replace(" ", "_", $temp);

// Loop through string
$result = '';
for ($i=0; $i<strlen($temp); $i++) 
{
if (preg_match('([0-9]|[a-z]|_)', $temp[$i])) 
{
$result = $result . $temp[$i];
} 
}
// Return filename
return $result;
}

// Convert Arabic numerals into Roman numerals.
function roman($arabic)
{
$ones = Array("", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX");
$tens = Array("", "X", "XX", "XXX", "XL", "L", "LX", "LXX", "LXXX", "XC");
$hundreds = Array("", "C", "CC", "CCC", "CD", "D", "DC", "DCC", "DCCC", "CM");
$thousands = Array("", "M", "MM", "MMM", "MMMM");

if ($arabic > 4999)
{
// For large numbers (five thousand and above), a bar is placed above a base numeral to indicate multiplication by 1000.
// Since it is not possible to illustrate this in plain ASCII, this function will refuse to convert numbers above 4999.
die("Cannot represent numbers larger than 4999 in plain ASCII.");
}
elseif ($arabic == 0)
{
// About 725, Bede or one of his colleagues used the letter N, the initial of nullae,
// in a table of epacts, all written in Roman numerals, to indicate zero.
return "N";
}
else
{
// Handle fractions that will round up to 1.
if (round(fmod($arabic, 1) * 12) == 12)
{
$arabic = round($arabic);
}

// With special cases out of the way, we can proceed.
// NOTE: modulous operator (%) only supports integers, so fmod() had to be used instead to support floating point.
$roman = $thousands[($arabic - fmod($arabic, 1000)) / 1000];
$arabic = fmod($arabic, 1000);
$roman .= $hundreds[($arabic - fmod($arabic, 100)) / 100];
$arabic = fmod($arabic, 100);
$roman .= $tens[($arabic - fmod($arabic, 10)) / 10];
$arabic = fmod($arabic, 10);
$roman .= $ones[($arabic - fmod($arabic, 1)) / 1];
$arabic = fmod($arabic, 1);


return $roman;
}
}

//--------------------------------------------------------------------------------------------------
/**
* @brief Format JSON nicely
*
* From umbrae at gmail dot com posted 10-Jan-2008 06:21 to http://uk3.php.net/json_encode
*
* @param json Original JSON
*
* @result Formatted JSON
*/
function json_format($json)
{
$tab = " ";
$new_json = "";
$indent_level = 0;
$in_string = false;

/* $json_obj = json_decode($json);

if($json_obj === false)
return false;

$json = json_encode($json_obj); */
$len = strlen($json);

for($c = 0; $c < $len; $c++)
{
$char = $json[$c];
switch($char)
{
case '{':
case '[':
if(!$in_string)
{
$new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
$indent_level++;
}
else
{
$new_json .= $char;
}
break;
case '}':
case ']':
if(!$in_string)
{
$indent_level--;
$new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
}
else
{
$new_json .= $char;
}
break;
case ',':
if(!$in_string)
{
$new_json .= ",\n" . str_repeat($tab, $indent_level);
}
else
{
$new_json .= $char;
}
break;
case ':':
if(!$in_string)
{
$new_json .= ": ";
}
else
{
$new_json .= $char;
}
break;
case '"':
if($c > 0 && $json[$c-1] != '\\')
{
$in_string = !$in_string;
}
default:
$new_json .= $char;
break; 
}
}

return $new_json;
}



//--------------------------------------------------------------------------------------------------
/**
* @brief Test whether HTTP code is valid
*
* HTTP codes 200 and 302 are OK.
*
* For JSTOR we also accept 403
*
* @param HTTP code
*
* @result True if HTTP code is valid
*/
function HttpCodeValid($http_code)
{
if ( ($http_code == '200') || ($http_code == '302') || ($http_code == '403'))
{
return true;
}
else{
return false;
}
}


//--------------------------------------------------------------------------------------------------
/**
* @brief GET a resource
*
* Make the HTTP GET call to retrieve the record pointed to by the URL. 
*
* @param url URL of resource
*
* @result Contents of resource
*/
function get($url, $userAgent = '', $timeout = 0)
{
global $config;

$data = '';

$ch = curl_init(); 
curl_setopt ($ch, CURLOPT_URL, $url); 
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,	1); 
//curl_setopt ($ch, CURLOPT_HEADER,	1); 

if (1)
{
curl_setopt ($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
}
else
{
// Set cookie manually if we need to
curl_setopt($ch, CURLOPT_COOKIE, 'GSP=ID=45d0cce2a9907b8b:NT=1370262658:S=vsd_aZY8BDJLXBd4; SID=DQAAAOEAAABCtYOQjDW6sHEMScrgxVS_jmafvfenvCU_xPeYpUJCTpKhe5-W79-DRb94ejN3Qx7Pc213g53nGN_YeiNRxChpZ2tAJzbTkggb0Mzx7aKrrO6lXtE8hZkUP2itkcqgC2CMMopkDoyeNH70Lepxy7dMZiQjOM0RorpYuNayi2-x4Yqo6HvATHJc7t2C97LpNCHw0J6GsC5ns88RZyqEJIF90eBxkKQmYEXMEdos750Aa_FmO2C6ydznJOPXocxL2RSAnq5CWK2llRBeFlCFGA3nRgqirO-xZCdheiw_XfUsdkHkdpy78SpQQpU07qpfvY4; APISID=8qS4v5b92z2mFT1V/A7GexiFHL-CMKt8N3; HSID=AA9HjA4Ps-B63hWet; NID=67=I1nCCJhVUhIg5DNQlS1AxOwdTwcGxX-fsmPs8LsM78z86EF1BIXykVXjPpHXj723fs1Yjpkp4XBCQc9gXvhd6Tt8SBYImHst_xfd7JnCcLF0dzvXYq00RuxnbGmCuC_enI6GyEDHIl6a7bsWvea52CCUGEj2TyP_QFLaFGMO9xu1_KZGLGJlKQQoryk; PREF=ID=45d0cce2a9907b8b:U=cc9b0852a7bf3f3c:FF=0:LD=en:TM=1369223666:LM=1369249075:S=tnrZy4gbq__3uv9f; SS=DQAAAN8AAAAMCpjHAJFkvVEINf1uUTRnOpGLo8ijZBE-3NSawmbhbZ2asQ0kCZYr5W15a7Nyi31hznHT-wOQPMHAGwDjI39I2y7FwfqU4EfL9XksGw7J6f3O7CsM0OoI7yy_7hEGq5fx64Erxf3c-1wogJyexmL4SDjsocZU7zTMSJcoYjoRbgLgVrfT8jp9XC0adS2sLFthHCiss7zIcjJs');
}

if ($userAgent != '')
{
curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
}	

if ($timeout != 0)
{
curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
}

if ($config['proxy_name'] != '')
{
curl_setopt ($ch, CURLOPT_PROXY, $config['proxy_name'] . ':' . $config['proxy_port']);
}


$curl_result = curl_exec ($ch); 

//echo $curl_result;

if (curl_errno ($ch) != 0 )
{
echo "CURL error: ", curl_errno ($ch), " ", curl_error($ch);
}
else
{
$info = curl_getinfo($ch);

//$header = substr($curl_result, 0, $info['header_size']);
//echo $header;


$http_code = $info['http_code'];

//echo "<p><b>HTTP code=$http_code</b></p>";

if (HttpCodeValid ($http_code))
{
$data = $curl_result;
}
}
return $data;
}

//--------------------------------------------------------------------------------------------------
// Set $store to true if we want to get BoStor to add this reference (may slow things down)
function import_from_openurl($openurl, $threshold = 0.5, $store = true)
{
$found = 0;

// 2. Call BioStor
$url = 'http://direct.biostor.org/openurl.php?' . $openurl . '&format=json';
$json = get($url);

//echo $url . "\n";

//echo $json;

// 3. Search result

$x = json_decode($json);

//print_r($x);
//exit();

if (isset($x->reference_id))
{
// 4. We have this already
$found = $x->reference_id;
}
else
{
// 5. Did we get a (significant) hit? 
// Note that we may get multiple hits, we use the best one
$h = -1;
$n = count($x);
for($k=0;$k<$n;$k++)
{
if ($x[$k]->score > $threshold)
{
$h = $k;
}
}

if (($h != -1) && $store)
{	
// 6. We have a hit, construct OpenURL that forces BioStor to save
$openurl .= '&id=http://www.biodiversitylibrary.org/page/' . $x[$h]->PageID;
$url = 'http://direct.biostor.org/openurl.php?' . $openurl . '&format=json';

$json = get($url);
$j = json_decode($json);
$found = $j->reference_id;
}
}
//echo "Found $found\n";

return $found;
}

//--------------------------------------------------------------------------------------------------
function reference2openurl($reference)
{
$openurl = '';
$openurl .= 'ctx_ver=Z39.88-2004&rft_val_fmt=info:ofi/fmt:kev:mtx:journal';
//$openurl .= '&genre=article';

if (isset($reference->authors))
{
foreach ($reference->authors as $author)
{
$openurl .= '&rft.au=' . urlencode($author);
}	
}
$openurl .= '&rft.atitle=' . urlencode($reference->title);
$openurl .= '&rft.jtitle=' . urlencode($reference->secondary_title);
if (isset($reference->issn))
{
$openurl .= '&rft.issn=' . $reference->issn;
}
if (isset($reference->series))
{
$openurl .= '&rft.series=' . $reference->series;
}
$openurl .= '&rft.volume=' . $reference->volume;

if (isset($reference->issue))
{
$openurl .= '&amp;rft.issue=' . $reference->issue;
}	


if (isset($reference->spage))
{
$openurl .= '&rft.spage=' . $reference->spage;
}
if (isset($reference->epage))
{
$openurl .= '&rft.epage=' . $reference->epage;
}
if (isset($reference->pagination))
{
$openurl .= '&rft.pages=' . $reference->pagination;
}

if (isset($reference->date))
{
$openurl .= '&rft.date=' . $reference->date;
}
else
{
$openurl .= '&rft.date=' . $reference->year;	
}
if (isset($reference->lsid))
{
$openurl .= '&rft_id=' . $reference->lsid;
}

if (isset($reference->doi))
{
$openurl .= '&rft_id=info:doi/' . $reference->doi;
}


if (isset($reference->url))
{
if (preg_match('/http:\/\/hdl.handle.net\//', $reference->url))
{
$openurl .= '&rft_id=' . $reference->url;
}
if (preg_match('/http:\/\/www.jstor.org\//', $reference->url))
{
$openurl .= '&rft_id=' . $reference->url;
}
}	

return $openurl;
}

//--------------------------------------------------------------------------------------------------
function bioguid($reference, $format='sql')
{
$found = false;

if ($format == 'sql')
{
echo "-- " . reference2openurl($reference) . "\n";
}

$url = 'http://bioguid.info/openurl.php?' . reference2openurl($reference) . '&display=json';
//	$url = 'http://iphylo.org/~rpage/bioguid/www/openurl.php?' . reference2openurl($reference) . '&display=json';
$json = get($url);

//echo $url . "\n";

$obj = json_decode($json);

//print_r($obj);

if ($obj->status == 'ok')
{
$found = true;

if (isset($obj->issn))
{
$reference->issn = $obj->issn;
}
if (isset($obj->doi))
{
$reference->doi = $obj->doi;
}
if (isset($obj->pmid))
{
$reference->pmid = $obj->pmid;
}
if (isset($obj->hdl))
{
$reference->hdl = $obj->hdl;
}
if (isset($obj->url))
{
$reference->url = $obj->url;
}
if (isset($obj->pdf))
{
$reference->pdf = $obj->pdf;
}

if (isset($obj->abstract))
{
$reference->abstract = $obj->abstract;
}

// Flesh out
if (isset($obj->atitle) && !isset($reference->title))
{
$reference->title = $obj->atitle;
}
if (isset($obj->issue) && !isset($reference->issue))
{
$reference->issue = $obj->issue;
}
if (isset($obj->spage) && !isset($reference->spage))
{
$reference->spage = $obj->spage;
}
if (isset($obj->epage) && !isset($reference->epage))
{
$reference->epage = $obj->epage;
}
if (isset($obj->url) && !isset($reference->url))
{
$reference->url = $obj->url;
}
if (isset($obj->pdf) && !isset($reference->pdf))
{
$reference->pdf = $obj->pdf;
}

if (isset($obj->authors) && !isset($reference->authors))
{
$reference->authors = array();
foreach ($obj->authors as $a)
{
$reference->authors[] = $a->forename . ' ' . $a->lastname;
}
}




}

return $found;
}

//--------------------------------------------------------------------------------------------------
function authors_from_string($authorstring)
{
$authors = array();

// Strip out suffix
$authorstring = preg_replace("/,\s*Jr./u", "", trim($authorstring));
$authorstring = preg_replace("/,\s*jr./u", "", trim($authorstring));

//echo $authorstring . "\n";
if (preg_match('/^(?<name>\w+((\s+\w+)+)?),/u', $authorstring, $m))
{
//print_r($m);
$authorstring = preg_replace("/,/u", "|", $authorstring);
$authorstring = preg_replace("/^" . $m['name'] . "\|/u", $m['name'] . ",", $authorstring);
//echo $authorstring . "\n";
}
else
{
$authorstring = preg_replace("/,/u", "|", trim($authorstring));
}



$authorstring = preg_replace("/,$/u", "", trim($authorstring));
$authorstring = preg_replace("/&/u", "|", $authorstring);
$authorstring = preg_replace("/;/u", "|", $authorstring);
$authorstring = preg_replace("/ and /u", "|", $authorstring);
$authorstring = preg_replace("/\.,/Uu", "|", $authorstring);	
$authorstring = preg_replace("/\|\s*\|/Uu", "|", $authorstring);	
$authorstring = preg_replace("/\|\s*/Uu", "|", $authorstring);	
$authors = explode("|", $authorstring);

//echo $authorstring . "\n";

for ($i = 0; $i < count($authors); $i++)
{
$authors[$i] = preg_replace('/\.([A-Z])/u', ". $1", $authors[$i]);
$authors[$i] = preg_replace('/^\s+/u', "", $authors[$i]);
$authors[$i] = mb_convert_case($authors[$i], MB_CASE_TITLE, 'UTF-8');
}

// try and catch obvious errors
$j = 0;
$a = array();
for ($i = 0; $i < count($authors); $i++)
{
if (preg_match('/^([A-Z]\.?((\s+[A-Z]\.?)+)?)$/', $authors[$i]))
{
$a[$j-1] = $authors[$i] . ' ' . $a[$j-1];
}
else
{
$a[$j] = $authors[$i];
$j++;
}
}
$authors = $a;

//print_r($a);


return $authors;
}

//--------------------------------------------------------------------------------------------------
function reference_from_matches($matches, &$reference = null)
{
if ($reference == null)
{
$reference = new stdclass;
}

//print_r($matches);

// title
$title = $matches['title'];
$title = html_entity_decode($title, ENT_NOQUOTES, 'UTF-8');
$title = trim(strip_tags($title));
$title = preg_replace('/\.$/', '', $title);

// authors
if (isset($matches['authorstring']))
{
$authorstring = $matches['authorstring'];
$reference->authors = authors_from_string($authorstring);


for ($i = 0; $i < count($reference->authors); $i++)
{
$reference->authors[$i]	= preg_replace('/\.$/u', '', $reference->authors[$i]);
$reference->authors[$i]	= preg_replace('/\.\s+/u', ' ', $reference->authors[$i]);
$reference->authors[$i]	= preg_replace('/\./u', ' ', $reference->authors[$i]);
if (strpos($reference->authors[$i], ",") === false)
{
$reference->authors[$i]	= preg_replace('/([A-Z])\.([A-Z])/', '$1 $2', $reference->authors[$i]);
}
else
{
$author_parts = explode(",", $reference->authors[$i]);
$forename = $author_parts[1];
$forename = preg_replace('/([A-Z])\.([A-Z])/u', '$1. $2', trim($forename));

$reference->authors[$i] = $forename . ' ' . $author_parts[0];
}
}
}

$reference->genre = 'article';
$reference->title = $title;
$reference->secondary_title = trim(strip_tags($matches['journal']));

if ($matches['series'] != '')
{
$reference->series = $matches['series'];
}

$reference->volume = $matches['volume'];

if ($matches['issue'] != '')
{
$reference->issue = $matches['issue'];
}

if ($matches['issn'] != '')
{
$reference->issn = $matches['issn'];
}

if (isset($matches['spage']))
{
$reference->spage = $matches['spage'];
}
if (isset($matches['epage']))
{
$reference->epage = $matches['epage'];
}

if (isset($matches['pages']))
{
if (preg_match('/^(?<spage>.*)\-(?<epage>.*)$/', $matches['pages'], $m))
{
$reference->spage = $m['spage'];
$reference->epage = $m['epage'];
}
else
{
$reference->spage = 1;
$reference->epage = $matches['pages'];
}	
}


if (isset($matches['year']))
{
$reference->year = $matches['year'];
}

if (isset($matches['url']))
{
$reference->url = $matches['url'];
}
if (isset($matches['pdf']))
{
$reference->pdf = $matches['pdf'];
}

if (isset($matches['id']))
{
$reference->id = $matches['id'];
}
if (isset($matches['date']))
{
$reference->date = $matches['date'];
}


return $reference;
}

//--------------------------------------------------------------------------------------------------
function reference2ris($reference)
{
$ris = '';

if (isset($reference->genre))
{
switch ($reference->genre)
{
case 'article':
$ris .= "TY - JOUR\n";
break;

case 'book':
$ris .= "TY - BOOK\n";
break;

case 'chapter':
$ris .= "TY - CHAP\n";
break;

default:
$ris .= "TY - GEN\n";
break;
}

}
else
{

if (isset($reference->secondary_title) || isset($reference->issn))
{
$ris .= "TY - JOUR\n";
}
else
{
$ris .= "TY - GEN\n";
}
}	

if (isset($reference->id))
{
$ris .= "ID - " . $reference->id . "\n";
}
if (isset($reference->publisher_id))
{
$ris .= "ID - " . $reference->publisher_id . "\n";
}

if (isset($reference->authors))
{
foreach ($reference->authors as $a)
{
if (is_object($a))
{
$ris .= "AU - ";
if (isset($a->forename))
{
$ris .= trim($a->forename);
}
if (isset($a->lastname))
{
$ris .= ' ' . trim($a->lastname);
}
if (isset($a->surname))
{
$ris .= ' ' . trim($a->surname);
}
$ris .= "\n";

}
else
{
//$a = preg_replace('/\.([A-Z])/u', ". $1", $a);
//$a = preg_replace('/\s\s/u', " ", $a);
$ris .= "AU - " . trim($a) . "\n";	
}
}
}

if (isset($reference->atitle))
{
$ris .= "TI - " . strip_tags($reference->atitle) . "\n";
$ris .= "JF - " . strip_tags($reference->title) . "\n";
}
else
{
$reference->title = str_replace('&quot;',"'", $reference->title);
$ris .= "TI - " . strip_tags($reference->title) . "\n";
}

if (isset($reference->secondary_title)) 
{
switch ($reference->genre)
{
case 'chap':
$ris .= "T2 - " . $reference->secondary_title . "\n";
break;

default:
$ris .= "JF - " . $reference->secondary_title . "\n";
break;
}
}

if (isset($reference->issn))
{
$ris .= "SN - " . $reference->issn . "\n";
}


if (isset($reference->secondary_authors))
{
foreach ($reference->secondary_authors as $a)
{
$ris .= "ED - " . trim($a) . "\n";	
}	
}	
if (isset($reference->volume)) $ris .= "VL - " . $reference->volume . "\n";
if (isset($reference->issue) && ($reference->issue != ''))
{
$ris .= "IS - " . $reference->issue . "\n";
}
if (isset($reference->spage)) $ris .= "SP - " . $reference->spage . "\n";
if (isset($reference->epage)) $ris .= "EP - " . $reference->epage . "\n";

if (isset($reference->date))
{
$ris .= "Y1 - " . str_replace("-", "/", $reference->date) . "\n";
}
else
{
$ris .= "Y1 - " . $reference->year . "///\n";
}
if (isset($reference->url))
{
if (preg_match('/dx.doi.org/', $reference->url))
{
}
elseif (preg_match('/biostor.org/', $reference->url))
{
}
else
{
$ris .= "UR - " . $reference->url . "\n";
}
}

if (isset( $reference->pdf))
{
$ris .= "L1 - " . $reference->pdf . "\n";
}
if (isset( $reference->doi))
{
$ris .= "UR - http://dx.doi.org/" . $reference->doi . "\n";
// Ingenta
$ris .= 'M3 - ' . $reference->doi . "\n"; 
// Mendeley 0.9.9.2
$ris .= "DO - " . $reference->doi . "\n";
}
if (isset( $reference->hdl))
{
$ris .= "UR - http://hdl.handle.net/" . $reference->hdl . "\n";
}
if (isset( $reference->biostor))
{
$ris .= "UR - http://biostor.org/reference/" . $reference->biostor . "\n";
}

if (isset( $reference->pmid))
{
$ris .= "UR - http://www.ncbi.nlm.nih.gov/pubmed/" . $reference->pmid . "\n";
}
if (isset( $reference->pmc))
{
$ris .= "UR - http://www.ncbi.nlm.nih.gov/pmc/articles/PMC" . $reference->pmc . "\n";
}



if (isset($reference->abstract))
{
$ris .= "N2 - " . $reference->abstract . "\n";
}

if (isset($reference->publisher))
{
$ris .= "PB - " . $reference->publisher . "\n";
}
if (isset($reference->publoc))
{
$ris .= "CY - " . $reference->publoc . "\n";
}

if (isset($reference->notes))
{
$ris .= "N1 - " . $reference->notes . "\n";
}


if (isset($reference->keywords))
{
foreach ($reference->keywords as $keyword)
{
$ris .= "KW - " . $keyword . "\n";
}
}

if (isset($reference->thumbnail))
{
$ris .= "L4 - " . $reference->thumbnail . "\n";
}	



$ris .= "ER - \n";
$ris .= "\n";

return $ris;
}

//--------------------------------------------------------------------------------------------------
function latex_safe($str)
{
if (1)
{
return $str;
}
else
{
global $transtab_unicode_latex;

$n = mb_strlen($str);
$safe_str = '';
for($i = 0; $i < $n; $i++)
{
$char = mb_substr($str, $i, 1);
if (array_key_exists($char, $transtab_unicode_latex)) 
{
$safe_str .= $transtab_unicode_latex[$char];
}
else
{
$safe_str .= $char;
}
}
return $safe_str;
}
}

//--------------------------------------------------------------------------------------------------
function reference_to_bibtex($reference)
{
global $config;

$bibtex = '';

switch ($reference->genre)
{
case 'article':
$bibtex .= "@article{";

// Citekey
$citekey = uniqid();

$bibtex .= $citekey;	


$num_authors = count($reference->authors);
if (count($num_authors) > 0)
{
$bibtex .= ",\n author = {" . latex_safe($reference->authors[0]);

for ($i = 1; $i < $num_authors; $i++)
{
$bibtex .= " and " . latex_safe($reference->authors[$i]);
}
$bibtex .= "}";
}
$bibtex .= ",\n title = {" . latex_safe($reference->title) . "}";
$bibtex .= ",\n journal = {" . latex_safe($reference->secondary_title) . "}";
if (isset($reference->issn))
{
$bibtex .= ",\n ISSN = {" . $reference->issn . "},";
}
$bibtex .= ",\n volume = {" . $reference->volume . "}";
if (isset($reference->issue) && ($reference->issue != ''))
{
$bibtex .= ",\n number = {" . $reference->issue . "}";
}
$bibtex .= ",\n pages = {" . $reference->spage; 
if (isset($reference->epage))
{
$bibtex .= "--" . $reference->epage;
}
$bibtex .= "}";
$bibtex .= ",\n year = {" . $reference->year . "}";

if (isset($reference->doi))
{
$bibtex .= ",\n DOI = {" . $reference->doi . "}";
}

if (isset($reference->url))
{
$bibtex .= ",\n url = {" . $reference->url . "}";
}
if (isset($reference->pdf))
{
$bibtex .= ",\n pdf = {" . $reference->pdf . "}";
}
if (isset($reference->abstract))
{
$bibtex .= ",\n abstract = {" . latex_safe($reference->abstract) . "}";
}
$bibtex .= "\n}\n\n";
break;

default:
break;
}

return $bibtex;
}

//--------------------------------------------------------------------------------------------------
/**
* @brief Inject XMP metadata into PDF
*
* We inject XMP metadata using Exiftools
*
* @param reference Reference 
* @param pdf_filename Full path of PDF file to process
* @param tags Tags to add to PDF
*
*/
function pdf_add_xmp ($reference, $pdf_filename)
{
global $config;

// URL
if (isset($reference->url))
{
$command = "exiftool" . " -XMP:URL=" . escapeshellarg($reference->url) . " " . $pdf_filename;	
system($command);
}

// Mendeley will overwrite XMP-derived metadata with CrossRef metadata if we include this
if (isset($reference->doi))
{
$command = "exiftool" . " -XMP:DOI=" . escapeshellarg($reference->doi) . " " . $pdf_filename;
system($command);
}

// Title and authors
$command = "exiftool" . " -XMP:Title=" . escapeshellarg($reference->title) . " " . $pdf_filename;
system($command);

foreach ($reference->authors as $a)
{
$command = "exiftool" . " -XMP:Creator+=" . escapeshellarg($a) . " " . $pdf_filename;
system($command);
}

// Article
if ($reference->genre == 'article')
{
$command = "exiftool" . " -XMP:AggregationType=journal " . $pdf_filename;
system($command);
$command = "exiftool" . " -XMP:PublicationName=" . escapeshellarg($reference->secondary_title) . " " . $pdf_filename;
system($command);

if (isset($reference->issn))
{
$command = "exiftool" . " -XMP:ISSN=" . escapeshellarg($reference->issn) . " " . $pdf_filename;
system($command);
}

$command = "exiftool" . " -XMP:Volume=" . escapeshellarg($reference->volume) . " " . $pdf_filename;
system($command);
if (isset($reference->issue))
{
$command = "exiftool" . " -XMP:Number=" . escapeshellarg($reference->issue) . " " . $pdf_filename;
system($command);
}
$command = "exiftool" . " -XMP:StartingPage=" . escapeshellarg($reference->spage) . " " . $pdf_filename;
system($command);
if (isset($reference->epage))
{
$command = "exiftool" . " -XMP:EndingPage=" . escapeshellarg($reference->epage) . " " . $pdf_filename;
system($command);
$command = "exiftool" . " -XMP:PageRange+=" . escapeshellarg($reference->spage. '-' . $reference->epage) . " " . $pdf_filename;
system($command);
}
}

$command = "exiftool" . " -XMP:CoverDate=" . escapeshellarg(str_replace("-", ":", $reference->date)) . " " . $pdf_filename;
system($command);
$command = "exiftool" . " -XMP:Date=" . escapeshellarg(str_replace("-", ":", $reference->date)) . " " . $pdf_filename;
system($command);

// cleanup
if (file_exists($pdf_filename . '_original'))
{
unlink($pdf_filename . '_original');
}

}	



?>