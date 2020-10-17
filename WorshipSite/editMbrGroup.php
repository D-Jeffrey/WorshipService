<?php
/*******************************************************************
 * editMbrGroup.php
 * Edit Member group Information
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
	if(!isset($_REQUEST["id"]) || $_SESSION["user_id"] != $_REQUEST["id"]) {
		echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
		echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
		echo "</form></body></html>\n";
		exit;
	}
}
$isAdmin = allow_access(Administrators) == "yes";

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


if(isset($_POST["mbract"]) || isset($_REQUEST["id"])) {
	$mbract = isset($_POST["mbract"])?$_POST["mbract"]:$_REQUEST["action"];
	$memberID = isset($_POST["memberID"])?$_POST["memberID"]:$_REQUEST["id"];
	$pageNum = $_POST["pageNum"];
	$txtSearch = $_POST["txtSearch"];
	$selRole = $_POST["selRole"];
} else {
	echo "Invalid Request.";
	exit;
}

if(isset($_REQUEST["rtn"]) && $_REQUEST["rtn"]=="1") {
	$rtnPage = "index.php";
} else {
	$rtnPage = "dspTeam.php";
}

// Delete Member
if($mbract=="del") {
	$q = "DELETE FROM memberavailability WHERE memberID=$memberID";
	$mbrRes = $db->query($q);
	$q = "DELETE FROM sitemessages WHERE toID=$memberID OR fromID=$memberID";
	$mbrRes = $db->query($q);
	$q = "DELETE FROM groupmembers WHERE groupID=$memberID";
	$mbrRes = $db->query($q);
	$q = "DELETE FROM members WHERE memberID=$memberID";
	$mbrRes = $db->query($q);
}

// Save changes
if(isset($_POST["save"])) {
	$mbrGroup1 = "Users";
	$mbrGroup2 = $_POST["mbrGroup"]>=1?"Administrators":"";
	$mbrGroup3 = $_POST["mbrGroup"]==2?"Coordinator":"";
	$sts = $_POST["mbrStatus"];
	if($mbract=="add") {
		$q = "INSERT INTO members VALUES(0,'G','".$_POST["roleID"]."','','','".$_POST["mbrLastName"]."','','','','','','','','','','','1980-01-01','$sts','/index.php','0')";
	} else {
		$q = "UPDATE members SET roleArray='".$_POST["roleID"]."',mbrLastName='".$_POST["mbrLastName"]."',mbrStatus='$sts' WHERE memberID=$memberID";
	}
	$resMbr = $db->query($q);
	
	// Send activation message if requested
	if($_POST["sndActMsg"]==1) {
		sndActMessage($_POST);
	}
	
	$mbract = 'saved';
}

if($mbract=="edit") {
	/* Retrieve Member for specified id */
	$sql = "SELECT * FROM members WHERE memberID=$memberID";
	$resMbr = $db->query($sql);
	$dbMbr=mysqli_fetch_array($resMbr);
	$mbrLastName = htmlentities($dbMbr["mbrLastName"],ENT_QUOTES);
	$mbrStatus = $dbMbr["mbrStatus"];
	$roleID = $dbMbr["roleArray"];
} else if($mbract=="add") {
	$mbrLastName = "";
	$mbrStatus = "";
	$roleID = "";
} else {
	// Return to song list
	echo "<html><head>\n";
	echo "</head><body onLoad='document.frmMember.submit();'>\n";
	echo "<form style='margin:0px;' name=\"frmMember\" action=\"dspTeam.php\"method=\"post\">\n";
	echo "<input type='hidden' name='pageNum' id='pageNum' value=$pageNum>\n";
	echo "<input type='hidden' name='txtSearch' id='txtSearch' value='$txtSearch'>\n";
	echo "<input type='hidden' name='selRole' id='selRole' value='$selRole'>\n";
	echo "</form>\n";
	exit;
}

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$uri = strpos($_SERVER['REQUEST_URI'],"?")===FALSE?"?id=$memberID&action=$mbract":"";
$trail->add('Edit Member', $_SERVER['REQUEST_URI'].$uri, 3);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Edit Member Group</title>

<script type="text/javascript" src="scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript">
function valMember() {
	var frm = document.frmMember;
	if(frm.mbrLastName.value=="") {
		alert("Please enter a value for the Group Name");
		frm.mbrLastName.focus();
		return false;
	}
	return true;
}

function delGroupMember(gid,id,desc) {
	var url = 'ajDelGroupMember.php';
	var pars = 'memberID='+id+'&groupID='+gid+'&desc='+desc;
	if(confirm("Are you sure you wish to remove "+desc+" from this group?")) {
		var myAjax = new Ajax.Updater(
			'memberDiv', 
			url, {
				method: 'get', 
				parameters: pars
			});
	
	}
}

