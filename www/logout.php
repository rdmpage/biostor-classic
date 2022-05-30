<?php

/* Load and clear sessions */
session_start();
session_destroy();

setcookie("openid", "", time() - 3600);

/* Redirect to home page */
header('Location: ' . $_SERVER['HTTP_REFERER']);

?>
