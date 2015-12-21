<?php


// remap BioStor article to new PageIDs

// 50997

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
		
		// print_r($info);
		
		
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

$Items = array(50997);

$Items = array(13980);
$Items = array(14016);

$Items=array(
89027,
13999,
13981,
13982,
13983,
13984,
13946,
13947,
13948,
13949,
13950,
13952,
13953,
13954,
13955,
13956,
13957,
13958,
13959,
13960,
13961,
13962,
13963,
13964,
13965,
13966,
13967,
13968,
13969,
13971,
13933,
13937,
13938,
13939,
13940,
13941,
13942,
13985,
14005,
13970,
13987,
13989,
13988,
13986,
13934,
13935,
13936,
14000,
14001,
13992,
13995,
13997,
13993,
14008,
14009,
14007,
13996,
14004,
14012,
14015,
14017,
14018,
14019,
14020,
14021,
14022,
14574,
14575,
14010,
14006,
14014,
13973,
13974,
13975,
13976,
13977,
13978,
13979,
13990,
13991,
13994,
13998,
13945,
13951,
13943,
12974,
14002,
14003,
14400,
14011,
14013,
14016,
13980
);

$all_ids = array();

$Items = array(13983);



foreach ($Items as $ItemID)
{

	$ids = array();

	if (0)
	{
		$sql = 'SELECT DISTINCT rdmp_reference.reference_id, page.SequenceOrder 
				FROM rdmp_reference_page_joiner
				INNER JOIN page USING(PageID) 
				INNER JOIN rdmp_reference USING(PageID)
				WHERE ItemID=' . $ItemID . '
				ORDER BY CAST(page.SequenceOrder AS SIGNED)';

		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
		while (!$result->EOF) 
		{	
			$ids[] = $result->fields['reference_id'];
		
			$all_ids[] = $result->fields['reference_id'];
		
			$result->MoveNext();		
		}
	}
	else
	{
		$ids[] = 11851;
		$all_ids[] = 11851;
	}
	echo '-- $ids=array(' . join(',', $ids) . ');' . "\n";

	foreach ($ids as $reference_id)
	{
		$article = db_retrieve_reference($reference_id);
	
		//print_r($article);
	
		// $article->PageID;
	
		$url = 'http://biodiversitylibrary.org/page/' . $article->PageID;
	
		$redirect_url = lookup($url);
	
		if ($redirect_url != '' && isset($article->spage))
		{
			echo "-- $redirect_url\n";
		
			$ItemID = str_replace('http://biodiversitylibrary.org/item/', '', $redirect_url);
		
			$sql = 'SELECT * FROM bhl_page WHERE ItemID=' . $ItemID . ' AND PagePrefix="Page" and (PageNumber=' . $article->spage . ' OR PageNumber="[' . $article->spage . ']")';
		
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
			if ($result->NumRows() == 1)
			{
				$PageID = $result->fields['PageID'];
			
				// reset page
				echo "UPDATE rdmp_reference SET PageID=$PageID WHERE reference_id=$reference_id;\n";
			
				// update page range
				$page_range = array();
				if (isset($article->spage) && isset($article->epage))
				{
					$page_range = 
						bhl_page_range($PageID, $article->epage - $article->spage + 1);
				}
				else
				{
					// No epage, so just get spage (to do: how do we tell user we don't have page range?)
					$page_range = 
						bhl_page_range($PageID, 0);				
				}
	
	
				echo "DELETE FROM rdmp_reference_page_joiner WHERE reference_id=$reference_id;\n";
	
				$count = 0;
				foreach ($page_range as $page)
				{
					$sql = 'INSERT INTO rdmp_reference_page_joiner (reference_id, PageID, page_order) VALUES (' . $reference_id . ',' . $page . ',' . $count++ . ');';
					echo $sql . "\n";
				}		
			
			
			
			}
		

		}
	
	}
}

echo '-- $ids=array(' . join(',', $all_ids) . ');' . "\n";


?>