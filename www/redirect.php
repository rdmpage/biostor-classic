<?php

/* Start session and load library. */
session_start();
require_once(dirname(__FILE__) . '/mendeleyoauth/mendeleyoauth.php');
require_once(dirname(dirname(__FILE__)) . '/config.inc.php');

// Store where we will return to after logging in 
$_SESSION['oauth_url'] = '';
if (isset($_GET['url']))
{
	$_SESSION['oauth_url'] = $_GET['url'];
}

/* Build MendeleyOauth object with client credentials. */
$connection = new MendeleyOauth(CONSUMER_KEY, CONSUMER_SECRET);
 
/* Get temporary credentials. */
$request_token = $connection->getRequestToken(OAUTH_CALLBACK);

/* Save temporary credentials to session. */
$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
 
/* If last connection failed don't display authorization link. */

//echo $connection->http_code;

switch ($connection->http_code) {
  case 200:
    /* Build authorize URL and redirect user to Mendeley. */
    $url = $connection->getAuthorizeURL($token);
    header('Location: ' . $url); 
    break;
  default:
    /* Show notification if something went wrong. */
    echo 'Could not connect to Mendeley. Refresh the page or try again later.';
}
