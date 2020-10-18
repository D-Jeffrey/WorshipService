<?php

//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();

session_start();

//clear session variables
session_unset();


//require the functions file
require ("config.php");
require ("functions.php");

//check to see if user name entered in login form
if ((isset($_POST['username'])) && ($_POST['username']!="")) {
	$username = $_POST['username'];
	$password = $_POST['password'];
//check to see if cookies are already set, remember me
} else if ((!$lr_user) || (!$lr_pass)) {
	$username = "";
	$password = "";
} else {
	$username = $lr_user;
	$password = $lr_pass;
}

//if username or password is blank, send to errorlogin.html
if ((!$username) || (!$password)) {
	header("Location:$base_dir/login.php?msg=0");
	exit;
}


//sets cookies to remember this computer if the user asks to
if ($_POST["remember"] == "Yes") {
	setcookie("lr_user", $username, $duration, "/", $domain);
// 	setcookie("lr_pass", $password, $duration, "/", $domain);
}
//make the connection to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


if ($_POST['activate'] == "Yes") {
	

	//build and issue the query
	$sql ="UPDATE $table_name SET mbrStatus = 'A' WHERE mbrUName = '$username'";
	$result = mysqli_query($db, $sql) or die(mysqli_error());
	
}

//sets session variables
sess_vars($base_dir, DB_SERVER, DB_USER, DB_PASS, DB_NAME, $table_name, $username, $password);

//check to see if the user has activated the account
if ($_SESSION["mbrStatus"] == "P") {
	$_SESSION["redirect"] = "$base_dir/login.php?msg=1";
}

//check to see if the user is active
if ($_SESSION["mbrStatus"] == "X") {
	$_SESSION["redirect"] = "$base_dir/login.php?msg=4";
}

//check to see if the user has to change their password
if ($_SESSION["pchange"] == "1") {
	$_SESSION["redirect"] = "$base_dir/pass_change.html";
}

		
//build and issue the query
$sql ="SELECT * FROM banned";
$result = $db -> query($sql) or die(mysqli_error());

if (mysqli_num_rows($result) > 0) {
	while ($sql = mysqli_fetch_object($result)) {
		$banned = $sql -> no_access;
		if ($username == $banned || $REMOTE_ADDR == $banned) {
			include ('banned.html');
			mysqli_free_result($result);
			mysqli_close($db);
			exit;
		}
	}

	mysqli_free_result($result);
}

$last_log = last_login();

//updates table with last log as now
$sql = "UPDATE $table_name SET last_login = '$last_log' WHERE mbrUName = '$username'";
$result = mysqli_query($db, $sql) or die(mysqli_error());

mysqli_close($db);

if (($_SESSION["redirect"] != "$base_dir/login.php?msg=0") && (LOG_LOGON)) {
	include('loglogin.php');
}
//redirects the user	
if(isset($_POST["ref"]) && $_POST["ref"]!="") {
	header("Location:".$_POST["ref"]);
} else {

  header("Location:" . $_SESSION['redirect']);
}
?>
<head><title>Redirect</title></head>



