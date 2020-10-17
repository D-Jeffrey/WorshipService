<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_cache_limiter();
session_start();

require('lr/config.php');
require($baseDir.'/lr/functions.php'); 

//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Users) != "yes") { 
	header("Location: /lr/login.php"); 
	exit;
}

if(isset($_REQUEST['id'])) {
	$serviceID = $_REQUEST['id'];
} else {
	exit;
}

include ('fnNicetime.php');

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Delete team member from the service
if(isset($_REQUEST['act']) && $_REQUEST['act']=="del") {
	$sql = "DELETE FROM serviceteam WHERE serviceID=$serviceID AND memberID=".$_REQUEST["mbr"]." AND roleID=".$_REQUEST["rid"];
	$resMbr = $db->query($sql);
}

// Retrieve site configuration values
$q = "SELECT soundRole FROM siteconfig LIMIT 1";
$resCfg = $db->query($q);
$dbCfg=mysqli_fetch_array($resCfg);

$isAdmin = allow_access(Administrators) == "yes";

// Is this user part of the sound crew?
$isSound = strpos(",".$_SESSION['roles'].",",$dbCfg["soundRole"])!==false;

// Retrieve service info
$sql = "SELECT *, date_format(svcPractice, '%W %M %D') as svcPDATE, date_format(svcPractice, '%H:%i') as svcPTIME, date_format(svcDateTime, '%W %M %D') as svcDATE, date_format(svcDateTime, '%H:%i') as svcTIME FROM services LEFT JOIN members ON svcContact=memberID WHERE serviceID=$serviceID";
$resSVC = $db->query($sql);
$dbSvc=mysqli_fetch_array($resSVC);

/* Retrieve team members */
$q = "SELECT serviceteam.memberID AS mbrID, serviceteam.roleID as rID,roleDescription,roleIcon,concat(mbrFirstName,' ',mbrLastName) as mbrName,mbrEmail1,mbrEmail2,mbrHPhone,mbrWPhone,mbrCPhone,soundNotes,roles.typeID as rType FROM serviceteam INNER JOIN members ON serviceteam.memberID = members.memberID INNER JOIN roles on serviceteam.roleID = roles.roleID INNER JOIN roletypes on roles.typeID = roletypes.typeID WHERE serviceID = $serviceID ORDER BY typeSort, roleDescription";
$resTeam = $db->query($q);
$roleType=0;

$teamDesc = "<table width='100%'>";
$i = 1;
while($dbteam=mysqli_fetch_array($resTeam)) {
	$mLink = "'adminCommunications.php?id=".$dbteam["mbrID"]."&dst=I' target='_self'";

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
	$teamDesc .= "	<tr$roleSep><td>";

	// Check for change request status
	if(substr($dbSvc["svcDateTime"],0,10)>date("Y-m-d") && $dbteam["mbrID"]>0) {
		// Display change request options
		$chgReq = dspChgStatus($dbteam["mbrID"],$dbteam["mbrName"],$dbteam["rID"],$dbteam["roleDescription"],$dbSvc["svcDATE"]." at ".nicetime($dbSvc["svcTIME"]));
		$chgDesc = $chgReq[0];
	} else {
		$chgDesc = "";
	}

	// Check for swap request status
	if(!$chgReq[1] && substr($dbSvc["svcDateTime"],0,10)>date("Y-m-d") && $dbteam["mbrID"]>0) {
		// Display swap request options
		$swapReq = dspSwapStatus($dbteam["mbrID"],$dbteam["mbrName"],$dbteam["rID"],$dbteam["roleDescription"],$dbSvc["svcDATE"]." at ".nicetime($dbSvc["svcTIME"]));
		$swapDesc = $swapReq[0];
	} else {
		$swapDesc = "";
		$swapReq[1] = 0;
	}
	if(!$chgReq[1] && !$swapReq[1]) {
		$reqChg = "$chgDesc&nbsp;$swapDesc";
	} else {
		$reqChg = $chgReq[1]?"$chgDesc":"";
		$reqChg = $swapReq[1]?"$swapDesc":$reqChg;
	}
	$delChgExists = $chgReq[1] || $swapReq[1]?"1":"0";
	$teamDesc .= $isAdmin?"<a href='#' onClick=\"delMember(".$dbteam["mbrID"].",".$dbteam["rID"].",'".$dbteam["mbrName"]." (".$dbteam["roleDescription"].")',$delChgExists);\" title='Remove Member from Service'><img src='images/icon_delete.gif'></a>&nbsp;":"";
	$teamDesc .= $isAdmin||$isSound?"<a id='hsEditTeam' href='ajEditTeam.php?act=edit&id=$serviceID&mbr=".$dbteam["mbrID"]."&sdte=".substr($dbSvc["svcDateTime"],0,10)."&rid=".$dbteam["rID"]."' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', contentID: 'divEditTeam', headingText: 'Add Team Member', width: 430 } )\"><img src='images/edit.png'></a>&nbsp;":"";
	$teamDesc .= "<img src='".$dbteam["roleIcon"]."' alt='".$dbteam["roleDescription"]."'>&nbsp;</td><td><b>".$dbteam["roleDescription"]."</b>&nbsp;</td><td>$ovrTxt".$dbteam["mbrName"]."</a>";
	$sndNotes = $dbteam["soundNotes"]==""?"":"&nbsp;(".$dbteam["soundNotes"].")";
	$teamDesc .= "$sndNotes</td>\n";
	$teamDesc .= "	<td>$reqChg</td></tr>\n";
	$i++;
}

