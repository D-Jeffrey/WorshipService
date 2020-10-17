<?php
session_cache_limiter();
session_start();

require('lr/config.php');
require($baseDir.'/lr/functions.php'); 

$serviceID = $_REQUEST["id"];
$action = $_REQUEST["act"];
$memberID = isset($_REQUEST["mbr"])?$_REQUEST["mbr"]:0;
$roleID = isset($_REQUEST["rid"])?$_REQUEST["rid"]:0;
$svcDate = isset($_POST["svcDate"])?$_POST["svcDate"]:isset($_REQUEST["sdte"])?$_REQUEST["sdte"]:"";
$errMsg ="";

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Retrieve site configuration values
$q = "SELECT soundRole FROM siteconfig LIMIT 1";
$resCfg = $db->query($q);
$dbCfg=mysqli_fetch_array($resCfg);

// Is this user part of the sound crew?
$isSound = strpos(",".$_SESSION['roles'].",",$dbCfg["soundRole"])!==false;

//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Administrators) != "yes" && (!$isSound || $action=="add")) { 
	header("Location: /lr/login.php"); 
	exit;
}

// Add new team member to the service
if(isset($_POST["updMbr"]) && $action=="add") {
	// Verify that member is available
	$q = "SELECT memberID from memberavailability WHERE memberID=".$_POST["newMbr"]." AND awayFrom <= '$svcDate' AND awayTo >= '$svcDate'";
	$resMbr = $db->query($q);
	if(mysqli_num_rows($resMbr) > 0 && $resMbr) {
		$errMsg = "<script>alert('Member is not available for this date.');document.frmTeam.newRole.focus();</script>";
	} else {
		$sql = "INSERT INTO serviceteam VALUES($serviceID,".$_POST["newMbr"].",".$_POST["newRole"].",'','".$_POST["soundNotes"]."')";
		$resMbr = $db->query($sql);
		echo "<script>parent.window.dspTeam($serviceID);parent.window.hs.close();</script>";
		exit;
	}
}

// Update service team member
if(isset($_POST["updMbr"]) && $action=="edit") {
	$sql = "UPDATE serviceteam SET svcTeamNotes='',soundNotes='".$_POST["soundNotes"]."' WHERE serviceID=$serviceID AND memberID=$memberID AND roleID=$roleID";
	$resMbr = $db->query($sql);
	echo "<script>parent.window.dspTeam($serviceID);parent.window.hs.close();</script>";
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Edit Service Team</title>
<link rel="stylesheet" href="<?php echo $baseFolder; ?>css/tw.css" type="text/css">
<style>
form {
	padding:0px;
}
</style>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/ajax_req.js"></script>
<script type="text/javascript">
function setMbrButtonRole() {
	htmlData('/scripts/mbrSelect.php', 'id='+document.frmTeam.newRole.options[document.frmTeam.newRole.selectedIndex].value);
	document.frmTeam.updMbr.disabled=true;
}

function setMbrButtonMbr() {
	if(document.frmTeam.newMbr.selectedIndex==0 || document.frmTeam.newRole.selectedIndex==0) {
		document.frmTeam.updMbr.disabled=true;
	} else {
		document.frmTeam.updMbr.disabled=false;
	}
}

</script>

<?php
echo "</head>\n";
echo "<body>\n";

// Retrieve team record if edit
if($action=="edit") {
	$q = "SELECT * FROM serviceteam WHERE serviceID=$serviceID AND memberID=$memberID AND roleID=$roleID";
	$resTeam = $db->query($q);
	$dbTeam=mysqli_fetch_array($resTeam);
	$soundNotes = $dbTeam["soundNotes"];
} else {
	$soundNotes = "";
}
echo "<form style='margin:0px;' name='frmTeam' method='post' action='ajEditTeam.php?id=$serviceID&act=$action&mbr=$memberID&rid=$roleID'>\n";
$editDis = $action=="edit"?" disabled":"";
echo "<input type='hidden' name='svcDate' value='$svcDate' />\n";
echo "<table bgcolor='#ffffff' width='100%'>\n";
echo "<tr>\n";
echo "	<td>Role: </td>\n";
echo "	<td><select$editDis id=\"roleSelect\" name=\"newRole\" onChange=\"setMbrButtonRole();\">\n";
/* Retrieve role list */
$q = "SELECT roleID, roleDescription FROM roles INNER JOIN roletypes ON roles.typeID = roletypes.typeID ORDER BY typeSort, roleDescription";
$resRole = $db->query($q);
echo "		<option value=\"0\">-- Select Role --</option>\n";
while($dbRole=mysqli_fetch_array($resRole)) {
	$sel = $roleID==$dbRole["roleID"]?" selected":"";
	echo "		<option value=\"".$dbRole["roleID"]."\"$sel>".$dbRole["roleDescription"]."</option>\n";
}
echo "	</select></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "	<td>Member: </td>\n";
echo "	<td><div id=\"txtResult\"><select$editDis id=\"mbrSelect\" name=\"newMbr\" onChange=\"setMbrButtonMbr();\">\n";
/* Retrieve member list */
$q = "SELECT memberID, concat(mbrFirstName,' ',mbrLastName) as mbrName FROM members WHERE mbrStatus='A' ORDER BY mbrName";
$resMbr = $db->query($q);
echo "		<option value=\"0\">-- Select Member --</option>\n";
while($dbMbr=mysqli_fetch_array($resMbr)) {
	$sel = $memberID==$dbMbr["memberID"]?" selected":"";
	echo "		<option value=\"".$dbMbr["memberID"]."\"$sel>".$dbMbr["mbrName"]."</option>\n";
}
echo "	</select></div></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "	<td>Equipment Notes: </td>\n";
echo "	<td><textarea id=\"soundNotes\" name=\"soundNotes\" cols=\"40\" rows=\"3\">$soundNotes</textarea></td>\n";
echo "</tr>\n";
$addDis = $action=="edit"?"":" disabled";
echo "<tr><td colspan='2' align='center'><input type='submit'$addDis name='updMbr' value='Save'>&nbsp;&nbsp;<input type='button' name='cancel' value='Cancel' onClick='parent.window.hs.close();'></td></tr>\n";
echo "</table>\n";
echo $errMsg;
echo "</body>\n</html>\n";
?>