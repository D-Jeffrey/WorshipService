<?php
/*******************************************************************
 * adminCommunications.php
 * Communicate with team through email
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
require($baseDir.'/lr/functions.php'); 

// Convert HTML to text

include("fnSmtp.php");


//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Users) != "yes") { 
	echo "<form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form>\n";
	echo "<script>document.frmLogin.submit();</script>\n";
	exit;
}

include ('fnNicetime.php');

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Send Message', $_SERVER['REQUEST_URI'], 2);

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

$DisplayEmailResults=1;
// Send email
$emailMsg = "";
if(isset($_POST["sbmComm"])) {
	
	
	include ("fnSendResult.php");
	if($_POST["dstType"]=="Individual") {
		if($_POST["mbrType"]=="G") {
			$aMembers = getGroupMembers($_POST["memberID"],0);
		} else {
			$aEmailIn = explode(";",$_POST["emailArray"]);
			$aMembers[] = array("memberID" => $_POST["memberID"], "memberName" => $_POST["selMember"], "email" => $aEmailIn);
		}
	} else {
		$aMembers = getEmails($_POST);
	}
	$emailMsg = sendCommunication($aMembers, $_POST);


	echo "<form action='/dspMessages.php' name='frmMsgSent' method='post'>\n";
	
	echo "<input type=\"hidden\" name=\"msgResult\" value=\"$emailMsg\" />\n";
	echo " <div style='text-align: center; font-size=14pt;' > &nbsp; <a href='javascript:; document.frmMsgSent.submit();' >Finish reviewing results </a> </div>";
	echo "</form>\n";
	
	echo "</body>";

	exit;
}

$dftDstType = isset($_REQUEST["dst"])?$_REQUEST["dst"]:"";
$dftMbrID = isset($_REQUEST["id"])?$_REQUEST["id"]:"";

$dftMbrName ="";
$dftEmails = "";
if($dftDstType=="I") {
	$q = "SELECT * FROM members WHERE memberID=$dftMbrID";
	$resMbr = $db->query($q);
	$dbMbr=mysqli_fetch_array($resMbr);
	$dftMbrName = $dbMbr["mbrFirstName"]." ".$dbMbr["mbrLastName"];
	$dftEmails = $dbMbr["mbrEmail1"];
	$dftEmails .= $dbMbr["mbrEmail1"]!="" && $dbMbr["mbrEmail2"]!=""?";".$dbMbr["mbrEmail2"]:$dbMbr["mbrEmail2"];
}

$title = "Send Messages";
$hlpID = 12;

// Retrieve the total number of active members
$q = "SELECT memberID FROM members WHERE mbrStatus='A' AND roleArray <> ''";
$resTeam = $db->query($q);
$totalMembers = mysqli_num_rows($resTeam);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle." - ".$title; ?></title>
<script type="text/javascript" src="/scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript" src="/scripts/scriptaculous/src/scriptaculous.js"></script>
<script type="text/javascript">

function init() {
		new Ajax.Autocompleter('selMember', 'memberBox', 'acGetMembers.php',{tokens:[','],minChars:2,afterUpdateElement:setMemberID});
}

function setMemberID(text,li) {
	var liParts = li.id.split(";");
	var eml = "";
	if(li.id && liParts[0] > 0) {
		document.frmComm.memberID.value=liParts[0];
		document.frmComm.mbrType.value=liParts[1];
		document.frmComm.sbmComm.disabled=false;
		if(liParts[2]!="") eml += liParts[2];
		if(liParts[3]!="") eml += ";"+liParts[3];
		document.getElementById("mbrEmail").innerHTML=eml;
	} else {
		document.frmComm.memberID.value=0;
		document.frmComm.mbrType.value="";
		document.frmComm.sbmComm.disabled=true;
		document.getElementById("mbrEmail").innerHTML="";
	}
}

function setSbmComm() {
	document.frmComm.sbmComm.disabled=!(document.frmComm.memberID.value>0);
}
</script>

<script type="text/javascript">
function valEntry(frm) {
	if(document.frmComm.dstType.selectedIndex==3) {
		if(document.frmComm.memberID.value<=0) {
			alert("Please select a member to send a message to.");
			document.frmComm.selMember.focus();
			return false;
		}
		document.frmComm.emailArray.value = document.getElementById("mbrEmail").innerHTML;
	}
	if(document.frmComm.msgSubject.value=="") {
		alert("Please enter a message subject.");
		document.frmComm.msgSubject.focus();
		return false;
	}
	if(tinymce.editors[0].getContent()=="") {
		alert("Please enter a message body.");
		document.frmComm.msgBody.focus();
		return false;
	}
	if(document.frmComm.dstType.selectedIndex==0) {
		if(document.frmComm.serviceID.selectedIndex==0) {
			return confirm("This message will be sent to all <?php echo $totalMembers; ?> worship team members\nAre you sure you wish to continue?");
			document.frmComm.emailArray.value = "ALL";
		} else {
			document.frmComm.emailArray.value = "SVC";
		}
	}
	if(document.frmComm.dstType.selectedIndex==1) {
		document.frmComm.emailArray.value = "CAT";
	}
	if(document.frmComm.dstType.selectedIndex==2) {
		document.frmComm.emailArray.value = "ROLES";
	}
	return true;
}

function setDstType() {
	if(document.frmComm.dstType.selectedIndex==0) {
		document.getElementById("dspCategories").style.display='none';
		document.getElementById("dspRoles").style.display='none';
		document.getElementById("dspMembers").style.display='none';
		document.getElementById("dspServices").style.display='';
	}
	if(document.frmComm.dstType.selectedIndex==1) {
		document.getElementById("dspCategories").style.display='';
		document.getElementById("dspRoles").style.display='none';
		document.getElementById("dspMembers").style.display='none';
		document.getElementById("dspServices").style.display='';
	}
	if(document.frmComm.dstType.selectedIndex==2) {
		document.getElementById("dspCategories").style.display='none';
		document.getElementById("dspRoles").style.display='';
		document.getElementById("dspMembers").style.display='none';
		document.getElementById("dspServices").style.display='';
	}
	if(document.frmComm.dstType.selectedIndex==3) {
		document.getElementById("dspCategories").style.display='none';
		document.getElementById("dspRoles").style.display='none';
		document.getElementById("dspServices").style.display='none';
		document.getElementById("dspMembers").style.display='';
	}
}

function chgMember() {
	document.getElementById('mbrEmail').innerHTML="";
	document.frmComm.memberID.value=0;
	setSbmComm();
}

window.onload=init;
</script>


<!-- TinyMCE -->
<script type="text/javascript" src="/scripts/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
      tinymce.init({
	selector: "textarea",
	plugins: [
			"advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
			"searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
			"save table  directionality emoticons paste "
	],
	add_unload_trigger: true,
	autosave_ask_before_unload: true,
	content_css : "<?php echo $baseFolder; ?>css/tw.css",
		toolbar: "fullscreen  | undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | searchreplace | link unlink  code | preview | forecolor backcolor | spellchecker | visualblocks restoredraft ",
		menubar: true,
		toolbar_items_size: 'small',
	  browser_spellcheck : true 
	});
</script>
<!-- /TinyMCE -->

<script type="text/javascript">
window.onbeforeunload = function() {
	if(!(tinymce.editors[0].isNotDirty)) {
		return "If you continue, your message will not be sent.";
	}
}
</script>


<style>
div.autocomplete {
	border: 1px solid #999;
	background-color: #fff;
	max-height:200px;
	overflow-y:scroll;
}
div.autocomplete ul {
	list-style: none;
	margin:0;
	padding:0;
}
div.autocomplete li { 
	padding: 2px 3px;
}
div.autocomplete strong { 
	font-weight: bold;
	text-decoration: underline;
}
div.autocomplete li.selected { 
	color: #fff;
	background-color: #8c1000;
	cursor:pointer;
}
div.tagb {
	color: #999;
	font-size: 9px;
	text-align: -webkit-center;
}
</style>

<?php
include("header.php");

if($emailMsg!="") {
	echo "<div style='border:2px inset;height:65px;overflow-y:scroll;'>$emailMsg</div>\n";
}
echo "<form style='height:100%;margin:0px;' name='frmComm' method='post' onSubmit='return valEntry(document.frmComm);' action='adminCommunications.php'>\n";
echo "<input name=\"memberID\" type=\"hidden\" value=\"$dftMbrID\" />\n";
echo "<input name=\"mbrType\" type=\"hidden\" />\n";
echo "<input name=\"emailArray\" type=\"hidden\" />\n";
echo "<table>\n";
echo "	<tr valign='top'>\n";
echo "		<td width='125'><b>Distribution:&nbsp;</b></td><td width='215'><select name='dstType' onChange='setDstType();'>\n";
echo "			<option>Everyone</option>\n";
echo "			<option>Selected Categories</option>\n";
echo "			<option>Selected Role(s)</option>\n";
echo "			<option value='Individual' selected>Individual/Group</option>\n";
echo "		</select></td>\n";
echo "		<td><b>Send></b>&nbsp;<input type=\"radio\" name=\"radMsgType\" value=\"1\" checked />&nbsp;Email&nbsp;&nbsp;\n";
echo "		        <input type=\"radio\" name=\"radMsgType\" value=\"2\" />&nbsp;Site Message&nbsp;&nbsp;\n";
echo "		        <input type=\"radio\" name=\"radMsgType\" value=\"3\" />&nbsp;Both\n";
echo "		</td>\n";
echo "	</tr>\n";
echo "</table>\n";

// Select by category
echo "<table>\n";
echo "	<tr id='dspCategories' style='display:none'>\n";
echo "		<td width='125'>Select Categories></td><td><table>\n";

// Setup Category checkboxes
$sql = "SELECT * FROM roletypes ORDER BY typeSort, typeDescription";
$resType = $db->query($sql);
$i=0;
while($dbType=mysqli_fetch_array($resType)) {
	echo "		<tr>\n";
	echo "			<td><input id='catArray[$i]' name='catArray[$i]' type='checkbox' value=".$dbType["typeID"].">&nbsp;".$dbType["typeDescription"]."&nbsp;</td>\n";
	$i++;
	if($dbType=mysqli_fetch_array($resType)) {
		echo "			<td><input id='catArray[$i]' name='catArray[$i]' type='checkbox' value=".$dbType["typeID"].">&nbsp;".$dbType["typeDescription"]."&nbsp;</td>\n";
		$i++;
	} else {
		echo "			<td>&nbsp;</td>\n";
	}
	$i++;
	if($dbType=mysqli_fetch_array($resType)) {
		echo "			<td><input id='catArray[$i]' name='catArray[$i]' type='checkbox' value=".$dbType["typeID"].">&nbsp;".$dbType["typeDescription"]."&nbsp;</td>\n";
		$i++;
	} else {
		echo "			<td>&nbsp;</td>\n";
	}
	echo "		<tr>\n";
}
echo "		</table></td>\n";
echo "	</tr>\n";

// Select by role
echo "<table>\n";
echo "	<tr id='dspRoles' style='display:none'>\n";
echo "		<td width='125'>Select Roles></td><td><table>\n";

// Setup Role checkboxes
$sql = "SELECT * FROM roles INNER JOIN roletypes ON roles.typeID = roletypes.typeID WHERE roleType='M' ORDER BY typeSort, roleDescription";
$resRole = $db->query($sql);
$i=0;
while($dbRole=mysqli_fetch_array($resRole)) {
	echo "		<tr>\n";
	echo "			<td><input id='roleArray[$i]' name='roleArray[$i]' type='checkbox' value=".$dbRole["roleID"].">&nbsp;<img src='".$dbRole["roleIcon"]."'>&nbsp;".$dbRole["roleDescription"]."&nbsp;</td>\n";
	$i++;
	if($dbRole=mysqli_fetch_array($resRole)) {
		echo "			<td><input id='roleArray[$i]' name='roleArray[$i]' type='checkbox' value=".$dbRole["roleID"].">&nbsp;<img src='".$dbRole["roleIcon"]."'>&nbsp;".$dbRole["roleDescription"]."&nbsp;</td>\n";
		$i++;
	} else {
		echo "			<td>&nbsp;</td>\n";
	}
	$i++;
	if($dbRole=mysqli_fetch_array($resRole)) {
		echo "			<td><input id='roleArray[$i]' name='roleArray[$i]' type='checkbox' value=".$dbRole["roleID"].">&nbsp;<img src='".$dbRole["roleIcon"]."'>&nbsp;".$dbRole["roleDescription"]."&nbsp;</td>\n";
		$i++;
	} else {
		echo "			<td>&nbsp;</td>\n";
	}
	echo "		<tr>\n";
}
echo "		</table></td>\n";
echo "	</tr>\n";

// Select By Individual
echo "	<tr id='dspMembers'>\n";
echo "		<td width='125'><b>Member Name:&nbsp;</b></td><td><input title=\"Member Name - List will pop up \nafter typing in several characters\" name=\"selMember\" type=\"text\" id=\"selMember\" size=\"45\" maxlength=\"100\" autocomplete=\"off\"  onChange=\"chgMember();\" value=\"$dftMbrName\" />\n";
echo "			&nbsp;Please select one member only<div id=\"memberBox\" class=\"autocomplete\" style=\"display:none\">&nbsp;</div>\n";
echo "			<div id='mbrEmail'>$dftEmails</div>\n";
echo "		</td>\n";
echo "	</tr>\n";

// Select by service
echo "	<tr id='dspServices' style='display:none'>\n";
echo "		<td width='125'><b>Worship Service:</b></td><td><select name='serviceID'>\n";
echo "			<option value='0'>-- Select From Service --</option>\n";

// Setup Service selection dropdown
$lastMonthDT = mktime(0,0,0,date("n")-1,date("j"),date("Y"));
$lastMonth = date("Y-m-d",$lastMonthDT);
$q = "SELECT serviceID, svcDateTime, date_format(svcDateTime, '%a %b %D') as svcDATE, date_format(svcDateTime, '%H:%i') as svcTIME FROM services WHERE svcDateTime>'$lastMonth' ORDER BY svcDateTime";
$resSched = $db->query($q);
$serviceOptions = "";
while($dbSched=mysqli_fetch_array($resSched)) {
	$serviceOptions .= "			<option value='".$dbSched["serviceID"]."'>".$dbSched["svcDATE"]." - ".nicetime($dbSched["svcTIME"])."</option>\n";
}
echo $serviceOptions;
echo "		</select></td>\n";
echo "	</tr>\n";

// Subject
echo "<table>\n";
echo "	<tr>\n";
echo "		<td width='125'><b>Subject:</b></td>\n";
echo "		<td><input name='msgSubject' type='text' size='80' maxlength='255' /></td>\n";
echo "	</tr>\n";
echo "</table>\n";

// Message body
echo "<table width='100%'>\n";
echo "	<tr>\n";
echo "		<td colspan='2'><b>Message Body:&nbsp;</b></td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td colspan='2'>\n";
echo "		<textarea name='msgBody' id='msgBody' style='width: 100%; height: 400px;'></textarea>\n";
echo "		</td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td colspan='2' align='center'>\n";
echo "			<input name=\"sbmComm\" type=\"submit\" value=\"Submit\" class=\"button\">&nbsp;&nbsp;<input name=\"back\" type=\"button\" value=\"Cancel\" onClick=\"document.location='/dspMessages.php';\" class=\"button\">\n";
echo "		</td>\n";
echo "	</tr>\n";
echo "</table>\n";
echo "</form>\n";

	
echo "</body>\n</html>\n";

function getEmails($usrData) {
	global $db;
	
	$dstType = $usrData["dstType"];
	$roleArray = $usrData["roleArray"];
	$aTeamMembers = array();
	if($dstType=="Everyone" && $usrData["serviceID"] > 0) {
		$q = "SELECT a.memberID as mbrID,mbrType,concat(mbrFirstName,' ',mbrLastName) as mbrName,mbrEmail1,mbrEmail2 FROM serviceteam a INNER JOIN members b ON a.memberID=b.memberID WHERE a.serviceID=".$usrData["serviceID"]." AND mbrStatus='A' ORDER BY a.memberID";
		$resTeam = $db->query($q);
		// Retrieve all member emails
		$oldMbrID = 0;
		while($dbteam=mysqli_fetch_array($resTeam)) {
			if($oldMbrID!=$dbteam["mbrID"]) {
				// Retrieve group members
				if($dbteam["mbrType"]=="G") {
					$aTeamMembers = $aTeamMembers + getGroupMembers($dbteam["mbrID"],count($aTeamMembers));
				} else if($dbteam["mbrEmail1"]!="" || $dbteam["mbrEmail2"]!="") {
					$aMbrEmail = array();
					if($dbteam["mbrEmail1"]!="") $aMbrEmail[] = $dbteam["mbrEmail1"];
					if($dbteam["mbrEmail2"]!="") $aMbrEmail[] = $dbteam["mbrEmail2"];
					$aTeamMembers[] = array("memberID" => $dbteam["mbrID"],"memberName" => $dbteam["mbrName"],"email"=>$aMbrEmail);
				}
				$oldMbrID = $dbteam["mbrID"];
			}
		}		
	} else {
		if($usrData["serviceID"] > 0) {
			$q = "SELECT a.memberID AS mbrID,mbrType,roleArray,roleID,concat(mbrFirstName,' ',mbrLastName) as mbrName,mbrEmail1,mbrEmail2 FROM serviceteam a INNER JOIN members b ON a.memberID=b.memberID WHERE a.serviceID=".$usrData["serviceID"]." AND mbrStatus='A' ORDER BY a.memberID";
		} else {
			$q = "SELECT memberID AS mbrID,roleArray,concat(mbrFirstName,' ',mbrLastName) as mbrName,mbrEmail1,mbrEmail2 FROM members WHERE roleArray <> '' AND mbrStatus='A'";
		}
		$resTeam = $db->query($q);
		if($dstType=="Everyone") {
			// Retrieve all member emails
			while($dbteam=mysqli_fetch_array($resTeam)) {
				if($dbteam["mbrEmail1"]!="" || $dbteam["mbrEmail2"]!="") {
					$aMbrEmail = array();
					if($dbteam["mbrEmail1"]!="") $aMbrEmail[] = $dbteam["mbrEmail1"];
					if($dbteam["mbrEmail2"]!="") $aMbrEmail[] = $dbteam["mbrEmail2"];
					$aTeamMembers[] = array("memberID" => $dbteam["mbrID"],"memberName" => $dbteam["mbrName"],"email"=>$aMbrEmail);
				}
			}
		} else if($dstType=="Selected Categories") {
			// Build role array base on selected categories
			$catRoleArray = array();
			$aCats = ",".implode(",",$usrData["catArray"]).",";
			$q = "SELECT roleID,roleType FROM roles INNER JOIN roletypes USING(typeID) WHERE roleType = 'M' AND '$aCats' LIKE CONCAT('%,',roletypes.typeID,',%')";
			$resCat = $db->query($q);
			while($dbCat=mysqli_fetch_array($resCat)) {
				$catRoleArray[] = $dbCat["roleID"];
			}
			// Retrieve member email based on selected roles
			while($dbteam=mysqli_fetch_array($resTeam)) {
				if($usrData["serviceID"] > 0) {
					$roleMatch = array_intersect(explode(",",$dbteam["roleID"]),$catRoleArray);
				} else {
					$roleMatch = array_intersect(explode(",",$dbteam["roleArray"]),$catRoleArray);
				}
				if(count($roleMatch)>0) {
					if($dbteam["mbrEmail1"]!="" || $dbteam["mbrEmail2"]!="") {
						$aMbrEmail = array();
						if($dbteam["mbrEmail1"]!="") $aMbrEmail[] = $dbteam["mbrEmail1"];
						if($dbteam["mbrEmail2"]!="") $aMbrEmail[] = $dbteam["mbrEmail2"];
						$aTeamMembers[] = array("memberID" => $dbteam["mbrID"],"memberName" => $dbteam["mbrName"],"email"=>$aMbrEmail);
					}
				}
			}
			// Add members within groups defined in selected categories
			if($usrData["serviceID"] > 0) {
				$q = "SELECT members.memberID AS mbrID FROM roles INNER JOIN roletypes USING(typeID) INNER JOIN members ON roleArray=roleID INNER JOIN serviceteam ON members.memberID=serviceteam.memberID WHERE serviceID=".$usrData["serviceID"]." AND roleType = 'G' AND '$aCats' LIKE CONCAT('%,',roletypes.typeID,',%') AND mbrStatus='A'";
			} else {
				$q = "SELECT memberID AS mbrID FROM roles INNER JOIN roletypes USING(typeID) INNER JOIN members ON roleArray=roleID WHERE roleType = 'G' AND '$aCats' LIKE CONCAT('%,',roletypes.typeID,',%') AND mbrStatus='A'";
			}
			$resCat = $db->query($q);
			while($dbCat=mysqli_fetch_array($resCat)) {
				$aTeamMembers = $aTeamMembers + getGroupMembers($dbCat["mbrID"],count($aTeamMembers));
			}
		} else if($dstType=="Selected Role(s)") {
			// Retrieve member email based on selected roles
			while($dbteam=mysqli_fetch_array($resTeam)) {
				$roleMatch = array_intersect(explode(",",$dbteam["roleArray"]),$roleArray);
				if(count($roleMatch)>0) {
					if($dbteam["mbrEmail1"]!="" || $dbteam["mbrEmail2"]!="") {
						$aMbrEmail = array();
						if($dbteam["mbrEmail1"]!="") $aMbrEmail[] = $dbteam["mbrEmail1"];
						if($dbteam["mbrEmail2"]!="") $aMbrEmail[] = $dbteam["mbrEmail2"];
						$aTeamMembers[] = array("memberID" => $dbteam["mbrID"],"memberName" => $dbteam["mbrName"],"email"=>$aMbrEmail);
					}
				}
			}
		}
	}
	return $aTeamMembers;
}

function sendCommunication($aTeamMembers,$usrData) {
	global $siteTitle,$db, $DisplayEmailResults;
  $rtnMsg = "";
	$aTeamMembers = array_msort($aTeamMembers,array('memberID'=>SORT_ASC));

	$subject = $usrData["msgSubject"];
	
	if($usrData["radMsgType"]=="2" || $usrData["radMsgType"]=="3") {
		$oldID = 0;
		for($recipient=0;$recipient<count($aTeamMembers);$recipient++) {
			if ($aTeamMembers[$recipient]["memberID"]!=$oldID) {
				$mg = $db->real_escape_string($usrData["msgBody"]);
				$sj = $db->real_escape_string($subject);
				$q = "INSERT INTO sitemessages VALUES(0,'U',".$aTeamMembers[$recipient]["memberID"].",".$_SESSION['user_id'].",now(),'$sj','$mg')";
				$resTeam = $db->query($q);
				$rtnMsg .= "Message saved for: ".$aTeamMembers[$recipient]["memberName"]."; ";
				$oldID = $aTeamMembers[$recipient]["memberID"];
			}
		}
	}
	if($usrData["radMsgType"]=="1" || $usrData["radMsgType"]=="3") {
		$msgBody = stripslashes(str_replace("\"/UserFiles/","\"https://".$_SERVER["HTTP_HOST"]."/UserFiles/",$usrData["msgBody"]));
		$emailHTML = SendEmailWrap($msgBody, $subject);

	
		$mailMessage = new PHPMailer;

		setUpSMTP($mailMessage, $subject, $emailHTML);
		
		
 	    $oldID = 0;
		for ($recipient=0;$recipient<count($aTeamMembers);$recipient++) {
			$mailMessage->clearAddresses();		// clear the TO address only
			
			/* Personalize the recipient address. */
			if($aTeamMembers[$recipient]["memberID"]!=$oldID) {
				$tMsg = SendEmailAddAddress ($mailMessage, $aTeamMembers[$recipient]["email"], 
							array($aTeamMembers[$recipient]["memberName"],$aTeamMembers[$recipient]["memberName"]));
				if ($tMsg != "") {
					$rMsg = SendEmailLog($mailMessage, $tMsg, "To:", FALSE);					
					$rtnMsg .= $tMsg;
					$rtnMsg .= $rMsg;
					}
				$oldID = $aTeamMembers[$recipient]["memberID"];
				if ($DisplayEmailResults) {
					$progress = ceil($recipient*100/(count($aTeamMembers)+1) );
					echo "<script>docprogress('" , $rMsg ."', ". $progress. " );</script>\n";
					ob_flush();
    				flush();
    					
				}
			}
		}
		SendEmailLogFinish(TRUE, $subject);
	}
	if ($DisplayEmailResults) {
		echo "<script>docprogress('.. Completed', 100 );</script>\n";
		ob_flush();
		flush();
	}
			
	
	return $rtnMsg;
}

