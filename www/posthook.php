<?php


// Take form data, clean, then construct POST query and send to user-supplied webhook
// which is set in "webhook" parameter

require_once (dirname(dirname(__FILE__)) . '/config.inc.php');
require_once (dirname(dirname(__FILE__)) . '/nameparse.php');

//print_r($_POST);

$reference = new stdclass;
$reference->identifiers = new stdclass;
$reference->authors = array();
$reference->urls = array();
$reference->type = 'Unknown';

foreach ($_POST as $k => $v)
{
	$v = trim($v);
	if ($v != '')
	{
		switch ($k)
		{
			case 'rft_val_fmt':
				switch ($v)
				{
					case 'info:ofi/fmt:kev:mtx:journal':
						$reference->type = 'Journal Article';
						break;
					default:
						break;
				}
				break;
				
			case 'authors':
				$authors = explode("\n", $v);
				foreach ($authors as $a)
				{
					$parts = parse_name($a);					
					$author = new stdClass();
					if (isset($parts['last']))
					{
						$author->surname = $parts['last'];
					}
					if (isset($parts['suffix']))
					{
						$author->suffix = $parts['suffix'];
					}
					if (isset($parts['first']))
					{
						$author->forename = $parts['first'];
						
						if (array_key_exists('middle', $parts))
						{
							$author->forename .= ' ' . $parts['middle'];
						}
						
						$author->forename = preg_replace('/\.\s/', ' ', $author->forename);
						$author->forename = preg_replace('/\.$/', '', $author->forename);						
					}
					$reference->authors[] = $author;									
				}
				break;
				
			case 'title':
			case 'publication_outlet':
			case 'series':
			case 'volume':
			case 'issue':
			case 'spage':
			case 'epage':
			case 'year':
			case 'pdf':
				$reference->$k = $v;
				break;
				
			case 'doi':
			case 'hdl':
			case 'issn':
			case 'pmid':
			case 'biostor':
				$reference->identifiers->$k = $v;
				break;
				
			case 'url':
				$reference->urls[] = $v;
				break;
			
			default:
				break;
		}
	}
}

if (isset($reference->spage))
{
	$reference->pages = $reference->spage;

	if (isset($reference->epage))
	{
		$reference->pages .= '-' . $reference->epage;
	}
}

// POST call to external webhook...

if (isset($_POST['webhook']))
{
	/*
	$post_data = array();
	if (isset($_POST['rfe_id']))
	{
		$post_data['id'] = $_POST['rfe_id'];
	}
	$post_data['data'] = json_encode($reference);
	*/
	//echo json_encode($reference);
	
	$reference->_id = $_POST['rfe_id'];
	
	$ch = curl_init(); 
	
	// Hook
	$url = $_POST['webhook'];
	
	curl_setopt ($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	if ($config['proxy_name'] != '')
	{
		curl_setopt ($ch, CURLOPT_PROXY, $config['proxy_name'] . ':' . $config['proxy_port']);
	}

	curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	
	// Set HTTP headers
	$headers = array();
	$headers[] = 'Content-type: application/json'; // we are sending JSON

	// Override Expect: 100-continue header (may cause problems with HTTP proxies
	// http://the-stickman.com/web-development/php-and-curl-disabling-100-continue-header/
	$headers[] = 'Expect:'; 
	curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);

	
	curl_setopt ($ch, CURLOPT_POST, TRUE);
	//curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt ($ch, CURLOPT_POSTFIELDS, json_encode($reference));
	
	
	
//	curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
	//curl_setopt ($ch, CURLOPT_POSTFIELDS, 'data=hello');
	$response = curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	echo $response;
}

?>