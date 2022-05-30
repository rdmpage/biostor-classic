<?php

error_reporting(E_ALL);

// very crude login

require_once ('../user.php');


$username = '';

if (isset($_GET['username']))
{
	$username = trim($_GET['username']);
}

print_r($_GET);


$ok = false;

if ($username != "")
{
	if (preg_match('/^[A-Z0-9a-z._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,6}$/', $username))
	{
		$user = user_with_email($username);
		
		if ($user)
		{
			$ok = true;
			
			//print_r($user);
		}
	}


}

if ($ok)
{
	session_start();

	setcookie("openid", $username, time()+3600);
	
	/* Redirect to home page */
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	
}
else
{
	/* Load and clear sessions */
	session_start();
	session_destroy();

	setcookie("openid", "", time() - 3600);
	
	/* Redirect to home page */
	header('Location: ./index.php');
}

?>
