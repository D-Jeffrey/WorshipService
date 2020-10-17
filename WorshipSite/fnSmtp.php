<?php
/*
     setup e-mail for PHPMailer
*/
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


require("classes/class.Html2Text.php");


// You must call 		$mailMessage = new PHPMailer; before calling this

global $mailMessage;
global $pendingLog;


function AjaxSendMessage($subject, $msgBody, $ToArray, $CCArray= array())
{
	// $ToArray[0]["e"] email  $ToArray[0]["n"] name
	// $CCArray
	
	$DisplayEmailResults= true;

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

			
	
	return $rtnMsg;
	}
	
function setUpSMTP($mailPHP, $sSubject, $sMsgBody, $defaultReplyTo = TRUE) {

	global $adminname;
	global $adminemail;
	global $domain;
	global $pendingLog;
	$pendingLog = "";

	//Tell PHPMailer to use SMTP
	$mailPHP->isSMTP();
	//Enable SMTP debugging
	// SMTP::DEBUG_OFF = off (for production use)
	// SMTP::DEBUG_CLIENT = client messages
	// SMTP::DEBUG_SERVER = client and server messages
	$mailPHP->SMTPDebug = SMTP::DEBUG_OFF;
	//Set the hostname of the mail server
	$mailPHP->Host = 'smtp.office365.com';
	
	$mailPHP->Port = 587;										//Set the SMTP port number - 587 for authenticated TLS
	$mailPHP->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;		//Set the encryption mechanism to use - STARTTLS or SMTPS
	$mailPHP->SMTPAuth = true;									//Whether to use SMTP authentication
	
	
	$mailPHP->Hostname = $domain;
	$mailPHP->Username = 'worshipsite@southcalgary.org';		//Username to use for SMTP authentication - use full email address for gmail
	$mailPHP->Password = str_rot13(smtpword);					//Password to use for SMTP authentication
	$mailPHP->isHTML(true);         							// Set email format to HTML
	
	//Set who the message is to be sent from (How it appears in the e-mail box)
	$mailPHP->setFrom('worshipsite@southcalgary.org', 'SCCC Worship Team');	
	if ($defaultReplyTo) {
		$mailPHP->addReplyTo($adminemail, $adminname);					//Set an alternative reply-to address
	}
		
	// do a    $mailMessage = new PHPMailer;
			
	$mailPHP->Subject = $sSubject; 
		
	// todo fix up conversion
	$mailPHP->Body    = $sMsgBody;
	
	// Convert HTML to text
	$h2t = new \Html2Text\Html2Text($sMsgBody);
	$emailText = $h2t->getText();
	
	$mailPHP->AltBody = $emailText;
	
return $mailPHP;

//Set who the message is to be sent to
//  $mailPHP->addAddress('darren@southcalgary.org', 'DJ-SCCC');

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
// $mailPHP->msgHTML(file_get_contents('contents.html'), __DIR__);

		
}

// this sends an email and logs the Message 
//     ; used as place holder for a newline in the return string
// 
function SendEmailLog($mailPHP, $toDisplayMsg, $SucessText = ">> ", $logEmail = TRUE) {

	global $errorSending;

	$errorSending = TRUE;
	if (ENABLEMAIL) {
		try {
			if ($mailPHP->send()) {				
				$rtnResult = $SucessText . $toDisplayMsg .  ";" ;
				$errorSending = FALSE;
			} else {
				$rtnResult = "Error: " . $toDisplayMsg . " - " . $mailPHP->ErrorInfo . ";" ;
				$errorSending = TRUE;
			}
		} catch (Exception $e) {
			$errorSending = TRUE;
			$rtnResult = "Error: " . $toDisplayMsg . " - " . $e->errorMessage() . ";" ; //Pretty error messages from PHPMailer
		} catch (\Exception $e) {
			$errorSending = TRUE;
			$rtnResult = 'Exterme mail failure' . $e->getMessage(); //Boring error messages from anything else!
		}
	} else // simulate email 
	{
		$rtnResult = $SucessText . $toDisplayMsg .  ";" ;
		$errorSending = FALSE;
		logit(1, __FILE__ . ":" . __LINE__ . " Email FAKE: ". $toDisplayMsg);
	}		
	SendEmailLogFinish($logEmail, $mailPHP->Subject, $rtnResult);
	
	// Write for Admin only any e-mail that is a problem
	if ($errorSending) {
		$log  = date("Y.m.d H:i")." \"" . $mailPHP->Subject . "\"" . PHP_EOL. str_replace(";",PHP_EOL, $rtnResult) . 
				PHP_EOL. "--------".PHP_EOL;
		file_put_contents('./logs/emailerr_'.date("Y.m").'.txt', $log, FILE_APPEND);
	}

	return $rtnResult;
}

function SendEmailLogFinish($logEmail, $sub = "", $logMsg = ""){
	
	global $pendingLog;
	
	if ($logEmail) { 
		$log  = date("Y.m.d H:i")." Subject: " . $sub .  PHP_EOL. 
				str_repeat(" ", 17) . str_replace(";",str_repeat(" ", 17). PHP_EOL, $pendingLog . $logMsg) . 
				PHP_EOL. "--------".PHP_EOL;
			//Write action to txt log
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/logs/email_'.date("Y.m").'.txt', $log, FILE_APPEND);
		$pendingLog = "";
	}
	else {
		$pendingLog .= $logMsg;
	}
	
}

// Array of email address and Array of names
//
function SendEmailAddAddress($mailPHP, $aEmail, $ToName, $bToAddress = TRUE) 
{
	
	$rtnResult = "";
	if(count($aEmail)>0) {
		$rtnResult = $ToName[0] . "  (";
		$lTo= $ToName[0];
		$t = FALSE;
		for ($a=0;$a<count($aEmail);$a++) {
				if (strlen($aEmail[$a])>0) {
					if ($bToAddress)
						$mailPHP->addAddress($aEmail[$a], $ToName[$a]);
					else 
						$mailPHP->addCC($aEmail[$a], $ToName[$a]);
					if ($ToName[$a] != $lTo) {
						$rtnResult .= ") ". $ToName[$a] . "  (";
						$t= FALSE;
						}
					$rtnResult .= ($t?", ":""). $aEmail[$a];
					$t=TRUE;
				}	
				
		}
		if (!$t) {
			$rtnResult .= "NO E-Mail Address";
		}
		$rtnResult .= ")";
		
	}				
	return $rtnResult;
}				

function SendEmailWrap( $message, $sub) 
{
		// Send Email
		$emailHTMLpre = "<html><head><title>" . $sub . "</title><style>".
				file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/css/twemail.css")."</style></head><body>";
		
		$emailHTMLpost = "</body></html>";
		
		
		return $emailHTMLpre. $message . $emailHTMLpost;
}
?>