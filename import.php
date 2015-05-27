<?php

// Import reference using OpenURL

require_once(dirname(__FILE__) . '/lib.php');

function import_from_openurl($openurl, $threshold = 0.5)
{
	$found = false;
	
	// 2. Call BioStor
	$url = 'http://biostor.org/openurl.php?' . $openurl . '&format=json';
	$json = get($url);
		
	// 3. Search result
		
	$x = json_decode($json);
	
	//print_r($x);
	
	if (isset($x->reference_id))
	{
		// 4. We have this already
		$found = true;
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
		
		if ($h != -1)
		{		
			// 6. We have a hit, construct OpenURL that forces BioStor to save
			$openurl .= '&id=http://www.biodiversitylibrary.org/page/' . $x[$h]->PageID;
			$url = 'http://biostor.org/openurl.php?' . $openurl . '&format=json';

			$json = get($url);	
			
			echo $json;
			
			$found = true;
		}
	}
	return $found;
}





?>