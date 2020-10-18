<?php

session_start();

//include config file
require_once('config.php');

//sets date and time variables
$last = gmdate("Y-m-d");
$time = gmdate("H:i", time() + $zone);

$viewer = $HTTP_USER_AGENT;

//checks to see if the browser the user is using is determinable
$browser = "unknown";
if (preg_match("/Netscape/", $viewer))
{
	$browser = "Netscape";
}
else if (preg_match("/Opera/", $viewer))
{
	$browser = "Opera";
}else if (preg_match("/Firefox/", $viewer))
{
	$browser = "FireFox";
}else if (preg_match("/MSIE/", $viewer))
{
	$browser = "Internet Explorer";
}

//checks to see if the OS the user is using is determinable
$platform = "unknown";
if (preg_match("/Windows NT/", $viewer))
{
	$platform = "Windows";
}
else if (preg_match("/Windows CE/", $viewer))
{
	$platform = "Windows PPC";
}
else if (preg_match("/Linux/", $viewer))
{
	$platform = "Linux";
}
else if (preg_match("/Mac/", $viewer))
{
	$platform = "MAC";
}

//make the connection to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

//build and issue the query
$sql ="INSERT INTO log_login VALUES
	('$_SESSION[user_name]', '$last', '$time', '$REMOTE_ADDR', '$platform', '$browser')";
$result = $db ->query($sql) or die(mysqli_error());
$db->close();

?>