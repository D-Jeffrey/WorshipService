<?php 
/*******************************************************************
 * dspMessages.php
 * Update Team Member availability
 *******************************************************************/
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

require('lr/config.php');
require('lr/functions.php'); 
//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Users) != "yes") { 
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());
if (!isset($_POST["action"])) {
	$_POST["action"] = "";
}

if($_POST["action"]=="del") {
	$sql = "DELETE FROM sitemessages WHERE messageID=".$_POST["messageID"];
	$resMsg = $db->query($sql);
}

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Display Messages', $_SERVER['REQUEST_URI'], 2);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Worship Team Roles</title>
<script type="text/javascript">
function delMessages(id,msg) {
	if(confirm("Delete message: "+msg+"?")) {
		document.frmMessages.action.value="del";
		document.frmMessages.messageID.value=id;
		document.frmMessages.submit();
	}
}
function editRole(id) {
	document.frmMessages.action.value="edit";
	document.frmMessages.roleID.value=id;
	document.frmMessages.submit();
}
<?php
if(isset($_POST["msgResult"])) {
	$msg = stripslashes($_POST["msgResult"]);
	$msg = str_replace(";","\\n", $msg);
    echo "window.onload=function() {\n";
    echo "  alert(\"".$msg."\");\n";
    echo "}";
}
echo "</script>\n";

$hlpID = 0;
$title = "Display Messages";
include("header.php");

echo "	<table border=\"0\" width=\"100%\" cellspacing=\"1\" cellpadding=\"2\">\n";
echo "		<tr>\n";
echo "			<td valign=\"middle\" align=\"left\">\n";
echo "				<a href='adminCommunications.php' title='Send Message'><img src=\"images/icon_new.gif\" style='vertical-align:middle'>Send Message</a>\n";
echo "			</td>\n";
echo "		</tr>\n";
echo "	</table>\n";

echo "	<form name=\"frmMessages\" action=\"dspMessages.php\" method=\"post\">\n";
echo "	<input name=\"action\" type=\"hidden\">\n";
echo "	<input name=\"messageID\" type=\"hidden\">\n";
$q = "SELECT *,CONCAT(mbrFirstName,' ',mbrLastName) AS mbrName FROM sitemessages INNER JOIN members ON fromID=memberID WHERE toID=".$_SESSION['user_id']." ORDER BY msgTime DESC";
$resMbr = $db->query($q);
if($resMbr && (mysqli_num_rows($resMbr) > 0)){
	$msgDesc = "<table style='border:2px inset;border-collapse:collapse;width:100%'>\n";
	$msgDesc .= "	<tr style='background-color:grey;border-bottom:1px solid #000000'><td style='color:#ffffff;font-weight:bold'>&nbsp;&nbsp;&nbsp;&nbsp;Subject</td><td style='color:#ffffff;font-weight:bold'>From</td><td style='color:#ffffff;font-weight:bold'>Received</td></tr>\n";
	$shade = false;
	while($dbMsg=mysqli_fetch_array($resMbr)) {
		$bgcolor = $shade?"#efefef":"";
		$shade = !$shade;
		$fw = $dbMsg["msgStatus"]=="U"?"bold;font-style: italic":"normal";
		$msgDesc .= "	<tr bgcolor='$bgcolor'><td nowrap>\n";
		$msgDesc .= "		<a onClick=\"delMessages(".$dbMsg["messageID"].",'".$dbMsg["msgSubject"]."');\" href='#' title='Delete Message'><img src=\"images/icon_delete.gif\"></a>\n";
		$msgDesc .= "		<a style='font-weight:$fw;' id='hsShowMessage' class='highslide' href='showMessage.php?id=".$dbMsg["messageID"]."' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divDspMsg', width:700,headingText: 'Display Message' } )\">".$dbMsg["msgSubject"]."</a>\n";
		$msgDesc .= "	</td>\n";
		$msgDesc .= "	<td style='font-weight:$fw;'>".$dbMsg["mbrName"]."</td>\n";
		$msgDesc .= "	<td style='font-weight:$fw;'>".$dbMsg["msgTime"]."</td>\n";
		$msgDesc .= "	</tr>\n";
	}
	echo $msgDesc."</table>\n";
} else {
	echo "<h2 align='center'>You have no messages.</h2>\n";
}

// Display Message
echo "<div id='divDspMsg' class='highslide-html-content'>\n";
	echo "	<div class='highslide-body' style='width: 680px'></div>\n";
echo "</div>\n";

$logs = "</td><td></td><td>";
if (allow_access(Administrators) == "yes") {
	$l1 = "";
	
	$n = './logs/email_' . date("Y.m"). '.txt';
	if (file_exists($n)) {
       $l1 = '</td><td><a href="'. $n .'?' .date("ymdhis")  . '">' . date("F Y") .'</a>' ;
	}
	$n = './logs/email_' . date("Y.m",strtotime("first day of last month")). '.txt';
	if (file_exists($n)) {
    
       $l1 .= '</td><td><a href="' . $n  . '?' . date("ymdhis")  . '">' . date("F Y", strtotime("first day of last month")) .'</a>';
       }
    $n = './logs/email_' . date("Y.m",strtotime("-2 month")). '.txt';
	if (file_exists($n)) {
    
       $l1 .= '</td><td><a href="' . $n  . '?' . date("ymdhis")  . '">' . date("F Y", strtotime("-2 month")) .'</a>';
       }
    // if files are missing fill in a message
    
	if ($l1 == "") { $l1 = ' <i>No Logs in the past three months</i>'; }

	$logs = 'Email Log for: ' . $l1; 
}
echo "<table width='100%' col='3'> <tbody><tr><td>
</td><td>" . $logs ."</td>
</tr></tbody></table>\n";

echo "</body>\n</html>\n";
