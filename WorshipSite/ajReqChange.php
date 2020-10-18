<?php
/*******************************************************************
 * ajReqChange.php
 * Edit Member Availability
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
include('fnNicetime.php');

include("fnSmtp.php");

global $errorSending;

//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.

//   $valUser = $_SESSION["user_id"] == $_REQUEST["id"];
if (allow_access(Users) != "yes") { 
	exit;
}


$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


$action = isset($_REQUEST["a"])?$_REQUEST["a"]:(isset($_REQUEST["action"])?$_REQUEST["action"]:"");
$serviceID= isset($_REQUEST["sid"])?$_REQUEST["sid"]:(isset($_REQUEST["serviceID"])?$_REQUEST["serviceID"]:-1);
$requestID= isset($_REQUEST["rid"])?$_REQUEST["rid"]:(isset($_REQUEST["requestID"])?$_REQUEST["requestID"]:-1);
$roleID = isset($_REQUEST["roleid"])?$_REQUEST["roleid"]:0;
$refPage = isset($_REQUEST["ref"])?$_REQUEST["ref"]:"";

$aRoles = isset($_POST["roleArray"])?$_POST["roleArray"]:array();

// Delete Request
if($action=="del") {
	$q = "DELETE FROM svcchangereq WHERE requestID=$requestID";
	$mbrRes = $db->query($q);
	if($refPage=="") {
		echo "<script>document.location='editService.php?id=$serviceID';</script>";
	} else {
		echo "<script>document.location='index.php';</script>";
	}
	exit;
}

// Accept Request
if($action=="acc") {
	// Retrieve role change rule
	$sql = "SELECT a.roleID as chgRole,changeRule,serviceID,orgMbrID FROM svcchangereq a INNER JOIN roles b ON a.roleID=b.roleID WHERE a.requestID=$requestID";
	$resRule = $db->query($sql);
	$dbRule=mysqli_fetch_array($resRule);
	// No Approval Required
	if($dbRule["changeRule"]==1) {
		$q = "UPDATE svcchangereq SET newMbrID=".$_SESSION['user_id'].",chgStatus='A' WHERE requestID=$requestID";
		$mbrRes = $db->query($q);
		$q = "UPDATE serviceteam SET memberID=".$_SESSION['user_id']." WHERE serviceID=".$dbRule["serviceID"]." AND memberID=".$dbRule["orgMbrID"]." AND roleID=".$dbRule["chgRole"];
		$mbrRes = $db->query($q);
		sendChgConfirmation($requestID,"mbr");
	}
	// Worship Coordinator Approval Required
	if($dbRule["changeRule"]>1) {
		$q = "UPDATE svcchangereq SET newMbrID=".$_SESSION['user_id'].",chgStatus='P' WHERE requestID=$requestID";
		$mbrRes = $db->query($q);
		sendAppMessage($requestID);
	}

	if($refPage=="") {
		echo "<script>document.location='editService.php?id=".$dbRule["serviceID"]."';</script>";
	} else {
		echo "<script>document.location='index.php';</script>";
	}
	exit;
}

// Coordinate Request
if($action=="coord") {
	echo "<html>\n";
	echo "<head>\n";
	echo "<link rel=\"stylesheet\" href=\"/css/tw.css\" type=\"text/css\">\n";
	echo "<style>\n";
	echo "td { font-size:10pt; }\n";
	echo "</style>\n";
	echo "</head>\n";
	echo "<body bgcolor='#ffffff'>\n";
	$q = "SELECT cr.roleID,mbrFirstName,mbrLastName,roleDescription FROM svcchangereq cr INNER JOIN roles rl ON cr.roleID=rl.roleID INNER JOIN members ON orgMbrID=memberID WHERE requestID=$requestID";
	$resChg = $db->query($q);
	$dbChg=mysqli_fetch_array($resChg);
	echo "<form style='margin:0px;' name='frmChange' method='post' action='ajReqChange.php?a=updc&rid=$requestID'>\n";
	echo "<table>\n";
	echo "	<tr><td>Member Requesting Change:&nbsp;</td><td><b>".$dbChg["mbrFirstName"]." ".$dbChg["mbrLastName"]."</b></td></tr>\n";
	echo "	<tr><td>Role Impacted:</td><td><b>".$dbChg["roleDescription"]."</b></td></tr>\n";
	echo "	<tr><td>Replace With Member:</td><td><select name='newMember' style='font-weight:bold;'>\n";
	$q = 'SELECT memberID, CONCAT(mbrFirstName," ",mbrLastName) AS mbrName FROM members WHERE mbrStatus="A" AND CONCAT(",",roleArray,",") LIKE "%,'.$dbChg["roleID"].',%" ORDER BY mbrLastName,mbrFirstName';
	$results = $db->query($q);
	while ($results && $row = mysqli_fetch_array($results)) {
		echo "		<option value=\"".$row["memberID"]."\">".$row["mbrName"]."</option>";
	}
	echo "	</select></td></tr>\n";
	echo "	<tr><td colspan='2' align='center'><input type='submit' name='updMbr' value='Process Change'>&nbsp;&nbsp;<input type='button' name='cancel' value='Cancel' onClick='parent.window.hs.close();'></td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
	echo "</body>\n";
	echo "</html>\n";
	exit;
}

// Process Coordinate Request
if($action=="updc") {
	// Retrieve change request
	$sql = "SELECT * FROM svcchangereq WHERE requestID=$requestID";
	$resChg = $db->query($sql);
	$dbChg=mysqli_fetch_array($resChg);
	$q = "UPDATE svcchangereq SET newMbrID=".$_POST["newMember"].",chgStatus='A' WHERE requestID=$requestID";
	$mbrRes = $db->query($q);
	$q = "UPDATE serviceteam SET memberID=".$_POST["newMember"]." WHERE serviceID=".$dbChg["serviceID"]." AND memberID=".$dbChg["orgMbrID"]." AND roleID=".$dbChg["roleID"];
	$mbrRes = $db->query($q);
	sendChgConfirmation($requestID,"coord");

}

// Retrieve service information
$sql = "SELECT *,date_format(svcDateTime, '%a %b %D') as svcDATE, date_format(svcDateTime, '%H:%i') as svcTIME FROM services WHERE serviceID=$serviceID";
$resSvc = $db->query($sql);
$dbSvc=mysqli_fetch_array($resSvc);

// Save changes
if(isset($_POST["save"])) {
	// logit(5, __FILE__ . ":" .  __LINE__ . "Save" . var_export($_POST,TRUE));
	if($action=="add") {
		logit(5, __FILE__ . ":" .  __LINE__ . "Add " . count($aRoles) . ":". count($_POST["roleArray"]));
		foreach ($aRoles as $aRoleID) {
			logit(5, __FILE__ . ":" .  __LINE__ );
			if($_POST["radChgType"]=="R") {
				logit(5, __FILE__ . ":" .  __LINE__ . "rad ". $aRoleID);
				// Retrieve change request rule for role
				$sql = "SELECT changeRule FROM roles WHERE roleID=$aRoleID";
				$resRole = $db->query($sql);
				logit(5, __FILE__ . ":" .  __LINE__ . " Q: ". $q .  " R: " . mysqli_num_rows($resRole) ." E:". $db->error);
				$dbRole=mysqli_fetch_array($resRole);
				$chgStatus = $dbRole["changeRule"]==3?"C":"O";
				
				// TODO encoding
				$q = "INSERT INTO svcchangereq VALUES(0,'C',$aRoleID,$serviceID,".$_SESSION['user_id'].",0,0,'".$_POST["chgDescription"]."','$chgStatus','')";
				$resMbr = $db->query($q);
				logit(3, __FILE__ . ":" .  __LINE__ . " Q: ". $q . " Insert " .$db->insert_id . " E:". $db->error);
				$requestID = $db->insert_id;
				// Retrieve email/user id's for selected roles
				$emailMsg = getEmails($requestID,$dbSvc["svcDATE"]." - ".nicetime($dbSvc["svcTIME"]),$_POST);
			} else {
				logit(5, __FILE__ . ":" .  __LINE__ . "other");
				
				$emailMsg = requestRemoval($dbSvc["svcDATE"]." - ".nicetime($dbSvc["svcTIME"]),$_POST);
			}
		}
	}

	logit(5, __FILE__ . ":" .  __LINE__ . "close");
				
	echo "<script>parent.window.dspTeam($serviceID,".$_SESSION['user_id'].");parent.window.hs.close();</script>";
	exit;
}

if($action=="edit") {
	/* Retrieve Role for specified id */
	$sql = "SELECT * FROM svcchangereq WHERE requestID=$requestID";
	$resMbr = $db->query($sql);
	$dbMbr=mysqli_fetch_array($resMbr);
	$chgDescription = htmlentities($dbMbr["chgDescription"],ENT_QUOTES);
} else {
	$chgDescription = "";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Request Change</title>

<script type="text/javascript">
function valRequest() {
	var frm = document.frmRequest;
	if(frm.chgDescription.value=="") {
		alert("Please enter a Reason");
		frm.chgDescription.focus();
		return false;
	}
	return true;
}

function removeMessage() {
	var frm = document.frmRequest;
	if(frm.radChgType[1].checked) {
		alert("Your request to be removed from this service has been sent\nYou will be notified when it has been processed.");
	}
	return true;
}

function setChgType(type) {
	if(type=='R') {
		document.getElementById("spanChgType").innerHTML = "(sent to members who can fill the role above)";
	} else {
		document.getElementById("spanChgType").innerHTML = "(sent to the coordinator for the role above)";
	}
}
</script>

<?php
echo "<link rel=\"stylesheet\" href=\"css/tw.css\" type=\"text/css\">";
echo "</head>\n";
echo "<body bgcolor='#ffffff'>\n";

echo "<form style='margin-top:0px;' name=\"frmRequest\" action=\"ajReqChange.php\" onSubmit=\"return valRequest();\" method=\"post\">\n";
echo "<input type='hidden' name='action' value='$action'>\n";
echo "<input name=\"serviceID\" type=\"hidden\" value='$serviceID'>\n";
echo "<input name=\"requestID\" type=\"hidden\" value='$requestID'>\n";
echo "<table>\n";
$mand = "<span style='font-weight:bold;color:#cc0000;'>*</span>";
echo "	<tr><td style='height:35px;font-size:12pt;font-weight:bold' valign='top' colspan='2' align='center'>Service change request for ".$dbSvc["svcDATE"]." - ".nicetime($dbSvc["svcTIME"])."</td></tr>\n";
echo "	<tr><td>Reason:$mand&nbsp;</td><td><input type='text' name='chgDescription' size='55' maxlength='50' value='$chgDescription' /></td></tr>";

echo "	<tr><td style='height:30px;font-weight:bold' valign='bottom' colspan='2'>Request a change for role:</td></tr>\n";
echo "	<tr><td colspan='2'>\n";
$sql = "SELECT roleDescription,roleIcon FROM roles WHERE roleID=$roleID";
$resRole = $db->query($sql);
$dbRole=mysqli_fetch_array($resRole);
echo "<input id='roleArray[0]' name='roleArray[0]' type='hidden' value=$roleID>";
echo "&nbsp;<img src='".$dbRole["roleIcon"]."'>&nbsp;".$dbRole["roleDescription"]."&nbsp;\n";
echo "	</td></tr>\n";
echo "	<tr><td style='height:25px;font-weight:bold' valign='bottom' colspan='2'><input type='radio' name='radChgType' value='R' checked onClick=\"setChgType('R');\" />&nbsp;<b>Find Replacement</b>&nbsp;&nbsp;&nbsp;<input type='radio' name='radChgType' value='D' onClick=\"setChgType('D');\" />&nbsp;<b>Remove From Service</b></td></tr>\n";
echo "	<tr><td style='height:30px;' valign='bottom' colspan='2'><b>Messaging Options:</b> <span id='spanChgType' style='font-size:8pt'>(sent to members who can fill the role above)</span></td></tr>\n";
echo "	<tr><td colspan='2'><input type=\"checkbox\" name=\"chkEmail\" value=\"1\" checked />&nbsp;Send Email&nbsp;&nbsp;\n";
echo "			<input type=\"checkbox\" name=\"chkSite\" value=\"1\" />&nbsp;Save As Site Message\n";
echo "	</td></tr>\n";

echo "	<tr><td colspan='2' align='right'><input name=\"cancel\" type=\"button\" value=\"Cancel\" onClick=\"parent.window.hs.close();\" class=\"button\">&nbsp;<input name=\"save\" type=\"submit\" value=\"Save\" onClick=\"removeMessage();\" class=\"button\"></td></tr>\n";
echo "</table>\n";
echo "</form>\n";
echo "</body>\n</html>\n";

function getEmails($requestID,$svcDate,$usrData) {
	global $db;

	$roleArray = $usrData["roleArray"];
	// Retrieve member email based on selected roles
	foreach ($roleArray as $roleID) {
	
		// Retrieve change request rule for role
		$sql = "SELECT changeRule,typeContact,roleDescription FROM roles INNER JOIN roletypes ON roles.typeID=roletypes.typeID WHERE roleID=$roleID";
		$resRole = $db->query($sql);
		$dbRole=mysqli_fetch_array($resRole);
		if($dbRole["changeRule"]==3) {
			$q = "SELECT memberID,roleArray,concat(mbrFirstName,' ',mbrLastName) as mbrName,mbrEmail1,mbrEmail2 FROM members WHERE memberID=".$dbRole["typeContact"];
		} else {
			$q = "SELECT memberID,roleArray,concat(mbrFirstName,' ',mbrLastName) as mbrName,mbrEmail1,mbrEmail2 FROM members WHERE concat(',',roleArray,',') LIKE '%,$roleID,%' AND mbrStatus='A'";
		}
		$resTeam = $db->query($q);
		while($dbteam=mysqli_fetch_array($resTeam)) {
			if($dbteam["mbrEmail1"]!="") $aTeamEmail[] = array("address"=>$dbteam["mbrEmail1"],"name"=>$dbteam["mbrName"]);
			if($dbteam["mbrEmail2"]!="") $aTeamEmail[] = array("address"=>$dbteam["mbrEmail2"],"name"=>$dbteam["mbrName"]);
			$aTeamMembers[] = array("memberID" => $dbteam["memberID"],"memberName" => $dbteam["mbrName"]);
		}
		$aMbrInfo = array("email" => $aTeamEmail, "member" => $aTeamMembers);
		$emailMsg .= sendCommunication($requestID,$roleID,$svcDate,$aMbrInfo,$dbRole["changeRule"],$dbRole["roleDescription"],$usrData);
	}
	return $emailMsg;
}

// Request removal
function requestRemoval($svcDate,$usrData) {
	global $siteTitle,$db;
	global $errorSending;

	$roleArray = $usrData["roleArray"];
	// Retrieve member email based on selected roles
	foreach ($roleArray as $roleID) {
		// Retrieve change request rule for role
		$sql = "SELECT changeRule,typeContact,roleDescription FROM roles INNER JOIN roletypes ON roles.typeID=roletypes.typeID WHERE roleID=$roleID";
		$resRole = $db->query($sql);
		$dbRole=mysqli_fetch_array($resRole);
		$q = "SELECT memberID,roleArray,concat(mbrFirstName,' ',mbrLastName) as mbrName,mbrEmail1,mbrEmail2 FROM members WHERE memberID=".$dbRole["typeContact"];
		$resTeam = $db->query($q);
		if($dbteam=mysqli_fetch_array($resTeam)) {
			if($dbteam["mbrEmail1"]!="") $aTeamEmail[] = array("address"=>$dbteam["mbrEmail1"],"name"=>$dbteam["mbrName"]);
			if($dbteam["mbrEmail2"]!="") $aTeamEmail[] = array("address"=>$dbteam["mbrEmail2"],"name"=>$dbteam["mbrName"]);
			$aTeamMembers[] = array("memberID" => $dbteam["memberID"],"memberName" => $dbteam["mbrName"]);
		}
		$aMbrInfo = array("email" => $aTeamEmail, "member" => $aTeamMembers);
		
		$subject = $siteTitle." - Service Removal Request";
		# $from_address=isset($_SESSION['email'])?$_SESSION['email']:"worship@southcalgary.org";
		$from_name=$_SESSION['first_name']." ".$_SESSION['last_name'];
		
		// Add Member Name
		$msgBody = "<p>Member <b>".$_SESSION['first_name']." ".$_SESSION['last_name']."</b> has requested to be removed from the schedule serving in the role of <b>".$dbRole["roleDescription"];
		$msgBody .= "</b> for the service on <b>$svcDate</b></p>\n";
		$msgBody .= "<p><b>Request Note:</b>&nbsp;".$usrData["chgDescription"]."</p>\n";
		$msgBody .= "<p>Please update the schedule to remove this member from the specified service.</p>\n";
		$msgBody .= "<p>Thank you.</p>\n";
		
		if(isset($usrData["chkSite"])) {
			for($recipient=0;$recipient<count($aTeamMembers);$recipient++) {
				$q = "INSERT INTO sitemessages VALUES(0,'U',".$aTeamMembers[$recipient]["memberID"].",".$_SESSION['user_id'].",now(),'$subject','".$db->real_escape_string($msgBody)."')";
				$resTeam = $db->query($q);
				$rtnMsg .= "Message saved for: ".$aTeamMembers[$recipient]["memberName"]."<br />\n";
			}
		}
		if(isset($usrData["chkEmail"])) {
			$emailHTML = SendEmailWrap($msgBody, $subject);
		
			// Send emails
		
			$mailMessage = new PHPMailer;

			setUpSMTP($mailMessage, $subject, $emailHTML);
	
			/* Personalize the recipient address. */
			$adrEmail= array_column($aTeamEmail, "address");
			$adrName= array_column($aTeamEmail, "name");
			
			$tMsg = SendEmailAddAddress($mailMessage, $adrEmail, $adrName);
		
			$rtnMsg = SendEmailLog($mailMessage, $tMsg);					
		}
	}

	return $emailMsg;
}

function sendCommunication($requestID,$roleID,$svcDate,$aMbrInfo,$chgRule,$roleDesc,$usrData) {
	global $siteTitle,$db;
	global $errorSending;


	$aTeamEmail = $aMbrInfo["email"];
	$aTeamMembers = $aMbrInfo["member"];
	
	$subject = $siteTitle." - Service Change Request";
	# $from_address=isset($_SESSION['email'])?$_SESSION['email']:"worship@southcalgary.org";
	$from_name=$_SESSION['first_name']." ".$_SESSION['last_name'];
	
	// Build message Body
	$q = "SELECT * FROM siteconfig LIMIT 1";
	$resMsg = $db->query($q);
	$dbMsg = mysqli_fetch_array($resMsg);
	$msgBody = $dbMsg["chgReqMessage"];
	logit(4, __FILE__ . ":" .  __LINE__ . " Q: ". $q .  " R: " . $msgBody ." E:". $db->error);
	// Add Member Name
	$msgBody = str_replace("{%memberFirstName%}",$_SESSION['first_name'],$msgBody);
	$msgBody = str_replace("{%memberLastName%}",$_SESSION['last_name'],$msgBody);
	// Add Member Email
	$msgBody = str_replace("{%memberEmail%}",$from_address,$msgBody);
	// Add Service Date
	$msgBody = str_replace("{%serviceDate%}",$svcDate,$msgBody);
	// Add Request ID
	$msgBody = str_replace("{%requestID%}",$requestID,$msgBody);
	// Add Request Reason
	$msgBody = str_replace("{%requestReason%}",$usrData["chgDescription"],$msgBody);
	// Add Role Description
	$msgBody = str_replace("{%roleDescription%}",$roleDesc,$msgBody);
	// Add Change Rule
	$aRules = array("","No approval required. If you accept this change request, you will automatically assume this role",
			"Coordinator approval required. If you accept this request, the worship coordinator will review prior to approving",
			"Closed Request. The request is only sent to the coordinator for action");
	$msgBody = str_replace("{%changeRule%}",$aRules[$chgRule],$msgBody);
	
	$rtnMsg = "";
	if(isset($usrData["chkSite"])) {
		for($recipient=0;$recipient<count($aTeamMembers);$recipient++) {
			$q = "INSERT INTO sitemessages VALUES(0,'U',".$aTeamMembers[$recipient]["memberID"].",".$_SESSION['user_id'].",now(),'$subject','".$db->real_escape_string($msgBody)."')";
			$resTeam = $db->query($q);
			$rtnMsg .= "Message saved for: ".$aTeamMembers[$recipient]["memberName"]."<br />\n";
		}
	}
	if(isset($usrData["chkEmail"])) {
		logit(3, __FILE__ . ":" .  __LINE__ . " Q: ". $q .  " M: " . $msgBody);
		$emailHTML = SendEmailWrap($msgBody, $subject);
		
		// Send emails

		$mailMessage = new PHPMailer;

		setUpSMTP($mailMessage, $subject, $emailHTML);

		$adrEmail= array_column($aTeamEmail, "address");
		$adrName = array_column($aTeamEmail, "name");
			
		$tMsg = SendEmailAddAddress($mailMessage, $adrEmail,$adrName );
		
		$rtnMsg .= SendEmailLog($mailMessage, $tMsg);					
	}

	return $rtnMsg;
}

// Send emails confirming change acceptance
function sendChgConfirmation($requestID,$orig="mbr") {
	global $siteTitle,$db;

	$subject = $siteTitle." - Service Change Completed";
	# $from_address=isset($_SESSION['email'])?$_SESSION['email']:"worship@southcalgary.org";
	$from_name=$_SESSION['first_name']." ".$_SESSION['last_name'];
	
	// Retrieve change request
	$q = "SELECT requestID,serviceID,m1.mbrEmail1 AS orgEmail1,m1.mbrEmail2 AS orgEmail2,m2.mbrEmail1 AS newEmail1,m2.mbrEmail2 AS newEmail2,concat(m1.mbrFirstName,' ',m1.mbrLastName) AS orgName,concat(m2.mbrFirstName,' ',m2.mbrLastName) AS newName,roleID FROM svcchangereq INNER JOIN members m1 ON orgMbrID=m1.memberID INNER JOIN members m2 ON newMbrID=m2.memberID WHERE requestID=$requestID";
	$resReq = $db->query($q);
	$aCC = array();
	if($dbReq = mysqli_fetch_array($resReq)) {
		if($dbReq["orgEmail1"]!="") $aCC[] = array("Name"=>$dbReq["orgName"],"Email"=>$dbReq["orgEmail1"]);
		if($dbReq["orgEmail2"]!="") $aCC[] = array("Name"=>$dbReq["orgName"],"Email"=>$dbReq["orgEmail2"]);
		if($dbReq["newEmail1"]!="") $aCC[] = array("Name"=>$dbReq["newName"],"Email"=>$dbReq["newEmail1"]);
		if($dbReq["newEmail2"]!="") $aCC[] = array("Name"=>$dbReq["newName"],"Email"=>$dbReq["newEmail2"]);
		// Retrieve Role Coordinator contact
		$q = "SELECT * FROM roles INNER JOIN roletypes ON roles.typeID=roletypes.typeID INNER JOIN members ON typeContact=memberID WHERE roleID=".$dbReq["roleID"];
		$resCoord = $db->query($q);
		if ($dbCoord = mysqli_fetch_array($resCoord)) {
			if (EMAILCOORD_ON) {
			    if($dbCoord["mbrEmail1"]!="") $aCC[] = array("Name"=>$dbCoord["mbrFirstName"]." ".$dbCoord["mbrLastName"],"Email"=>$dbCoord["mbrEmail1"]);
			    if($dbCoord["mbrEmail2"]!="") $aCC[] = array("Name"=>$dbCoord["mbrFirstName"]." ".$dbCoord["mbrLastName"],"Email"=>$dbCoord["mbrEmail2"]);
			}
		}
		// Retrieve Service Information
		$q = "SELECT * FROM services WHERE serviceID=".$dbReq["serviceID"];
		$resSvc = $db->query($q);
		$dbSvc = mysqli_fetch_array($resSvc);
		$msgBody = "<h2>Service change completed for ".$dbSvc["svcDescription"]." on ".$dbSvc["svcDateTime"]."</h2>\n";
		$msgBody .= "<p><b>".$dbReq["orgName"]."</b> has requested a change for the role <b>".$dbCoord["roleDescription"]."</b> on the above service and<br />\n";
		if($orig=="mbr") {
			$msgBody .= "<b>".$dbReq["newName"]."</b> has accepted the request</p>\n";
		} else {
			$msgBody .= "<b>".$_SESSION['first_name']." ".$_SESSION['last_name']."</b> has assigned <b>".$dbReq["newName"]."</b> as a replacement</p>\n";
		}
		$msgBody .= "<br /><p>The service schedule has been updated to reflect this change.</p>\n";

		// Update site messages
		$q = "INSERT INTO sitemessages VALUES(0,'U',".$dbReq["orgMbrID"].",".$_SESSION['user_id'].",now(),'$subject','".$db->real_escape_string($msgBody)."')";
		$resMsg = $db->query($q);
		if($orig=="coord") {
			$q = "INSERT INTO sitemessages VALUES(0,'U',".$dbReq["newMbrID"].",".$_SESSION['user_id'].",now(),'$subject','".$db->real_escape_string($msgBody)."')";
			$resMsg = $db->query($q);
		} else {
			if($dbReq["orgMbrID"]!=$dbCoord["typeContact"]) {
				$q = "INSERT INTO sitemessages VALUES(0,'U',".$dbCoord["typeContact"].",".$_SESSION['user_id'].",now(),'$subject','".$db->real_escape_string($msgBody)."')";
				$resMsg = $db->query($q);
			}
		}
		$emailHTML = SendEmailWrap($msgBody, $subject);
		
		// Send emails
		
		$mailMessage = new PHPMailer;

		setUpSMTP($mailMessage, $subject, $emailHTML);
	
		/* Personalize the recipient address. */

		$adrEmail= array_column($aCC, "Email");
		$adrName= array_column($aCC, "Name");
	
		$tMsg = SendEmailAddAddress($mailMessage, $adrEmail, $adrName);
	
		$rtnMsg = SendEmailLog($mailMessage, $tMsg);					

	//	$email_message->SetEncodedEmailHeader("To",$aCC[0]["Email"],$aCC[0]["Name"]);
//			$email_message->SetEncodedEmailHeader("Cc",$aCC[$recipient]["Email"],$aCC[$recipient]["Name"]);
	}
	return true;
}

// Send email describing approval required
function sendAppMessage($requestID) {
	global $siteTitle,$db;

	$subject = $siteTitle." - Service Change Accepted - Approval Required";
	# $from_address=isset($_SESSION['email'])?$_SESSION['email']:"worship@southcalgary.org";
	$from_name=$_SESSION['first_name']." ".$_SESSION['last_name'];
	
	// Retrieve change request
	$q = "SELECT * FROM svcchangereq INNER JOIN members ON orgMbrID=memberID WHERE requestID=$requestID";
	$resReq = $db->query($q);
	$aCC = array();
	if ($dbReq = mysqli_fetch_array($resReq)) {
		$to_address = $dbReq["mbrEmail1"];
		$to_name = $dbReq["mbrFirstName"]." ".$dbReq["mbrLastName"];
		if($dbReq["mbrEmail2"]!="") {
			$aCC[] = array("Name"=>$dbReq["mbrFirstName"]." ".$dbReq["mbrLastName"],"Email"=>$dbReq["mbrEmail2"]);
		}
		// Retrieve Role Coordinator contact
		$q = "SELECT * FROM roles INNER JOIN roletypes ON roles.typeID=roletypes.typeID INNER JOIN members ON typeContact=memberID WHERE roleID=".$dbReq["roleID"];
		$resCoord = $db->query($q);
		if($dbCoord = mysqli_fetch_array($resCoord))  {
			if (EMAILCOORD_ON) {
			if($dbCoord["mbrEmail1"]!="") {
				$aCC[] = array("Name"=>$dbCoord["mbrFirstName"]." ".$dbCoord["mbrLastName"],"Email"=>$dbCoord["mbrEmail1"]);
			}
			if($dbCoord["mbrEmail2"]!="") {
				$aCC[] = array("Name"=>$dbCoord["mbrFirstName"]." ".$dbCoord["mbrLastName"],"Email"=>$dbCoord["mbrEmail2"]);
			}
            }
		}
		// Retrieve Service Information
		$q = "SELECT * FROM services WHERE serviceID=".$dbReq["serviceID"];
		$resSvc = $db->query($q);
		$dbSvc = mysqli_fetch_array($resSvc);
		$msgBody = "<h2>Service change request accepted for ".$dbSvc["svcDescription"]." on ".$dbSvc["svcDateTime"]."</h2>\n";
		$msgBody .= "<p><b>".$dbReq["mbrFirstName"]." ".$dbReq["mbrLastName"]."</b> has requested a change for the role <b>".$dbCoord["roleDescription"]."</b> on the above service and<br />\n";
		$msgBody .= "<b>".$_SESSION['first_name']." ".$_SESSION['last_name']."</b> has accepted the request</p>\n";
		$msgBody .= "<br /><p>This request must be approved by the team coordinator (".$dbCoord["mbrFirstName"]." ".$dbCoord["mbrLastName"].") before the schedule will be updated.</p>\n";

		// Update site messages
		$q = "INSERT INTO sitemessages VALUES(0,'U',".$dbReq["orgMbrID"].",".$_SESSION['user_id'].",now(),'$subject','".$db->real_escape_string($msgBody)."')";
		$resMsg = $db->query($q);
		if($dbReq["orgMbrID"]!=$dbCoord["typeContact"]) {
			$q = "INSERT INTO sitemessages VALUES(0,'U',".$dbCoord["typeContact"].",".$_SESSION['user_id'].",now(),'$subject','".$db->real_escape_string($msgBody)."')";
			$resMsg = $db->query($q);
		}

		$emailHTML = SendEmailWrap($msgBody, $subject);
		
//		for($recipient=0;$recipient<count($aCC);$recipient++) {
//			$email_message->SetEncodedEmailHeader("Cc",$aCC[$recipient]["Email"],$aCC[$recipient]["Name"]);
//		}
		
		/* Personalize the recipient address. */
//		$email_message->SetEncodedEmailHeader("To",$to_address,$to_name);
		
		
		
		// Send emails
	
		$mailMessage = new PHPMailer;

		setUpSMTP($mailMessage, $subject, $emailHTML);

		$tMsg = SendEmailAddAddress($mailMessage, array($to_address),array($to_name));
		
		$adrEmail= array_column($aCC, "Email");
		$adrName= array_column($aCC, "Name");
	
		$tMsg .= SendEmailAddAddress($mailMessage, $adrEmail, $adrName, FALSE);
	
	
		$rtnMsg .= SendEmailLog($mailMessage, $tMsg);					

	} 	
	
	return true;
}



?>