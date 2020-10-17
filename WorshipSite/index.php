<?php
/*******************************************************************
 * index.php
 * Display Member home page
 *******************************************************************/
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

require('lr/config.php');
require($baseDir . 'lr/functions.php'); 



//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
$isAdmin = (allow_access(Administrators)=="yes");
if (allow_access(Users) != "yes") { 
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form>";
?>
<?php
	echo "</body></html>\n";
	exit;
}

include ('fnNicetime.php');

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Home', $_SERVER['REQUEST_URI'], 0);



$memberID = $_SESSION["user_id"];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Member Home Page</title>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/overlibmws/overlibmws.js"></script>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/overlibmws/overlibmws_exclusive.js"></script>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/overlibmws/overlibmws_shadow.js"></script>
<script type="text/javascript" src="scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript" src="scripts/scriptaculous/src/scriptaculous.js"></script>
<script type="text/javascript" src="scripts/StarryWidget2/starsSM.js"></script>
<link rel="stylesheet" href="scripts/StarryWidget2/stars.css" type="text/css" />
<script type="text/javascript">
function dspTeam(svcid,mbrid) {
	document.location.reload();
}
</script>
<style>
td.ndxLeft {
	width:175px;
	padding:0px;
	font-size:8pt;
	border-right:2px ridge #69B5FB;
	background-color:#000000;
}
td.ndxMiddle {
	padding:0px;
	font-size:9pt;
	border-left:2px ridge;
}
td.ndxRight {
	width:175px;
	padding:0px;
	font-size:8pt;
	border-left:2px ridge #69B5FB;
	background-color:#000000;
}

.songRate {
	width:171px;
}
.songButton {
	font-size:8pt;
}
.songRate td {
	font-size:9pt;
	color:#ffffff;
	font-family: Arial, Helvetica, Verdana;
}
</style>
<?php
echo "\n<script>\n";
echo "function delRequest(sid,rid,desc) {\n";
echo "	if(confirm('Do you wish to delete the change request for: '+desc+'?')) {\n";
echo "		document.location='ajReqChange.php?a=del&ref=index.php&rid='+rid;\n";
echo "	}\n";
echo "	return false;\n";
echo "}\n";
echo "function accRequest(rid,rds,mbr,date) {\n";
echo "	if(confirm('Do you wish to accept the request from: '+mbr+'\\nTo take on the role of: '+rds+'\\nFor the service on: '+date+'?')) {\n";
echo "		document.location='ajReqChange.php?a=acc&ref=index.php&rid='+rid;\n";
echo "	}\n";
echo "	return false;\n";
echo "}\n";
echo "</script>\n";

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

/* Retrieve member for specified id */
// $sql = "SELECT *,date_format(last_login, '%W %M %D') as mbrLastLogin FROM members WHERE memberID=$memberID";
// $resMBR = $db->query($sql);
// $dbMbr= mysqli_fetch_array($resMBR);

//$hideMenu = true;
$hlpID = 0;
$title = "SCCC Team Worship Home Page";
include("header.php");




