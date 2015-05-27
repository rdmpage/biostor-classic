<?php

//$config['proxy_name'] = 'wwwcache.gla.ac.uk';
//$config['proxy_port'] = 8080;

$config['proxy_name'] = '';
$config['proxy_port'] = '';


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
function import_from_openurl($openurl, $threshold = 0.5)
{
	$found = 0;
	
	// 2. Call BioStor
	$url = 'http://biostor.org/openurl.php?' . $openurl . '&format=json';
	$json = get($url);

	// 3. Search result
		
	$x = json_decode($json);
		
	if (isset($x->reference_id))
	{
		// 4. We have this already
		$found = $x->reference_id;
	}
	else
	{
		// 5. Did we get a hit with text similarity above the threshold
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
			$j = json_decode($json);
			$found = $j->reference_id;
		}
	}
	
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
	$openurl .= '&rft.date=' . $reference->year;

	return $openurl;
}

?>