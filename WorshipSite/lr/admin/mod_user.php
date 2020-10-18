<?php
// Obsoleted
die("This function is no longer value please contact SCCC IT Support, if you need this\n")

//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();

session_start();

include ('../config.php');
include ('../functions.php');
//make connection to dbase
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());


if ($_POST['del_user'] != "") {
	$sql = "SELECT * FROM $table_name WHERE mbrUName = '$_POST['del_user']'";
	$result = @$db->query($sql) or die(mysqli_error());
	//get the number of rows in the result set
	$num = mysqli_num_rows($result);
	//set session variables if there is a match
	if ($num != 0) {
		$trash_user = "INSERT INTO trash select *,'$del_dat' FROM $table_name WHERE mbrUName = '$_POST['del_user']'";
		$del = "DELETE FROM $table_name WHERE mbrUName = '$_POST['del_user']'";
		
		$result1 = @$db->query($trash_user) or die(mysqli_error());
		$result = @$db->query($del) or die(mysqli_error());
		
		$msg .= "User $_POST['del_user'] has been trashed from the database.<br>";
	} else {
		$msg .= "User $_POST['del_user'] could not be located in the database.<br>";
	}
	
	$del_banned = "DELETE FROM banned WHERE no_access = '$_POST['del_user']'";
		$result = @$db->query($del_banned) or die(mysqli_error());


}

if (($_POST['username'] != "") && ($_POST['mod_pass'] == "Same as Old")) {
	$sql = "UPDATE $table_name SET mbrFirstName='$_POST['mod_first']', mbrLastName='$_POST['mod_last']', mbrUName='$_POST['username']', mbrGroup1='$_POST['mod_group1']', mbrGroup2='$_POST['mod_group2']', mbrGroup3='$_POST['mod_group3']', pchange='0', mbrEmail1='$_POST['mod_email']', redirect='$_POST['mod_redirect']' WHERE mbrUName = '$_POST['username']'";
	$result = @$db->query($sql) or die(mysqli_error());
	$msg .= "The information for $_POST['username'] has been changed updated.<br>";
}

if (($_POST['username'] != "") && ($_POST['mod_pass'] != "Same as Old")) {
	$sql = "UPDATE $table_name SET mbrFirstName='$_POST['mod_first']', mbrLastName='$_POST['mod_last']', mbrUName='$_POST['username']', mbrPassword=password('$_POST['mod_pass']'), mbrGroup1='$_POST['mod_group1']', mbrGroup2='$_POST['mod_group2']', mbrGroup3='$_POST['mod_group3']', pchange='0', mbrEmail1='$_POST['mod_email']', redirect='$_POST['mod_redirect']' WHERE mbrUName = '$_POST['username']'";
	$result = @$db->query($sql) or die(mysqli_error());
	$msg .= "The information for $_POST['username'] has been changed updated.<br>";
}	
 
if ($_POST['ban_user'] != "") {
	$ban = "INSERT INTO banned (no_access, type) VALUES ('$_POST['ban_user']', 'user')";
	$result = @$db->query($ban) or die(mysqli_error());
	$msg .= "User $_POST['ban_user'] has been banned.<br>";
}

$ip_addr = "$_POST['oct1'].$_POST['oct2'].$_POST['oct3'].$_POST['oct4']";

if ($ip_addr != "...") {
	$ban_ip = "INSERT INTO banned (no_access, type) VALUES ('$ip_addr', 'ip')";
	$result = @$db->query($ban_ip) or die(mysqli_error());
	$msg .= "IP Address $ip_addr has been banned.<br>";
}

if ($_POST['lift_user_ban'] != "") {
	$lift_user = "DELETE FROM banned (no_access, type) WHERE no_access = '$_POST['lift_user_ban']'";
	$result = @$db->query($lift_user) or die(mysqli_error());
	$msg .= "The Ban for user $_POST['lift_user_ban'] has been lifted.<br>";
}	

if ($_POST['lift_ip_ban'] != "") {
	$lift_ip = "DELETE FROM banned (no_access, type) WHERE no_access = '$_POST['lift_ip_ban']'";
	$result = @$db->query($lift_ip) or die(mysqli_error());
	$msg .= "The Ban for IP Address $_POST['lift_ip_ban'] has been lifted.<br>";
}

if ($_POST['restore'] != "") {
	$ruser = "SELECT * FROM trash WHERE mbrUName = '$_POST['restore']'";
	$result0 = @$db->query($ruser) or die(mysqli_error());
	//get the number of rows in the result set
	$num = mysqli_num_rows($result0);
	//set session variables if there is a match
	if ($num != 0) {
		$r_user = "INSERT INTO $table_name select *,'$del_dat' FROM trash WHERE mbrUName = '$_POST['restore']'";
		$del = "DELETE FROM trash WHERE mbrUName = '$_POST['restore']'";

		$result1 = @$db->query($r_user) or die(mysqli_error());
		$result = @$db->query($del) or die(mysqli_error());
	
		$msg .= "User ".$_POST['restore']." has been restored.<br>";
	} else {
		$msg .= "User ".$_POST['restore']." could not be located in the database.<br>";
	}
}

if ($_POST['empt_trash'] == "yes") {
	$empty = "DELETE FROM trash";
	$gone = @$db->query($empty) or die(mysqli_error());
	
	$msg .= "The trash has been emptied.<br>";
}

if ($_POST['amt_time'] != "" &&  $_POST['incr_time'] != "") {
	$msg .= "The following accounts were inactive for $amt_time $incr_time or more and have been moved to the trash.<br><br>";
	$killtime = "NOW() - INTERVAL $_POST['amt_time'] $_POST['incr_time']";
	$xfer = "SELECT * FROM $table_name WHERE last_login < $killtime";
	$resultp1 = @$db->query($xfer) or die(mysqli_error());
	while ($xfer = mysqli_fetch_object($resultp1))
	{
		$puname	= $xfer -> mbrUName;
		$pdel_date	= last_login();
		
		$msg .= "$puname<br>";
		$xfer2 = "INSERT INTO trash select *,'$del_dat' FROM $table_name WHERE mbrUName = '$puname'";
		$del = "DELETE FROM $table_name WHERE mbrUName = '$puname'";
		
		$result1 = @$db->query($xfer2) or die(mysqli_error());
		$result = @$db->query($del) or die(mysqli_error());
	}
}


echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"adminpage.css\">";

echo $msg;

if ($_POST['username'] == $_SESSION['user_name']) {
	session_destroy();
	echo "<html>";
	echo "<head>";
	echo "<meta http-equiv=\"refresh\" content=\"3; url=../login.php\">";
	echo "<title>New Page 2</title>";
	echo "</head>";
	exit;
}
?>

<html>

<head>
<meta http-equiv="refresh" content="3; url=adminpage.php">
<title>Modify User</title>
</head>

<body>

</body>

</html>


