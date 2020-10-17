<?php
/*******************************************************************
 * editRoles.php
 * Edit Member Availability
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
//   $valUser = $_SESSION["user_id"] == $_REQUEST["id"];
if (allow_access(Administrators) != "yes") { 
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


$action = $_REQUEST["action"];
$roleID = $_REQUEST["roleID"];
$typeID = $_REQUEST["typeID"];

// Save changes
if(isset($_POST["save"])) {
	if($action=="add") {
		$q = "INSERT INTO roles VALUES(0,'".$_POST["roleDescription"]."','".$_POST["roleIcon"]."',$typeID,".$_POST["changeRule"].",'".$_POST["roleType"]."')";
		$resMbr = $db->query($q);
		$roleID = $db->insert_id;
	} else {
		$q = "UPDATE roles SET roleDescription='".$_POST["roleDescription"]."',roleIcon='".$_POST["roleIcon"]."',changeRule=".$_POST["changeRule"].",roleType='".$_POST["roleType"]."' WHERE roleID=$roleID";
		$resMbr = $db->query($q);
	}
	echo "<script>parent.window.dspRoles($roleID);parent.window.hs.close();</script>";
	exit;
}

// Retrieve Role Type
$sql = "SELECT * FROM roletypes WHERE typeID=$typeID";
$resMbr = $db->query($sql);
$dbMbr=mysqli_fetch_array($resMbr);
$typeDescription = htmlentities($dbMbr["typeDescription"],ENT_QUOTES);

if($action=="edit") {
	/* Retrieve Role for specified id */
	$sql = "SELECT * FROM roles WHERE roleID=$roleID";
	$resMbr = $db->query($sql);
	$dbMbr=mysqli_fetch_array($resMbr);
	$roleType = $dbMbr["roleType"];
	$roleDescription = htmlentities($dbMbr["roleDescription"],ENT_QUOTES);
	$roleIcon = $dbMbr["roleIcon"];
	$changeRule = $dbMbr["changeRule"];
} else {
	$roleType = "M";
	$roleDescription = "";
	$roleIcon = "";
	$changeRule = 1;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Edit Roles</title>

<script type="text/javascript">
function valRole() {
	var frm = document.frmRole;
	if(frm.roleDescription.value=="") {
		alert("Please enter a Role Description");
		frm.roleDescription.focus();
		return false;
	}
	if(frm.roleIcon.value=="") {
		alert("Please enter a Role Icon");
		frm.roleIcon.focus();
		return false;
	}

	return true;
}
function SetUrl(url) {
	document.getElementById("iconimage").src=url;
	document.frmRole.roleIcon.value=url;
}
</script>

<?php
echo "<link rel=\"stylesheet\" href=\"{$baseFolder}css/tw.css\" type=\"text/css\">\n";
echo "<style>\n";
echo "form {\n";
echo "	padding:0px;\n";
echo "}\n";
echo "</style>\n";
echo "</head><body style='background-color:#ffffff'>\n";
echo "<form style='margin-top:0px;border-bottom:2px ridge;border-top:2px ridge;' name=\"frmRole\" action=\"editRoles.php?action=$action&typeID=$typeID&roleID=$roleID\" onSubmit=\"return valRole();\" method=\"post\">\n";
echo "<input type='hidden' name='action' value='$action'>\n";
echo "<input name=\"roleID\" type=\"hidden\" value='$roleID'>\n";
echo "<input name=\"typeID\" type=\"hidden\" value='$typeID'>\n";
echo "<table>\n";
$mand = "<span style='font-weight:bold;color:#cc0000;'>*</span>";
echo "	<tr><td>Role Category:</td><td>$typeDescription</td></tr>";
$chkM = $roleType=="M"?" checked":"";
$chkG = $roleType=="G"?" checked":"";
echo "	<tr><td>Role Type:</td><td><input type='radio' name='roleType' value='M'$chkM />&nbsp;Member&nbsp;&nbsp;&nbsp;<input type='radio' name='roleType' value='G'$chkG />&nbsp;Group</td></tr>";
echo "	<tr><td>Role Description:$mand</td><td><input type='text' name='roleDescription' size='40' maxlength='50' value='$roleDescription' /></td></tr>";
echo "	<tr><td>Role Icon:$mand</td><td><input type='text' name='roleIcon' size='40' maxlength='100' value='$roleIcon' />&nbsp;";
$imgSrc = $action=="edit"?$roleIcon:"/UserFiles/Image/Icons/blank.gif";
echo "		<img src=\"$imgSrc\" id=\"iconimage\" height=\"16\">&nbsp;\n";
echo "		<input type=\"button\" name=\"selPic\" value=\"Browse\" onClick=\"window.open('/scripts/fckeditor/editor/filemanager/browser/default/browser.html?Type=Image&Connector=connectors/php/connector.php','Filebrowser','width=600,height=400');\">\n";
echo "	<tr><td>Chage Rule:</td><td><select name='changeRule' />\n";
$sel = $changeRule==0?" selected":"";
echo "		<option value=0$sel>No Changes Allowed</option>\n";
$sel = $changeRule==1?" selected":"";
echo "		<option value=1$sel>Open (No Approval Required)</option>\n";
$sel = $changeRule==2?" selected":"";
echo "		<option value=2$sel>Approval (Worship Coordinator to approve)</option>\n";
$sel = $changeRule==3?" selected":"";
echo "		<option value=3$sel>Closed (Worship coordinator receives request)</option>\n";
echo "	</select></td></tr>";
echo "	<tr><td colspan='2' align='center'><br /><input name=\"cancel\" type=\"button\" value=\"Cancel\" onClick=\"parent.window.hs.close();\" class=\"button\">&nbsp;<input name=\"save\" type=\"submit\" value=\"Save\" class=\"button\"></td></tr>\n";
echo "</table>\n";
echo "</form>\n";
echo "</body>\n</html>\n";
?>