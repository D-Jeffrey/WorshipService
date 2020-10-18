<?php

//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();

session_start();

error_reporting(-1 );
ini_set('display_errors', '1');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require ($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
//require the config file
require($_SERVER['DOCUMENT_ROOT'] . "/lr/config.php");
require($baseDir."/lr/functions.php"); 


include($baseDir. "/fnSmtp.php");

$msg = "";
$headmsg = "";
if(isset($_POST["submit"])) {
    //make the connection to the database
    $db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());
    
    //build and issue the query
    if($_POST['email']!="") {
        $sql ="SELECT * FROM $table_name WHERE mbrEmail1 = '". $_POST['email'] . "' LIMIT 1";
        $rqsType = "Email Address";
        $notRqsType = "Username";
    } else if($_POST['username']!="") {
        $sql ="SELECT * FROM $table_name WHERE mbrUName = '". $_POST['username'] . "'";
        $rqsType = "Username";
        $notRqsType = "Email Address";
    }

    $result = @$db->query($sql) or die(mysqli_error());
    
    //get the number of rows in the result set
    $num = mysqli_num_rows($result);
    
    //If match was found, get username and email from database
    if ($num != 0) {
    	while ($sql = mysqli_fetch_object($result)) 
    	{
    	$email		= $sql -> mbrEmail1;
    	$uname		= $sql -> mbrUName;
    	$name 		= $sql -> mbrFirstName . " " . $sql -> mbrLastName;
    	}
    
        //Update database with new password
    	$newpass = rand(10000000,99999999);
    	$chng = "UPDATE $table_name SET mbrPassword = md5('$newpass'), pchange = '1' WHERE mbrEmail1 = '$email'";
    	
    	$result2 = @$db->query($chng) or die(mysqli_error());
    
        //create message to user
    	$headmsg = "<p>Your username & temporary password has been emailed to you.</p>";
    	$headmsg .= "<p>You must change this password immediately after your next login.</p>";
    	$headmsg .= "<p></p>\n<font size=\"3\" >&nbsp;<a href=\"login.php\" style=\"background-color: darkslateblue;color: white;\">Return to Login page</a></font><br /><p></p><HR>\n";	
    	$msg = "- Or try again -";
    	
        //create mail message
    	$mailheaders = "From: $siteTitle\n";
    	$mailheaders .= "Your username is $uname.\n";
    	$mailheaders .= "Your password is $newpass.\n";
    	$mailheaders .= "$base_dir/login.php";
		$to = "$email";
        $subject = "Your Username & Password for $domain";
		mail($to, $subject, $mailheaders, "From: No Reply <$adminemail>\n");

		$emailHTML = SendEmailWrap($mailheaders, $subject);
		$mailMessage = new PHPMailer;

		setUpSMTP($mailMessage, $subject, $emailHTML);


		$tMsg = SendEmailAddAddress($mailMessage, array( $to), array($name));
	
		$rtnMsg = SendEmailLog($mailMessage, $tMsg);
		$log  = date("Y.m.d H:i")." Validate Reset: mail `". $_POST['email']. "` User: `". $_POST['username']. "`". PHP_EOL;		
			
			//Write action to txt log
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/logs/resetpwd_'.date("Y.m").'.txt', $log, FILE_APPEND);
    	
    } else {

        //If no email was found in the database send a notification to the admin
    	$email = $adminemail;
    	$msg = "<p>Your $rqsType could not be located<br />";
    	$msg .="<p>Please verify that you have entered the correct $rqsType. You can also try entering your $notRqsType. If all else fails contact your site administrator.</p>";
    
    	$mailheaders = "From: $domain\n";
    	$mailheaders .= "A user with the email address of $_POST[email] has requested a username and password reminder.\n";
    	$mailheaders .= "$_POST[email] could not be located in the database.\n";
    	$log  = date("Y.m.d H:i")." Bad Attempted : mail `". $_POST['email']. "` User: `". $_POST['username']. "` IP: " . $_SERVER['REMOTE_ADDR'] . PHP_EOL;	
				
			//Write action to txt log
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/logs/resetpwd_'.date("Y.m").'.txt', $log, FILE_APPEND);
    	sleep (2);
    	// todo flag on volume per hour
    	
    }
            //Email the request	
}

?>
<HTML>
<HEAD>
<TITLE>Username and Password Request</TITLE>
<link rel="stylesheet" href="/css/tw.css" type="text/css">
<link rel="stylesheet" href="/css/style.css" type="text/css">
<script>
function valEntry(frm) {
	if(frm.email.value=="" && frm.username.value=="") {
		alert("Please enter your email addres or username");
		frm.email.focus();
		return false;
	}
	return true;
}
</script>
</HEAD>

<body onLoad='document.frmForgot.email.focus();'>

<table class='topbar'><tr><td>&nbsp;</td><td align='right'>TeamWorship&nbsp;<br /><br />Member Login&nbsp;</td></tr></table>
<?php echo $headmsg; ?>
<section class="loginform cf">
<h2 style="margin-top:0px">Request Your Username &amp; Password</h2>
<h3 style="color:red ; background-color:lightyellow;"><?php echo $msg; ?></h3>
<FORM name="frmForgot" METHOD="POST" ACTION="emailpass.php" onSubmit="return valEntry(this);">
<P><font color="#0080C0"><strong><font size="2" face="Verdana">Email Address</font></strong><font face="Verdana"><STRONG><font size="2">:</font></STRONG><BR>
</font></font><font color="#0080C0" face="Verdana">
<INPUT TYPE="text" NAME="email" SIZE=25 MAXLENGTH=50></font></p>
<P><font color="darkblue"><strong><font size="2" face="Verdana"> - OR - <br /><br />User Name</font></strong><font face="Verdana"><STRONG><font size="2">:</font></STRONG><BR>
</font></font><font color="#7080C0" face="Verdana">
<INPUT TYPE="text" NAME="username" SIZE=25 MAXLENGTH=50></font></p>

<P>
<font color="#0080C0">
<INPUT TYPE="submit" NAME="submit" VALUE="Submit" style="font-family: Verdana">&nbsp;&nbsp;<INPUT TYPE="button" NAME="cancel" VALUE="Cancel" onClick="document.location='/lr/login.php';" style="font-family: Verdana"></font></P>
</FORM>
</section>
</BODY>
</HTML>