echo $teamDesc."\n";
echo "	<tr><td colspan='3' height='30'><span id='btnLink'><a id='hsEditTeam' href='ajEditTeam.php?act=add&id=$serviceID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', contentID: 'divEditTeam', headingText: 'Add Team Member', width: 430 } )\">Add Member</a></span>\n";
echo "	</td></tr>\n";
echo "</table>\n";

function dspChgStatus($mbrID,$mbrName,$roleID,$roleDescription,$svcDesc) {
	global $db, $serviceID;
	$chgExists = false;
	$sql = "SELECT requestID,chgStatus FROM svcchangereq WHERE reqType='C' AND chgStatus <> 'A' AND orgMbrID=$mbrID AND serviceID=$serviceID AND roleID=$roleID";
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
	$chgExists = $chgSts=="mine" || $chgSts=="O";
	if($chgSts=="mine") {
		$chgOut = "<a href='#' onClick='delRequest($serviceID,".$dbReq["requestID"].",\"$svcDesc\");' title='Remove Change Request'><img border='0' src='/images/delRequest.gif' alt='Request Change' /></a>";
	} else if($chgSts=="O" && $canDo) {
		$chgOut = "<a href='#' onClick='accRequest(".$dbReq["requestID"].",\"$roleDescription\",\"$mbrName\",\"$svcDesc\");' title='Accept Change Request'><img border='0' src='/images/accRequest.gif' alt='Request Change' /></a>";
	} else if($chgSts=="" && $mbrID==$_SESSION['user_id']) {
		$chgOut = "<a title='Request Change' id='hsEditTeam' href='ajReqChange.php?a=add&sid=$serviceID&roleid=$roleID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', contentID: 'divReqChg', headingText: 'Request Change', width: 500, height:250 } )\"><img border='0' src='/images/reqChange.gif' alt='Request Change' /></a>";
	} else {
		$chgOut = "";
	}
	return array($chgOut,$chgExists);
}

function dspSwapStatus($mbrID,$mbrName,$roleID,$roleDescription,$svcDesc) {
	global $db, $serviceID;
	$chgExists = false;
	$sql = "SELECT requestID,chgStatus,orgMbrID FROM svcchangereq WHERE reqType='S' AND chgStatus <> 'A' AND chgStatus <> 'R' AND ((orgMbrID=$mbrID AND serviceID=$serviceID) OR (newMbrID=$mbrID AND newSvcID=$serviceID)) AND roleID=$roleID";
	$resReq = $db->query($sql);
	if(mysqli_num_rows($resReq) > 0 && $resReq) {
		$dbReq=mysqli_fetch_array($resReq);
		$chgSts = $dbReq["chgStatus"];
		if($mbrID==$_SESSION['user_id'] && $dbReq["orgMbrID"]==$mbrID) {
			$chgSts = "mine";
		}
	} else {
		$chgSts = "";
	}
	$canDo = strpos(",".$_SESSION['roles'].",",",".$roleID.",")!==false;
	$chgExists = $chgSts=="mine" || $chgSts=="O";
	if($chgSts=="mine") {
		$chgOut = "<a href='#' onClick='delSwap($serviceID,".$dbReq["requestID"].",\"$svcDesc\");' title='Remove Swap Request'><img border='0' src='/images/delSwap.gif' alt='Delete Swap Request' /></a>";
	} else if($chgSts=="O" && $canDo) {
		$chgOut = "<a title='Reply to Swap' id='hsEditTeam' href='ajAcceptSwap.php?a=acc&rid=".$dbReq["requestID"]."&sid=$serviceID&roleid=$roleID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', contentID: 'divReqChg', headingText: 'Reply to Swap', width: 540, height:300 } )\"><img border='0' src='/images/accSwap.gif' alt='Reply to Swap Request' /></a>";
	} else if($chgSts=="" && $mbrID==$_SESSION['user_id']) {
		$chgOut = "<a title='Request Swap' id='hsEditTeam' href='ajReqSwap.php?a=add&sid=$serviceID&roleid=$roleID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', contentID: 'divReqChg', headingText: 'Request Swap', width: 500, height:300 } )\"><img border='0' src='/images/reqSwap.gif' alt='Request Swap' /></a>";
	} else {
		$chgOut = "";
	}
	return array($chgOut,$chgExists);
}


?>