<?php

//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();

session_start();

//include config and functions pages
include ('config.php');
include ('functions.php');

//if a user is trying to access this page without logging in first - send them back to login
if (!$_SESSION[user_name])
{
	header('Location:login.php');
	exit;
}

//make connection to dbase
$db = @mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());

//update the table with the new email address				
	$sql = "UPDATE $table_name SET 
			mbrEmail1 = '$_POST[email]' 
			WHERE mbrUName = '$_SESSION[user_name]'";
	$result = @$db->query($sql) or die(mysqli_error());

//after table is updated, send the use back to their redirect to page
	header("Location:$_SESSION[redirect]");
	exit;
?>
