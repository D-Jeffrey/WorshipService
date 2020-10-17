<?php
/*******************************************************************
 * getMember.php
 * Format Member Information
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

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


//build and issue the query
$q ="SELECT * FROM members WHERE memberID = ".$_REQUEST["memberID"];
$resMbr = $db->query($q);
if(!$resMbr || (mysqli_num_rows($resMbr) == 0)) {
	exit;
}
$dbMbr=mysqli_fetch_array($resMbr);
echo "<b class=\"b1h\"></b><b class=\"b2h\"></b><b class=\"b3h\"></b><b class=\"b4h\"></b>\n";
echo "	<div class=\"headh\">\n";
echo "		<h4 align='center'>Member Information</h4>\n";
echo "	</div>\n";
echo "	<div class=\"contenth\"><div>\n";
echo "<table>\n";
echo "	<tr><td>First Name:</td><td>".$dbMbr["mbrFirstName"]."</td></tr>\n";
echo "	<tr><td>Last Name:</td><td>".$dbMbr["mbrLastName"]."</td></tr>\n";
echo "	<tr><td>User Name:</td><td>".$dbMbr["mbrUName"]."</td></tr>\n";
echo "	<tr><td>Email Address:</td><td>".$dbMbr["mbrEmail1"]."</td></tr>\n";
echo "	<tr><td>Alternate Email:</td><td>".$dbMbr["mbrEmail2"]."</td></tr>\n";
echo "	<tr><td>Home Phone:</td><td>".$dbMbr["mbrHPhone"]."</td></tr>\n";
echo "	<tr><td>Work Phone:</td><td>".$dbMbr["mbrWPhone"]."</td></tr>\n";
echo "	<tr><td>Cell Phone:</td><td>".$dbMbr["mbrCPhone"]."</td></tr>\n";
if($dbMbr["mbrStatus"]=="A") {
	$sts = "<span style='background-color:green;color:#ffffff'>&nbsp;Active&nbsp;</span>";
} else if($dbMbr["mbrStatus"]=="P") {
	$sts = "<span style='background-color:yellow'>&nbsp;Pending&nbsp;</span>";
} else if($dbMbr["mbrStatus"]=="X") {
	$sts = "<span style='background-color:#cc0000;color:#ffffff'>&nbsp;Disabled&nbsp;</span>";
}
echo "	<tr><td>Status:</td><td>".$sts."</td></tr>\n";
echo "	<tr><td valign=\"top\">Roles:</td><td>\n";
$aRoles = explode(",",$dbMbr["roleArray"]);
if(count($aRoles)>0) {
	for($i=0;$i<count($aRoles);$i++) {
		$q ="SELECT roleDescription, roleIcon FROM roles WHERE roleID = ".$aRoles[$i];
		$resRole = $db->query($q);
		if($resRole && (mysqli_num_rows($resRole) > 0)) {
			$dbRole=mysqli_fetch_array($resRole);
			echo "		<img src='".$dbRole["roleIcon"]."' />&nbsp;".$dbRole["roleDescription"]."<br />\n";
		}
	}
}
echo "</td></tr>\n";
if($dbMbr["groupArray"]!="") {
	echo "	<tr><td valign=\"top\">Groups:</td><td>\n";
	$aGroups = explode(",",$dbMbr["groupArray"]);
	if(count($aGroups)>0) {
		for($i=0;$i<count($aGroups);$i++) {
			$q = "SELECT * FROM members LEFT JOIN roles ON members.roleArray = roles.roleID WHERE memberID = ".$aGroups[$i]." ORDER BY mbrLastName";
			$resGroup = $db->query($q);
			if($resGroup && (mysqli_num_rows($resGroup) > 0)) {
				$dbGroup=mysqli_fetch_array($resGroup);
				echo "		<img src='".$dbGroup["roleIcon"]."' />&nbsp;".$dbGroup["mbrLastName"]."<br />\n";
			}
		}
	}
	echo "</td></tr>\n";
}
echo "</table>\n";
echo "	</div></div><b class=\"b4bh\"></b><b class=\"b3bh\"></b><b class=\"b2bh\"></b><b class=\"b1h\"></b>\n";
?>
