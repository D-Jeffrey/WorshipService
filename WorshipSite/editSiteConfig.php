<?php
/*******************************************************************
 * editSiteConfig.php
 * Update Site Configuration Settings
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
if (allow_access(Administrators) != "yes") { 
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Site Configuration', $_SERVER['REQUEST_URI'], 1);

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Update text
if(isset($_POST["save"])) {
	$sql = "UPDATE siteconfig SET svcContact=".$_POST["svcContact"].",svcDescription='".$_POST["svcDescription"]."',svcTime='".$_POST["svcTime"]."',svcPracticeOffset=".$_POST["svcPracticeOffset"].",svcPracticeTime='".$_POST["svcPracticeTime"]."',availStatus='".$_POST["availStatus"]."', soundRole=".$_POST["soundRole"];
	$resSong = $db->query($sql);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Site Configuration</title>
<link rel="stylesheet" href="<?php echo $baseFolder; ?>css/tw.css" type="text/css">
<style>
form {
	padding:0px;
}
</style>

<script type="text/javascript" src="/scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript" src="/scripts/scriptaculous/src/scriptaculous.js"></script>

<script type="text/javascript">

function dspSchedule() {
	var oUpdater = new Ajax.Updater({ success:'divSvcSchedule' }, '/ajDspDefSchedule.php', { 
		method: "get"
	});
}

function init() {
		new Ajax.Autocompleter('selMember', 'memberBox', 'acGetMembers.php',{tokens:[','],minChars:2,afterUpdateElement:setMemberID});
<?php
if(isset($_POST["save"])) {
?>
	alert("Changes have been saved.");
<?php
}
?>
}

function setMemberID(text,li) {
	var liParts = li.id.split(";");
	var eml = "";
	if(li.id && liParts[0] > 0) {
		document.frmConfig.svcContact.value=liParts[0];
	} else {
		document.frmConfig.svcContact.value=0;
	}
}

function chgMember() {
	document.frmConfig.svcContact.value=0;
}
function delSchedule(id,desc) {
	if(confirm('Do you wish to remove schedule item '+desc+' from this service?')) {
    	var oUpdater = new Ajax.Updater({ success:'divSvcSchedule' }, '/ajDspDefSchedule.php', { 
    		method: "get",
    		parameters: { act: "del", sid: id }
    	});
	}
}
window.onload=init;
</script>

<style>
div.autocomplete {
	border: 1px solid #999;
	background-color: #fff;
	max-height:200px;
	overflow-y:scroll;
}
div.autocomplete ul {
	list-style: none;
	margin:0;
	padding:0;
}
div.autocomplete li { 
	padding: 2px 3px;
}
div.autocomplete strong { 
	font-weight: bold;
	text-decoration: underline;
}
div.autocomplete li.selected { 
	color: #fff;
	background-color: #8c1000;
	cursor:pointer;
}
</style>

<?php

$hlpID = 0;
$title = "Site Configuration";
include("header.php");


/* Retrieve Site Configuration */
$q = "SELECT * FROM siteconfig LEFT JOIN members on svcContact=memberID LIMIT 1";
$resConfig = $db->query($q);
if($dbConfig=mysqli_fetch_array($resConfig)) {
	$svcContact = $dbConfig["svcContact"];
	$svcDescription = $dbConfig["svcDescription"];
	$svcTime = substr($dbConfig["svcTime"],0,5);
	$svcPracticeOffset = $dbConfig["svcPracticeOffset"];
	$svcPracticeTime = substr($dbConfig["svcPracticeTime"],0,5);
	$availStatus = $dbConfig["availStatus"];
	$contactName = $dbConfig["mbrFirstName"]." ".$dbConfig["mbrLastName"];
	$soundRole = $dbConfig["soundRole"];
} else {
	$svcContact = "";
	$svcDescription = "";
	$svcTime = "";
	$svcPracticeOffset = "";
	$svcPracticeTime = "";
	$availStatus = "";
	$contactName = "";
	$soundRole = 0;
}



