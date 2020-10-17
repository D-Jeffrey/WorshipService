<?php
/*******************************************************************
 * editService.php
 * Update Service information
 *******************************************************************/
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

$session = md5(uniqid(rand(), true));

require('lr/config.php');
require($baseDir.'/lr/functions.php'); 
//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Users) != "yes") { 
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

$isAdmin = allow_access(Administrators) === "yes";

$actDesc = $isAdmin?"Update":"Display";

include ('fnNicetime.php');

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add("$actDesc Service", $_SERVER['REQUEST_URI'], 2);

$serviceID = isset($_REQUEST["id"])? $_REQUEST["id"]: "";
$action = isset($_REQUEST["action"])?$_REQUEST["action"]: "";
$hdnContact = "";
// Only administrators allowed to add services
if (allow_access(Administrators) != "yes" && $action=="add") { 
	echo "<form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form>\n";
	echo "<script>document.frmLogin.submit();</script>\n";
	exit;
}

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Save new service
if(isset($_POST["save"])) {
	$cancelPractice = 0;
	// TODO fix up blank date and time in Service Data
	// TODO Fix up database to convert svcPratice 0000-00-00 to NULL
	if($_POST["save"]=="Next>") {
		$sql = "INSERT INTO services VALUES(0,'".$_POST["svcDATE"].":00','".$_POST["svcPDATE"].":00','".htmlentities($_POST["svcDescription"],ENT_QUOTES)."','".htmlentities($_POST["svcNotes"],ENT_QUOTES)."','".htmlentities($_POST["svcPNotes"],ENT_QUOTES)."',".$_POST["scID"].",$cancelPractice)";
		$resMbr = $db->query($sql);
		$serviceID = $db->insert_id;
		logit(2, __FILE__ . ":" .  __LINE__ . " Q: ". $sql . " ID:" . $serviceID ." E:". $db->error);
		$action = "edit";
		$_POST["save"]=="Save";
	} else {
			$sp = ",svcPractice='" . $_POST["svcPDATE"] . ":00'"; 
			
		$sql = "UPDATE services SET svcDateTime='".$_POST["svcDATE"].":00',". $sp . "svcDescription='".htmlentities($_POST["svcDescription"],ENT_QUOTES)."',svcNotes='".htmlentities($_POST["svcNotes"],ENT_QUOTES)."',svcPNotes='".htmlentities($_POST["svcPNotes"],ENT_QUOTES)."',svcContact=".$_POST["scID"].",cancelPractice=$cancelPractice WHERE serviceID=$serviceID";
		$resMbr = $db->query($sql);
		logit(2, __FILE__ . ":" .  __LINE__ . " Q: ". $sql . " E:". $db->error);
		header("Location: ".$_POST["rtnVal"]);
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle." - $actDesc"; ?> Service</title>
<?php
if($action!="add") {
?>
<link href="<?php echo $baseFolder; ?>scripts/lightloader/upload.css" type="text/css" rel="stylesheet"/>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/overlibmws/overlibmws.js"></script>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/overlibmws/overlibmws_exclusive.js"></script>
<script type="text/javascript" src="scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript" src="scripts/scriptaculous/src/scriptaculous.js"></script>
<script type="text/javascript">

var saveForm = false;

<?php if($isAdmin) { ?>
function startContact() {
	new Ajax.Autocompleter('selMember', 'contactBox', 'acGetMembers.php',{tokens:[','],minChars:2,afterUpdateElement:setContactID});
}

function setContactID(text,li) {
	var liParts = li.id.split(";");
	if(li.id && liParts[0] > 0) {
		document.frmService.scID.value=liParts[0];
	} else {
		document.frmService.scID.value=0;
	}
}

function delSong(id1,id2,name) {
	if(confirm('Do you wish to remove '+name+' from this service?')) {
    	var oUpdater = new Ajax.Updater({ success:'divSvcOrder' }, '/ajDspSvcOrder.php', { 
    		method: "get",
    		parameters: { act:"del", id: <?php echo $serviceID; ?>, oid: id2 }
    	});
	}
}

function setMbrButtonRole() {
	htmlData('/scripts/mbrSelect.php', 'id='+document.frmService.newRole.options[document.frmService.newRole.selectedIndex].value);
	document.frmService.addMbr.disabled=true;
}

function setMbrButtonMbr() {
	if(document.frmService.newMbr.selectedIndex==0 || document.frmService.newRole.selectedIndex==0) {
		document.frmService.addMbr.disabled=true;
	} else {
		document.frmService.addMbr.disabled=false;
	}
}

function delMember(id,role,name,chg) {
	if(chg==1) {
		alert("Cannot remove. Change Request currently outstanding for this member.");
	} else {
		if(confirm('Do you wish to remove '+name+' from this service?')) {
			var oUpdater = new Ajax.Updater({ success:'divTeam' }, '/ajDspTeam.php', { 
				method: "get",
				parameters: { act:"del", id: <?php echo $serviceID; ?>, mbr: id, rid: role }
			});
		}
	}
}

function delSchedule(id,desc) {
	if(confirm('Do you wish to remove schedule item '+desc+' from this service?')) {
    	var oUpdater = new Ajax.Updater({ success:'divSvcSchedule' }, '/ajDspSvcSchedule.php', { 
    		method: "get",
    		parameters: { act: "del", id: <?php echo $serviceID; ?>, sid: id }
    	});
	}
}

function delResource(id,name) {
	if(confirm('Do you wish to remove '+name+' from this service?')) {
    	var oUpdater = new Ajax.Updater({ success:'divSvcResource' }, '/ajDspSvcResource.php', { 
    		method: "get",
    		parameters: { act: "del", id: <?php echo $serviceID; ?>, rsc: id }
    	});
	}
}
<?php } ?>

function dspTeam(svcid) {
	var oUpdater = new Ajax.Updater({ success:'divTeam' }, '/ajDspTeam.php', { 
		method: "get",
		parameters: { 	id: svcid }
	});
}

function dspSchedule() {
	var oUpdater = new Ajax.Updater({ success:'divSvcSchedule' }, '/ajDspSvcSchedule.php', { 
		method: "get",
		parameters: { 	id: <?php echo $serviceID; ?> }
	});
}

function dspOrder() {
	var oUpdater = new Ajax.Updater({ success:'divSvcOrder' }, '/ajDspSvcOrder.php', { 
		method: "get",
		parameters: { 	id: <?php echo $serviceID; ?> }
	});
}

function dspResource(add) {
	if(add) alert("Resource file has been uploaded.");
	var oUpdater = new Ajax.Updater({ success:'divSvcResource' }, '/ajDspSvcResource.php', { 
		method: "get",
		parameters: { 	id: <?php echo $serviceID; ?> }
	});
}

function atmOK(response) {
}

function delRequest(sid,rid,desc) {
	if(confirm('Do you wish to delete the change request for: '+desc+'?')) {
		document.location='ajReqChange.php?a=del&sid='+sid+'&rid='+rid;
	}
	return false;
}
function accRequest(rid,rds,mbr,date) {
	if(confirm('Do you wish to accept the request from: '+mbr+'\nTo take on the role of: '+rds+'\nFor the service on: '+date+'?')) {
		document.location='ajReqChange.php?a=acc&rid='+rid;
	}
	return false;
}

function delSwap(sid,rid,desc) {
	if(confirm('Do you wish to delete the change request for: '+desc+'?')) {
		document.location='ajReqSwap.php?a=del&sid='+sid+'&rid='+rid;
	}
	return false;
}

function emailService(id) {
	window.location='emailService.php?id='+id;
	return false;
}

function form_is_modified(oForm) {
    var el, opt, hasDefault, i = 0, j;
    if(!saveForm) {
        while (el = oForm.elements[i++]) {
            switch (el.type) {
                case 'text' :
                case 'textarea' :
                case 'hidden' :
                    if (!/^\s*$/.test(el.value) && el.value != el.defaultValue) return true;
                    break;
                case 'checkbox' :
                case 'radio' :
                    if (el.checked != el.defaultChecked) return true;
                    break;
                case 'select-one' :
                case 'select-multiple' :
                    j = 0, hasDefault = false;
                    while (opt = el.options[j++])
                    if (opt.defaultSelected) hasDefault = true;
                    j = hasDefault ? 0 : 1;
                    while (opt = el.options[j++]) 
                    if (opt.selected != opt.defaultSelected) return true;
                    break;
            }
        }
    }
    return false;
}


window.onbeforeunload = function() {
    if (form_is_modified(document.frmService)) {
        return "You have some changes that have not been saved.";
    }
}

</script>
<style>
.teamTable td,img {
	margin:0px;
	padding:0px;
}
</style>

<?php if($isAdmin) { ?>
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
}
}

// Retrieve site configuration values
$q = "SELECT svcContact,svcDescription,svcTime,svcPracticeOffset,svcPracticeTime,concat(mbrFirstName,' ',mbrLastName) as mbrName,soundRole FROM siteconfig INNER JOIN members ON svcContact=memberID LIMIT 1";
$resCfg = $db->query($q);
$dbCfg=mysqli_fetch_array($resCfg);

// Is this user part of the sound crew?
$isSound = strpos(",".$_SESSION['roles'].",",$dbCfg["soundRole"])!==false;

if($action!="add") {
	if (intval($serviceID) == 0) {
		die("There was a problem with the previous page, please go BACK and change the values you entered.</Body></HTML>");
			
	}
	/* Retrieve Service for specified id */
	$sql = "SELECT *, date_format(svcPractice, '%W %M %D') as svcPDATE, date_format(svcPractice, '%H:%i') as svcPTIME, date_format(svcDateTime, '%W %M %D') as svcDATE, date_format(svcDateTime, '%H:%i') as svcTIME FROM services LEFT JOIN members ON svcContact=memberID WHERE serviceID=$serviceID";
	$resSVC = $db->query($sql);
	$dbSvc=mysqli_fetch_array($resSVC);
	$svcDescription = $dbSvc["svcDescription"];
	$svcDateTime = $dbSvc["svcDateTime"];
	$svcDate = $dbSvc["svcDATE"];
	$svcTime = $dbSvc["svcTIME"];
	$svcNotes = $dbSvc["svcNotes"];
	$svcPractice = $dbSvc["svcPractice"];
	$cancelPractice = $dbSvc["cancelPractice"];
	$svcPNotes = $dbSvc["svcPNotes"];
	$selMember = $dbSvc["mbrFirstName"]." ".$dbSvc["mbrLastName"];
	$scID = $dbSvc["svcContact"];
} else {
	$selMember = $dbCfg["mbrName"];
	$scID = $dbCfg["svcContact"];
	$svcDescription = $dbCfg["svcDescription"];
	$svcDateTime = $_REQUEST["sd"]." ".$dbCfg["svcTime"];
	$svcNotes = "";
	$svcPractice = strftime("%G-%m-%d ".$dbCfg["svcPracticeTime"],strtotime($_REQUEST["sd"]." ".$dbCfg["svcPracticeOffset"]." days"));
	$cancelPractice = 0;
	$svcPNotes = "";
}

$intYear = isset($_REQUEST["y"])?$_REQUEST["y"]:0;
$intMonth = isset($_REQUEST["m"])?$_REQUEST["m"]:0;
$intMember = isset($_REQUEST["mbr"])?$_REQUEST["mbr"]:"*";

$hlpID = 16;
$actText = $action=="add"?"Add New":$actDesc;
$title = "$actText Service Details";
include("header.php");


echo "<form style='margin:0px;' name='frmService' method='post' action='editService.php?id=$serviceID&action=edit' onSubmit='saveForm=true;'>\n";

echo "<input name=\"subact\" type=\"hidden\">\n";
echo "<input name=\"subkey1\" type=\"hidden\">\n";
echo "<input name=\"subkey2\" type=\"hidden\">\n";



if($intYear==0) {
    $rtnVal = "/index.php";
} else {
    $rtnVal = "/calendar.php?y=$intYear&m=$intMonth&mbr=*";
}
echo "<input name=\"rtnVal\" type=\"hidden\" value=\"$rtnVal\">\n";
echo "<input name=\"scID\" type=\"hidden\" value=\"$scID\">\n";

if($action!="add") {
	if($isAdmin) { echo "<span style='float:right;font-size:12pt;font-weight:bold'><a href='#' onClick='emailService($serviceID);'><img src='/images/icon_email.gif' width='15' height='12' alt='Email' border='0' />&nbsp;Email Service Order</a>&nbsp;&nbsp;</span>"; }
	echo "<h2 align='center'>$svcDescription on $svcDate - ".nicetime($svcTime)."</h2>\n";
}

$order = array("\r\n","\n", "\r");
$replace = "<br \>";

// If not an administrator, disable input fields
$disFields = $isAdmin?"":" disabled";
echo "<table style='width:100%' align='center'>\n";
echo "<tr valign='top'><td width='50%' style='padding:5px;'>\n";
echo "<b class=\"b1h\"></b><b class=\"b2h\"></b><b class=\"b3h\"></b><b class=\"b4h\"></b>\n";
echo "	<div class=\"headh\">\n";
echo "		<h4>Service Information</h4>\n";
echo "	</div>\n";
echo "	<div class=\"contenth\"><div>\n";
echo "<table>\n";
echo "<tr><td>$hdnContact<b>Service Description:</b>&nbsp;</td><td><input$disFields onChange=\"setDirty();\" type=\"Text\" id=\"svcDescription\" maxlength=\"100\" size=\"40\" name=\"svcDescription\" value=\"$svcDescription\">&nbsp;&nbsp;</td></tr>";
if($action!="add") {
	/* Service Contact */
	echo "<tr><td><b>Service Contact:</b></td><td><input$disFields onFocus=\"startContact();\" type=\"Text\" id=\"selMember\" maxlength=\"100\" size=\"40\" name=\"selMember\" value=\"$selMember\" autocomplete=\"off\" onChange=\"document.frmService.scID.value=0;\" ><div id=\"contactBox\" class=\"autocomplete\" style=\"display:none\">&nbsp;</div></td></tr>";
}
echo "<tr><td><b>Service Time:</b>&nbsp;</td><td><input$disFields type=\"Text\" id=\"svcDATE\" maxlength=\"25\" size=\"25\" name=\"svcDATE\" value=\"".substr($svcDateTime,0,16)."\">";
if($isAdmin) {
	echo "<a href=\"javascript:void(0)\" onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.frmService.svcDATE);return false;\" ><img align=\"top\" src=\"{$baseFolder}scripts/popcal/calbtn.gif\" alt=\"Pick a date\"></a>";
} else {
	echo "&nbsp;";
}
echo "</td><tr>";
echo "<tr><td colspan='2'><b>Service Notes:</b><br />";
if($isAdmin) {
	echo "<textarea name='svcNotes' rows='2' cols='40'>$svcNotes</textarea>";
} else {
	$svcNotes = str_replace($order, $replace, $svcNotes);
	echo "<div style='margin:0px;border:1px solid gray;width:100%'>$svcNotes</div>";
}
echo "</td></tr>";
echo "<tr><td><b>Practice Time:</b>&nbsp;</td><td><input$disFields type=\"Text\" id=\"svcPDATE\" maxlength=\"25\" size=\"25\" name=\"svcPDATE\" value=\"".substr($svcPractice,0,16)."\">";
if($isAdmin) {
	echo "<a href=\"javascript:void(0)\" onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.frmService.svcPDATE);return false;\" ><img align=\"top\" src=\"{$baseFolder}scripts/popcal/calbtn.gif\" alt=\"Pick a date\"></a>";
} else {
	echo "&nbsp;";
}
echo "</td></tr>";
echo "<tr><td colspan='2'><b>Practice Notes:</b><br />";
if($isAdmin) {
echo "<textarea name='svcPNotes' rows='2' cols='40'>$svcPNotes</textarea>";
} else {
	echo "<div style='margin:0px;border:1px solid gray;width:100%'>$svcPNotes</div>";
}
echo "</td></tr>";
echo "</table>\n";
echo "</div></div><b class=\"b4bh\"></b><b class=\"b3bh\"></b><b class=\"b2bh\"></b><b class=\"b1h\"></b>\n";
echo "</td>\n";

// Service Schedule
echo "<td width='50%' style='padding:5px;'>\n";
echo "<b class=\"b1h\"></b><b class=\"b2h\"></b><b class=\"b3h\"></b><b class=\"b4h\"></b>\n";
echo "	<div class=\"headh\">\n";
echo "		<h4>Service Schedule</h4>\n";
echo "	</div>\n";
echo "	<div class=\"contenth\"><div>\n";

/* Retrieve Service Schedule */
$q = "SELECT *,date_format(schDateTime,'%b %D, %Y') AS schDATE,date_format(schDateTime,'%l:%i%p') AS schTIME,date_format(schDateTime+INTERVAL schDuration*60 MINUTE,'%l:%i%p') AS schENDTIME FROM serviceschedule LEFT JOIN roletypes ON CONCAT(',',schCategories,',') LIKE CONCAT('%,',typeID,',%') WHERE serviceID=$serviceID ORDER BY schType,schDateTime,typeSort";
$resSch = $db->query($q);
$schDesc = "<div id='divSvcSchedule'><table width='100%'>\n";
$i = 1;
$saveSchDATE = "";
$saveSchID = 0;
$schLines = "";
$schCategories = "";
$saveSchType = "";
$chgReq ="";
$swapReq ="";

if($resSch && mysqli_num_rows($resSch)>0) {
	while($dbSch=mysqli_fetch_array($resSch)) {
		if($saveSchDATE=="") {
			$saveSchDATE = $dbSch["schDATE"];
			$saveSchID = $dbSch["scheduleID"];
		}
		if($saveSchID!=$dbSch["scheduleID"]) {
			if($isAdmin) {
				$schAdmLink = "<td width='40' nowrap>";
				$schAdmLink .= "<a href='#' onClick=\"delSchedule($saveSchID,'$schDATE - $schTIME');\" title='Remove Schedule Item from Service'><img src='images/icon_delete.gif'></a>&nbsp;";
				$schAdmLink .= "<a id='hsEditTeam' href='editSvcSchedule.php?id=$serviceID&act=edit&sid=$saveSchID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 450, contentID: 'divAddSong', headingText: 'Update Service Schedule' } )\"><img src='images/edit.png'></a>";
				$schAdmLink .= "</td>\n";
			} else {
				$schAdmLink = "";
			}
			$schLines .= "<tr>$schAdmLink<td nowrap$schStyle>$schTIME&nbsp;</td><td$schStyle>$schCategories</td><td$schStyle>$schDescription</td></tr>";
			$schCategories = "";
			$saveSchID = $dbSch["scheduleID"];
		}
		if($saveSchDATE!=$dbSch["schDATE"] || ($saveSchType!=$dbSch["schType"] && $saveSchType!="")) {
			$schDesc .= "<tr><td nowrap>$saveSchDATE</td><td><table>$schLines</table></td></tr>";
			if($saveSchDATE!=$dbSch["schDATE"]) {
				$schDesc .= "<tr><td colspan='2'><hr style='margin:0px;' /></td></tr>";
			}
			$schLines = "";
			$saveSchDATE = $dbSch["schDATE"];
		}
		if($saveSchType!=$dbSch["schType"]) {
			$schType = $dbSch["schType"]=="P"?"Practice":"Service";
			$schDesc .= "<tr><td colspan='2' style='text-align:center;background-color:#cccccc;color:#000000'><b>$schType</b></td></tr>";
			$saveSchType = $dbSch["schType"];
		}
		$schDATE = $dbSch["schDATE"];
		if($dbSch["schCancel"]) {
			$schTIME = $dbSch["schTIME"]."-Cancelled";
			$schStyle = " style='color:#cc0000'";
		} else {
			$schTIME = $dbSch["schTIME"]."-".$dbSch["schENDTIME"];
			$schStyle = "";
		}
		if($dbSch["schCategories"]=="*") {
			$schCategories = "Everyone";
		} else {
			$schCategories .= $schCategories!=""?", ":"";
			$schCategories .= $dbSch["typeDescription"];
		}
		$schDescription = $dbSch["schDescription"]!=""?"(".$dbSch["schDescription"].")":"";
	}

	if($isAdmin) {
		$schAdmLink = "<td width='40' nowrap>";
		$schAdmLink .= "<a href='#' onClick=\"delSchedule($saveSchID,'$schDATE - $schTIME');\" title='Remove Schedule Item from Service'><img src='images/icon_delete.gif'></a>&nbsp;";
		$schAdmLink .= "<a id='hsEditTeam' href='editSvcSchedule.php?id=$serviceID&act=edit&sid=$saveSchID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 450, contentID: 'divAddSong', headingText: 'Update Service Schedule' } )\"><img src='images/edit.png'></a>";
		$schAdmLink .= "</td>\n";
	} else {
		$schAdmLink = "";
	}
	$schLines .= "<tr>$schAdmLink<td nowrap>$schTIME&nbsp;</td><td>$schCategories</td><td>$schDescription</td></tr>";
	$schDesc .= "<tr><td nowrap>$saveSchDATE</td><td><table>$schLines</table></td></tr>";
}	
echo $schDesc."</table></div>\n";

if($isAdmin) {
	echo "	<table><tr><td height='30'>\n";
	echo "		<span id='btnLink'><a id='hsEditTeam' href='editSvcSchedule.php?id=$serviceID&act=add' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 450, contentID: 'divAddSong', headingText: 'Add Schedule Item' } )\">Add Schedule Item</a></span>\n";
	echo "	</td></tr></table>\n";
}
echo "</div></div><b class=\"b4bh\"></b><b class=\"b3bh\"></b><b class=\"b2bh\"></b><b class=\"b1h\"></b>\n";
echo "</td></tr></table>\n";

$saveTxt = $action=="add"?"Next>":"Save";

if($isAdmin) {
	echo "<input name=\"save\" id=\"save\" type=\"submit\" value=\"$saveTxt\" class=\"button\">&nbsp;&nbsp;";
	echo "<input name=\"back\" type=\"button\" value=\"Cancel\" onClick=\"document.location='$rtnVal';\" class=\"button\">\n";
}

if($action!="add") {
	echo "<table width='100%'><tr><td valign='top' width='50%'>\n";
	echo "<table width='100%'>\n";
	echo "<tr valign='top'><td style='padding:5px;'>\n";
	echo "<b class=\"b1h\"></b><b class=\"b2h\"></b><b class=\"b3h\"></b><b class=\"b4h\"></b>\n";
	echo "	<div class=\"headh\">\n";
	echo "		<h4>Worship Team</h4>\n";
	echo "	</div>\n";
	echo "	<div class=\"contenth\"><div>\n";

	/* Retrieve team members */
	$q = "SELECT serviceteam.memberID AS mbrID, serviceteam.roleID as rID,roleDescription,roleIcon,concat(mbrFirstName,' ',mbrLastName) as mbrName,mbrEmail1,mbrEmail2,mbrHPhone,mbrWPhone,mbrCPhone,soundNotes,roles.typeID as rType FROM serviceteam INNER JOIN members ON serviceteam.memberID = members.memberID INNER JOIN roles on serviceteam.roleID = roles.roleID INNER JOIN roletypes on roles.typeID = roletypes.typeID WHERE serviceID = $serviceID ORDER BY typeSort, roleDescription";
	$resTeam = $db->query($q);
	$teamDesc = "<div id='divTeam'><table class='teamTable' width='100%'>";
	$i = 1;
  $roleType  = "";
  
	while($dbteam=mysqli_fetch_array($resTeam)) {
		$mLink = "'adminCommunications.php?id=".$dbteam["mbrID"]."&dst=I' target='_self'";

		// Build Hover info table
		$ovrTxt = "<a href=$mLink onmouseover='return overlib(\"<table><tr><th align=left>Member:</th><td>".$dbteam["mbrName"]."</td></tr>";
		$ovrTxt .= $dbteam["mbrHPhone"]==""?"":"<tr><th align=left>Home Phone:</th><td>".$dbteam["mbrHPhone"]."</td></tr>";
		$ovrTxt .= $dbteam["mbrWPhone"]==""?"":"<tr><th align=left>Work Phone:</th><td>".$dbteam["mbrWPhone"]."</td></tr>";
		$ovrTxt .= $dbteam["mbrCPhone"]==""?"":"<tr><th align=left>Cell Phone:</th><td>".$dbteam["mbrCPhone"]."</td></tr>";
		$ovrTxt .= $dbteam["mbrEmail1"]==""?"":"<tr><th align=left>Email:</th><td>".$dbteam["mbrEmail1"]."</td></tr>";
		$ovrTxt .= $dbteam["mbrEmail2"]==""?"":"<tr><th align=left>Email2:</th><td>".$dbteam["mbrEmail2"]."</td></tr>";
		$ovrTxt .= "</table>\", WIDTH, 500, TEXTPADDING, 10, TEXTFONTCLASS, \"oltxt\", FGCOLOR, \"#cceeff\")' onmouseout='return nd();'>";
		$roleSep = $dbteam["rType"]!=$roleType && $roleType>0?" style='border-top:1px solid gray'":"";
		$roleType = $dbteam["rType"];
		$teamDesc .= "	<tr$roleSep><td nowrap>";

		$swapDesc = "";
		$chgDesc = "";
		$chgReq = array("",false);
		$swapReq= array("",false);
		// Check for change request status
		if(substr($svcDateTime,0,10)>date("Y-m-d") && $dbteam["mbrID"]>0) {
			// Display change request options
			$chgReq = dspChgStatus($dbteam["mbrID"],$dbteam["mbrName"],$dbteam["rID"],$dbteam["roleDescription"],$dbSvc["svcDATE"]." at ".nicetime($dbSvc["svcTIME"]));
			$chgDesc = $chgReq[0];
		} 
		// Check for swap request status
		if (!$chgReq[1] && substr($svcDateTime,0,10)>date("Y-m-d") && $dbteam["mbrID"]>0 ) {
			// Display swap request options
			$swapReq = dspSwapStatus($dbteam["mbrID"],$dbteam["mbrName"],$dbteam["rID"],$dbteam["roleDescription"],$dbSvc["svcDATE"]." at ".nicetime($dbSvc["svcTIME"]));
			$swapDesc = $swapReq[0];
		}
		if (!$chgReq[1] && !$swapReq[1]) {
			$reqChg = "$chgDesc&nbsp;$swapDesc";
		} else {
			$reqChg = $swapReq[1]?"$swapDesc":($chgReq[1]?"$chgDesc":"");
		}
		$delChgExists = $chgReq[1] || $swapReq[1]?"1":"0";
		$teamDesc .= $isAdmin?"<a href='#' onClick=\"delMember(".$dbteam["mbrID"].",".$dbteam["rID"].",'".$dbteam["mbrName"]." (".$dbteam["roleDescription"].")',$delChgExists);\" title='Remove Member from Service'><img src='images/icon_delete.gif'></a>&nbsp;":"";
		$teamDesc .= ($isAdmin||$isSound)?"<a id='hsEditTeam' href='ajEditTeam.php?act=edit&id=$serviceID&mbr=".$dbteam["mbrID"]."&sdte=".substr($svcDateTime,0,10)."&rid=".$dbteam["rID"]."' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', contentID: 'divEditTeam', headingText: 'Add Team Member', width: 430 } )\"><img src='images/edit.png'></a>&nbsp;":"";
		$teamDesc .= "<img src='".$dbteam["roleIcon"]."' alt='".$dbteam["roleDescription"]."'>&nbsp;</td><td><b>".$dbteam["roleDescription"]."</b>&nbsp;</td><td>$ovrTxt".$dbteam["mbrName"]."</a>";
		$sndNotes = $dbteam["soundNotes"]==""?"":"&nbsp;(".$dbteam["soundNotes"].")";
		$teamDesc .= "$sndNotes</td>\n";
		$teamDesc .= "	<td>$reqChg</td></tr>\n";
		$i++;
	}

	echo $teamDesc."\n";
	echo "	<tr><td colspan='5' style='padding-top:8px;padding-bottom:8px;'>";
	
	if ($isAdmin) {
	
		echo "<span id='btnLink'><a id='hsEditTeam' href='ajEditTeam.php?act=add&sdte=".substr($svcDateTime,0,10)."
			&id=$serviceID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', contentID: 'divEditTeam', headingText: 'Add Team Member', width: 430 } )\">Add Member</a></span>\n";
	}
	echo "	</td></tr>\n";
	echo "</table>\n";
	echo "</div></div></div><b class=\"b4bh\"></b><b class=\"b3bh\"></b><b class=\"b2bh\"></b><b class=\"b1h\"></b></td></tr></table></td>\n";

	echo "<td valign='top' width='50%'>\n";
	echo "<table width='100%'>\n";
	echo "<tr valign='top'><td style='padding:5px;'>\n";
	echo "<b class=\"b1h\"></b><b class=\"b2h\"></b><b class=\"b3h\"></b><b class=\"b4h\"></b>\n";
	echo "	<div class=\"headh\">\n";
	echo "		<h4><div style='float:right'><a href='createSongSheet.php?sid=$serviceID&c=1' target='_blank'>Print Songbook</a></div>Worship Order</h4>\n";
	echo "	</div>\n";
	echo "	<div class=\"contenth\"><div>\n";

	/* Retrieve Order Details */
	$q = "SELECT orderID,orderType,songKey,iTunesLink, songNumber, songName,orderDescription,orderDetails, serviceorder.songLink as sLink, songText, serviceorder.songID as sID FROM serviceorder LEFT JOIN songs ON serviceorder.songID = songs.songID WHERE serviceID = $serviceID ORDER BY songNumber";
	$resSong = $db->query($q);
	$songDesc = "<div id='divSvcOrder'><table>\n";
	$i = 1;
	while($dbsong=mysqli_fetch_array($resSong)) {
		if($dbsong["orderType"]=="S") {
			$sLink = $dbsong["songText"]==""?"&nbsp;":"<a href='dspSvcSong.php?id=".$dbsong["sID"]."&sid=$serviceID'>";
			$sLink2 = $dbsong["songText"]==""?"":"</a>";
			if($dbsong["sLink"]=="") {
				$sLinkPlay = "<span style='font-size:9pt;'>&nbsp;&nbsp;&nbsp;</span>";
			} else {
				// Fixed up any http links for youtube to https: youtube to avovd a security error
				$linkSrc = str_replace(array("/v/","http://www.youtube"),array("/embed/","https://www.youtube"),$dbsong["sLink"]);
				$sLinkPlay = "<a href='".$linkSrc."' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',  Height: 482, width: 600  } )\"><img src='images/play.png' border='0' alt='Play in new window'></a>";
				if (strpos(strtolower($linkSrc), "youtube.com/embed/")) {
				    preg_match("/\/embed\/([a-z0-9A-Z\-]+)/", $linkSrc, $matches);
					$linkSrcn = "https://www.youtube.com/watch?v=" . $matches[1] . "&feature=player_embedded";
				    $sLinkPlay = $sLinkPlay . "<a href='".$linkSrcn."' title='Play music video in new tab' Target='_blank' \"><img src='images/playr.png' border='0' alt='Play in new window'></a>";
				}
			}
			if($dbsong["iTunesLink"]=="") {
				$sLinkiTunes = "&nbsp;";
			} else {
				$sLinkiTunes = $dbsong["iTunesLink"];
				
				
			}
			$songDesc .= "	<tr>";
			if($isAdmin) {
				$songDesc .= "<td width='40'>";
				$songDesc .= "<a href='#' onClick=\"delSong(".$dbsong["sID"].",".$dbsong["orderID"].",'".addslashes($dbsong["songName"])."');\" title='Remove Song from Service'><img src='images/icon_delete.gif'></a>&nbsp;";
				$songDesc .= "<a id='hsEditTeam' href='editOrderSong.php?id=$serviceID&act=edit&oid=".$dbsong["orderID"]."' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 450, contentID: 'divAddSong', headingText: 'Update Song' } )\"><img src='images/edit.png'></a>";
				$songDesc .= "</td>\n";
			}
			$songDesc .= "		<td title='Song Key'><b>".$dbsong["songKey"]."</b></td>\n";
			$songDesc .= "		<td style='padding-left:3px'>$sLink".$dbsong["songName"]."$sLink2</td>\n";
			$songDesc .= "		<td>$sLinkPlay&nbsp;$sLinkiTunes</td>\n";
			$songDesc .= "	</tr>\n";
		} else {
			$songDesc .= "	<tr>";
			if($isAdmin) {
				$songDesc .= "<td width='40'>";
				$songDesc .= "<a href='#' onClick=\"delSong(".$dbsong["sID"].",".$dbsong["orderID"].",'".addslashes($dbsong["orderDescription"])."');\" title='Remove Item from Service'><img src='images/icon_delete.gif'></a>&nbsp;";
				$songDesc .= "<a id='hsEditTeam' href='editOrderText.php?id=$serviceID&act=edit&oid=".$dbsong["orderID"]."' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 692, contentID: 'divAddSong', headingText: 'Update Text' } )\"><img src='images/edit.png'></a>";
				$songDesc .= "</td>\n";
			}
			$songDesc .= "		<td>--</td>\n";
			if($dbsong["orderDetails"]=="") {
				$sLinkPlay = "";
				$sLinkPlay2 = "";
				$tdiv = "";
			} else {
				$sLinkPlay = "<a title='Display Details' href='' onclick=\"return hs.htmlExpand(this, { contentId: 'hsc".$dbsong["orderID"]."' } )\">";
				$sLinkPlay2 = "</a>";
				$tdiv = "<div class=\"highslide-html-content\" id=\"hsc".$dbsong["orderID"]."\" style=\"width: 480px\">";
				$tdiv .= "    <div width='100%' align='right'><a href=\"#\" onclick=\"hs.close(this)\">";
				$tdiv .= "        Close";
				$tdiv .= "    </a></div>";
				$tdiv .= "    <div style='background-color:#efefef;overflow:auto;border:2px inset;padding:3px' class=\"highslide-body\">";
				$tdiv .= "        ".nl2br($dbsong["orderDetails"]);
				$tdiv .= "    </div>";
				$tdiv .= "</div>";
			}
			$songDesc .= "		<td colspan='2'>$sLinkPlay".$dbsong["orderDescription"]."$sLinkPlay2$tdiv</td>\n";
			$songDesc .= "	</tr>\n";
		}
		$i++;
	}
	echo $songDesc."</table></div><table>\n";

	if($isAdmin) {
		echo "	<tr><td colspan='5' height='30'><span id='btnLink'><a id='hsEditTeam' href='editOrderSong.php?id=$serviceID&act=add' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 450, contentID: 'divAddSong', headingText: 'Add Song' } )\">Add Song</a></span>&nbsp;\n";
		echo "			<span id='btnLink'><a id='hsEditTeam' href='editOrderText.php?id=$serviceID&act=add&oid=".$dbsong["orderID"]."' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 692, contentID: 'divAddSong', headingText: 'Add Text' } )\">Add Text</a></span>&nbsp;&nbsp;\n";
		echo "			<span id='btnLink'><a href='ajUpdServiceOrder.php?id=$serviceID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', contentID: 'divEditOrder', headingText: 'Update Order' } )\">Update Order</a></span>\n";
		echo "	</td></tr>\n";
	}
	echo "</table>\n";
	echo "</div>\n";
	echo "</div></div><b class=\"b4bh\"></b><b class=\"b3bh\"></b><b class=\"b2bh\"></b><b class=\"b1h\"></b></td></tr></table>\n";


	echo "<br /><table width='100%'>\n";
	echo "<tr valign='top'><td style='padding:5px;'>\n";
	echo "<b class=\"b1h\"></b><b class=\"b2h\"></b><b class=\"b3h\"></b><b class=\"b4h\"></b>\n";
	echo "	<div class=\"headh\">\n";
	echo "		<h4>Resources</h4>\n";
	echo "	</div>\n";
	echo "	<div class=\"contenth\"><div>\n";

	/* Retrieve member Categories */
	$q = "SELECT typeID FROM roles WHERE CONCAT(',','".$_SESSION['roles']."',',') LIKE CONCAT('%,',roleID,',%') ORDER BY typeID";
	$resRsc = $db->query($q);
	$catArray = "";
	$oldCat = "";
	while($dbRsc=mysqli_fetch_array($resRsc)) {
		if($oldCat!=$dbRsc["typeID"]) {
			$catArray .= ",".$dbRsc["typeID"];
			$oldCat = $dbRsc["typeID"];
		}
	}
	$catArray .= ",";
	/* Retrieve Resources */
	$q = "SELECT * FROM serviceresources WHERE serviceID = $serviceID ORDER BY rscDescription";
	$resRsc = $db->query($q);
	$rscDesc = "<div id='divSvcResource'><table>\n";
	while($dbRsc=mysqli_fetch_array($resRsc)) {
		if(chkResourceAccess($dbRsc["rscCategories"],$catArray)) {
			$rscDesc .= "	<tr>";
			$editRscLink = "<a id='hsEditResource' href='editResource.php?id=$serviceID&rid=".$dbRsc["resourceID"]."&act=Edit' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 530, contentID: 'divAddSong', headingText: 'Edit Resource' } )\"><img src='images/edit.png'></a>";
			$rscDesc .= $isAdmin?"<td width='42'><a href='#' onClick=\"delResource(".$dbRsc["resourceID"].",'".addslashes($dbRsc["rscDescription"])."');\" title='Remove Resource from Service'><img src='images/icon_delete.gif'></a>&nbsp;$editRscLink</td>\n":"";
			$rscDesc .= "		<td><a href='/getResource.php?sid=$serviceID&amp;rid=".$dbRsc["resourceID"]."' target='_blank'>".$dbRsc["rscDescription"]."</a></td>\n";
			$rscDesc .= "	</tr>\n";
		}
	}
	echo $rscDesc;
	echo $isAdmin?"	<tr><td colspan='2' height='30'><span id='btnLink'><a id='hsEditResource' href='editResource.php?id=$serviceID&act=Add' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 530, contentID: 'divAddSong', headingText: 'Add Resource' } )\">Add Resource</a></span></td></tr>\n":"";

	echo "</table>\n";
	echo "</div>\n";
	echo "</div></div><b class=\"b4bh\"></b><b class=\"b3bh\"></b><b class=\"b2bh\"></b><b class=\"b1h\"></b></td></tr></table>\n";

    echo "</table>\n";
	echo "<input name=\"back\" type=\"button\" value=\"Done\" onClick=\"document.location='$rtnVal';\" class=\"button\">\n";
    

	$hdnContact = "";
} else {
	$hdnContact = "<input type=\"hidden\" id=\"selMember\" name=\"selMember\" value=\"$selMember\" />";
}


echo "</form>\n";

echo "<iframe width=188 height=166 name=\"gToday:datetime:/scripts/popcal/agenda.js:gfPop:/scripts/popcal/plugins_time.js\" id=\"gToday:datetime:/scripts/popcal/agenda.js:gfPop:/scripts/popcal/plugins_time.js\" src=\"/scripts/popcal/ipopeng.htm\" scrolling=\"no\" frameborder=\"0\" style=\"visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;\">\n";
echo "</iframe>\n";


// Edit Team Members
echo "<div id='divEditTeam' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";

// Edit Service Order
echo "<div id='divEditOrder' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";

// Add Song
echo "<div id='divAddSong' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";

echo "</body>\n</html>\n";


// Check to see if current member has access to this resource
function chkResourceAccess($cats,$mbrCat) {
	global $isAdmin;
	
	$aCat = explode(",",$cats);
	$aMbrCat = explode(",",$mbrCat);
	if($isAdmin) return true;
	if($cats == "*") return true;
	$catMatch = array_intersect($aMbrCat,$aCat);
	if(count($catMatch)>0) return true;
	return false;
}

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

	// Check originating values first
	$sql = "SELECT requestID,chgStatus,orgMbrID FROM svcchangereq WHERE reqType='S' AND chgStatus <> 'A' AND chgStatus <> 'R' AND ((orgMbrID=$mbrID AND serviceID=$serviceID) OR (newMbrID=$mbrID AND newSvcID=$serviceID)) AND roleID=$roleID";
	$resReq = $db->query($sql);
	if(mysqli_num_rows($resReq) > 0 && $resReq) {
		$dbReq=mysqli_fetch_array($resReq);
		$chgSts = $dbReq["chgStatus"];
		if($dbReq["orgMbrID"]==$_SESSION['user_id']) {
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