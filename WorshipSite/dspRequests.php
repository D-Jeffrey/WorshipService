<?php 
/*******************************************************************
 * dspRequests.php
 * Display Change Requests
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
if (allow_access(Administrators) != "yes") { 
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


$action = isset($_POST["action"])?$_POST["action"]:"";

// Delete Request
if($action=="del") {
	$q = "DELETE FROM svcchangereq WHERE requestID=".$_POST["requestID"];
	$mbrRes = $db->query($q);
}

// Approve Request
if($action=="app") {
	// Update request status
	$q = "UPDATE svcchangereq SET chgStatus='A' WHERE requestID=".$_POST["requestID"];
	$mbrReq = $db->query($q);
	// Retrieve request info
	$q = "SELECT * FROM svcchangereq WHERE requestID=".$_POST["requestID"];
	
	$mbrReq = $db->query($q);
	logit(4, __FILE__ . ":" . __LINE__ . " Q: ". $q . " ID:" . $_POST["requestID"] ." E:". $db->error);
	
	$dbReq=mysqli_fetch_array($mbrReq);
	
	$roleID = $dbReq["roleID"];
	$q = "UPDATE serviceteam SET memberID=".$dbReq["newMbrID"]." WHERE serviceID = ".$dbReq["serviceID"]." AND memberID = ".$dbReq["orgMbrID"]." AND roleID = $roleID";
	$mbrReq = $db->query($q);
	sendChgConfirmation($_POST["requestID"]);
	
}

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Review Requests', $_SERVER['REQUEST_URI'], 1);

$isAdmin = (allow_access(Administrators)=="yes");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Review Change Requests</title>

<script type="text/javascript">

function dspRoles(id) {
	var oUpdater = new Ajax.Updater({ success:'divDspRequests' }, '/ajDspRequests.php', { 
		method: "get"
	});
}

function delRequest(type,id,desc) {
	var chgType = type=="C"?"Change":"Swap";
	if(confirm("Delete "+chgType+" Request: "+desc+"?")) {
		document.frmRequest.action.value="del";
		document.frmRequest.requestID.value=id;
		document.frmRequest.submit();
	}
}
function rejRequest(id,desc) {
	if(confirm("Reject Request: "+desc+"?")) {
		document.frmRequest.action.value="rej";
		document.frmRequest.requestID.value=id;
		document.frmRequest.submit();
	}
}
function appRequest(type,id,desc) {
	var chgType = type=="C"?"Change":"Swap";
	if(confirm("Approve "+chgType+" Request: "+desc+"?")) {
		document.frmRequest.action.value="app";
		document.frmRequest.requestID.value=id;
		document.frmRequest.submit();
	}
}
</script>
<?php

$hlpID = 23;
$title = "Review Change Requests";
include("header.php");

echo "	<form name=\"frmRequest\" action=\"dspRequests.php\" method=\"post\">\n";
echo "	<input name=\"action\" type=\"hidden\">\n";
echo "	<input name=\"requestID\" type=\"hidden\">\n";
$q = "SELECT * FROM roles ORDER BY roleDescription";
$resRole = $db->query($q);
$aRoles=array();
while($dbRole=mysqli_fetch_array($resRole)) {
	$aRoles[$dbRole["roleID"]] = $dbRole["roleDescription"];
}

$q = "SELECT requestID,sc.serviceID AS svcID,reqType,chgStatus,roleID,CONCAT(m1.mbrFirstName,' ',m1.mbrLastName) AS origName,CONCAT(m2.mbrFirstName,' ',m2.mbrLastName) AS newName,m1.mbrEmail1 AS origEmail1,m1.mbrEmail2 AS origEmail2,m2.mbrEmail1 AS newEmail1,m2.mbrEmail2 AS newEmail2,date_format(s1.svcDateTime,'%b %D') AS svcMD,date_format(s1.svcDateTime,'%a, %b %D, %Y') AS origDate,date_format(s2.svcDateTime,'%a, %b %D, %Y') AS newDate,chgDescription FROM svcchangereq sc INNER JOIN members m1 ON m1.memberID=sc.orgMbrID LEFT JOIN members m2 ON m2.memberID=sc.newMbrID INNER JOIN services s1 ON s1.serviceID=sc.serviceID LEFT JOIN services s2 ON s2.serviceID=sc.newSvcID WHERE s1.svcDateTime>'".date("Y-m-d")." 23:59:59' OR s2.svcDateTime>'".date("Y-m-d")." 23:59:59'";
//$q = "SELECT requestID,reqType,chgStatus,r.roleID,changeRule,date_format(svcDateTime,'%a, %b %D, %Y') AS svcDate,date_format(svcDateTime,'%b %D') AS svcMD,m.mbrFirstName as orgMF,m.mbrLastName as orgML,m2.mbrFirstName as newMF,m2.mbrLastName as newML FROM svcchangereq r INNER JOIN roles rl ON r.roleID=rl.roleID INNER JOIN services s USING(serviceID) INNER JOIN members m ON r.orgMbrID=m.memberID LEFT JOIN members m2 ON r.newMbrID=m2.memberID WHERE svcDateTime>'".date("Y-m-d")."' ORDER BY svcDateTime";
$resReq = $db->query($q);
echo "<div id='divDspRequests'><table style='font-size:8pt;border-collapse:collapse;width:100%'>\n";
echo "	<tr bgcolor='#cccccc'>\n";
echo "		<th align='left' nowrap>Req. Type</td>\n";
echo "		<th align='left' nowrap>Status</td>\n";
echo "		<th align='left' nowrap>Service Date</td>\n";
echo "		<th align='left' nowrap>Original Team Member</td>\n";
echo "		<th align='left' nowrap>New Service Date</td>\n";
echo "		<th align='left' nowrap>New Team Member</td>\n";
echo "		<th align='left' nowrap>Roles</td>\n";
echo "		<th>&nbsp;</td>\n";
echo "	</tr>\n";
$reqDesc ="";
if($resReq && (mysqli_num_rows($resReq) > 0)){
	
	$shade = false;
	while($dbReq=mysqli_fetch_array($resReq)) {
		$fgcolor = "#000000";
		if($dbReq["chgStatus"]=="A") {
			$bgcolor = "#AEFFA6";
		} else if($dbReq["chgStatus"]=="P") {
			$bgcolor = "#FEFF8D";
		} else if($dbReq["chgStatus"]=="C") {
			$bgcolor = "#FFCAC3";
		} else {
			$bgcolor = $shade?"#efefef":"";
		}
		$shade = !$shade;
		if($dbReq["chgStatus"]=="A") $reqStatus = "Change Completed";
		if($dbReq["chgStatus"]=="O") $reqStatus = "Request Outstanding";
		if($dbReq["chgStatus"]=="P") $reqStatus = "Pending Approval";
		if($dbReq["chgStatus"]=="R") $reqStatus = "Request Rejected";
		if($dbReq["chgStatus"]=="C") $reqStatus = "Manual Action Required";
		$reqType = $dbReq["reqType"]=="C"?"Change":"Swap";
		$reqDesc .= "	<tr bgcolor='$bgcolor'>\n";
		$reqDesc .= "		<td nowrap>$reqType</td>\n";
		$reqDesc .= "		<td nowrap>$reqStatus</td>\n";
		$reqDesc .= "		<td nowrap>".$dbReq["origDate"]."</td>\n";
		$reqDesc .= "		<td nowrap>".$dbReq["origName"]."</td>\n";
		$reqDesc .= "		<td nowrap>".$dbReq["newDate"]."</td>\n";
		$reqDesc .= "		<td nowrap>".$dbReq["newName"]."</td>\n";
		$reqDesc .= "		<td nowrap>";
		$roleID = $dbReq["roleID"];
		$reqDesc .= $aRoles[$roleID];
		$reqDesc .= "</td>\n";
		$appReq = $dbReq["chgStatus"]=="P"?"<a onClick=\"appRequest('".$dbReq["reqType"]."',".$dbReq["requestID"].",'".$dbReq["origName"].". (".$dbReq["svcMD"].")');\" href='#' title='Approve Change Request'><img src=\"images/icon_accept.gif\"></a>":"&nbsp;";
		$coordReq = $dbReq["chgStatus"]=="C"?"<a href='ajReqChange.php?a=coord&rid=".$dbReq["requestID"]."' title='Coordinate Change Request' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdRequest',headingText: 'Coordinate Change Request' });\"><img src=\"images/edit.png\"></a>":"&nbsp;";
		$reqDesc .= "		<td>$appReq$coordReq\n";
		$reqDesc .= "<a onClick=\"delRequest('".$dbReq["reqType"]."',".$dbReq["requestID"].",'".$dbReq["origName"].". (".$dbReq["svcMD"].")');\" href='#' title='Delete Change Request'><img src=\"images/icon_delete.gif\"></a></td>\n";
		$reqDesc .= "	</tr>\n";
	}
}
echo $reqDesc."</table></div>\n";

// Update Requests
echo "<div id='divUpdRequest' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";

echo "</body>\n</html>\n";


// Send emails confirming change acceptance
function sendChgConfirmation($requestID) {
	global $siteTitle,$db;

	$subject = $siteTitle." - Service Change Approved";
	# $from_address=isset($_SESSION['email'])?$_SESSION['email']:"worship@southcalgary.org";
	$from_name=$_SESSION['first_name']." ".$_SESSION['last_name'];
	
	// Retrieve change request
	$q = "SELECT serviceID, r.roleID, orgMbrID, newMbrID, concat(m1.mbrFirstName,' ',m1.mbrLastName) as orgName, concat(m2.mbrFirstName,' ',m2.mbrLastName) as newName, m1.mbrEmail1 as orgEmail1, m1.mbrEmail2 as orgEmail2, m2.mbrEmail1 as newEmail1, m2.mbrEmail2 as newEmail2, roleDescription FROM svcchangereq r INNER JOIN roles rl ON r.roleID=rl.roleID INNER JOIN members m1 ON orgMbrID=m1.memberID WHERE requestID=$requestID";
	
	$q = "SELECT reqResponse,orgMbrID,newMbrID,newSvcID,sc.serviceID AS svcID, sc.roleID, roles.roleDescription, concat(m1.mbrFirstName,' ',m1.mbrLastName) AS origName, concat(m2.mbrFirstName,' ',m2.mbrLastName) AS newName," .
		" m1.mbrEmail1 AS origEmail1, m1.mbrEmail2 AS origEmail2,m2.mbrEmail1 AS newEmail1,m2.mbrEmail2 AS newEmail2, s1.svcDateTime AS origDateTime,s2.svcDateTime AS newDateTime, chgDescription FROM svcchangereq sc " .
		"INNER JOIN members m1 ON m1.memberID=sc.orgMbrID INNER JOIN members m2 ON m2.memberID=sc.newMbrID INNER JOIN services s1 ON s1.serviceID=sc.serviceID INNER JOIN services s2 ON s2.serviceID=sc.newSvcID inner join roles on sc.roleID = roles.roleID WHERE requestID=" . $requestID;
		
	$resReq = $db->query($q);
	logit(4, __FILE__ . ":" . __LINE__ . " Q: ". $q . " ID:" . $requestID ." E:". $db->error);

	$aCC = array();

	if($dbReq = mysqli_fetch_array($resReq)) {
		
		if($dbReq["newEmail1"]!="") $aCC[] = array("Name"=>$dbReq["newName"],"Email"=>$dbReq["newEmail1"]);
		if($dbReq["newEmail2"]!="") $aCC[] = array("Name"=>$dbReq["newName"],"Email"=>$dbReq["newEmail2"]);
		if($dbReq["origEmail1"]!="") $aCC[] = array("Name"=>$dbReq["origName"],"Email"=>$dbReq["origEmail1"]);
		if($dbReq["origEmail2"]!="") $aCC[] = array("Name"=>$dbReq["origName"],"Email"=>$dbReq["origEmail2"]);
		// Retrieve Service Information
		$q = "SELECT * FROM services WHERE serviceID=".$dbReq["svcID"];
		$resSvc = $db->query($q);
		$dbSvc = mysqli_fetch_array($resSvc);
		$msgBody = "<h2>Service change completed for ".$dbSvc["svcDescription"]." on ".$dbSvc["svcDateTime"]."</h2>\n";
		$msgBody .= "<p><b>".$dbReq["origName"]."</b> has requested a change for the role <b>".$dbReq["roleDescription"]."</b> on the above service and<br />\n";
		$msgBody .= "<b>".$dbReq["newName"]."</b> has accepted the request and the request has been approved by ".$_SESSION['first_name']." ".$_SESSION['last_name']."</p>\n";
		$msgBody .= "<br /><p>The service schedule has been updated to reflect this change.</p>\n";

		// Update site messages
		$q = "INSERT INTO sitemessages VALUES(0,'U',".$dbReq["orgMbrID"].",".$_SESSION['user_id'].",now(),'$subject','".$db->real_escape_string($msgBody)."')";
		$resMsg = $db->query($q);
		$q = "INSERT INTO sitemessages VALUES(0,'U',".$dbReq["newMbrID"].",".$_SESSION['user_id'].",now(),'$subject','".$db->real_escape_string($msgBody)."')";
		$resMsg = $db->query($q);


		$emailHTML = SendEmailWrap($msgBody, $subject);

		// Send emails
		
		$mailMessage = new PHPMailer;

		setUpSMTP($mailMessage, $subject, $emailHTML);

		$adrEmail= array_column($aCC, "Email");
		$adrName = array_column($aCC, "Name");
		
	
		$tMsg = SendEmailAddAddress($mailMessage, $adrEmail,$adrName );
		
		
		$rtnMsg = SendEmailLog($mailMessage, $tMsg);					

	}
	return true;
}
?>