function getGroupMembers($groupID,$aKey) {
	global $db;

	$aTeamMembers = array();
	$q = "SELECT memberID,concat(mbrFirstName,' ',mbrLastName) as mbrName,mbrEmail1,mbrEmail2 FROM members WHERE mbrStatus='A' AND concat(',',groupArray,',') LIKE concat('%,',$groupID,',%') ORDER BY memberID";
	$resTeam = $db->query($q);
	while($dbteam=mysqli_fetch_array($resTeam)) {
		if($dbteam["mbrEmail1"]!="" || $dbteam["mbrEmail2"]!="") {
			$aMbrEmail = array();
			if($dbteam["mbrEmail1"]!="") $aMbrEmail[] = $dbteam["mbrEmail1"];
			if($dbteam["mbrEmail2"]!="") $aMbrEmail[] = $dbteam["mbrEmail2"];
			$aTeamMembers[$aKey] = array("memberID" => $dbteam["memberID"],"memberName" => $dbteam["mbrName"],"email"=>$aMbrEmail);
			$aKey++;
		}
	}
	return $aTeamMembers;
}


function array_msort($array, $cols) {
	$colarr = array();
	foreach ($cols as $col => $order) {
		$colarr[$col] = array();
		foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
	}
	$params = array();
	foreach ($cols as $col => $order) {
		$params[] =& $colarr[$col];
		$params = array_merge($params, (array)$order);
	}
	call_user_func_array('array_multisort', $params);
	$ret = array();
	$keys = array();
	$first = true;
	foreach ($colarr as $col => $arr) {
		foreach ($arr as $k => $v) {
			if ($first) { $keys[$k] = substr($k,1); }
			$k = $keys[$k];
			if (!isset($ret[$k])) $ret[$k] = $array[$k];
			$ret[$k][$col] = $array[$k][$col];
		}
		$first = false;
	}
	return $ret;
}
?>