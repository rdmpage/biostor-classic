<?php

/**
 * @file (new) search.php
 *
 * Search 
 *
 */


require_once ('../config.inc.php');
require_once ('../db.php');
require_once (dirname(__FILE__) . '/html.php');
require_once ('../lib.php');


function search($query)
{
	$hits = null;
	//http://localhost:8983/solr/select?q=Florida&wt=json&facet.field=publication_outlet&facet=true&fl=*,score&facet.field=authors&facet.mincount=1&&hl=on&hl.fl=title
	
	$limit = 30;
	
	$params = array(
		'q' 				=> $query,
		
		// facets
		'facet'				=> 'on',
		'facet.field' 		=> array('publication_outlet', 'year', 'authors'),
		'facet.mincount'	=> 1,
		'facet.limit'		=> 5, // limit number of facets
		
		// Output scores
		'fl'				=> '*,score',
		
		// highlight
		'hl'				=> 'on',
		'hl.fl'				=> 'citation',
		
		// number of results to return
		'rows'				=> $limit,
		
		// JSON
		'wt' 				=> 'json'
		);
		
	$url = 'http://localhost:8983/solr/select?';
	
	
	// We need to remove the square brackets from the URL
	
	$query_string = http_build_query($params);
	$query_string = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $query_string);	
	
	$url .= $query_string;	
	//echo $url . "\n";
	
	$ch = curl_init(); 
	curl_setopt ($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	$response = curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	if (curl_errno ($ch) != 0 )
	{
		echo "CURL error: ", curl_errno ($ch), " ", curl_error($ch);
	}
	
	if ($http_code == 200)
	{
		$hits = json_decode($response);
	}
	
	//echo $response;
	
	return $hits;
}


$query = '';

if (isset($_GET['q']))
{
	$query = $_GET['q'];
	
	echo html_html_open();
	echo html_head_open();
	echo html_title (htmlspecialchars($query, ENT_QUOTES, 'UTF-8') . ' - ' . $config['site_name']);
	echo html_head_close();
	echo html_body_open();
	echo html_page_header(true, htmlspecialchars($query, ENT_QUOTES, 'UTF-8'));	
	echo '<h1>Search &quot;' .  htmlspecialchars($query, ENT_QUOTES, 'UTF-8') . '&quot;</h1>';
	
	if ($query == '')
	{
		echo '<p>Please enter a query</p>';
		echo html_body_close();
		echo html_html_close();		
		exit();
	}


	$hits = search($query);	
	
	if ($hits)
	{
		echo '<table>';
		
		echo '<tr>';
		
		// Facets
		echo '<td width="300" valign="top">';
		
		//print_r($hits->facet_counts->facet_fields);
		
		echo '<ul style="font-size:10px;">';
		foreach ($hits->facet_counts->facet_fields as $k => $v)
		{
			echo '<li>';
			echo $k;
			{
				$n = count($v);
				echo '<ul>';
				for ($i = 0; $i < $n; $i+=2)
				{
					echo '<li>';
					echo $v[$i] . ' (' . $v[$i+1] . ')';
					echo '</li>';
				}
				echo '</ul>';
					
			}
			echo '</li>';
		}
		echo '</ul>';
		
		echo '</td>';
		
		// Hits
		echo '<td valign="top">';
		echo '<table>';
		
		foreach ($hits->response->docs as $doc)
		{
			echo '<tr>';
			echo '<td>';
			
			if (preg_match('/reference\/(?<id>\d+)/', $doc->id, $m))
			{
				$pages = bhl_retrieve_reference_pages($m['id']);
				
				if (isset($pages[0]->PageID))
				{
					$image = bhl_fetch_page_image($pages[0]->PageID);
					$imageURL = $image->thumbnail->url;
					echo '<img style="border:1px solid rgb(192,192,192);" src="' . $imageURL . '" width="40" />';
				}
				else
				{
					echo '<img style="border:1px solid rgb(192,192,192);" src="' . $config['web_root'] .  'images/blank70x100.png' . '" width="40" />';
				}
			}
	
			echo '</td>';
			
			echo '<td valign="top">';
			echo '<span><a href="' . $doc->id . '">' . $doc->title . '</a></span><br />';
			echo '<span style="color:green;font-size:12px;">';
			
			$str = $hits->highlighting->{$doc->id}->citation[0];
			$str = str_replace('<em>', '<b>', $str);
			$str = str_replace('</em>', '</b>', $str);
			
			echo $str;
			echo '</span>';
			echo '</td>';
			echo '</tr>';
		}
	
		echo '</table>';
		
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		
	}
	
	/*	
	echo '<pre>';
	print_r($hits);
	echo '</pre>';
	*/
	
	/*
	foreach ($hits as $k => $v)
	{
		echo '<h2>';
		switch ($k)
		{
			case 'author':
				echo "Authors";
				break;
			case 'title':
				echo "Reference";
				break;
			default:
				echo "[Unknown]";
				break;
		}
		echo '</h2>';
		echo '<ol>';
		foreach ($v as $hit)
		{
			echo '<li style="padding:4px;"><a href="' . $hit->uri . '">' . $hit->snippet . '</a></li>';
			//print_r($hit);
		}
		echo '</ol>';
		echo '</li>';
	}

	*/
	echo html_body_close();
	echo html_html_close();



}
else
{
	echo html_html_open();
	echo html_head_open();
	echo html_title ('Search');
	echo html_head_close();
	echo html_body_open();
	echo html_page_header(true);	
	echo '<h1>Search</h1>';
	echo html_body_close();
	echo html_html_close();
}



?>