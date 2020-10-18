<?php

session_start();

include("../config.php");
include("../functions.php");


//make connection to dbase
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());


$sql="SELECT * FROM log_login ORDER BY '$_POST[D1]'";
$result = @$db->query($sql) or die(mysqli_error());

while ($sql = mysqli_fetch_object($result)) 
{
	$user = $sql -> username;
	$whend	=	$sql -> date;
	$whent	=	$sql -> time;
	$ip_add	=	$sql -> ip_addr;
	$operat	= 	$sql -> oper_sys;
	$browse	=	$sql -> brow;
	
	echo "<p><font size=\"1\" face=\"Tahoma\"><b>Username: 	</b>$user</font><br>";
	echo "<font size=\"1\" face=\"Tahoma\"><b>Date:     	</b>$whend</font><br>";
	echo "<font size=\"1\" face=\"Tahoma\"><b>Time: 		</b>$whent</font><br>";
	echo "<font size=\"1\" face=\"Tahoma\"><b>IP Addres: </b>$ip_add</font><br>";
	echo "<font size=\"1\" face=\"Tahoma\"><b>O/S: 		</b>$operat</font><br>";
	echo "<font size=\"1\" face=\"Tahoma\"><b>Browser: 		</b>$browse</font></p>";
}

?>