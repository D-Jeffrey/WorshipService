<?php
session_start();
require('lr/config.php');
require('lr/functions.php'); 
if ($use_phpBB3) {

	// Login to the phpBB forum
	include("phpBB3/includes/extBridge.php");
	$phpbb = new PhpBB3Component;
	$phpbb->startup();
	$phpbb->login($_SESSION[user_name],$_SESSION[password],$_SESSION[email]);
	header("Location: phpBB3/index.php");
}
else
{
	header("Location: /index.php");
}
?>