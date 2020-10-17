<?php
/*******************************************************************
 * ajDelGroupMember.php
 * Remove Group Member
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

$isAdmin = allow_access(Administrators) == "yes";

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Remove member from group
$q ="SELECT groupArray FROM members WHERE memberID = ".$_REQUEST["memberID"];
$resMbr = $db->query($q);
$dbMbr=mysqli_fetch_array($resMbr);
$groupArray = explode(",",$dbMbr["groupArray"]);
$arPos = array_search($_REQUEST["groupID"],$groupArray);
if($arPos!==FALSE) {
	unset($groupArray[$arPos]);
	$updGroup = implode(",",$groupArray);
	$q ="UPDATE members SET groupArray='$updGroup' WHERE memberID = ".$_REQUEST["memberID"];
	$resMbr = $db->query($q);
}

// Retrieve Group Members
$q ="SELECT * FROM members WHERE concat(',',groupArray,',') like concat(',','%".$_REQUEST["groupID"]."%',',')";
$resGrp = $db->query($q);
if(!$resGrp || (mysqli_num_rows($resGrp) == 0)) {
	exit;
}
echo "<table width='100%'>\n";
$odd = false;
while($dbGrp=mysqli_fetch_array($resGrp)) {
	$shade=$odd?" style='background-color:#ebebeb'":"";
	$odd = !$odd;
	echo "	<tr$shade>\n";
	echo "		<td>\n";
	echo $isAdmin?"<a href='#' onClick=\"delGroupMember($memberID,".$dbGrp["memberID"].",'".$dbGrp["mbrFirstName"]." ".$dbGrp["mbrLastName"]."')\" title=\"Delete Group Member\"><img border='0' src='/images/icon_delete.gif' /></a>&nbsp;\n":"";
	echo "			".$dbGrp["mbrFirstName"]." ".$dbGrp["mbrLastName"]."\n";
	echo "		</td>\n";
	if($dbGrp=mysqli_fetch_array($resGrp)) {
		echo "		<td>\n";
		echo $isAdmin?"<a href='#' onClick=\"delGroupMember($memberID,".$dbGrp["memberID"].",'".$dbGrp["mbrFirstName"]." ".$dbGrp["mbrLastName"]."')\" title=\"Delete Group Member\"><img border='0' src='/images/icon_delete.gif' /></a>&nbsp;\n":"";
		echo "			".$dbGrp["mbrFirstName"]." ".$dbGrp["mbrLastName"]."\n";
		echo "		</td>\n";
		if($dbGrp=mysqli_fetch_array($resGrp)) {
			echo "		<td>\n";
			echo $isAdmin?"<a href='#' onClick=\"delGroupMember($memberID,".$dbGrp["memberID"].",'".$dbGrp["mbrFirstName"]." ".$dbGrp["mbrLastName"]."')\" title=\"Delete Group Member\"><img border='0' src='/images/icon_delete.gif' /></a>&nbsp;\n":"";
			echo "			".$dbGrp["mbrFirstName"]." ".$dbGrp["mbrLastName"]."\n";
			echo "		</td>\n";
		} else {
			echo "		<td>&nbsp;</td>\n";
		}
	} else {
		echo "		<td colspan='2'>&nbsp;</td>\n";
	}
	echo "	</tr>\n";
}
echo "</table>\n";
?>