if(MAINTENANCE_MODE) {
	echo "<p style='position:relative;top:-2px;margin:0px;' align='center'><img src='/images/maintenance.png' /></p>\n";
} else {
	echo "<table style='height:100%' width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='ndxLeft'><tr style='height:100%' valign='top'>\n";
	
	// Left panel
	
	echo "	<td valign='top' style='height:100%' class='ndxLeft'>\n";
	
	// My Schedules
	echo "	<table width='100%' cellpadding='0' cellspacing='0'>";

	$schDesc ="";
	$schRoles = "";
	$schPrac ="";
	$schServ = "";
	/* Retrieve member service schedule */
	$q = "SELECT services.serviceID as svcID, svcTeamNotes, date_format(svcPractice, '%W %M %D') as svcPDATE, date_format(svcPractice, '%H:%i') as svcPTIME, date_format(svcDateTime, '%a %b %D') as svcDATE, date_format(svcDateTime, '%H:%i') as svcTIME,roleIcon,roleDescription FROM services INNER JOIN serviceteam ON services.serviceID = serviceteam.serviceID INNER JOIN roles on serviceteam.roleID = roles.roleID WHERE (serviceteam.memberID = $memberID OR concat(',',serviceteam.memberID,',') LIKE concat('%,','".$_SESSION["groups"]."',',%')) AND svcDateTime>='".date("Y-m-d")."' ORDER BY svcDateTime, roleDescription";
	$resSched = $db-> query($q);
	$schDesc .= "<tr><td style='background-image:url(/UserFiles/Image/navhead.jpg);color:#ffffff;height:26px' align='center'><b>My Schedule</b></td></tr>\n";
	$schedExists = mysqli_num_rows($resSched)>0;
	$saveID = 0;
	while($dbSch=mysqli_fetch_array($resSched)) {
		if($saveID != $dbSch["svcID"] && $saveID != 0) {
			$schDesc .= str_replace("%OVRTEXT%",$schRoles.$schPrac,$schServ);
		}
		if($saveID != $dbSch["svcID"]) {
			$schRoles = "";
			///  WRONG Field TODO??
			$swapLink = "";
//			$schPrac = "<b>Practice on</b> ".$dbSch["svcPDATE"]." - ".nicetime($dbSch["svcPTIME"]);
			$schServ = "	<tr><td nowrap><a href='editService.php?id=".$dbSch["svcID"]."&action=edit' onmouseover='return overlib(\"%OVRTEXT%\", WIDTH, 325, SHADOW, SHADOWCOLOR, &quot;#000000&quot;, TEXTSIZE, 2)' onmouseout='return nd();'>".$dbSch["svcDATE"]." - ".nicetime($dbSch["svcTIME"])."</a>&nbsp;$swapLink</td></tr>\n";
		}
		$schRoles .= "<img align=middle src=".$dbSch["roleIcon"].">&nbsp;&nbsp;".$dbSch["roleDescription"]."<br />";
		$schRoles .= $dbSch["svcTeamNotes"]==""?"":"<b>Notes:</b><br />".$dbSch["svcTeamNotes"]."<br />";
		$saveID = $dbSch["svcID"];
	}
	$schDesc .= str_replace("%OVRTEXT%",$schRoles.$schPrac,$schServ);
	
	echo $schDesc;
	if($schedExists) {
		echo "		<tr><td align='center'><a href='/expMySchedule.php?act=sel' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divExpSched',headingText: 'Export Schedule' });\">Export My Schedule</a></td></tr>\n";
	}
	
	// Quick Links Panel
	echo "		<tr><td>&nbsp;</td></tr>\n";
	echo "		<tr><td style='background-image:url(/UserFiles/Image/navhead.jpg);color:#ffffff;height:26px' align='center'><b>Quick Links</b></td></tr>\n";
	echo "		<tr><td>&nbsp;<a href='calendar.php'>Worship Calendar</a></td></tr>\n";
	echo "		<tr><td>&nbsp;<a href='teamSchedule.php'>Team Schedule</a></td></tr>\n";
	echo "		<tr><td>&nbsp;<a href='listSongRating.php'>New Song Rating</a></td></tr>\n";
	echo "		<tr><td>&nbsp;<a href='listSongs.php'>Songs</a></td></tr>\n";
	echo "		<tr><td>&nbsp;<a href='dspSongBooks.php'>Song Books</a></td></tr>\n";
	echo "		<tr><td>&nbsp;<a href='editMember.php?action=edit&id=".$_SESSION["user_id"]."&rtn=1'>Edit Profile</a></td></tr>\n";
	echo "		<tr><td>&nbsp;<a href='dspTeam.php'>Team Directory</a></td></tr>\n";
	echo "		<tr><td>&nbsp;<a href='teamAvailability.php'>Team Availability</a></td></tr>\n";
	#echo "		<tr><td>&nbsp;<a href='http://www.facebook.com/group.php?gid=156422186342' target='_blank'>Facebook Group <img src='/images/facebook.jpg' border='0' align='top' /></a></td></tr>\n";
	# echo "		<tr><td>&nbsp;<a href='/phpBBGo.php'>Member Forum</a></td></tr>\n";
	echo "		<tr><td>&nbsp;<a href='dspMessages.php'>Team Messages</a></td></tr>\n";
	
	//  Admin Panel
	if($isAdmin) {
		echo "		<tr><td>&nbsp;</td></tr>\n";
		echo "		<tr><td style='background-image:url(/UserFiles/Image/navhead.jpg);color:#ffffff;height:26px' align='center'><b>Admin Links</b></td></tr>\n";
		echo "		<tr><td>&nbsp;<a href='editSchedule.php'>Worship Team Planner</a></td></tr>\n";
		echo "		<tr><td>&nbsp;<a href='dspRoles.php'>Manage Roles</a></td></tr>\n";
		echo "		<tr><td>&nbsp;<a href='dspRoleTypes.php'>Manage Role Categories</a></td></tr>\n";
		echo "		<tr><td>&nbsp;<a href='dspRequests.php'>Review Requests</a></td></tr>\n";
		echo "		<tr><td>&nbsp;<a href='editHome.php'>Edit Home Page</a></td></tr>\n";
		echo "		<tr><td>&nbsp;<a href='editActMessage.php'>Activation Template</a></td></tr>\n";
		echo "		<tr><td>&nbsp;<a href='editChgMessage.php'>Change Req. Template</a></td></tr>\n";
		echo "		<tr><td>&nbsp;<a href='editSvcMessage.php'>Service Order Template</a></td></tr>\n";
		echo "		<tr><td>&nbsp;<a href='editSiteConfig.php'>Site Configuration</a></td></tr>\n";
		echo "		<tr><td>&nbsp;<a href='/scripts/extplorer/index.php'>Manage Files</a></td></tr>\n";
	}
	echo "	</table><br /></td>\n";
	
	// Middle Panel
	echo "	<td valign='top' style='height:100%' class='ndxMiddle'>\n";
	echo "		<div style=\"height:26px;font-variant:small-caps;color:#ffffff;background-image:url(/UserFiles/Image/navback.jpg);text-align:center;font-size:14pt;font-weight:bold;\"><img src='/UserFiles/Image/tw_text.png' /></div>\n";
	$sql = "SELECT * FROM siteconfig";
	$resCfg = $db->query($sql);
	$dbCfg=mysqli_fetch_array($resCfg);
	echo "		<div style='padding:10px'>".$dbCfg["homePage"]."</div>\n";;
	echo "	</td>\n";
	
	// Right Panel
	echo "	<td valign='top' style='height:100%' class='ndxRight'>\n";
		
	echo "	<table width='100%' cellpadding='0' cellspacing='0'>";
	
	// Change Requests
	echo "		<tr><td style='background-image:url(/UserFiles/Image/navhead.jpg);color:#ffffff;height:26px' align='center'><b>Change Requests</b></td></tr>\n";
	$sql = "SELECT *,s1.serviceID AS svcID,m.mbrFirstName AS origFName,m.mbrLastName AS origLName,m2.mbrFirstName AS newFName,m2.mbrLastName AS newLName,date_format(s1.svcDateTime,'%b %D') AS svcMD,date_format(s2.svcDateTime,'%b %D') AS newMD,r.roleID FROM svcchangereq r INNER JOIN services s1 ON r.serviceID=s1.serviceID LEFT JOIN services s2 ON newSvcID=s2.serviceID INNER JOIN members m ON r.orgMbrID=m.memberID LEFT JOIN members m2 ON r.newMbrID=m2.memberID WHERE chgStatus <> 'A' AND (s1.svcDateTime>'".date("Y-m-d")." 23:59:59' OR s2.svcDateTime>'".date("Y-m-d")." 23:59:59') ORDER BY s1.svcDateTime,s2.svcDateTime";
	
	
	$chgsFound = false;
	$resReq = $db->query($sql);
	
	if(mysqli_num_rows($resReq) > 0 && $resReq) {
		while($dbReq=mysqli_fetch_array($resReq)) {
			$reqRoles = "";
			$myRoles = explode(",",$_SESSION["roles"]);
			$aReqRoles = array($dbReq["roleID"]);
	
			$commonRoles = array_intersect($aReqRoles,$myRoles);
			
			if(count($commonRoles)>0) {
			
				for($i=0;$i<count($aReqRoles);$i++) {
					$sql = "SELECT roleDescription,roleIcon FROM roles WHERE roleID = ".$aReqRoles[$i];
					
					$reqRole = $db->query($sql);
					$dbReqRole=mysqli_fetch_array($reqRole);
	
					$reqRoles .= "<img align=middle src=".$dbReqRole["roleIcon"].">&nbsp;&nbsp;".$dbReqRole["roleDescription"]."<br />";
				}
				if($dbReq["chgStatus"]=="A") {
					$bgcolor = "#AEFFA6";
					$reqRoles .= "Status: Approved Request";
				} else if($dbReq["chgStatus"]=="P") {
					$bgcolor = "#FEFF8D";
					$reqRoles .= "Status: Pending Approval";
				} else if($dbReq["chgStatus"]=="C") {
					$bgcolor = "#FFCAC3";
					$reqRoles .= "Status: Pending Leader Approval";
				} else {
					$bgcolor = "#FDFF72";
					$reqRoles .= $dbReq["reqType"]=="C"?"Status: Looking for replacement":"Status: requesting swap with ".$dbReq["newFName"]." ".substr($dbReq["newLName"],0,1).". on ".$dbReq["newMD"];
				}
				echo "		<tr><td bgcolor='$bgcolor'><a href='/editService.php?id=".$dbReq["svcID"]."' onmouseover='return overlib(\"$reqRoles\", WIDTH, 200, SHADOW, SHADOWCOLOR, &quot;#000000&quot;, TEXTSIZE, 2)' onmouseout='return nd();'>".$dbReq["origFName"]." ".substr($dbReq["origLName"],0,1).". (".$dbReq["svcMD"].")</a></td></tr>\n";
				$chgsFound = true;
			}
		}
	}
	if(!$chgsFound) {
		echo "		<tr><td style='color:#ffffff;text-align:center'><em>-- no outstanding requests --</em></td></tr>\n";
	}
	
	echo "	</table><br />\n";
	
	// New Sound Rating
	
	echo "	<table width='100%' cellpadding='0' cellspacing='0'>";
	echo "		<tr><td style='background-image:url(/UserFiles/Image/navhead.jpg);color:#ffffff;height:26px' align='center'><a href='listSongRating.php' style='color:#ffffff'>New Song Rating</a></td></tr>\n";
	echo "		<tr><td style='padding:2px'>\n";
	echo "			<iframe frameborder='no' style='margin:0px;padding:0px;overflow:hidden;scroll:none' height='252' width='170' src='ajUpdSongRate.php'></iframe>\n";
	echo "		</td></tr>\n";
	echo "	</table>\n";


	// Resources
	echo "	<table width='100%' cellpadding='0' cellspacing='0'>";
	echo "		<tr><td style='background-image:url(/UserFiles/Image/navhead.jpg);color:#ffffff;height:26px;font-weight:bold' align='center'>Resources</td></tr>\n";
	echo "		<tr><td>&nbsp;<a href='dspTeamResources.php?id=5'>Worhip Band</a></td></tr>\n";
	echo "		<tr><td>&nbsp;<a href='dspTeamResources.php?id=4'>Worship Singers</a></td></tr>\n";
	echo "		<tr><td>&nbsp;<a href='dspTeamResources.php?id=1'>Sound Team</a></td></tr>\n";
	echo "		<tr><td>&nbsp;<a href='dspTeamResources.php?id=2'>Media Team</a></td></tr>\n";
	echo "		<tr><td>&nbsp;<a href='dspTeamResources.php?id=3'>Lighting Team</a></td></tr>\n";
	echo "	</table>\n";

	echo "	</td>\n";
	
	echo "</tr></table>\n";
	echo "<form style='margin:0px;' name=\"frmRequest\" action=\"requestChange.php\" method=\"post\">\n";
	$action = isset($action)?$action:"";
	$serviceID = isset($serviceID)?$serviceID:"";
	$serviceID = isset($serviceID)?$serviceID:"";
	echo "<input type='hidden' name='action' value='$action'>\n";
	echo "<input name=\"serviceID\" type=\"hidden\" value='$serviceID'>\n";
	echo "<input name=\"requestID\" type=\"hidden\" value='$serviceID'>\n";
	echo "<input name=\"newMbrID\" type=\"hidden\">\n";
	echo "</form>\n";
	
	
	// Export Schedule
	echo "<div id='divExpSched' class='highslide-html-content'>\n";
	echo "	<div class='highslide-body'></div>\n";
	echo "</div>\n";
}
echo "</body>\n</html>\n";


?>
