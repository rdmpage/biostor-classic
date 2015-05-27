<?php
/**
 * @file
 * Take the user when they return from Mendeley. Get access tokens.
 * Verify credentials and redirect to based on response from Mendeley.
 */

/* Start session and load lib */
session_start();
require_once(dirname(__FILE__) . '/mendeleyoauth/mendeleyoauth.php');
require_once(dirname(dirname(__FILE__)) . '/config.inc.php');

/* If the oauth_token is old redirect to the connect page. */
if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
  $_SESSION['oauth_status'] = 'oldtoken';
  header('Location: ./clearsessions.php');
}

/* Create MendeleyOauth object with app key/secret and token key/secret from default phase */
$connection = new MendeleyOauth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

/* Request access tokens from Mendeley */
$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

/* Save the access tokens. Normally these would be saved in a database for future use. */
$_SESSION['access_token'] = $access_token;

/* Remove no longer needed request tokens */
unset($_SESSION['oauth_token']);
unset($_SESSION['oauth_token_secret']);

/* If HTTP response is 200 continue otherwise send to connect page to retry */
if (200 == $connection->http_code) {
  /* The user has been verified and the access tokens can be saved for future use */
  $_SESSION['status'] = 'verified';
  
  if (empty($_SESSION['oauth_url']))
  {
  	header('Location: ./index.php');
  }
  else
  {
  	header('Location: ' . $_SESSION['oauth_url']);
  }
} else {
  /* Save HTTP status for error dialog on connnect page.*/
  header('Location: ./clearsessions.php');
}
