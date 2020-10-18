<?php
/*******************************************************************
 * editMember.php
 * Edit Member Information
 *******************************************************************/
 
 // Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';


//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();


require('lr/config.php');
require('lr/functions.php'); 

include("fnSmtp.php");

$mbract = isset($_POST["mbract"])?$_POST["mbract"]:$_REQUEST["action"];
if(!isset($_POST["memberID"]) && !isset($_REQUEST["id"])) {
	$memberID = $_SESSION['user_id'];
} else {
	$memberID = isset($_POST["memberID"])?$_POST["memberID"]:$_REQUEST["id"];
}
$pageNum = isset($_POST["pageNum"])?$_POST["pageNum"]:1;
$txtSearch = isset($_POST["txtSearch"])?$_POST["txtSearch"]:"";
$selRole = isset($_POST["selRole"])?$_POST["selRole"]:0;
$isActive = isset($_POST["isActive"])?$_POST["isActive"]:0;

//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
//   $valUser = $_SESSION["user_id"] == $_REQUEST["id"];
$isAdmin = allow_access(Administrators) == "yes";
if (!$isAdmin) { 
	if ($_SESSION["user_id"] != $memberID) {
		echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
		echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
		echo "</form></body></html>\n";
		exit;
	}
}

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


if(isset($_REQUEST["rtn"]) && $_REQUEST["rtn"]=="1") {
	$rtnPage = "index.php";
} else {
	$rtnPage = "dspTeam.php";
}

// Delete Member
if($mbract=="del") {
	$mbrID = $memberID;
	if ($use_phpBB3) {
		include("./phpBB3/includes/extBridge.php");
		$phpbb = new PhpBB3Component;
		$phpbb->startup();
		$phpbb->delUser($_POST["mbrUName"]);
	}
	$q = "DELETE FROM memberavailability WHERE memberID=$mbrID";
	$mbrRes = $db->query($q);
	$q = "DELETE FROM sitemessages WHERE toID=$mbrID OR fromID=$mbrID";
	$mbrRes = $db->query($q);
	$q = "DELETE FROM members WHERE memberID=$mbrID";
	$mbrRes = $db->query($q);
}

