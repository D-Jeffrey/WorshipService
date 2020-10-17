<?php 
/*******************************************************************
 * dspRoleTypes.php
 * List role types for editing
 *******************************************************************/
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

require('lr/config.php');
require($baseDir.'/lr/functions.php'); 
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
$trail->add('Display Role Categories', $_SERVER['REQUEST_URI'], 2);

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
function delRoleType(id,type) {
	if(confirm("Delete role category: "+type+"?")) {
		var oUpdater = new Ajax.Updater({ success:'divDspRoleTypes' }, '/ajDspRoleTypes.php', { 
			method: "get",
			parameters: { act: 'del', id: id }
		});
	}
}

function dspRoleTypes() {
	var oUpdater = new Ajax.Updater({ success:'divDspRoleTypes' }, '/ajDspRoleTypes.php', { 
		method: "get",
		parameters: { act: 'dsp', id: '0' }
	});
}

function addRoleType() {
	document.frmRole.action.value="add";
	document.frmRole.roleID.value=0;
	document.frmRole.submit();
}
function editRoleType(id) {
	document.frmRole.action.value="edit";
	document.frmRole.roleID.value=id;
	document.frmRole.submit();
}
</script>
<?php

$hlpID = 22;
$title = "Worship Team Role Categories";
include("header.php");

echo "	<table border=\"0\" cellspacing=\"1\" cellpadding=\"2\">\n";
echo "		<tr>\n";
echo "			<td valign=\"middle\" align=\"left\">\n";
echo "				<a href='editRoleTypes.php?&action=add&typeID=0' title='Add Role Category' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdRoleType',headingText: 'Add Role Category',width: 540,height:180 });\"><img src=\"images/icon_new.gif\" style='vertical-align:middle'>Add Role Category</a>\n";
echo "			</td>\n";
echo "		</tr>\n";
echo "	</table>\n";

echo "	<form name=\"frmRole\" action=\"editRoleTypes.php\" method=\"post\">\n";
echo "	<input name=\"action\" type=\"hidden\">\n";
echo "	<input name=\"typeID\" type=\"hidden\" value=$typeID>\n";
$q = "SELECT *, concat(mbrFirstName,' ',mbrLastName) as mbrName FROM roletypes LEFT JOIN members ON typeContact=memberID ORDER BY typeSort,typeDescription";
$resMbr = $db->query($q);
if($resMbr && (mysqli_num_rows($resMbr) > 0)){
	$roleDesc = "<div id='divDspRoleTypes'><table style='border:2px inset;border-collapse:collapse;width:100%'>\n";
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
	echo $roleDesc."</table></div>\n";
}
echo "<br /><span id='btnLink'><a href='ajUpdRoleTypeOrder.php' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', contentID: 'divEditOrder', headingText: 'Update Order' } )\">Update Order</a></span>\n";

// Update Roles
echo "<div id='divUpdRoleType' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";

echo "</body>\n</html>\n";
