<?php

/**
 * @file user.php Users
 *
 */
 
session_start(); // vital! Without this user_is_logged_in will fail

require_once (dirname(__FILE__) . '/db.php');

//--------------------------------------------------------------------------------------------------
function user_is_logged_in()
{
	global $config;
	
	if (0)
	{
		return true;
	}
	else
	{
		if ($config['use_mendeley_oauth'])
		{
			$logged_in = false;
			
			if (empty($_SESSION['access_token']) 
				|| empty($_SESSION['access_token']['oauth_token']) 
				|| empty($_SESSION['access_token']['oauth_token_secret'])) 
			{
				$logged_in = false;
			}
			else
			{
				$logged_in = true;
			}
			return $logged_in;
		}
		else
		{
			return isset($_COOKIE['openid']);
		}
	}
}

//--------------------------------------------------------------------------------------------------
function user_with_openid($openid)
{
	global $db;
	
	$user = NULL;
	
	$sql = 'SELECT * FROM rdmp_user WHERE openid=' . $db->qstr($openid) . ' LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$user = new stdclass;
		
		$user->user_id  = $result->fields['user_id'];
		$user->openid  = $result->fields['openid'];
		$user->username  = $result->fields['username'];
		$user->email  = $result->fields['email'];
	}
	
	return $user;
}

?>