<?php

//prevent caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();

//start session
session_start();

//include config and functions files
include ("../config.php");
include ("../functions.php");

//make the connection to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());


//make query to database
$sql ="SELECT * FROM favorites WHERE username= '$_SESSION['user_name'']'";
$result = @$db->query($sql) or die(mysqli_error());

if ($_POST['nick'] != "")
{
	//make query to database
	$sql ="INSERT INTO favorites VALUES ('$_SESSION['user_name']', '$_POST['nick']', '$_POST['link']')";
	$result = @$db->query($sql) or die(mysqli_error());
}

if ($_POST['del_fav'] != "")
{
	//make query to database
	$sql ="DELETE FROM favorites WHERE username = '$_SESSION['user_name']' AND nickname = '$_POST['del_fav']'";
	$result = @$db->query($sql) or die(mysqli_error());
}

if ($_POST['this_fav'] != "")
{
	//make query to database
	$sql ="DELETE FROM favorites WHERE username = '$_SESSION['user_name']' AND nickname = '$_POST['this_fav']'";
	$sql2 = "INSERT INTO favorites VALUES ('$_SESSION['user_name']', '$_POST['new_nick']', '$_POST['new_link']')";
	$result = @$db->query($sql) or die(mysqli_error());
	$result2 = @$db->query($sql2) or die(mysqli_error());
}

header("Location:links.php");

?>