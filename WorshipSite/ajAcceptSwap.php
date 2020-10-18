<?php
/*******************************************************************
 * ajReqSwap.php
 * Request service swap - this script will allow members to request
 * a service swap with another specified member.
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

//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
//   $valUser = $_SESSION["user_id"] == $_REQUEST["id"];
if (allow_access(Users) != "yes") { 
	exit;
}

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


$action = isset($_POST["action"])?$_POST["action"]:$_REQUEST["a"];
$serviceID= isset($_POST["serviceID"])?$_POST["serviceID"]:$_REQUEST["sid"];
$requestID= isset($_POST["requestID"])?$_POST["requestID"]:$_REQUEST["rid"];
$roleID = isset($_POST["roleID"])?$_POST["roleID"]:(isset($_REQUEST["roleid"])?$_REQUEST["roleid"]:-1);
$refPage = isset($_REQUEST["ref"])?$_REQUEST["ref"]:"";

/* Retrieve swap request */
$sql = "SELECT roleID,CONCAT(m.mbrFirstName,' ',m.mbrLastName) AS newMember,s.svcDateTime AS origDateTime,s2.svcDateTime AS newDateTime,chgDescription FROM svcchangereq sc INNER JOIN members m ON m.memberID=sc.orgMbrID INNER JOIN services s ON s.serviceID=sc.serviceID INNER JOIN services s2 ON s2.serviceID=sc.newSvcID WHERE requestID=$requestID";
$resMbr = $db->query($sql);
$dbMbr=mysqli_fetch_array($resMbr);
$chgDescription = htmlentities($dbMbr["chgDescription"],ENT_QUOTES);
$newMember = $dbMbr["newMember"];
$origService = $dbMbr["origDateTime"];
$newService = $dbMbr["newDateTime"];
$roleID = $dbMbr["roleID"];

// Retrieve service information
$sql = "SELECT *,date_format(svcDateTime, '%a %b %D') as svcDATE, date_format(svcDateTime, '%H:%i') as svcTIME FROM services WHERE serviceID=$serviceID";
$resSvc = $db->query($sql);
$dbSvc=mysqli_fetch_array($resSvc);

