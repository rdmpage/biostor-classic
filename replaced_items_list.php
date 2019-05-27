<?php

// Identify items in a title that have been replaced 


require_once('db.php');

//----------------------------------------------------------------------------------------
function lookup($url, $userAgent = '', $timeout = 0)
{
	global $config;
	
	$redirect_url = '';
	
	$ch = curl_init(); 
	curl_setopt ($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	//curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,	1); 
	//curl_setopt ($ch, CURLOPT_HEADER,		  1);  

	curl_setopt ($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
	
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
		
		//print_r($info);
		
		
		if ($info['http_code'] == 302)
		{
			// page moved
			$redirect_url = $info['redirect_url'];
		}
		
	}
	
	curl_close($ch);
	
	return $redirect_url;

}

//----------------------------------------------------------------------------------------

$items = array();
$deleted = array();

$TitleID = 13041;

// get item list
$sql = 'SELECT ItemID FROM bhl_item WHERE TitleID=' . $TitleID;

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{	
	$items[] = $result->fields['ItemID'];

	$result->MoveNext();		
}

$items= array(
120129
);


$map = array();

foreach ($items as $ItemID)
{
	echo $ItemID;
	
	$url = 'https://www.biodiversitylibrary.org/item/' . $ItemID;
	
	$redirect_url = lookup($url);
	
	if ($redirect_url != '')
	{
		$newItemID = str_replace('https://www.biodiversitylibrary.org/item/', '', $redirect_url);
		
		echo "=$newItemID";
		
		$map[$ItemID] = $newItemID;
		$deleted[] = $ItemID;
	}
	echo "\n";
}

print_r($map);

echo "\nThese items need their articles remapped\n";
echo '$replace_list = array(' . "\n";
foreach ($map as $k => $v)
{
	echo $k . " => " . $v . "\n";
}
echo ");\n";

//print_r($deleted);

echo "\Delete these items from BioStor database\n";
foreach ($deleted as $id)
{
	echo "DELETE FROM bhl_item WHERE ItemID=$id;\n";
}


?>