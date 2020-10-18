<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_cache_limiter();
session_start();

require('lr/config.php');
require('lr/functions.php'); 

if(isset($_REQUEST['id'])) {
	$serviceID = $_REQUEST['id'];
	$memberID = $_REQUEST['mid'];
} else {
	exit;
}

include ('fnNicetime.php');

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


/* Retrieve Service for specified id */
$sql = "SELECT *, date_format(svcPractice, '%W %M %D') as svcPDATE, date_format(svcPractice, '%H:%i') as svcPTIME, date_format(svcDateTime, '%W %M %D') as svcDATE, date_format(svcDateTime, '%H:%i') as svcTIME FROM services WHERE serviceID=$serviceID";
$resSVC = $db->query($sql);
$dbSvc=mysqli_fetch_array($resSVC);

/* Retrieve team members */
$q = "SELECT serviceteam.memberID AS mbrID, serviceteam.roleID as rID,roleDescription,roleIcon,concat(mbrFirstName,' ',mbrLastName) as mbrName,mbrEmail1,mbrEmail2,mbrHPhone,mbrWPhone,mbrCPhone,soundNotes,roles.typeID as rType FROM serviceteam INNER JOIN members ON serviceteam.memberID = members.memberID INNER JOIN roles on serviceteam.roleID = roles.roleID INNER JOIN roletypes on roles.typeID = roletypes.typeID WHERE serviceID = $serviceID ORDER BY typeSort, roleDescription";
$resTeam = $db->query($q);

$teamDesc = "<table>";
$roleType = 0;
while($dbteam=mysqli_fetch_array($resTeam)) {
	$mLink = $dbteam["mbrEmail1"]==""?"'#'":"'mailto:".$dbteam["mbrEmail1"]."' target='_blank'";

	// Build Hover info table
	$ovrTxt = "<a href=$mLink onmouseover='return overlib(\"<table><tr><th align=left>Member:</th><td>".$dbteam["mbrName"]."</td></tr>";
	$ovrTxt .= $dbteam["mbrHPhone"]==""?"":"<tr><th align=left>Home Phone:</th><td>".$dbteam["mbrHPhone"]."</td></tr>";
	$ovrTxt .= $dbteam["mbrWPhone"]==""?"":"<tr><th align=left>Work Phone:</th><td>".$dbteam["mbrWPhone"]."</td></tr>";
	$ovrTxt .= $dbteam["mbrCPhone"]==""?"":"<tr><th align=left>Cell Phone:</th><td>".$dbteam["mbrCPhone"]."</td></tr>";
	$ovrTxt .= $dbteam["mbrEmail1"]==""?"":"<tr><th align=left>Email:</th><td>".$dbteam["mbrEmail1"]."</td></tr>";
	$ovrTxt .= $dbteam["mbrEmail2"]==""?"":"<tr><th align=left>Email2:</th><td>".$dbteam["mbrEmail2"]."</td></tr>";
	$ovrTxt .= "</table>\", WIDTH, 400)' onmouseout='return nd();'>";
	$roleSep = $dbteam["rType"]!=$roleType && $roleType>0?" style='border-top:1px solid gray'":"";
	$roleType = $dbteam["rType"];
	$teamDesc .= "	<tr$roleSep><td><img src='".$dbteam["roleIcon"]."' alt='".$dbteam["roleDescription"]."'>&nbsp;</td>\n";
	$teamDesc .= "		<td><b>".$dbteam["roleDescription"]."</b>&nbsp;</td><td>$ovrTxt".$dbteam["mbrName"]."</a>&nbsp;\n";
	$sndNotes = $dbteam["soundNotes"]==""?"":"&nbsp;(".$dbteam["soundNotes"].")";
	$teamDesc .= "$sndNotes</td>\n";
	
	if(substr($dbSvc["svcDateTime"],0,10)>date("Y-m-d") && $dbteam["mbrID"]>0) {
		// Display change request options
		$chgReq = dspChgStatus($dbteam["mbrID"],$dbteam["mbrName"],$dbteam["rID"],$dbteam["roleDescription"],$dbSvc["svcDATE"]." at ".nicetime($dbSvc["svcTIME"]));
		$teamDesc .= "		<td>$chgReq</td></tr>\n";
	} else {
		$teamDesc .= "		<td>&nbsp;</td></tr>\n";
	}
}

echo $teamDesc."</table>\n";


function dspChgStatus($mbrID,$mbrName,$roleID,$roleDescription,$svcDesc) {
	global $db, $serviceID;
	$sql = "SELECT requestID,chgStatus FROM changerequests WHERE chgStatus <> 'A' AND orgMbrID=$mbrID AND serviceID=$serviceID AND roleID=$roleID";
	$resReq = $db->query($sql);
	if(mysqli_num_rows($resReq) > 0 && $resReq) {
		$dbReq=mysqli_fetch_array($resReq);
		$chgSts = $dbReq["chgStatus"];
		if($mbrID==$_SESSION['user_id']) {
			$chgSts = "mine";
		}
	} else {
		$chgSts = "";
	}
	$canDo = strpos(",".$_SESSION['roles'].",",",".$roleID.",")!==false;
	if($chgSts=="mine") {
		$chgOut = "<a href='#' onClick='delRequest($serviceID,".$dbReq["requestID"].",\"$svcDesc\");' title='Remove Change Request'><img border='0' src='/images/delRequest.gif' alt='Request Change' /></a>";
	} else if($chgSts=="O" && $canDo) {
		$chgOut = "<a href='#' onClick='accRequest(".$dbReq["requestID"].",\"$roleDescription\",\"$mbrName\",\"$svcDesc\");' title='Accept Change Request'><img border='0' src='/images/accRequest.gif' alt='Request Change' /></a>";
	} else if($chgSts=="" && $mbrID==$_SESSION['user_id']) {
		$chgOut = "<a title='Request Change' id='hsEditTeam' href='ajReqChange.php?a=add&sid=$serviceID&roleid=$roleID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', contentID: 'divReqChg', headingText: 'Request Change', width: 500, height:250 } )\"><img border='0' src='/images/reqChange.gif' alt='Request Change' /></a>";
	} else {
		$chgOut = "&nbsp;";
	}
	return $chgOut;
}

?>