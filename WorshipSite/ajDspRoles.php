<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_cache_limiter();
session_start();

require('lr/config.php');
require($baseDir.'/lr/functions.php'); 

if(isset($_REQUEST['act'])) {
	$action = $_REQUEST['act'];
	$typeID = $_REQUEST['typ'];
	$roleID = $_REQUEST['id'];
} else {
	exit;
}

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Delete Role
if($action=="del") {
	$q = "DELETE FROM roles WHERE roleID=$roleID";
	$resRole = $db->query($q);
}

// Load Schedule Role
$q = "SELECT * FROM roles WHERE typeID=$typeID ORDER BY roleDescription";

$resMbr = $db->query($q);
$roleDesc = "<table style='border:2px inset;border-collapse:collapse;width:100%'>\n";
if($resMbr && (mysqli_num_rows($resMbr) > 0)){
	$roleDesc .= "<tr style='background-color:#ebebeb;border-bottom:1px solid #000000;'><td>&nbsp;</td><td><b>Role Description</b></td><td><b>Change Request Rule</b></td></tr>\n";
	$shade = false;
	while($dbMbr=mysqli_fetch_array($resMbr)) {
		$bgcolor = $shade?"#efefef":"";
		$shade = !$shade;
		$roleDesc .= "	<tr bgcolor='$bgcolor'><td nowrap width='32'>\n";
		if (allow_access(Administrators) == "yes") { 
			$q = "SELECT * FROM members WHERE concat(',',roleArray,',') LIKE '%,".$dbMbr["roleID"].",%'";
			$resDel = $db->query($q);
			$roleDesc .= "		<a href='editRoles.php?&action=edit&roleID=".$dbMbr["roleID"]."&typeID=$typeID' title='Edit Role' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdRole',headingText: 'Edit Role',width: 540,height:180 });\"><img src=\"images/edit.png\"></a>\n";
			$roleDesc .= !$resDel || mysqli_num_rows($resDel)==0?"		<a onClick=\"delRole(".$dbMbr["roleID"].",'".$dbMbr["roleDescription"]."');\" href='#' title='Delete Role'><img src=\"images/icon_delete.gif\"></a>\n":"";
		}
		if($dbMbr["roleIcon"]!="") {
			$roleDesc .= "		</td><td><img src='".$dbMbr["roleIcon"]."'>\n";
		}
		$roleDesc .= "		".$dbMbr["roleDescription"]."</td>\n";
		if($dbMbr["changeRule"]==0) $changeRule = "No Change Requests Allowed";
		if($dbMbr["changeRule"]==1) $changeRule = "Open (No Approval Required)";
		if($dbMbr["changeRule"]==2) $changeRule = "Approval (Worship Coordinator to approve)";
		if($dbMbr["changeRule"]==3) $changeRule = "Closed (Worship coordinator receives request)";
		$roleDesc .= "		<td>$changeRule</td>\n";
		$roleDesc .= "	</tr>\n";
	}
} else {
	$roleDesc = "	<tr><td align='center'><br />No roles defined for this category</td></tr>\n";
}
echo $roleDesc."</table>\n";
?>