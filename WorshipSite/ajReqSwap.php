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
require($baseDir.'/lr/functions.php'); 

include("fnSmtp.php");


//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
//   $valUser = $_SESSION["user_id"] == $_REQUEST["id"];
if (allow_access(Users) != "yes") { 
	exit;
}

include ('fnNicetime.php');

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());



$action = isset($_REQUEST["a"])?$_REQUEST["a"]:$_POST["action"];
$serviceID= isset($_REQUEST["sid"])?$_REQUEST["sid"]:$_POST["serviceID"];
$requestID= isset($_REQUEST["rid"])?$_REQUEST["rid"]:(isset($_POST["requestID"])?$_POST["requestID"]:"");
$roleID = isset($_REQUEST["roleid"])?$_REQUEST["roleid"]:$_POST["roleID"];
$refPage = isset($_REQUEST["ref"])?$_REQUEST["ref"]:"";

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

// Retrieve service information
$sql = "SELECT *,date_format(svcDateTime, '%a %b %D') as svcDATE, date_format(svcDateTime, '%H:%i') as svcTIME FROM services WHERE serviceID=$serviceID";
$resSvc = $db->query($sql);
$dbSvc=mysqli_fetch_array($resSvc);

// Save changes
if(isset($_POST["save"])) {
	// Retrieve change request rule for role
	$sql = "SELECT changeRule,roleDescription FROM roles WHERE roleID=$roleID";
	$resRole = $db->query($sql);
	$dbRole=mysqli_fetch_array($resRole);
	$chgStatus = $dbRole["changeRule"]==3?"C":"O";
	$newMember = preg_split("/;/",$_POST["newMember"]);
	$q = "INSERT INTO svcchangereq VALUES(0,'S',$roleID,$serviceID,".$_SESSION['user_id'].",".$newMember[0].",".$newMember[1].",'".$db->real_escape_string($_POST["chgDescription"])."','$chgStatus','')";
	$resMbr = $db->query($q);
	
//	if (!$resMbr) {
//    die ( 'Invalid query: ' . mysqli_error() . '\n' . 'Whole query: ' . $q );
//  }

	$requestID = $db->insert_id;
	// Retrieve email/user id's for selected roles
	$emailMsg = sendCommunication($requestID,$_POST);
	
	if ($errorSending) {
		echo "Failed: " . $emailMsg;
		exit;
	}
	echo "<script>parent.window.dspTeam($serviceID);parent.window.hs.close();</script>";
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


// Otherwise this MUST be a 'add' Event action=add
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Request Swap</title>

<script type="text/javascript">
function valRequest() {
	var frm = document.frmRequest;
	if(frm.chgDescription.value=="") {
		alert("Please enter a Reason");
		frm.chgDescription.focus();
		return false;
	}
	if(frm.newMember.selectedIndex==0) {
		alert("Please select a member to swap with");
		frm.newMember.focus();
		return false;
	}
	return true;
}
</script>

<?php
echo "<link rel=\"stylesheet\" href=\"css/tw.css\" type=\"text/css\">";
echo "</head>\n";
echo "<body bgcolor='#ffffff'>\n";

$q = "SELECT members.memberID AS mbrID,CONCAT(mbrFirstName,' ',mbrLastName) AS mbrName,services.serviceID AS svcID,svcDateTime FROM serviceteam INNER JOIN services USING(serviceID) INNER JOIN members USING(memberID) WHERE roleID=$roleID AND svcDateTime>'".date("Y-m-d")." 23:59:59' AND members.memberID <> ".$_SESSION['user_id']." ORDER BY mbrName, svcDateTime";
$results = $db->query($q);

$GotSome = (mysqli_num_rows($results)> 0);
logit(4, __FILE__ . ":" .  __LINE__ . " Q: ". $q .  " R: " . mysqli_num_rows($results) ." E:". $db->error);

echo "<form style='margin-top:0px;' name=\"frmRequest\" action=\"ajReqSwap.php\" onSubmit=\"return valRequest();\" method=\"post\">\n";
echo "<input type='hidden' name='action' value='$action'>\n";
echo "<input name=\"serviceID\" type=\"hidden\" value='$serviceID'>\n";
echo "<input name=\"requestID\" type=\"hidden\" value='$requestID'>\n";
$mand = "<span style='font-weight:bold;color:#cc0000;'>*</span>";
echo "	<p style='height:35px;font-size:12pt;font-weight:bold' valign='top' colspan='2' align='center'>Service swap request for ".$dbSvc["svcDATE"]." - ".nicetime($dbSvc["svcTIME"])."</p>\n";
if ($GotSome) {
	echo "	<p><b>Reason:$mand</b>&nbsp;<input type='text' name='chgDescription' size='55' maxlength='255' value='$chgDescription' /></p>";
}
echo "	<p><b>Request a replacement for role:</b>&nbsp;";
$sql = "SELECT roleDescription,roleIcon FROM roles WHERE roleID=$roleID";
$resRole = $db->query($sql);
$dbRole=mysqli_fetch_array($resRole);
echo "<input id='roleID' name='roleID' type='hidden' value=$roleID>";
echo "&nbsp;<img src='".$dbRole["roleIcon"]."'>&nbsp;".$dbRole["roleDescription"]."</p>\n";

if ($GotSome) {
	echo "	<p><b>Request Swap:</b>&nbsp;<select name='newMember' style='font-weight:bold;'>\n";

	echo "		<option value=\"0\">-- Select member to swap with --</option>";
	while ($results && $row = mysqli_fetch_array($results)) {
		echo "		<option value=\"".$row["mbrID"].";".$row["svcID"]."\">".$row["mbrName"]." (".$row["svcDateTime"].")</option>";
	}
	echo "	</select></p>\n";
	echo "	<p><b>Messaging Options:</b> <span style='font-size:8pt'>(sent to member selected above)</span><br />\n";
	echo "	<input type=\"checkbox\" name=\"chkEmail\" value=\"1\" checked />&nbsp;Send Email&nbsp;&nbsp;\n";
	echo "			<input type=\"checkbox\" name=\"chkCC\" value=\"1\" checked />&nbsp;CC Email&nbsp;&nbsp;\n";
	echo "			<input type=\"checkbox\" name=\"chkSite\" value=\"1\" />&nbsp;Save As Site Message</p>\n"
	;
} else {
	echo "<div style='color:red; font-size:large'>There is <b>no one available</b> to swap with</div>\n";
}

echo "	<p align='right'><input name=\"cancel\" type=\"button\" value=\"Cancel\" onClick=\"parent.window.hs.close();\" class=\"button\">" ;
if ($GotSome) {
	echo	"&nbsp;<input name=\"save\" type=\"submit\" value=\"Save\" class=\"button\"></p>\n";
}
echo "</form>\n";
echo "</body>\n</html>\n";


function sendCommunication($requestID,$usrData) {
	global $siteTitle,$db;
	global $errorSending;

	$sql = "SELECT sc.serviceID AS svcID, roleID,m1.mbrFirstName AS origFName,m1.mbrLastName AS origLName,m2.mbrFirstName AS newFName,m2.mbrLastName as newLName, concat(m2.mbrFirstName,' ',m2.mbrLastName) AS newName,sc.newMbrID as newMbrID, m1.mbrEmail1 AS origEmail1,m1.mbrEmail2 AS origEmail2,m2.mbrEmail1 AS newEmail1,m2.mbrEmail2 AS newEmail2,s1.svcDateTime AS origDateTime,s2.svcDateTime AS newDateTime,chgDescription FROM svcchangereq sc INNER JOIN members m1 ON m1.memberID=sc.orgMbrID INNER JOIN members m2 ON m2.memberID=sc.newMbrID INNER JOIN services s1 ON s1.serviceID=sc.serviceID INNER JOIN services s2 ON s2.serviceID=sc.newSvcID WHERE requestID=$requestID";
	$resReq = $db->query($sql);
	$dbReq=mysqli_fetch_array($resReq);

	logit(4, __FILE__ . ":" .  __LINE__ . " Q: ". $sql .  " R: " . mysqli_num_rows($resReq) ." E:". $db->error);

	$roleID = $dbReq["roleID"];

	// Retrieve change request rule for role
	$sql = "SELECT changeRule,typeContact,roleDescription FROM roles INNER JOIN roletypes ON roles.typeID=roletypes.typeID WHERE roleID=$roleID";
	$resRole = $db->query($sql);

	$dbRole=mysqli_fetch_array($resRole);
	$chgRule = $dbRole["changeRule"];
	if($dbRole["changeRule"]==3) {
	// Retrieve coordinator email based on request
		$q = "SELECT memberID,roleArray,concat(mbrFirstName,' ',mbrLastName) as mbrName,mbrEmail1,mbrEmail2 FROM members WHERE memberID=".$dbRole["typeContact"];
		$resTeam = $db->query($q);
		if($dbteam=mysqli_fetch_array($resTeam)) {
			if($dbteam["mbrEmail1"]!="") $aTeamEmail[] = array("address"=>$dbteam["mbrEmail1"],"name"=>$dbteam["mbrName"]);
			if($dbteam["mbrEmail2"]!="") $aTeamEmail[] = array("address"=>$dbteam["mbrEmail2"],"name"=>$dbteam["mbrName"]);
			$aTeamMembers[] = array("memberID" => $dbteam["memberID"],"memberName" => $dbteam["mbrName"]);
		}
	} else {
	// Retrieve member email based on request
		if($dbReq["newEmail1"]!="") $aTeamEmail[] = array("address"=>$dbReq["newEmail1"],"name"=>$dbReq["newName"]);
		if($dbReq["newEmail2"]!="") $aTeamEmail[] = array("address"=>$dbReq["newEmail2"],"name"=>$dbReq["newName"]);
		$aTeamMembers[] = array("memberID" => $dbReq["newMbrID"],"memberName" => $dbReq["newName"]);
	}
	
	$subject = $siteTitle." - Service Swap Request";
	# $from_address=isset($_SESSION['email'])?$_SESSION['email']:"worship@southcalgary.org";
	$from_name=$_SESSION['first_name']." ".$_SESSION['last_name'];
	
	// Build message Body
	$msgBody = "<p>".$dbReq["origFName"]." ".$dbReq["origLName"]." is requesting if you (".$dbReq["newFName"]." ".$dbReq["newLName"];
	$msgBody .= ") would agree to providing your service in the role of <b>".$dbRole["roleDescription"]."</b> on <b>".$dbReq["origDateTime"];
	$msgBody .= "</b>. In exchange, ".$dbReq["origFName"]." will take over for you on ".$dbReq["newDateTime"].".</p>\n";
	$msgBody .= "<p>Please sign in to the <a href=\"https://worship.southcalgary.org\">SCCC Team Worship</a> website, go to the ";
	$msgBody .= "<a href=\"https://worship.southcalgary.org/editService.php?id=".$dbReq["svcID"]."\">Edit Service</a> page and click on the";
	$msgBody .= " \"Accept Swap\" button beside ".$dbReq["origFName"]."'s name. This will allow you to either accept or decline this request.</p>\n";
	$msgBody .= "<p><b>Request Note:</b> ".$usrData["chgDescription"]."</p>\n";
	// Add Change Rule
	$aRules = array("","No approval required. If you accept this swap request, you will automatically assume this role",
			"Coordinator approval required. If you accept this request, the worship coordinator will review prior to approving",
			"Closed Request. The request is only sent to the coordinator for action");
	$msgBody .= "<p><b>*** Note:</b> ".$aRules[$chgRule]."</p>\n";
	$msgBody .= "<p>Thank you for your service</p>\n";

	$errorSending = FALSE;
	
	if(isset($usrData["chkSite"])) {
		for($recipient=0;$recipient<count($aTeamMembers);$recipient++) {
			$q = "INSERT INTO sitemessages VALUES(0,'U',".$aTeamMembers[$recipient]["memberID"].",".$_SESSION['user_id'].",now(),'$subject','".$db->real_escape_string($msgBody)."')";
			$resTeam = $db->query($q);
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
		
		
		$adrName = $dbReq["origFName"]." ".$dbReq["origLName"];
		$adrName = array($adrName,$adrName);
		if ($tMsg != "") {
			// CC the person who requested it.
			if (isset($usrData["chkCC"])) {
				$tMsg .= SendEmailAddAddress($mailMessage, array ($dbReq["origEmail1"], $dbReq["origEmail2"]),
					 array($dbReq["origFName"]." ".$dbReq["origLName"],$dbReq["origFName"]." ".$dbReq["origLName"]), FALSE );
				}
			$rtnMsg .= SendEmailLog($mailMessage, $tMsg);					
		}
	
		// TODO need to CC the requester
		

	}
	return $rtnMsg;
}

?>