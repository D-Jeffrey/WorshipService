<?php


echo "Subject: " .$_REQUEST["Subject"];
sleep (4);
echo "from : Jim <br>";
exit;

// WIP
// TODO Unconstruction
//
//
// https://www.webslesson.info/2017/10/how-to-send-bulk-email-in-php-using.html
/*******************************************************************
 * ajSendEmail.php
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
require('lr/functions.php'); 

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