function cancelEdit() {
	document.frmMember.action = "<?php echo $rtnPage; ?>";
	document.frmMember.mbract.value='cancel';
	document.frmMember.submit();
}
</script>

<?php

$hlpID = $isAdmin?18:9;
$title = "Edit Member Group Information";
include("header.php");

echo "<form style='margin-top:0px;border-bottom:2px ridge;border-top:2px ridge;' name=\"frmMember\" action=\"editMbrGroup.php?id=$memberID\" onSubmit=\"return valMember();\" method=\"post\">\n";
echo "<input type='hidden' name='mbract' value='$mbract'>\n";
echo "<input type='hidden' name='pageNum' id='pageNum' value=$pageNum>\n";
echo "<input type='hidden' name='txtSearch' id='txtSearch' value='$txtSearch'>\n";
echo "<input type='hidden' name='selRole' id='selRole' value='$selRole'>\n";
echo "<input type='hidden' name='sndActMsg' id='sndActMsg' value='0'>\n";
echo "<table cellpadding='5' width='100%'><tr>\n";
echo "	<td valign='top'>\n";
echo "<b class=\"b1h\"></b><b class=\"b2h\"></b><b class=\"b3h\"></b><b class=\"b4h\"></b>\n";
echo "	<div class=\"headh\">\n";
echo "		<h4>Member Group Information</h4>\n";
echo "	</div>\n";
echo "	<div class=\"contenth\"><div>\n";
echo "	<table width='100%'>\n";
$mand = "<span style='font-weight:bold;color:#cc0000;'>*</span>";
echo "		<tr><td width='115'>Group Name:$mand </td><td><input type='text' name='mbrLastName' size='40' maxlength='50' value='$mbrLastName' /></td></tr>";
$chk1 = $mbrStatus=="P"?" checked":"";
$chk2 = $mbrStatus=="A"?" checked":"";
$chk3 = $mbrStatus=="X"?" checked":"";
if($isAdmin) {
	echo "		<tr><td width='115'>Status: </td><td><input name='mbrStatus' type='radio' value='P'$chk1>&nbsp;Pending&nbsp;&nbsp;&nbsp;<input name='mbrStatus' type='radio' value='A'$chk2>&nbsp;Active&nbsp;&nbsp;&nbsp;<input name='mbrStatus' type='radio' value='X'$chk3>&nbsp;Disabled</td></tr>\n";
} else {
	echo "		<tr><td width='115'>&nbsp;</td><td>&nbsp;<input name='mbrStatus' type='hidden' value=$mbrStatus /></td></tr>\n";
}
echo "		<tr><td width='115'>Role:$mand </td><td><select name='roleID'>\n";
echo "			<option value=0>-- Select Role --</option>\n";
$sql = "SELECT * FROM roles INNER JOIN roletypes ON roles.typeID = roletypes.typeID WHERE roleType='G' ORDER BY typeSort, roleDescription";
$resRole = $db->query($sql);
while($dbRole=mysqli_fetch_array($resRole)) {
	$sel = $roleID==$dbRole["roleID"]?" selected":"";
	echo "			<option value='".$dbRole["roleID"]."'$sel>".$dbRole["roleDescription"]."</option>\n";
}
echo "		</select></td></tr>";
echo "		<tr><td colspan='2' align='right'><input name=\"cancel\" type=\"button\" value=\"Cancel\" onClick=\"cancelEdit();\" class=\"button\">&nbsp;<input name=\"save\" type=\"submit\" value=\"Save\" class=\"button\"></td></tr>\n";
echo "	</table>\n";
echo "	</div></div><b class=\"b4bh\"></b><b class=\"b3bh\"></b><b class=\"b2bh\"></b><b class=\"b1h\"></b>\n";

echo "	</td></tr>\n";

// Retrieve group members
if($mbract=="edit") {
	$q ="SELECT * FROM members WHERE concat(',',groupArray,',') like concat('%,','$memberID',',%') ORDER BY mbrLastName, mbrFirstName";
	$resGrp = $db->query($q);
	if(!$resGrp || (mysqli_num_rows($resGrp) == 0)) {
		exit;
	}
	echo "<tr><td>\n";
	echo "<b class=\"b1h\"></b><b class=\"b2h\"></b><b class=\"b3h\"></b><b class=\"b4h\"></b>\n";
	echo "	<div class=\"headh\">\n";
	echo "		<h4>Group Members</h4>\n";
	echo "	</div>\n";
	echo "	<div class=\"contenth\"><div>\n";
	echo "<div id='memberDiv'><table width='100%'>\n";
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
	echo "</table></div>\n";
	echo "</div></div><b class=\"b4bh\"></b><b class=\"b3bh\"></b><b class=\"b2bh\"></b><b class=\"b1h\"></b>\n";
	echo "</td></tr>\n";
}
echo "</table>\n";
echo "</form>\n";
echo "</body>\n</html>\n";
?>