// Save changes
if(isset($_POST["save"])) {
	// Retrieve role change rule
	$sql = "SELECT a.roleID as chgRole,changeRule,serviceID,newSvcID,orgMbrID,newMbrID FROM svcchangereq a INNER JOIN roles b ON a.roleID=b.roleID WHERE a.requestID=$requestID";
	$resRule = $db->query($sql);
	$dbRule=mysqli_fetch_array($resRule);
	// Accept swap request
	if($_POST["swapReply"]=="1") {
		// No Approval Required
		if($dbRule["changeRule"]==1) {
			$q = "UPDATE svcchangereq SET chgStatus='A', reqResponse='".$_POST["reqResponse"]."' WHERE requestID=$requestID";
			$mbrRes = $db->query($q);
			$q = "UPDATE serviceteam SET memberID=".$dbRule["newMbrID"]." WHERE serviceID=".$dbRule["serviceID"]." AND memberID=".$dbRule["orgMbrID"]." AND roleID=".$dbRule["chgRole"];
			$mbrRes = $db->query($q);
			$q = "UPDATE serviceteam SET memberID=".$dbRule["orgMbrID"]." WHERE serviceID=".$dbRule["newSvcID"]." AND memberID=".$dbRule["newMbrID"]." AND roleID=".$dbRule["chgRole"];
			$mbrRes = $db->query($q);
		}
		// Worship Coordinator Approval Required
		if($dbRule["changeRule"]>1) {
			$q = "UPDATE svcchangereq SET chgStatus='P', reqResponse='".$_POST["reqResponse"]."' WHERE requestID=$requestID";
			$mbrRes = $db->query($q);
		}
	} else { // Reject swap request
		$q = "UPDATE svcchangereq SET chgStatus='R', reqResponse='".$_POST["reqResponse"]."' WHERE requestID=$requestID";
		$mbrRes = $db->query($q);
	}
	// Send messages
	sendChgConfirmation($requestID,$dbRule["changeRule"],$_POST);
	if($refPage=="") {
		echo "<script>parent.window.dspTeam($serviceID);parent.window.hs.close();</script>";
	} else {
		echo "<script>document.location='index.php';</script>";
	}
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Accept Swap</title>

<script type="text/javascript">
function valRequest() {
	var frm = document.frmRequest;
	return true;
}
</script>
<style>
p {
	margin:5px;
}
</style>
<?php
echo "<link rel=\"stylesheet\" href=\"css/tw.css\" type=\"text/css\">";
echo "</head>\n";
echo "<body bgcolor='#ffffff'>\n";

echo "<form style='margin-top:0px;' name=\"frmRequest\" action=\"ajAcceptSwap.php\" onSubmit=\"return valRequest();\" method=\"post\">\n";
echo "<input type='hidden' name='action' value='$action'>\n";
echo "<input name=\"serviceID\" type=\"hidden\" value='$serviceID'>\n";
echo "<input name=\"requestID\" type=\"hidden\" value='$requestID'>\n";
$mand = "<span style='font-weight:bold;color:#cc0000;'>*</span>";
echo "	<p style='height:35px;font-size:12pt;font-weight:bold' valign='top' colspan='2' align='center'>Accept Service swap request</p>\n";

echo "	<p><b>Swap Requested by:</b>&nbsp;$newMember</p>\n";
echo "	<p><b>For role:</b>&nbsp;";
$sql = "SELECT roleDescription,roleIcon FROM roles WHERE roleID=$roleID";
$resRole = $db->query($sql);
$dbRole=mysqli_fetch_array($resRole);
echo "<input id='roleID' name='roleID' type='hidden' value=$roleID>";
echo "&nbsp;<img src='".$dbRole["roleIcon"]."'>&nbsp;".$dbRole["roleDescription"]."</p>\n";
echo "	<p><b>Swap service:</b>&nbsp;$newService <b>for</b> $origService</p>\n";
echo "	<p><b>Reason:</b>&nbsp;$chgDescription</p>";
echo "	<p><input type='radio' name='swapReply' value='1' checked />&nbsp;<b>Accept</b>&nbsp;&nbsp;&nbsp;<input type='radio' name='swapReply' value='0' />&nbsp;<b>Decline</b></p>";
$reqResponse = isset($_REQUEST["reqResponse"])?$_REQUEST["reqResponse"]:"";
echo "	<p><b>Comments:</b>&nbsp;<input type='text' name='reqResponse' size='55' maxlength='255' value='$reqResponse' /></p>";

echo "	<p><b>Messaging Options:</b> <span style='font-size:8pt'>(sent to member selected above)</span><br />\n";
echo "	<input type=\"checkbox\" name=\"chkEmail\" value=\"1\" checked />&nbsp;Send Email&nbsp;&nbsp;\n";
echo "			<input type=\"checkbox\" name=\"chkSite\" value=\"1\" />&nbsp;Save As Site Message</p>\n";
echo "	<p align='right'><input name=\"cancel\" type=\"button\" value=\"Cancel\" onClick=\"parent.window.hs.close();\" class=\"button\">&nbsp;<input name=\"save\" type=\"submit\" value=\"Submit\" class=\"button\"></p>\n";
echo "</form>\n";
echo "</body>\n</html>\n";

// Send emails confirming change acceptance
function sendChgConfirmation($requestID,$changeRule,$usrData) {
	global $siteTitle,$db;

	$swapResult = $usrData["swapReply"]=="1"?"Accepted":"Declined";
	$subject = $siteTitle." - Service Swap $swapResult";
	### $from_address=isset($_SESSION['email'])?$_SESSION['email']:"worship@southcalgary.org";
	$from_name=$_SESSION['first_name']." ".$_SESSION['last_name'];
	
	// Retrieve change request
	$q = "SELECT reqResponse,orgMbrID,newSvcID,sc.serviceID AS svcID, roleID,concat(m1.mbrFirstName,' ',m1.mbrLastName) AS origName,concat(m2.mbrFirstName,' ',m2.mbrLastName) AS newName,m1.mbrEmail1 AS origEmail1,m1.mbrEmail2 AS origEmail2,m2.mbrEmail1 AS newEmail1,m2.mbrEmail2 AS newEmail2,s1.svcDateTime AS origDateTime,s2.svcDateTime AS newDateTime,chgDescription FROM svcchangereq sc INNER JOIN members m1 ON m1.memberID=sc.orgMbrID INNER JOIN members m2 ON m2.memberID=sc.newMbrID INNER JOIN services s1 ON s1.serviceID=sc.serviceID INNER JOIN services s2 ON s2.serviceID=sc.newSvcID WHERE requestID=$requestID";
	$resReq = $db->query($q);
	$aTo = array();
	$aCC = array();
	if($dbReq = mysqli_fetch_array($resReq)) {
		if($dbReq["origEmail1"]!="") $aTo[] = array("Name"=>$dbReq["origName"],"Email"=>$dbReq["origEmail1"]);
		if($dbReq["origEmail2"]!="") $aTo[] = array("Name"=>$dbReq["origName"],"Email"=>$dbReq["origEmail2"]);
		if($dbReq["newEmail1"]!="") $aCC[] = array("Name"=>$dbReq["newName"],"Email"=>$dbReq["newEmail1"]);
		if($dbReq["newEmail2"]!="") $aCC[] = array("Name"=>$dbReq["newName"],"Email"=>$dbReq["newEmail2"]);
		
		// Retrieve Role Coordinator contact
		$q = "SELECT * FROM roles INNER JOIN roletypes ON roles.typeID=roletypes.typeID INNER JOIN members ON typeContact=memberID WHERE roleID=".$dbReq["roleID"];
		$resCoord = $db->query($q);
		if($dbCoord = mysqli_fetch_array($resCoord)) {
			if($usrData["swapReply"]=="1" && EMAILCOORD_ON) {
				if($dbCoord["mbrEmail1"]!="") $aCC[] = array("Name"=>$dbCoord["mbrFirstName"]." ".$dbCoord["mbrLastName"],"Email"=>$dbCoord["mbrEmail1"]);
				if($dbCoord["mbrEmail2"]!="") $aCC[] = array("Name"=>$dbCoord["mbrFirstName"]." ".$dbCoord["mbrLastName"],"Email"=>$dbCoord["mbrEmail2"]);
			
			}
		}
		$msgBody = "<h2>Service swap $swapResult</h2>\n";
		$msgBody .= "<br /><p><b>".$dbReq["origName"]."</b> has requested a swap for the role <b>".$dbCoord["roleDescription"]."</b> with <b>".$dbReq["newName"];
		$msgBody .= "</b> for services on ".$dbReq["origDateTime"]." and ".$dbReq["newDateTime"]." respectively.</p>\n";
		$msgBody .= "<p>".$dbReq["newName"]." has <b>$swapResult</b> this request</p>\n";
		$msgBody .= "<p><b>Response Comment:</b> ".$dbReq["reqResponse"]."</p>\n";
		if($usrData["swapReply"]=="1") {
			if($changeRule=="1") {
				$msgBody .= "<br /><p>The service schedule has been updated to reflect this change.</p>\n";
			} else {
				$msgBody .= "<br /><p>This request must be approved by the team coordinator (".$dbCoord["mbrFirstName"]." ".$dbCoord["mbrLastName"].") before the schedule will be updated.</p>\n";
			}
		} else {
			$msgBody .= "<br /><p>This request has been removed. If you still require a replacement for this service, please attempt another swap with a different member.</p>\n";
		}
		$msgBody .= "<br /><p>Thank you for your service.</p>\n";

		// Update site messages
		if(isset($usrData["chkSite"])) {
			$q = "INSERT INTO sitemessages VALUES(0,'U',".$dbReq["orgMbrID"].",".$_SESSION['user_id'].",now(),'$subject','".$db->real_escape_string($msgBody)."')";
			$resMsg = $db->query($q);
			if($changeRule>"1" && $dbReq["orgMbrID"]!=$dbCoord["typeContact"]) {
				$q = "INSERT INTO sitemessages VALUES(0,'U',".$dbCoord["typeContact"].",".$_SESSION['user_id'].",now(),'$subject','".$db->real_escape_string($msgBody)."')";
				$resMsg = $db->query($q);
			}
		}
		
		// Send Email
		if(isset($usrData["chkEmail"])) {
			$emailHTMLpre = "<html><head><title>$subject</title><style>".file_get_contents("css/twemail.css")."</style></head><body>";
			$emailHTML = $emailHTMLpre.$msgBody.$emailHTMLpost;
		
			// Send emails
		
			$mailMessage = new PHPMailer;

			setUpSMTP($mailMessage, $subject, $emailHTML);

			$adrEmail= array_column($aTo, "Email");
			$adrName= array_column($aTo, "Name");
		
			$tMsg = SendEmailAddAddress($mailMessage, $adrEmail, $adrName);
			
			$adrEmail= array_column($aCC, "Email");
			$adrName= array_column($aCC, "Name");
		
			$tMsg .= SendEmailAddAddress($mailMessage, $adrEmail, $adrName, FALSE);
		
		
			$rtnMsg .= SendEmailLog($mailMessage, $tMsg);					
		

			
		}
	}
	return true;
}
?>