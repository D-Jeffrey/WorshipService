<?php
/*******************************************************************
 * resetMemberBase.php
 * Reset all active member passwords and send activation email.
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

global $errorSending;

//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Administrators) != "yes") { 
	echo "<form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form>\n";
	echo "<script>document.frmLogin.submit();</script>\n";
	exit;
}

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Reset Member Base', $_SERVER['REQUEST_URI'], 2);

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Send email
$emailMsg = "";
if(isset($_POST["sbmComm"])) {
	require("classes/class.Html2Text.php");

	$subject = "$siteTitle - Member Activation";
	// Build message Body
	$q = "SELECT * FROM siteconfig LIMIT 1";
	$resMsg = $db->query($q);
	$dbMsg = mysqli_fetch_array($resMsg);
	$emailHTMLBase = $dbMsg["actMessage"];

	$emailMsg = updateMembers($_POST,true);
}

// Retrieve the total number of active members
$q = "SELECT memberID FROM members WHERE mbrStatus='A'";
$resTeam = $db->query($q);
$totalMembers = mysqli_num_rows($resTeam);

$title = "Reset Member Base";
$hlpID = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle." - ".$title; ?></title>
<script type="text/javascript">
function valEntry(frm) {
	return confirm("Are you sure you wish to reset ALL PENDING worship team members?");
}

<?php
if($emailMsg!="") {
	echo "window.onload = function() {\n";
	echo "	alert('$emailMsg');\n";
	echo "}\n";
}
?>
</script>

<?php
include("header.php");

echo "<form style='margin:0px;' name='frmComm' method='post' onSubmit='return valEntry(document.frmComm);' action='resetMemberBase.php'>\n";
echo "<h2 align='center'>Team Worship Member Reset - PENDING</h2>\n";
echo "<h3 align='center'>Please note that when you pres [Submit], all active members will have their passwords reset and a message will be sent with their new password</h3>\n";
echo "<table width='100%'>\n";
echo "	<tr valign='top'>\n";
echo "		<td align='center'><input type=\"checkbox\" name=\"chkEmail\" value=\"1\" checked />&nbsp;Send Email&nbsp;&nbsp;\n";
echo "			<input type=\"checkbox\" name=\"chkSite\" value=\"1\" checked />&nbsp;Save As Site Message\n";
echo "		</td>\n";
echo "	</tr>\n";
echo "</table>\n";
echo "<p align='center'><input type=\"submit\" name=\"sbmComm\" value=\"Submit\" /></p>\n";
echo "</form>\n";

echo "</body>\n</html>\n";

function updateMembers($usrData,$addPHPBB=false) {
	global $db;
	
	$q = "SELECT memberID,mbrFirstName,mbrLastName,mbrUName,mbrEmail1,mbrEmail2 FROM members WHERE mbrStatus='P'";
	$resTeam = $db->query($q);

	if ($use_phpBB3) {

		// Include PHPBB functions
		include("./phpBB3/includes/extBridge.php");
		$phpbb = new PhpBB3Component;
		$phpbb->startup();
	}

	// Retrieve all active members
	while($dbteam=mysqli_fetch_array($resTeam)) {
		$aTeamEmail = array();
		// Update member password
		$newPassword = generatePassword();
		if($addPHPBB) {
			$phpbb->addUser($dbteam["mbrUName"], $newPassword, $dbteam["mbrEmail1"]);
		} else {
			$phpbb->changePassword($dbteam["mbrUName"], $newPassword);
		}
		$q = "UPDATE members SET mbrPassword=md5('$newPassword'), mbrStatus = 'A', pchange='1' WHERE memberID = ".$dbteam["memberID"];
		$resUpd = $db->query($q);
		sendCommunication($dbteam["memberID"],$dbteam["mbrFirstName"],$dbteam["mbrLastName"],$dbteam["mbrUName"],$newPassword,$dbteam["mbrEmail1"],$dbteam["mbrEmail2"],$usrData);
	}
	return "All active members have been reset";
}

function sendCommunication($memberID,$mbrFName,$mbrLName,$mbrUName,$mbrPassword,$mbrEmail1,$mbrEmail2,$usrData) {
	global $siteTitle,$db,$subject,$emailHTMLBase;

	// created a email message $email_message=new smtp_message_class & set $from_address + $reply_address to standard from so it does not Anti-spam
	include("classes/mimemessage/create_email_message.php");
			
	# $email_message=new email_message_class; 
	$h2t = new \Html2Text\Html2Text("");

	$order = array("\r\n","\n", "\r");
	$replace = "<br \>";

	$emailHTML = str_replace("{%memberFirstName%}",str_replace($order, $replace, $mbrFName),$emailHTMLBase);
	$emailHTML = str_replace("{%memberFirstName%}",$mbrFName,$emailHTML);
	$emailHTML = str_replace("{%memberLastName%}",$mbrLName,$emailHTML);
	$emailHTML = str_replace("{%userName%}",$mbrUName,$emailHTML);
	$emailHTML = str_replace("{%userPassword%}",$mbrPassword,$emailHTML);
	
	if(isset($usrData["chkSite"])) {
		$q = "INSERT INTO sitemessages VALUES(0,'U',$memberID,".$_SESSION['user_id'].",now(),'$subject','".$emailHTML."')";
		$resTeam = $db->query($q);
//		$rtnMsg .= "Message saved for: ".$aTeamMembers[$recipient]["memberName"]."<br />\n";
	}
	if(isset($usrData["chkEmail"]) && ($mbrEmail1!="" || $mbrEmail2!="")) {
		
		$emailHTML = SendEmailWrap($emailHTML, $subject);

		$mailMessage = new PHPMailer;

		setUpSMTP($mailMessage, $subject, $emailHTML);
		$tMsg = SendEmailAddAddress($mailMessage, $adrEmail, $adrName);
		
		$to_name=$mbrFName." ".$mbrLName;
		$adrName = array($to_name,$to_name);
		$tMsg .= SendEmailAddAddress($mailMessage, array ($mbrEmail1, $mbrEmail2), $adrName);
		$rtnMsg .= SendEmailLog($mailMessage, $tMsg);					

		
	}
	unset($email_message);
	unset($h2t);
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