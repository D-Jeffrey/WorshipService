<?php

//start session
session_start();

//include config and functions files
include ("../config.php");
include ("../functions.php");

echo "<p><b><font face=\"Tahoma\" size=\"2\"><a href=\"edit_links.php\">Add/Change Favorites</a></font></b></p>";

//make the connection to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());


//make query to database
$sql ="SELECT * FROM favorites WHERE username= '". $_SESSION["user_name"] ."'";
$result = @$db->query($sql) or die(mysqli_error());

while ($sql = mysqli_fetch_object($result))
{
	$_link = $sql -> link;
	$_nickname = $sql -> nickname; 
	echo "<font face=\"Tahoma\" size=\"2\"><a target=\"_blank\" href=\"$_link\">$_nickname</a></font><br>";
}

?>