echo "<br /><form style='margin:0px;' name='frmConfig' method='post' action='editSiteConfig.php'>\n";
echo "<table style='margins:10px;' border='0' align='center'>\n";
echo "	<tr bgcolor='#ebebeb'>\n";
echo "		<td><b>Default Service Contact:</b></td>\n";
echo "		<td><input title=\"Member Name - List will pop up \nafter typing in several characters\" name=\"selMember\" type=\"text\" id=\"selMember\" size=\"45\" maxlength=\"100\" autocomplete=\"off\" onChange=\"chgMember();\" value=\"$contactName\" />\n";
echo "			<input name=\"svcContact\" type=\"hidden\" id=\"svcContact\" value='$svcContact'>\n";
echo "			<div id=\"memberBox\" class=\"autocomplete\" style=\"display:none\">&nbsp;</div>\n";
echo "		</td>\n";
echo "		<td rowspan='5' style='border-left:1px solid #000000'>&nbsp;<b>Used for new services</b></td>\n";
echo "	</tr>\n";
echo "	<tr bgcolor='#ebebeb'>\n";
echo "		<td><b>Default Service Description:</b>&nbsp;</td>\n";
echo "		<td><input name=\"svcDescription\" type=\"text\" id=\"svcDescription\" size=\"40\" maxlength=\"100\" value='$svcDescription' /></td>\n";
echo "	</tr>\n";
echo "	<tr bgcolor='#ebebeb'>\n";
echo "		<td><b>Default Service Time:</b></td>\n";
echo "		<td><input name=\"svcTime\" type=\"text\" id=\"svcTime\" size=\"7\" maxlength=\"5\" value='$svcTime' />&nbsp; Format - hh:mm</td>\n";
echo "	</tr>\n";
echo "	<tr bgcolor='#ebebeb'>\n";
echo "		<td><b>Default Practice Offset:</b></td>\n";
echo "		<td><input name=\"svcPracticeOffset\" type=\"text\" id=\"svcPracticeOffset\" size=\"4\" maxlength=\"3\" value='$svcPracticeOffset' />&nbsp;Days prior to service (must be <= 0)</td>\n";
echo "	</tr>\n";
echo "	<tr bgcolor='#ebebeb'>\n";
echo "		<td><b>Default Practice Time:</b></td>\n";
echo "		<td><input name=\"svcPracticeTime\" type=\"text\" id=\"svcPracticeTime\" size=\"7\" maxlength=\"5\" value='$svcPracticeTime' />&nbsp; Format - hh:mm (00:00 = None)</td>\n";
echo "	</tr>\n";

// Service Schedule
echo "	<tr>\n";
echo "		<td colspan='3'>\n";
echo "<b class=\"b1h\"></b><b class=\"b2h\"></b><b class=\"b3h\"></b><b class=\"b4h\"></b>\n";
echo "	<div class=\"headh\">\n";
echo "		<h4>Pratice Schedule Defaults</h4>\n";
echo "	</div>\n";
echo "	<div class=\"contenth\"><div>\n";


// Make changed to  ajDspDefSchedule.php to duplicate this code
include ("incDspDefSchedule.php");

// this includes the Ajax Div ..	
echo "<div id='divSvcSchedule'>\n".$schDesc."</div>\n";

if($isAdmin) {
	echo "	<table><tr><td height='30'>\n";
	echo "		<span id='btnLink'><a id='hsEditTeam' href='editDefSchedule.php?act=add' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 450, contentID: 'divAddSch', headingText: 'Add Schedule Item' } )\">Add Schedule Item</a></span>\n";
	echo "	</td></tr></table>\n";
}
echo "</div></div><b class=\"b4bh\"></b><b class=\"b3bh\"></b><b class=\"b2bh\"></b><b class=\"b1h\"></b>\n";
echo "		</td>\n";
echo "	</tr>\n";

echo "	<tr>\n";
echo "		<td colspan='3'><hr /></td>\n";
echo "	</tr>\n";
echo "<tr>\n";
echo "	<td><b>Role: </b></td>\n";


echo "	<td colspan='2'><select id=\"soundRole\" name=\"soundRole\">\n";
/* Retrieve role list */
$q = "SELECT roleID, roleDescription FROM roles INNER JOIN roletypes ON roles.typeID = roletypes.typeID ORDER BY typeSort, roleDescription";
$resRole = $db->query($q);
while($dbRole=mysqli_fetch_array($resRole)) {
	$sel = $soundRole==$dbRole["roleID"]?" selected":"";
	echo "		<option value=\"".$dbRole["roleID"]."\"$sel>".$dbRole["roleDescription"]."</option>\n";
}
echo "	</select>&nbsp;(allow these members to update equipment notes on service team)</td>\n";
echo "</tr>\n";
echo "	<tr>\n";
echo "		<td colspan='3'><hr /></td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td><b>Availability Schedule:</b></td>\n";
$availO = $availStatus=="O"?" checked":"";
$availC = $availStatus=="C"?" checked":"";
echo "		<td><input name=\"availStatus\" type=\"radio\" value='O'$availO />&nbsp;Open&nbsp;&nbsp;&nbsp;<input name=\"availStatus\" type=\"radio\" value='C'$availC />&nbsp;Closed</td>\n";
echo "		<td>&nbsp;</td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td colspan='3'><hr /></td>\n";
echo "	</tr>\n";
echo "	<tr><td colspan='3' align='center'><input type='submit' name='save' value='Save' />&nbsp;<input type='button' name='cancel' value='Cancel' onClick='document.location=\"index.php\"' /></td></tr>\n";
echo "</table>\n";
echo "</form>\n";
echo "</body>\n</html>\n";
?>