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
	$typeID = $_REQUEST['id'];
} else {
	exit;
}

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Delete Role category
if($action=="del") {
	$q = "DELETE FROM roletypes WHERE typeID=$typeID";
	$resRole = $db->query($q);
}

// Load Schedule Role
$q = "SELECT *, concat(mbrFirstName,' ',mbrLastName) as mbrName FROM roletypes LEFT JOIN members ON typeContact=memberID ORDER BY typeSort,typeDescription";
$resMbr = $db->query($q);
if($resMbr && (mysqli_num_rows($resMbr) > 0)){
	$roleDesc = "<table style='border:2px inset;border-collapse:collapse;width:100%'>\n";
	$roleDesc .= "<tr style='background-color:#ebebeb;border-bottom:1px solid #000000;'><td>&nbsp;</td><td><b>Category Description</b></td><td><b>Coordinator</b></td></tr>\n";
	$shade = false;
	while($dbMbr=mysqli_fetch_array($resMbr)) {
		$bgcolor = $shade?"#efefef":"";
		$shade = !$shade;
		$roleDesc .= "	<tr bgcolor='$bgcolor'><td nowrap width='32'>\n";
		if (allow_access(Administrators) == "yes") { 
			$q = "SELECT * FROM roles WHERE typeID=".$dbMbr["typeID"];
			$resDel = $db->query($q);
			$roleDesc .= "		<a href='editRoleTypes.php?&action=edit&typeID=".$dbMbr["typeID"]."' title='Edit Role Category' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdRoleType',headingText: 'Edit Role Category',width: 540,height:180 });\"><img src=\"images/edit.png\"></a>\n";
			$roleDesc .= !$resDel || mysqli_num_rows($resDel)==0?"		<a onClick=\"delRoleType(".$dbMbr["typeID"].",'".$dbMbr["typeDescription"]."');\" href='#' title='Delete Role Category'><img src=\"images/icon_delete.gif\"></a>\n":"";
		}
		$roleDesc .= "		</td><td>".$dbMbr["typeDescription"]."</td>\n";
		$roleDesc .= "		<td>".$dbMbr["mbrName"]."</td>\n";
		$roleDesc .= "	</tr>\n";
	}
	echo $roleDesc."</table>\n";
}
?>