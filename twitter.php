<?php

require_once(dirname(__FILE__) . '/config.inc.php');

// OAuth
require_once(dirname(__FILE__) . '/twitteroauth/twitteroauth.php');

function getConnectionWithAccessToken($oauth_token, $oauth_token_secret) 
{
	global $config;
	
	$connection = new TwitterOAuth(
		$config['twitter_consumer_key'], 
		$config['twitter_consumer_secret'], 
		$oauth_token, 
		$oauth_token_secret);
	return $connection;
}


function tweet($update)
{
	global $config; 

	$connection = getConnectionWithAccessToken($config['twitter_oauth_token'], 
		$config['twitter_oauth_token_secret']);

	$parameters = array('status' => $update);
	$status = $connection->post('statuses/update', $parameters);
	
	// Don't uncomment this as it will break "Update" button in OpenURL results
	//print_r($status);
}

// test
/*
$update = "Test from BioStor";
tweet($update);
*/

?>