// Save changes
if(isset($_POST["save"])) {
	$mbrGroup1 = "Users";
	$mbrGroup2 = $_POST["mbrGroup"]>=1?"Administrators":"";
	$mbrGroup3 = $_POST["mbrGroup"]==2?"Coordinator":"";
	$pChange = isset($_POST["pchange"])?"1":"0";
	if(isset($_POST["roleArray"])) {
		$roleArray = implode(",",$_POST["roleArray"]);
	} else {
		$roleArray = "";
	}
	if(isset($_POST["groupArray"])) {
		$groupArray = implode(",",$_POST["groupArray"]);
	} else {
		$groupArray = "";
	}
	$sts = $_POST["mbrStatus"];

	$newMemberID = $memberID;
	$newPassword = generatePassword();
	$_POST["mbrEmail1"] = chop($_POST["mbrEmail1"]);
	$_POST["mbrEmail2"] = chop($_POST["mbrEmail2"]);
	$_POST["mbrUName"] = chop($_POST["mbrUName"]);
	if($mbract=="add") {
		if($_POST["sndActMsg"]==1) {
			$setPWD = $newPassword;
		} else {
			$setPWD = $_POST["pwdNew"];
		}
		// Fixup NULL, 0000-00-00 for last_login
		$q = "INSERT INTO members VALUES(0,'I','$roleArray','$groupArray','".$_POST["mbrFirstName"]."','".$_POST["mbrLastName"]."','".$_POST["mbrUName"]."','".$_POST["mbrEmail1"]."','".$_POST["mbrEmail2"]."','".$_POST["mbrHPhone"]."','".$_POST["mbrWPhone"]."','".$_POST["mbrCPhone"]."',md5('".$setPWD."'),'$mbrGroup1','$mbrGroup2','$mbrGroup3','1900-01-01','$sts','/index.php','$pChange')";
		$errMsg = array();
		// Validate that user/email does not already exist
		$chk = "SELECT memberID FROM members WHERE mbrFirstName='".$_POST["mbrFirstName"]."' AND mbrLastName='".$_POST["mbrLastName"]."'";
		$resChk = $db->query($chk);
		if($resChk && mysqli_num_rows($resChk)>0) {
			$errMsg = array("fld"=>"mbrFirstName","msg"=>"A member with this name already exists.");
		} else {
			$chk = "SELECT memberID FROM members WHERE mbrUName='".$_POST["mbrUName"]."'";
			$resChk = $db->query($chk);
			if($resChk && mysqli_num_rows($resChk)>0) {
				$errMsg = array("fld"=>"mbrUName","msg"=>"A member with this User Name already exists.");
			} else {
				if($_POST["mbrEmail1"]!="") {
					$chk = "SELECT memberID FROM members WHERE mbrEmail1='".$_POST["mbrEmail1"]."'";
					$resChk = $db->query($chk);
					if($resChk && mysqli_num_rows($resChk)>0) {
						$errMsg = array("fld"=>"mbrEmail1","msg"=>"A member with this Email Address already exists.");
					}
				}
			}
		}
		if(count($errMsg)==0) {
			if ($use_phpBB3) {

				include("./phpBB3/includes/extBridge.php");
				$phpbb = new PhpBB3Component;
				$phpbb->startup();
				$phpbb->addUser($_POST["mbrUName"], $setPWD, $_POST["mbrEmail1"]);
			}
		}
	} else {
		if($_POST["sndActMsg"]==1) {
			$setPWD = $newPassword;
			$passSQL = ",mbrPassword=md5('".$newPassword."')";
		} else {
			if($_POST["pwdNew"]!="") {
				$setPWD = $_POST["pwdNew"];
				$passSQL = ",mbrPassword=md5('".$_POST["pwdNew"]."')";
			} else {
				$passSQL = "";
			}
		}
		$q = "UPDATE members SET roleArray='$roleArray',groupArray='$groupArray',mbrFirstName='".$_POST["mbrFirstName"]."',mbrLastName='".$_POST["mbrLastName"]."',mbrUName='".$_POST["mbrUName"]."',mbrEmail1='".$_POST["mbrEmail1"]."',mbrEmail2='".$_POST["mbrEmail2"]."',mbrHPhone='".$_POST["mbrHPhone"]."',mbrWPhone='".$_POST["mbrWPhone"]."',mbrCPhone='".$_POST["mbrCPhone"]."'$passSQL,mbrGroup1='$mbrGroup1',mbrGroup2='$mbrGroup2',mbrGroup3='$mbrGroup3',mbrStatus='$sts',pchange='$pChange' WHERE memberID=$memberID";
		$errMsg = array();
		// Validate that user/email does not already exist
//		$chk = "SELECT memberID FROM members WHERE mbrFirstName='".$_POST["mbrFirstName"]."' AND mbrLastName='".$_POST["mbrLastName"]."' AND memberID<>$memberID";
//		$resChk = $db->query($chk);
//		if($resChk && mysqli_num_rows($resChk)>0) {
//			$errMsg = array("fld"=>"mbrFirstName","msg"=>"A member with this name already exists.");
//		} else {
//			$chk = "SELECT memberID FROM members WHERE mbrUName='".$_POST["mbrUName"]."' AND memberID<>$memberID";
//			$resChk = $db->query($chk);
//			if($resChk && mysqli_num_rows($resChk)>0) {
//				$errMsg = array("fld"=>"mbrUName","msg"=>"A member with this User Name already exists.");
//			} else {
//				if($_POST["mbrEmail1"]!="") {
//					$chk = "SELECT memberID FROM members WHERE mbrEmail1='".$_POST["mbrEmail1"]."' AND memberID<>$memberID";
//					$resChk = $db->query($chk);
//					if($resChk && mysqli_num_rows($resChk)>0) {
//						$errMsg = array("fld"=>"mbrEmail1","msg"=>"A member with this Email Address already exists.");
//					}
//				}
//			}
//		}
		if(count($errMsg)==0) {
			if($_POST["pwdNew"]!="") {
				if ($use_phpBB3) {

					include("./phpBB3/includes/extBridge.php");
					$phpbb = new PhpBB3Component;
					$phpbb->startup();
					$phpbb->changePassword($_POST["mbrUName"], $setPWD);
				}
			}
		}
	}
	if(count($errMsg)==0) {
		$resMbr = $db->query($q);
		logit(2, __FILE__ . ":" . __LINE__ . " Q: ". $q . " id: " . $db->insert_id ." E:". $db->error);
		
		// Save ID for new members
		if ($mbract=="add") {
			 $newMemberID = $db->insert_id; }
		
		// Send activation message if requested
		if($_POST["sndActMsg"]==1) {
			sndActMessage($newMemberID,$newPassword,$_POST);
		}
	
		$mbract = 'saved';
	} else {
		$mbract = $mbract=="add"?"erradd":"erredit";
	}
}

