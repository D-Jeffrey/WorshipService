<?php 
/*******************************************************************
 * dspRoles.php
 * Update Team Member availability
 *******************************************************************/
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

require('lr/config.php');
require('lr/functions.php'); 
//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Administrators) != "yes") { 
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Display Roles', $_SERVER['REQUEST_URI'], 2);

if(isset($_POST["typeID"])) {
	$typeID = $_POST["typeID"];
} else if(isset($_REQUEST["typ"])) {
	$typeID = $_REQUEST["typ"];
} else {
	$typeID = 1;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Worship Team Roles</title>
<script type="text/javascript" src="scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript" src="scripts/scriptaculous/src/scriptaculous.js"></script>
<script type="text/javascript">
function delRole(id,role) {
	if(confirm("Delete role: "+role+"?")) {
		var oUpdater = new Ajax.Updater({ success:'divDspRoles' }, '/ajDspRoles.php', { 
			method: "get",
			parameters: { act: 'del', id: id, typ: '<?php echo $typeID; ?>' }
		});
	}
}

function dspRoles(id) {
	var oUpdater = new Ajax.Updater({ success:'divDspRoles' }, '/ajDspRoles.php', { 
		method: "get",
		parameters: { act: 'dsp', id: id, typ: '<?php echo $typeID; ?>' }
	});
}

function addRole() {
	document.frmRole.action.value="add";
	document.frmRole.roleID.value=0;
	document.frmRole.submit();
}
function editRole(id) {
	document.frmRole.action.value="edit";
	document.frmRole.roleID.value=id;
	document.frmRole.submit();
}
function selRoleType() {
	document.frmRoleType.submit();
}
</script>
<?php

$hlpID = 22;
$title = "Worship Team Roles";
include("header.php");

echo "	<table border=\"0\" cellspacing=\"1\" cellpadding=\"2\">\n";
echo "		<tr>\n";
echo "			<td valign=\"middle\" align=\"left\">\n";
echo "				<a href='editRoles.php?&action=add&roleID=0&typeID=$typeID' title='Add Role' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdRole',headingText: 'Add Role',width: 540 });\"><img src=\"images/icon_new.gif\" style='vertical-align:middle'>Add Role</a>\n";
echo "			</td>\n";
echo "		</tr>\n";
echo "	</table>\n";

echo "	<form name=\"frmRoleType\" action=\"dspRoles.php\" method=\"post\">\n";
echo "	<table style='border-collapse:collapse;width:100%'>\n";
echo "		<tr><td>Select Role Category:&nbsp;<select name=\"typeID\" onChange=\"selRoleType();\">\n";

$q = "SELECT * FROM roletypes ORDER BY typeDescription";
$resRT = $db->query($q);
while($dbRT=mysqli_fetch_array($resRT)) {
	$sel = $dbRT["typeID"]==$typeID?" selected":"";
	echo "			<option value=".$dbRT["typeID"]."$sel>".$dbRT["typeDescription"]."</option>\n";
}
echo "		</select></td></tr>\n";
echo "	</table>\n";
echo "	</form>\n";
echo "	<form name=\"frmRole\" action=\"editRoles.php\" method=\"post\">\n";
echo "	<input name=\"action\" type=\"hidden\">\n";
echo "	<input name=\"roleID\" type=\"hidden\">\n";
echo "	<input name=\"typeID\" type=\"hidden\" value=$typeID>\n";
$q = "SELECT * FROM roles WHERE typeID=$typeID ORDER BY roleDescription";
$resMbr = $db->query($q);
$roleDesc = "<div id='divDspRoles'><table style='border:2px inset;border-collapse:collapse;width:100%'>\n";
if($resMbr && (mysqli_num_rows($resMbr) > 0)){
	$roleDesc .= "<tr style='background-color:#ebebeb;border-bottom:1px solid #000000;'><td>&nbsp;</td><td><b>Role Type</b></td><td><b>Role Description</b></td><td><b>Change Request Rule</b></td></tr>\n";
	$shade = false;
	while($dbMbr=mysqli_fetch_array($resMbr)) {
		$bgcolor = $shade?"#efefef":"";
		$shade = !$shade;
		$roleDesc .= "	<tr bgcolor='$bgcolor'>\n";
		if (allow_access(Administrators) == "yes") { 
			$q = "SELECT * FROM members WHERE concat(',',roleArray,',') LIKE '%,".$dbMbr["roleID"].",%'";
			$resDel = $db->query($q);
			$roleDesc .= "		<td nowrap width='32'><a href='editRoles.php?&action=edit&roleID=".$dbMbr["roleID"]."&typeID=$typeID' title='Edit Role' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdRole',headingText: 'Edit Role',width: 540 });\"><img src=\"images/edit.png\"></a>\n";
			$roleDesc .= !$resDel || mysqli_num_rows($resDel)==0?"		<a onClick=\"delRole(".$dbMbr["roleID"].",'".$dbMbr["roleDescription"]."');\" href='#' title='Delete Role'><img src=\"images/icon_delete.gif\"></a>\n":"";
			$roleDesc .= "		</td>\n";
		}
		$roleTypeDesc = $dbMbr["roleType"]=="M"?"Member":"Group";
		$roleDesc .= "		<td width='70'>$roleTypeDesc</td>\n";
		$roleDesc .= "		<td>";
		if($dbMbr["roleIcon"]!="") {
			$roleDesc .= "<img src='".$dbMbr["roleIcon"]."'>\n";
		}
		$roleDesc .= $dbMbr["roleDescription"]."</td>\n";
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
echo $roleDesc."</table></div>\n";

// Update Roles
echo "<div id='divUpdRole' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";

echo "</body>\n</html>\n";
