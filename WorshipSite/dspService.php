<?php
/*******************************************************************
 * dspService.php
 * Display Service information
 *******************************************************************/
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

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

include ('fnNicetime.php');

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Display Service', $_SERVER['REQUEST_URI'], 2);

$isAdmin = (allow_access(Administrators)=="yes");


if (isset($_REQUEST["id"]) && $_REQUEST["id"]>0) { 
	$serviceID = $_REQUEST["id"];
} else {
	header("Location: /lr/login.php"); 
	exit;
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Calendar (Display Service)</title>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/overlibmws/overlibmws.js"></script>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/overlibmws/overlibmws_exclusive.js"></script>

<script type="text/javascript" src="scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript" src="scripts/scriptaculous/src/scriptaculous.js"></script>
<script type="text/javascript">
function dspTeam(svcid,mbrid) {
	var oUpdater = new Ajax.Updater({ success:'divTeam' }, '/ajDspTeamDSP.php', { 
		method: "get",
		parameters: { 	id: svcid, mid: mbrid }
	});
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
</script>
<?php
 
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


/* Retrieve Service for specified id */
$sql = "SELECT *, date_format(svcPractice, '%W %M %D') as svcPDATE, date_format(svcPractice, '%H:%i') as svcPTIME, date_format(svcDateTime, '%W %M %D') as svcDATE, date_format(svcDateTime,'%Y-%m-%d') as svcCompDate, date_format(svcDateTime, '%H:%i') as svcTIME FROM services WHERE serviceID=$serviceID";
$resSVC = $db->query($sql);
$dbSvc=mysqli_fetch_array($resSVC);

$hlpID = $isAdmin?16:7;
$title = $dbSvc["svcDescription"]." on ".$dbSvc["svcDATE"]." at ".nicetime($dbSvc["svcTIME"]);
include("header.php");

echo "<table class='serviceDetails' border='1' align='center'><tr valign='top'><td><b><u>Worship Team</u></b>\n";

/* Retrieve team members */
$q = "SELECT serviceteam.memberID AS mbrID, serviceteam.roleID as rID,roleDescription,roleIcon,concat(mbrFirstName,' ',mbrLastName) as mbrName,mbrEmail1,mbrEmail2,mbrHPhone,mbrWPhone,mbrCPhone,soundNotes,roles.typeID as rType FROM serviceteam INNER JOIN members ON serviceteam.memberID = members.memberID INNER JOIN roles on serviceteam.roleID = roles.roleID INNER JOIN roletypes on roles.typeID = roletypes.typeID WHERE serviceID = $serviceID ORDER BY typeSort, roleDescription";
$resTeam = $db->query($q);
$teamDesc = "<div id='divTeam'><table>";
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

echo $teamDesc."</table></div>\n";
echo "</td><td><div style='float:right'><a href='createSongSheet.php?sid=$serviceID&c=1' target='_blank'>Print Songbook</a></div><b><u>Worship Order</u></b><br />";
			
/* Retrieve Song List */
$q = "SELECT orderID,orderType,songKey, songName,orderDescription,orderDetails,orderType, serviceorder.songLink as sLink, songText, serviceorder.songID as sID FROM serviceorder LEFT JOIN songs ON serviceorder.songID = songs.songID WHERE serviceID = $serviceID ORDER BY songNumber";
$resSong = $db->query($q);
$songDesc = "<table>\n";
while($dbsong=mysqli_fetch_array($resSong)) {
	if($dbsong["orderType"]=="S") {
		$sLink = $dbsong["songText"]==""?"&nbsp;":"<a href='dspSong.php?id=".$dbsong["sID"]."&sid=$serviceID'>";
		$sLink2 = $dbsong["songText"]==""?"":"</a>";
		
		if($dbsong["sLink"]=="") {
			$sLinkPlay = "&nbsp;";
		} else {
			// Fixed up any http links for youtube to https: youtube to avovd a security error
			$linkSrc = str_replace(array("/v/","http://www.youtube"),array("/embed/","https://www.youtube"),$dbsong["sLink"]);
			
			$sLinkPlay = "<a href='".$linkSrc."' title='Play music video' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',  Height: 482, width: 600  } )\"><img src='images/play.png' border='0' alt='Play in new window'></a>";
			if (strpos(strtolower($linkSrc), "youtube.com/embed/")) {
			    preg_match("/\/embed\/([a-z0-9A-Z\-]+)/", $linkSrc, $matches);
				$linkSrcn = "https://www.youtube.com/watch?v=" . $matches[1] . "&feature=player_embedded";
			    $sLinkPlay = $sLinkPlay . "<a href='".$linkSrcn."' title='Play music video in new tab' Target='_blank' \"><img src='images/playr.png' border='0' alt='Play in new window'></a>";
			}

		}
		$songDesc .= "	<tr>\n";
		$songDesc .= "		<td><b>".$dbsong["songKey"]."</b></td>\n";
		$songDesc .= "		<td>$sLink".$dbsong["songName"]."$sLink2</td>\n";
		$songDesc .= "		<td>$sLinkPlay</td>\n";
		$songDesc .= "	</tr>\n";
	} else {
		$songDesc .= "	<tr>\n";
		$songDesc .= "		<td>--</td>\n";
		if($dbsong["orderDetails"]=="") {
			$sLinkPlay = "";
			$sLinkPlay2 = "";
			$tdiv = "";
		} else {
			$sLinkPlay = "<a title='Display Details' href='' onclick=\"return hs.htmlExpand(this, { contentId: 'hsc".$dbsong["orderID"]."' } )\" class='highslide'>";
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
}
echo $songDesc."</table>\n";

echo "<hr /><b><u>Resources</u></b><br />";

/* Retrieve Resources */
$q = "SELECT * FROM serviceresources WHERE serviceID = $serviceID ORDER BY rscDescription";
$resRsc = $db->query($q);
$rscDesc = "<table>\n";
$i = 1;
while($dbRsc=mysqli_fetch_array($resRsc)) {
	$rscDesc .= "	<tr>\n";
	$rscDesc .= "		<td><a href='".$dbRsc["rscLink"]."' target='_blank'>".$dbRsc["rscDescription"]."</a></td>\n";
	$rscDesc .= "	</tr>\n";
	$i++;
}
echo $rscDesc;
echo "</table></td></tr>\n";

if(!is_null($dbSvc["svcPDATE"])) {
	echo "<tr><td colspan='2'><strong>Practice scheduled at ".nicetime($dbSvc["svcPTIME"])." on ".$dbSvc["svcPDATE"]."</strong></td></tr>";
}

if($dbSvc["svcNotes"]!="") {
	echo "<tr><td colspan='2'><b>Service Notes:</b><br /> ".nl2br($dbSvc["svcNotes"])."</td></tr>";
}

if($dbSvc["svcPNotes"]!="") {
	echo "<tr><td colspan='2'><strong><b>Practice Notes:</b><br /> ".nl2br($dbSvc["svcPNotes"])."</td></tr>";
}
echo "</tr></table>\n";
//echo "<form><input name=\"refresh\" type=\"button\" value=\"Refresh\" onClick=\"document.location.reload();\" class=\"button\"><input name=\"back\" type=\"button\" value=\"Back\" onClick=\"document.location='calendar.php';\" class=\"button\"></form>\n";


// Request Change
echo "<div id='divReqChg' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";

echo "</body>\n</html>\n";


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