$dspError = false;
$pwdNew = "";
$pwdConfirm = "";

if($mbract=="edit") {
	/* Retrieve member for specified id */
	$sql = "SELECT * FROM members WHERE memberID=$memberID";
	$resMbr = $db->query($sql);
	$dbMbr=mysqli_fetch_array($resMbr);
	$mbrFirstName = htmlentities($dbMbr["mbrFirstName"],ENT_QUOTES);
	$mbrLastName = htmlentities($dbMbr["mbrLastName"],ENT_QUOTES);
	$mbrUName = htmlentities($dbMbr["mbrUName"],ENT_QUOTES);
	$mbrEmail1 = htmlentities($dbMbr["mbrEmail1"],ENT_QUOTES);
	$mbrEmail2 = htmlentities($dbMbr["mbrEmail2"],ENT_QUOTES);
	$mbrHPhone = $dbMbr["mbrHPhone"];
	$mbrWPhone = $dbMbr["mbrWPhone"];
	$mbrCPhone = $dbMbr["mbrCPhone"];
	$mbrGroup1 = $dbMbr["mbrGroup1"];
	$mbrGroup2 = $dbMbr["mbrGroup2"];
	$mbrGroup3 = $dbMbr["mbrGroup3"];
	$last_login = $dbMbr["last_login"];
	$mbrStatus = $dbMbr["mbrStatus"];
	$roleArray = explode(",",$dbMbr["roleArray"]);
	$groupArray = explode(",",$dbMbr["groupArray"]);
	$pChange = $dbMbr["pchange"];
} else if($mbract=="add") {
	$mbrFirstName = "";
	$mbrLastName = "";
	$mbrUName = "";
	$mbrEmail1 = "";
	$mbrEmail2 = "";
	$mbrHPhone = "";
	$mbrWPhone = "";
	$mbrCPhone = "";
	$mbrGroup1 = "Users";
	$mbrGroup2 = "";
	$mbrGroup3 = "";
	$last_login = "";
	$mbrStatus = "P";
	$roleArray = array();
	$groupArray = array();
	$pChange = "0";
} else if(substr($mbract,0,3)=="err") {
	$mbrFirstName = htmlentities($_POST["mbrFirstName"],ENT_QUOTES);
	$mbrLastName = htmlentities($_POST["mbrLastName"],ENT_QUOTES);
	$mbrUName = htmlentities($_POST["mbrUName"],ENT_QUOTES);
	$pwdNew = $_POST["pwdNew"];
	$pwdConfirm = $_POST["pwdConfirm"];
	$mbrEmail1 = htmlentities($_POST["mbrEmail1"],ENT_QUOTES);
	$mbrEmail2 = htmlentities($_POST["mbrEmail2"],ENT_QUOTES);
	$mbrHPhone = $_POST["mbrHPhone"];
	$mbrWPhone = $_POST["mbrWPhone"];
	$mbrCPhone = $_POST["mbrCPhone"];
	$last_login = $_POST["last_login"];
	$mbrStatus = $_POST["mbrStatus"];
	$roleArray = explode(",",$roleArray);
	$groupArray = explode(",",$groupArray);
	$pChange = $_POST["pchange"];
	$dspError = true;
	$mbract = substr($mbract,3);
} else {
	// Return to team list
	echo "<html><head>\n";
	echo "</head><body onLoad='document.frmMember.submit();'>\n";
	echo "<form style='margin:0px;' name=\"frmMember\" action=\"dspTeam.php\"method=\"post\">\n";
	echo "<input type='hidden' name='pageNum' id='pageNum' value=$pageNum>\n";
	echo "<input type='hidden' name='isActive' id='isActive' value=$isActive>\n";
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
<title><?php echo $siteTitle; ?> - Edit Member</title>

<script type="text/javascript">
function valMember() {
	var frm = document.frmMember;
	if(frm.mbrFirstName.value=="") {
		alert("Please enter a value for the First Name");
		frm.mbrFirstName.focus();
		return false;
	}
	if(frm.mbrLastName.value=="") {
		alert("Please enter a value for the Last Name");
		frm.mbrLastName.focus();
		return false;
	}
	if(frm.mbrUName.value=="" || frm.mbrUName.value.length<4) {
		alert("Please enter a value for the User Name\n(Must be at least 4 characters)");
		frm.mbrUName.focus();
		return false;
	}

	// Send activation email?
	if(("<?php echo $mbrStatus; ?>"!="A" && frm.mbrStatus[1].checked) || ("<?php echo $mbract; ?>"=="add" && frm.mbrStatus[1].checked)) {
		if(confirm("Do you wish to send a member activation email at this time?\nThe password will be randomly generated.")) {
			frm.sndActMsg.value=1;
<?php if($mbract=="add") { ?>
		} else {
			if(frm.pwdNew.value.length<5) {
				alert("Please enter a value for the Password\n(Must be at least 5 characters)");
				frm.pwdNew.focus();
				return false;
			}
<?php } ?>
		}
	}

	// Verify confirm password
	if(frm.pwdNew.value != frm.pwdConfirm.value) {
		alert("Confirm value does not match the entered password");
		frm.pwdConfirm.focus();
		return false;
	}

	return true;
}

function cancelEdit() {
	document.frmMember.action = "<?php echo $rtnPage; ?>";
	document.frmMember.mbract.value='cancel';
	document.frmMember.submit();
}

window.onload = function() {
<?php if($dspError) { ?>
	alert('<?php echo $errMsg["msg"]; ?>');
	document.frmMember.<?php echo $errMsg["fld"]; ?>.focus();
<?php } else { ?>
	document.frmMember.mbrFirstName.focus();
<?php } ?>
}
</script>

<?php
$hlpID = $isAdmin?18:9;
$title = "Edit Member Information";
include("header.php");

echo "<form style='margin-top:5px;' name=\"frmMember\" action=\"editMember.php\" onSubmit=\"return valMember();\" method=\"post\">\n";
echo "<input type='hidden' name='memberID' id='memberID' value='$memberID'>\n";
echo "<input type='hidden' name='mbract' id='mbract' value='$mbract'>\n";
echo "<input type='hidden' name='pageNum' id='pageNum' value=$pageNum>\n";
echo "<input type='hidden' name='isActive' id='isActive' value=$isActive>\n";
echo "<input type='hidden' name='txtSearch' id='txtSearch' value='$txtSearch'>\n";
echo "<input type='hidden' name='selRole' id='selRole' value='$selRole'>\n";
echo "<input type='hidden' name='sndActMsg' id='sndActMsg' value='0'>\n";
echo "<table cellpadding='5'><tr>\n";
echo "	<td valign='top'>\n";
echo "<b class=\"b1h\"></b><b class=\"b2h\"></b><b class=\"b3h\"></b><b class=\"b4h\"></b>\n";
echo "	<div class=\"headh\">\n";
echo "		<h4>Member Information</h4>\n";
echo "	</div>\n";
echo "	<div class=\"contenth\"><div>\n";
echo "	<table>\n";
$mand = "<span style='font-weight:bold;color:#cc0000;'>*</span>";
echo "		<tr><td width='115'>First Name:$mand </td><td><input type='text' name='mbrFirstName' size='40' maxlength='50' value='$mbrFirstName' /></td></tr>";
echo "		<tr><td width='115'>Last Name:$mand </td><td><input type='text' name='mbrLastName' size='40' maxlength='50' value='$mbrLastName' /></td></tr>";
echo "		<tr><td width='115'>User Name:$mand </td>";
if($mbract=="edit") {
	echo "<td><input type='hidden' name='mbrUName' value='$mbrUName' />&nbsp;$mbrUName</td></tr>";
} else {
	echo "<td><input type='text' name='mbrUName' size='30' maxlength='50' value='$mbrUName' /></td></tr>";
}
$pm = $mbract=="add"?$mand:"";
echo "		<tr><td width='115'>Password:$pm </td><td><input title='Leave blank if not changing' type='password' name='pwdNew' size='30' maxlength='50' value='$pwdNew' /></td></tr>\n";
echo "		<tr><td width='115'>&nbsp;&nbsp;Confirm:$pm </td><td><input type='password' name='pwdConfirm' size='30' maxlength='50' value='$pwdConfirm' /></td></tr>\n";
if($isAdmin) {
	$chk = $pChange == "1"?" checked":"";
	echo "		<tr><td colspan='2'>Force Password Change:&nbsp;<input title='Require member to change their password on next login' type='checkbox' name='pchange' value='1'$chk /></td></tr>\n";
} else {
	echo $pChange=="1"?"		<tr><td width='115'>&nbsp;</td><td>&nbsp;<input type='hidden' name='pchange' value='1' /></td></tr>\n":"";
}
echo "		<tr><td width='115'>Email Address: </td><td><input type='text' name='mbrEmail1' size='40' maxlength='100' value='$mbrEmail1' /></td></tr>\n";
echo "		<tr><td width='115'>Alternate Email: </td><td><input type='text' name='mbrEmail2' size='40' maxlength='100' value='$mbrEmail2' /></td></tr>\n";
echo "		<tr><td width='115'>Home Phone: </td><td><input type='text' name='mbrHPhone' size='20' maxlength='25' value='$mbrHPhone' /></td></tr>\n";
echo "		<tr><td width='115'>Work Phone: </td><td><input type='text' name='mbrWPhone' size='20' maxlength='25' value='$mbrWPhone' /></td></tr>\n";
echo "		<tr><td width='115'>Cell Phone: </td><td><input type='text' name='mbrCPhone' size='20' maxlength='25' value='$mbrCPhone' /></td></tr>\n";
$uchk = $mbrGroup1=="Users"?" checked":"";
$achk = $mbrGroup2=="Administrators"?" checked":"";
$cchk = $mbrGroup3=="Coordinator"?" checked":"";
if($isAdmin) {
	echo "		<tr><td width='115'>Class: </td><td><input type='radio' name='mbrGroup' value='0'$uchk />&nbsp;User&nbsp;&nbsp;&nbsp;<input type='radio' name='mbrGroup' value='1'$achk />&nbsp;Admin.&nbsp;&nbsp;&nbsp;<input type='radio' name='mbrGroup' value='2'$cchk />&nbsp;Coord.</td></tr>\n";
} else {
	$mgVal = 0;
	$mgVal = $mbrGroup2=="Administrators"?1:0;
	$mgVal = $mbrGroup3=="Coordinator"?2:$mgVal;
	echo "		<tr><td width='115'>&nbsp;</td><td>&nbsp;<input type='hidden' name='mbrGroup' value='$mgVal' /></td></tr>\n";
}
if($isAdmin) echo "		<tr><td width='115'>Last Login: </td><td>$last_login<input type='hidden' name='last_login' value='$last_login' /></td></tr>\n";
$chk1 = $mbrStatus=="P"?" checked":"";
$chk2 = $mbrStatus=="A"?" checked":"";
$chk3 = $mbrStatus=="X"?" checked":"";
if($isAdmin) {
	echo "		<tr><td width='115'>Status: </td><td><input name='mbrStatus' type='radio' value='P'$chk1>&nbsp;Pending&nbsp;&nbsp;&nbsp;<input name='mbrStatus' type='radio' value='A'$chk2>&nbsp;Active&nbsp;&nbsp;&nbsp;<input name='mbrStatus' type='radio' value='X'$chk3>&nbsp;Disabled</td></tr>\n";
} else {
	echo "		<tr><td width='115'>&nbsp;</td><td>&nbsp;<input name='mbrStatus' type='hidden' value=$mbrStatus /></td></tr>\n";
}
echo "	</table>\n";
echo "	</div></div><b class=\"b4bh\"></b><b class=\"b3bh\"></b><b class=\"b2bh\"></b><b class=\"b1h\"></b>\n";
echo "	</td>\n";

// Roles
echo "	<td valign='top'>\n";
echo "<b class=\"b1h\"></b><b class=\"b2h\"></b><b class=\"b3h\"></b><b class=\"b4h\"></b>\n";
echo "	<div class=\"headh\">\n";
echo "		<h4>Roles</h4>\n";
echo "	</div>\n";
echo "	<div class=\"contenth\"><div>\n";
echo "	<table>\n";

// Setup Role checkboxes
$sql = "SELECT * FROM roles INNER JOIN roletypes ON roles.typeID = roletypes.typeID WHERE roleType = 'M' ORDER BY typeSort, roleDescription";
$resRole = $db->query($sql);
$i=0;
while($dbRole=mysqli_fetch_array($resRole)) {
	echo "		<tr>\n";
	$chk = array_search($dbRole["roleID"],$roleArray)===FALSE?"":" checked";
	echo "			<td><input name='roleArray[$i]' type='checkbox' value=".$dbRole["roleID"]."$chk>&nbsp;<img src='".$dbRole["roleIcon"]."'>&nbsp;".$dbRole["roleDescription"]."&nbsp;</td>\n";
	$i++;
	if($dbRole=mysqli_fetch_array($resRole)) {
		$chk = array_search($dbRole["roleID"],$roleArray)===FALSE?"":" checked";
		echo "			<td><input name='roleArray[$i]' type='checkbox' value=".$dbRole["roleID"]."$chk>&nbsp;<img src='".$dbRole["roleIcon"]."'>&nbsp;".$dbRole["roleDescription"]."&nbsp;</td>\n";
		$i++;
		if($dbRole=mysqli_fetch_array($resRole)) {
			$chk = array_search($dbRole["roleID"],$roleArray)===FALSE?"":" checked";
			echo "			<td><input name='roleArray[$i]' type='checkbox' value=".$dbRole["roleID"]."$chk>&nbsp;<img src='".$dbRole["roleIcon"]."'>&nbsp;".$dbRole["roleDescription"]."&nbsp;</td>\n";
			$i++;
		} else {
			echo "			<td>&nbsp;</td>\n";
		}
	} else {
		echo "			<td>&nbsp;</td><td>&nbsp;</td>\n";
	}
	echo "		</tr>\n";
}

echo "	</table><br />\n";
echo "	</div></div><b class=\"b4bh\"></b><b class=\"b3bh\"></b><b class=\"b2bh\"></b><b class=\"b1h\"></b>\n";
echo "	<br />\n";
echo "<b class=\"b1h\"></b><b class=\"b2h\"></b><b class=\"b3h\"></b><b class=\"b4h\"></b>\n";
echo "	<div class=\"headh\">\n";
echo "		<h4>Groups</h4>\n";
echo "	</div>\n";
echo "	<div class=\"contenth\"><div>\n";
echo "	<table style='width:100%;'>\n";

// Setup Group checkboxes
$sql = "SELECT * FROM members LEFT JOIN roles ON members.roleArray = roles.roleID WHERE mbrType='G' ORDER BY mbrLastName";
$resGrp = $db->query($sql);
$i=0;
while($dbGrp=mysqli_fetch_array($resGrp)) {
	echo "		<tr>\n";
	$chk = array_search($dbGrp["memberID"],$groupArray)===FALSE?"":" checked";
	echo "			<td><input name='groupArray[$i]' type='checkbox' value=".$dbGrp["memberID"]."$chk>&nbsp;<img src='".$dbGrp["roleIcon"]."'>&nbsp;".$dbGrp["mbrLastName"]."&nbsp;</td>\n";
	$i++;
	if($dbGrp=mysqli_fetch_array($resGrp)) {
		$chk = array_search($dbGrp["memberID"],$groupArray)===FALSE?"":" checked";
		echo "			<td><input name='groupArray[$i]' type='checkbox' value=".$dbGrp["memberID"]."$chk>&nbsp;<img src='".$dbGrp["roleIcon"]."'>&nbsp;".$dbGrp["mbrLastName"]."&nbsp;</td>\n";
		$i++;
		if($dbGrp=mysqli_fetch_array($resGrp)) {
			$chk = array_search($dbGrp["memberID"],$groupArray)===FALSE?"":" checked";
			echo "			<td><input name='groupArray[$i]' type='checkbox' value=".$dbGrp["memberID"]."$chk>&nbsp;<img src='".$dbGrp["roleIcon"]."'>&nbsp;".$dbGrp["mbrLastName"]."&nbsp;</td>\n";
			$i++;
		} else {
			echo "			<td>&nbsp;</td>\n";
		}
	} else {
		echo "			<td>&nbsp;</td><td>&nbsp;</td>\n";
	}
	echo "		</tr>\n";
}

echo "	</table>\n";
echo "	</div></div><b class=\"b4bh\"></b><b class=\"b3bh\"></b><b class=\"b2bh\"></b><b class=\"b1h\"></b>\n";
echo "</td></tr>\n";
echo "	<tr><td colspan='2' align='right'><input name=\"cancel\" type=\"button\" value=\"Cancel\" onClick=\"cancelEdit();\" class=\"button\">&nbsp;<input name=\"save\" type=\"submit\" value=\"Save\" class=\"button\"></td></tr>\n";
echo "</table>\n";
echo "</form>\n";
echo "</body>\n</html>\n";

// Send email for activating new members
function sndActMessage($memberID, $mbrPassword, $usrData) {
	global $siteTitle,$db;
	
	$mbrFName = $usrData["mbrFirstName"];
	$mbrLName = $usrData["mbrLastName"];
	$mbrUName = $usrData["mbrUName"];
	$mbrEmail1 = $usrData["mbrEmail1"];
	$mbrEmail2 = $usrData["mbrEmail2"];

	

	$subject = "$siteTitle - Member Activation";

	// Build message Body
	$q = "SELECT * FROM siteconfig LIMIT 1";
	$resMsg = $db->query($q);
	$dbMsg = mysqli_fetch_array($resMsg);
	$emailHTMLBase = $dbMsg["actMessage"];


	$order = array("\r\n","\n", "\r");
	$replace = "<br \>";

	$emailHTML = str_replace("{%memberFirstName%}",str_replace($order, $replace, $mbrFName),$emailHTMLBase);
	$emailHTML = str_replace("{%memberFirstName%}",$mbrFName,$emailHTML);
	$emailHTML = str_replace("{%memberLastName%}",$mbrLName,$emailHTML);
	$emailHTML = str_replace("{%userName%}",$mbrUName,$emailHTML);
	$emailHTML = str_replace("{%userPassword%}",$mbrPassword,$emailHTML);
	
	if($mbrEmail1!="" || $mbrEmail2!="") {

		$emailHTML = SendEmailWrap($emailHTML, $subject);
		$mailMessage = new PHPMailer;

		setUpSMTP($mailMessage, $subject, $emailHTML);

		$adrEmail= array( $mbrEmail1,  $mbrEmail2);
		$adrName = array($mbrFName." ".$mbrLName, $mbrFName." ".$mbrLName);
		$tMsg = SendEmailAddAddress($mailMessage, $adrEmail, $adrName);
	
		$rtnMsg = SendEmailLog($mailMessage, $tMsg);					
	
	}
	unset($email_message);
	unset($h2t);
	return $rtnMsg;
}

function generatePassword($length=8, $strength=0) {
	$vowels = 'aeuy';
	$consonants = 'bdghjmnpqrstvz';
	if ($strength == 1) {
		$consonants .= 'BDGHJLMNPQRSTVWXZ';
	}
	if ($strength == 2) {
		$vowels .= "AEUY";
	}
	if ($strength == 4) {
		$consonants .= '23456789';
	}
	if ($strength == 8) {
		$consonants .= '@#$%';
	}
	
	$rndPass = '';
	$alt = time() % 2;
	for ($i = 0; $i < $length; $i++) {
		if ($alt == 1) {
			$rndPass .= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		} else {
			$rndPass .= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
	return $rndPass;
}
?>