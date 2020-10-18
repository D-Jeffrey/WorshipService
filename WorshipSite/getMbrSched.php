<?php
/*******************************************************************
 * getMbrSched.php
 * Format Member Schedule Information
 *******************************************************************/
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

require($_SERVER["DOCUMENT_ROOT"].'/lr/config.php');
require($_SERVER["DOCUMENT_ROOT"].'/lr/functions.php'); 
//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Users) != "yes") {
	exit;
}

include 'fnNicetime.php';

$memberID = $_REQUEST["memberID"];

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


//build and issue the query
$q ="SELECT * FROM members WHERE memberID = $memberID";
$resMbr = $db->query($q);
if(!$resMbr || (mysqli_num_rows($resMbr) == 0)) {
	exit;
}
$dbMbr=mysqli_fetch_array($resMbr);

/* Retrieve member service schedule */
$q = "SELECT services.serviceID as svcID, svcTeamNotes, date_format(svcPractice, '%W %M %D') as svcPDATE, date_format(svcPractice, '%H:%i') as svcPTIME, date_format(svcDateTime, '%a %b %D') as svcDATE, date_format(svcDateTime, '%H:%i') as svcTIME,roleIcon,roleDescription FROM services INNER JOIN serviceteam ON services.serviceID = serviceteam.serviceID INNER JOIN roles on serviceteam.roleID = roles.roleID WHERE (serviceteam.memberID = $memberID OR concat(',','".$dbMbr["groupArray"]."',',') LIKE concat('%,',serviceteam.memberID,',%'))" .
       // " AND svcDateTime>='".date("Y-m-d"). "'".
        " ORDER BY svcDateTime, roleDescription";
                
$resSched = $db->query($q);
$mbrName = $dbMbr["mbrType"]=="G"?$dbMbr["mbrLastName"]:$dbMbr["mbrFirstName"]." ".$dbMbr["mbrLastName"];
echo "<b class=\"b1h\"></b><b class=\"b2h\"></b><b class=\"b3h\"></b><b class=\"b4h\"></b>\n";
echo "	<div class=\"headh\">\n";
echo "		<h4 align='center'>Schedule for $mbrName</h4>\n";
echo "	</div>\n";
echo "	<div class=\"contenth\"><div>\n";
echo "	<table width='100%' cellpadding='0' cellspacing='0'>";
$saveID = 0;
$shade=false;
$numRoles = 0;

if(mysqli_num_rows($resSched)>0) {
	while($dbSch=mysqli_fetch_array($resSched)) {
		if($saveID != $dbSch["svcID"] && $saveID != 0) {
			$bgcolor=$shade?"#ebebeb":"#ffffff";
			$shade = !$shade;
			echo "<tr bgcolor='$bgcolor'><td><a title='Display Service Details' href='editService.php?id=$saveID&action=edit'>$svcDateTime</a></td><td>$schRoles</td></tr>\n";
		}
		if($saveID != $dbSch["svcID"]) {
			$schRoles = "";
			$svcDateTime = $dbSch["svcDATE"]." - ".nicetime($dbSch["svcTIME"]);
		}
		$numRoles++;
		$schRoles .= "<img align=middle src=".$dbSch["roleIcon"].">&nbsp;&nbsp;".$dbSch["roleDescription"]."<br />";
		$saveID = $dbSch["svcID"];
	}
	$bgcolor=$shade?"#ebebeb":"#ffffff";
	$shade = !$shade;
	echo "<tr bgcolor='$bgcolor'><td><a title='Display Service Details' href='editService.php?id=$saveID&action=edit'>$svcDateTime</a></td><td>$schRoles</td></tr>\n";
} else {
	echo "<tr><td colspan='2' style='text-align:center;'>No service scheduled at this time</td></tr>\n";
}
echo "</table>\n";
echo "	</div></div><b class=\"b4bh\"></b><b class=\"b3bh\"></b><b class=\"b2bh\"></b><b class=\"b1h\"></b>\n";



?>
