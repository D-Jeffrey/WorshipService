<?php
/*******************************************************************
 * requestChange.php
 * Edit Member Availability
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
//   $valUser = $_SESSION["user_id"] == $_REQUEST["id"];
if (allow_access(Users) != "yes") { 
	exit;
}

include ('fnNicetime.php');

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Request Change', $_SERVER['REQUEST_URI'], 3);

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


$action = isset($_POST["action"])?$_POST["action"]:$_REQUEST["a"];
$serviceID= isset($_POST["serviceID"])?$_POST["serviceID"]:$_REQUEST["sid"];
$requestID= isset($_POST["requestID"])?$_POST["requestID"]:$_REQUEST["rid"];

// Delete Request
if($action=="del") {
	$q = "DELETE FROM changerequests WHERE requestID=$requestID";
	$mbrRes = $db->query($q);
	header("Location: index.php");
	exit;
}

// Accept Request
if($action=="acc") {
	$q = "UPDATE changerequests SET newMbrID=".$_POST["newMbrID"].",chgStatus='P' WHERE requestID=$requestID";
	$mbrRes = $db->query($q);
	header("Location: index.php");
	exit;
}

// Save changes
if(isset($_POST["save"])) {
	/* Retrieve Service for specified id */
	$sql = "SELECT roleID FROM serviceteam WHERE serviceID=$serviceID AND memberID=".$_SESSION['user_id']." ORDER BY roleID";
	$resSVC = $db->query($sql);
	$roles = "";
	while($dbSvc=mysqli_fetch_array($resSVC)) {
		$roles .= $roles==""?"":",";
		$roles .= $dbSvc["roleID"];
	}
	
	$chkService = isset($_POST["chgService"])?1:0;
	$chkPractice = isset($_POST["chgPractice"])?1:0;
	if($action=="add") {
		$q = "INSERT INTO changerequests VALUES(0,$serviceID,".$_SESSION['user_id'].",$chkService,$chkPractice,'".$_POST["chgDescription"]."','O',0,'$roles')";
	} else {
		$q = "UPDATE changerequests SET chgService=$chkService,$chgPractice=$chkPractice,chgDescription='".$_POST["chgDescription"]."' WHERE requestID=$requestID";
	}
	$resMbr = $db->query($q);
	header("Location: index.php");
	exit;
}

if($action=="edit") {
	/* Retrieve Role for specified id */
	$sql = "SELECT * FROM changerequests WHERE requestID=$requestID";
	$resMbr = $db->query($sql);
	$dbMbr=mysqli_fetch_array($resMbr);
	$chgDescription = htmlentities($dbMbr["chgDescription"],ENT_QUOTES);
	$chkService = $dbMbr["chgService"];
	$chkPractice = $dbMbr["chgPractice"];
} else {
	$chgDescription = "";
	$chkService = 0;
	$chkPractice = 0;
}

// Retrieve service information
$sql = "SELECT *,date_format(svcDateTime, '%a %b %D') as svcDATE, date_format(svcDateTime, '%H:%i') as svcTIME FROM services WHERE serviceID=$serviceID";
$resSvc = $db->query($sql);
$dbSvc=mysqli_fetch_array($resSvc);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Request Change</title>

<script type="text/javascript">
function valRequest() {
	var frm = document.frmRequest;
	if(frm.roleDescription.value=="") {
		alert("Please enter a Request Description");
		frm.roleDescription.focus();
		return false;
	}
	return true;
}
</script>

<?php
echo "<link rel=\"stylesheet\" href=\"css/tw.css\" type=\"text/css\">";
echo "</head>\n";
echo "<body>\n";

$curUser = $_SESSION['first_name']." ".$_SESSION['last_name'];
$nav = "<br /><span style='font-size:10pt;'>Member: $curUser</span>&nbsp;<a href='{$baseFolder}lr/logout.php'>Logout</a>&nbsp;";
echo "<table class='topbar'><tr><td align='right'>Request Service Change&nbsp;<a href='/help/index.php' title='Help'><img src='/images/icon_help.png' border='0' title='Help' /></a><br />$nav</td></tr></table>\n";
$trail->output();

echo "<form style='margin-top:0px;border-bottom:2px ridge;border-top:2px ridge;' name=\"frmRequest\" action=\"requestChange.php\" onSubmit=\"return valRequest();\" method=\"post\">\n";
echo "<input type='hidden' name='action' value='$action'>\n";
echo "<input name=\"serviceID\" type=\"hidden\" value='$serviceID'>\n";
echo "<input name=\"requestID\" type=\"hidden\" value='$requestID'>\n";
echo "<table>\n";
$mand = "<span style='font-weight:bold;color:#cc0000;'>*</span>";
echo "	<tr><td style='height:35px;font-size:12pt;font-weight:bold' valign='top' colspan='2' align='center'>Service change request for ".$dbSvc["svcDATE"]." - ".nicetime($dbSvc["svcTIME"])."</td></tr>\n";
echo "	<tr><td>Request Description:$mand&nbsp;</td><td><input type='text' name='chgDescription' size='40' maxlength='50' value='$roleDescription' /></td></tr>";
echo "	<tr><td style='height:30px;font-weight:bold' valign='bottom' colspan='2'>Select Roles to Request a replacement for:</td></tr>\n";
echo "	<tr><td colspan='2'><table>\n";

// Setup Role checkboxes
$sql = "SELECT *,roles.roleID AS rID FROM serviceteam INNER JOIN roles ON serviceteam.roleID=roles.roleID WHERE serviceID=$serviceID AND memberID=".$_SESSION['user_id']." ORDER BY roleDescription";
$resRole = $db->query($sql);
$i=0;
while($dbRole=mysqli_fetch_array($resRole)) {
	echo "		<tr>\n";
	echo "			<td><input id='roleArray[$i]' name='roleArray[$i]' type='checkbox' value=".$dbRole["roleID"]." checked>&nbsp;<img src='".$dbRole["roleIcon"]."'>&nbsp;".$dbRole["roleDescription"]."&nbsp;</td>\n";
	$i++;
	if($dbRole=mysqli_fetch_array($resRole)) {
		echo "			<td><input name='roleArray[$i]' type='checkbox' value=".$dbRole["roleID"]." checked>&nbsp;<img src='".$dbRole["roleIcon"]."'>&nbsp;".$dbRole["roleDescription"]."&nbsp;</td>\n";
		$i++;
	} else {
		echo "			<td>&nbsp;</td>\n";
	}
	echo "		<tr>\n";
}
echo "		</table>\n";
echo "	</td></tr>\n";
echo "	<tr><td style='height:30px;' valign='bottom' colspan='2'><b>Messaging Options:</b> <span style='font-size:8pt'>(sent to members who can fill the selected roles above)</span></td></tr>\n";
echo "	<tr><td colspan='2'><input type=\"checkbox\" name=\"chkEmail\" value=\"1\" checked />&nbsp;Send Email&nbsp;&nbsp;\n";
echo "			<input type=\"checkbox\" name=\"chkSite\" value=\"1\" checked />&nbsp;Save As Site Message\n";
echo "	</td></tr>\n";
echo "	<tr><td colspan='2' align='right'><input name=\"cancel\" type=\"button\" value=\"Cancel\" onClick=\"document.location='index.php';\" class=\"button\">&nbsp;<input name=\"save\" type=\"submit\" value=\"Save\" class=\"button\"></td></tr>\n";
echo "</table>\n";
echo "</form>\n";
echo "</body>\n</html>\n";


?>