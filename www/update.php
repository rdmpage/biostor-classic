<?php

/**
 * @file update.php
 *
 * Update a database record, with checks that user has permission, etc.
 * Permission might be recaptcha, for example.
 *
 * Called as a Ajax web service with variables passed in $_POST, returns JSON response
 *
 */
session_start();

$can_update = false;
$response = new stdclass;
$response->is_valid = false;

require_once ('../config.inc.php');
require_once ('../db.php');
require_once ('../nameparse.php');
require_once ('../recaptcha-php-1.10/recaptchalib.php');
require_once ('../user.php');

if (1)
{
	$o = print_r($_POST, true);
	file_put_contents('/tmp/' . uniqid() . '.json', $o);
}

if (!isset($_POST))
{
	header('HTTP/1.1 404 Not Found');
	header('Status: 404 Not Found');
	$_SERVER['REDIRECT_STATUS'] = 404;	
	
	echo "No variables passed to web service";
	
	exit();
}

// We can update only if user is logged in, or has passed recaptcha

// Check whether user is logged
if (user_is_logged_in())
{
	$can_update = true;
	$response->is_valid = true;
}
else
{
	if (isset($_POST['recaptcha_response_field']))
	{
		$response = recaptcha_check_answer ($config['recaptcha_privatekey'],
										$_SERVER["REMOTE_ADDR"],
										$_POST["recaptcha_challenge_field"],
										$_POST["recaptcha_response_field"]);
										
		$can_update = $response->is_valid;
	}
	else
	{
		$can_update = true;
	}
}

// Are we storing (e.g., called by openurl.php) or updating (called by display_reference.php)
$updating = false;
if (isset($_POST['update']))
{
	$updating = true;
	$response->updating = 1;
}

if ($can_update)
{
	// update
	$reference = new stdclass;
	$reference->authors = array();
	$PageID = 0;
	
	foreach ($_POST as $key => $value)
	{
		switch ($key)
		{
			case 'reference_id':
			case 'genre':
			case 'title':
			case 'secondary_title':
			case 'volume':
			case 'series':
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
				$reference->{$key} = stripcslashes($value);
				break;
				
			// database contributor
			case 'contributor':
				$reference->{$key} = stripcslashes($value);
				break;
				
				
			case 'PageID':
				if (!$updating)
				{
					$PageID = $value;
				}
				break;
				

			case 'authors':
				//$value = $value;
				if ($value != '')
				{
					$authors = explode("\n", trim($value));
					//$authors = preg_split('/\n/u', $value);
					$reference->a = $authors;
					foreach ($authors as $v)
					{
						$v = trim($v);
						$parts = parse_name($v);					
						$author = new stdClass();
						
						//$author->lastname = $v;
						//$author->forename = $v;
						/*
						$matched = false;
						if (!$matched)
						{
							if (preg_match('/(?<last>.*),\s=(?<first>.*)/Uu', $v, $m))
							{
								$author->forename = $m['first'];
								$author->lastname = $m['last'];
								$matched = true;
							}
						}
						if (!$matched)
						{
							if (preg_match('/(?<first>.*)\s+(?<last>\w+(-\w+))$/Uu', $v, $m))
							{
								$author->forename = $m['first'];
								$author->lastname = $m['last'];
								$matched = true;
							}
						}
						*/
						
						
						if (isset($parts['last']))
						{
							$author->lastname = $parts['last'];
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
						}
						
						$reference->authors[] = $author;
					}
				}
				break;
						
			default:
				break;
		} 
	}
	
	if (1)
	{
		$o = print_r($_POST, true);
		file_put_contents('/tmp/reference-' . uniqid() . '.json', $o);
		$o = print_r($reference, true);
		file_put_contents('/tmp/reference-' . uniqid() . '.json', $o);
	}
	
	db_store_article($reference, $PageID, $updating);
}

header("Content-type: text/plain; charset=utf-8\n\n");
echo json_encode($response);

?>