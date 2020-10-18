<?php
/*******************************************************************
 * calendar.php
 * Main script for calendar EDIT function - used to edit events
 * of calendar.
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
	echo "<form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form>\n";
	echo "<script>document.frmLogin.submit();</script>\n";
	exit;
}

$intYear = isset($_REQUEST["y"])?$_REQUEST["y"]:date( "Y" );
$intMonth = intval(isset($_REQUEST["m"])?$_REQUEST["m"]:date( "n" ));
$intMember = isset($_REQUEST["mbr"])?$_REQUEST["mbr"]:"*";


include ('fnNicetime.php');

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Process Delete Service Request
if(isset($_REQUEST["action"]) && $_REQUEST["action"]=="del") {
	$q = "DELETE FROM serviceorder WHERE serviceID=".$_REQUEST["id"];
	$result = $db->query($q);
	$q = "DELETE FROM serviceteam WHERE serviceID=".$_REQUEST["id"];
	$result = $db->query($q);
    
    $q = "DELETE FROM serviceschedule WHERE serviceID=".$_REQUEST["id"];
	$result = $db->query($q);
    $q = "DELETE FROM serviceresources WHERE serviceID=".$_REQUEST["id"];
	$result = $db->query($q);
    $q = "DELETE FROM svcchangereq WHERE serviceID=".$_REQUEST["id"];
	$result = $db->query($q);
	$sql = "SELECT date_format(svcDateTime, '%W %M %D') as svcDATE FROM services WHERE serviceID=".$_REQUEST["id"];
    $dbSch=mysqli_fetch_array($db->query($sql));
    $d = $dbSch["svcDATE"];
    $q = "DELETE FROM teamschedule WHERE svcDate='$d'";
	$result = $db->query($q);
	$q = "DELETE FROM services WHERE serviceID=".$_REQUEST["id"];
    $result = $db->query($q);
	$q = "DELETE FROM svcchangereq WHERE serviceID=".$_REQUEST["id"];

	$result = $db->query($q);
	
	header("Location: calendar.php?y=$intYear&m=$intMonth&mbr=$intMember"); 
}

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Calendar', $_SERVER['REQUEST_URI'], 1);

$isAdmin = (allow_access(Administrators)=="yes");

?>
<!DOCTYPE>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Calendar</title>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/overlibmws/overlibmws.js"></script>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/overlibmws/overlibmws_exclusive.js"></script>

<?php

echo "\n<script>\n";

echo "function delService(id,svcdate) {\n";
echo "	if(confirm('Do you wish to delete the:\\n'+svcdate+'?')) {\n";
echo "		window.location='".$_SERVER['PHP_SELF']."?y=$intYear&m=$intMonth&mbr=$intMember&id='+id+'&action=del';\n";
echo "	}\n";
echo "	return false;\n";
echo "}\n";
echo "function emailService(id,svcdate) {\n";
echo "	window.location='emailService.php?id='+id;\n";
echo "	return false;\n";
echo "}\n";

if(isset($_POST["msgResult"])) {
    echo "window.onload=function() {\n";
    echo "  alert(\"".stripslashes($_POST["msgResult"])."\");\n";
    echo "}";
}

echo "</script>\n";

$hlpID = $isAdmin?15:6;
$title = "Worship Team Schedule Calendar";
include("header.php");

/* Month Names */
$gaMonthTitles = array("January","February","March", "April","May","June", "July", "August", "September", "October", "November","December");

/* if month and/or year not set, change to current month and year */
if ($intMonth < 1) {
	$intMonth = date( "n" );
}
if ($intYear < 1) {
	$intYear = date( "Y" );
}

/* determine total days in month */
$iLastDayThisMonth = 0;
while ( checkdate( $intMonth, $iLastDayThisMonth + 1, $intYear ) ) $iLastDayThisMonth++;

$intPreMonth = $intMonth-1;
$intPreYear = $intYear;
if ($intPreMonth<1) {
	$intPreMonth = 12;
	$intPreYear = $intYear-1;
}
$intNxtMonth = $intMonth+1;
$intNxtYear = $intYear;
if ($intNxtMonth>12) {
	$intNxtMonth = 1;
	$intNxtYear = $intYear+1;
}
$preMonth = $gaMonthTitles[ $intPreMonth-1 ];
$nxtMonth = $gaMonthTitles[ $intNxtMonth-1 ];
$strTitle = $gaMonthTitles[ $intMonth-1 ] . "&nbsp;" . $intYear;
echo "<form name='frm' methor='post'><input type='hidden' name='action' value='logout'></form>\n";

echo "<table class=\"calhead\"><tr><td class=\"calheadleft\"><a href=\"calendar.php?y=$intPreYear&amp;m=$intPreMonth&amp;mbr=*\">&lt;$preMonth</a></td><td class=\"calheadtitle\">$strTitle</td><td class=\"calheadright\"><a href=\"calendar.php?y=$intNxtYear&amp;m=$intNxtMonth&amp;mbr=*\">$nxtMonth&gt;</a></td></tr></table>\n";
echo "<table class=\"calendar\">\n";
echo "	<tr class=\"daynames\"><th  abbr=\"Sunday\">Sun</th><th  abbr=\"Monday\">Mon</th><th abbr=\"Tuesday\">Tue</th><th  abbr=\"Wednesday\">Wed</th><th abbr=\"Thursday\">Thu</th><th  abbr=\"Friday\">Fri</th><th abbr=\"Saturday\">Sat</th></tr>\n";

// Service
/* Retrieve Services for current month */

if ($intMember == "*") { /* No member specified, select all events */
	$sql = "SELECT serviceID AS svcID, date_format(svcDateTime, '%W %M %D') as svcDATE, date_format(svcPractice, '%W %M %D') as svcPDATE, date_format(svcPractice, '%H:%i') as svcPTIME, svcDescription, date_format(svcDateTime, '%e') as svcDOM, date_format(svcDateTime, '%H:%i') as svcTime,svcNotes FROM services WHERE date_format(svcDateTime, '%Y%c')=".$intYear.$intMonth." order by svcDateTime";
} else { /* If member specified, filter on ID */
	$sql = "SELECT DISTINCT services.serviceID AS svcID, date_format(svcDateTime, '%W %M %D') as svcDATE, date_format(svcPractice, '%W %M %D') as svcPDATE, date_format(svcPractice, '%H:%i') as svcPTIME, svcDescription, memberID, date_format(svcDateTime, '%e') as svcDOM, date_format(svcDateTime, '%H:%i') as svcTime,svcNotes FROM serviceteam INNER JOIN services ON services.serviceID = serviceteam.serviceID WHERE memberID = $intMember AND date_format(svcDateTime, '%Y%c')=".$intYear.$intMonth." order by svcDateTime";
}
$result = $db->query($sql);
$dbrow=mysqli_fetch_array($result);

// Pratice
/* Retrieve Service Practice times for current month */
if ($intMember == "*") { /* No member specified, select all events */
	$sql = "SELECT serviceID AS svcID, date_format(svcDateTime, '%W %M %D') as svcDATE, svcDescription, date_format(svcPractice, '%e') as svcDOM, date_format(svcPractice, '%H:%i') as svcTime,svcPNotes FROM services WHERE date_format(svcPractice, '%Y%c')=".$intYear.$intMonth." and cancelPractice=0 order by svcPractice";
} else { /* If member specified, filter on ID */
	$sql = "SELECT DISTINCT services.serviceID AS svcID, date_format(svcDateTime, '%W %M %D') as svcDATE, svcDescription, memberID, date_format(svcPractice, '%e') as svcDOM, date_format(svcPractice, '%H:%i') as svcTime,svcPNotes FROM serviceteam INNER JOIN services ON services.serviceID = serviceteam.serviceID WHERE memberID = $intMember AND date_format(svcPractice, '%Y%c')=".$intYear.$intMonth." and cancelPractice=0 order by svcPractice";
}
$resPrac = $db->query($sql);
$dbprac=mysqli_fetch_array($resPrac);

/* Retrieve Service Schedule Information for current month */
if ($intMember == "*") { /* No member specified, select all events */
	$sql = "SELECT serviceID AS svcID,schCancel,IF(schType='P','Practice','Service') AS scheduleType,schCategories, date_format(schDateTime, '%W %M %D') as schDATE, schDescription, date_format(schDateTime, '%e') as schDOM, date_format(schDateTime, '%l:%i%p') as schTime,date_format(schDateTime+INTERVAL schDuration*60 MINUTE,'%l:%i%p') AS schENDTIME FROM serviceschedule WHERE date_format(schDateTime, '%Y%c')=".$intYear.$intMonth." order by schDateTime";
} else { /* If member specified, filter on ID */
	$sql = "SELECT DISTINCT serviceschedule.serviceID AS svcID,schCancel,IF(schType='P','Practice','Service') AS scheduleType,schCategories, date_format(schDateTime, '%W %M %D') as schDATE, schDescription, memberID, date_format(schDateTime, '%e') as schDOM, date_format(schDateTime, '%l:%i%p') as schTime,date_format(schDateTime+INTERVAL schDuration*60 MINUTE,'%l:%i%p') AS schENDTIME FROM serviceteam INNER JOIN serviceschedule ON serviceschedule.serviceID = serviceteam.serviceID WHERE memberID = $intMember AND date_format(schDateTime, '%Y%c')=".$intYear.$intMonth." order by schDateTime";
}
$resSch = $db->query($sql);
$dbSch=mysqli_fetch_array($resSch);
$editLink ="";

/* Load Role Categories */
$sql = "SELECT typeID,typeDescription FROM roletypes ORDER BY typeID";
$resCat = $db->query($sql);
$aCategories = array();
while($dbCat=mysqli_fetch_array($resCat)) {
	$aCategories[$dbCat["typeID"]] = $dbCat["typeDescription"];
}

/* ensure that enough blanks are put in so that the first day of the month lines up with the proper day of the week */
$iDayNumOfW = date( "w", mktime( 0, 0, 0, $intMonth, 1, $intYear ) );

echo "	<tr>\n";
if($iDayNumOfW > 0) {
	echo "		<td colspan='$iDayNumOfW'>&nbsp;</td>\n";
}

$today = mktime( 0, 0, 0, date("n"), date("j"), date("Y") );

/* start filling in the days of the month */
for ( $iDay = 1; $iDay <= $iLastDayThisMonth; $iDay++ ) {
	$calendarDay = mktime( 0, 0, 0, $intMonth, $iDay, $intYear );
	if($isAdmin){
		$newCalIcon = "<span class='newbutton'><a title='Create new service' href='editService.php?sd=$intYear-".sprintf("%02s\n",$intMonth)."-".sprintf("%02s\n",$iDay)."&action=add&y=$intYear&m=$intMonth&mbr=$intMember'>New <img align='top' src='{$baseFolder}images/new.png' width='14' height='14' alt='New Service' style='border:0;' title='Create New Service' /></a></span>";
	} else {
		$newCalIcon = "&nbsp;";
	}
	

	$dayclass = $calendarDay==$today?"caltoday":"calday"; // ( $iDay % 2 === 0?"calday":"calday1");
	// Before today
	if ($today > $calendarDay) {
		$dayclass = "caldayold";
	}
	// any service information for today?
	echo "		<td class=calbox><table class='$dayclass'><tr><th align='right' style='border:0px;'>$iDay</th><th class='calbuttons' align='right'>$newCalIcon</th></tr><tr><td class='$dayclass' colspan='2'>";
	if ($iDay != $dbrow["svcDOM"] && $iDay != $dbprac["svcDOM"] && $iDay != $dbSch["schDOM"]) {
		// No details at all for this day
		echo "</td></tr></table></td>\n";
	} else {
	
		// Service details  - may be more than one service record
		while($iDay == $dbSch["schDOM"]) {
			if($dbSch["schCancel"]) {
				$evtTime = $dbSch["schTime"]."-Cancelled";
				$schStyle = "schedulecanceltitle";
				$schCancel = " <b><font color=red>CANCELLED</font></b>";
			} else {
				$evtTime = $dbSch["schTime"]."-".$dbSch["schENDTIME"];
				$schStyle = "scheduletitle";
				$schCancel = "";
			}
			if($dbSch["scheduleType"]=="Service") $schStyle = "servicetitle";
			$order = array("\r\n","\n", "\r");
			$replace = "<br \>";
			$svcDesc = addslashes(htmlentities(str_replace($order, $replace, $dbSch["schDescription"]),ENT_COMPAT));
			$svcDesc2 = addslashes(str_replace($order, $replace, $dbSch["schDescription"]));
			$ovTitle = $svcDesc2;
			$edetails = "";
			echo "<span class='$schStyle'><a href='editService.php?id=".$dbSch["svcID"]."' onmouseover='return overlib(\"".$dbSch["scheduleType"]."$schCancel - $ovTitle on ".$dbSch["schDATE"];
			if($dbSch["schCategories"]!="") {
				if($dbSch["schCategories"]!="*") {
					echo "<br /><br /><strong>Service Categories:</strong>";
					$cats = explode(",",$dbSch["schCategories"]);
					for($i=0;$i<count($cats);$i++) {
						echo "<br />".htmlentities($aCategories[$cats[$i]],ENT_QUOTES);
					}
				} else {
					echo "<br /><br /><strong>Service Categories:</strong> ALL";
				}
			}
			echo "\", WIDTH, 400)' onmouseout='return nd();'>$evtTime - ".$dbSch["scheduleType"]." $svcDesc</a></span><br />";
			$dbSch=mysqli_fetch_array($resSch);
		}
		// Pratice details  - may be more than one service record
		while($iDay == $dbprac["svcDOM"]) {
			$evtTime = nicetime($dbprac["svcTime"]);
			$order = array("\r\n","\n", "\r");
			$replace = "<br \>";
			$svcDesc = addslashes(htmlentities(str_replace($order, $replace, $dbprac["svcDescription"]),ENT_COMPAT));
			$edetails = "";

			echo "<span class='rehearsaltitle'><a href='editService.php?id=".$dbprac["svcID"]."' onmouseover='return overlib(\"Practice for $svcDesc on ".$dbprac["svcDATE"];
			if($dbprac["svcPNotes"]!="") {
				echo "<br /><br /><strong>Notes:</strong><br />".str_replace("\r\n","",nl2br(htmlentities($dbprac["svcPNotes"],ENT_QUOTES)));
			}
			echo "\", WIDTH, 400)' onmouseout='return nd();'>$evtTime - $svcDesc Practice</a></span><br />";
			$dbprac=mysqli_fetch_array($resPrac);
		}
		
		// Service details  - may be more than one service record
		while($iDay == $dbrow["svcDOM"]) {
			$evtTime = nicetime($dbrow["svcTime"]);
			$order = array("\r\n","\n", "\r");
			$replace = "<br \>";
			$svcDesc = addslashes(htmlentities(str_replace($order, $replace, $dbrow["svcDescription"]),ENT_COMPAT));
			$edetails = "";

			echo "<span class='eventitle'><a href='editService.php?id=".$dbrow["svcID"]."&action=edit&y=$intYear&m=$intMonth&mbr=$intMember' onmouseover='return overlib(\"<strong><u>$svcDesc Team:</u></strong><br />";

			/* Retrieve team members */
			$q = "SELECT roleDescription,concat(mbrFirstName,' ',mbrLastName) as mbrName FROM serviceteam INNER JOIN members ON serviceteam.memberID = members.memberID INNER JOIN roles on serviceteam.roleID = roles.roleID INNER JOIN roletypes on roles.typeID = roletypes.typeID WHERE serviceID = ".$dbrow["svcID"]." ORDER BY typeSort, roleDescription";
			$resTeam = $db->query($q);
			$teamDesc = "<table class=svcn>";
			$leftCol = true;
			while($dbteam=mysqli_fetch_array($resTeam)) {
//				if($teamDesc!="") $teamDesc .= ", ";
				if($leftCol) $teamDesc .= "<tr>"; 
				$teamDesc .= "<td align=right><strong>".$dbteam["roleDescription"]."</strong></td><td>".str_replace(" ","&nbsp;",trim($dbteam["mbrName"]));
				if($leftCol) $teamDesc .= "&nbsp;&nbsp;&nbsp;";
				$teamDesc .= "</td>";
				if(!$leftCol) $teamDesc .= "</tr>";
				$leftCol = !$leftCol;
			}
			if(!$leftCol) $teamDesc .= "</tr>";
			$teamDesc .= "</table>";
			echo $teamDesc."<br /><strong><u>Worship Order:</u></strong><br />";
			
			/* Retrieve Song List */
			$q = "SELECT songKey, songName,orderDescription,orderType FROM serviceorder LEFT JOIN songs ON serviceorder.songID = songs.songID WHERE serviceID = ".$dbrow["svcID"]." ORDER BY songNumber";
			$resSong = $db->query($q);
			$songDesc = "";
			$songsExist = false;
			while($dbsong=mysqli_fetch_array($resSong)) {
				if($dbsong["orderType"]=="S") {
					$sKey = $dbsong["songKey"]==""?"":"&nbsp;<b>(".$dbsong["songKey"].")</b>";
					$songDesc .= htmlentities(addslashes($dbsong["songName"]),ENT_QUOTES)."$sKey<br />";
					$songsExist = true;
				} else {
					$songDesc .= htmlentities(addslashes($dbsong["orderDescription"]),ENT_QUOTES)."<br />";
				}
			}
			echo $songDesc;
			if($dbrow["svcNotes"]!="") {
				echo "<br /><strong>Notes:</strong><br />".str_replace("\r\n","",nl2br(htmlentities($dbrow["svcNotes"],ENT_QUOTES)))."<br />";
			}
			if(!is_null($dbrow["svcPDATE"])) {
				echo "<br /><strong>Practice scheduled at ".nicetime($dbrow["svcPTIME"])." on ".$dbrow["svcPDATE"]."</strong>";
			}
			
			// Build the Hover message for the service
			$svcLDesc = "$evtTime-$svcDesc on \\n".$dbrow["svcDATE"];
			$delLink = $isAdmin?"&nbsp;<a title='Delete Service' href='#' onClick='delService(".$dbrow["svcID"].",\"$svcLDesc\");'><br /><img src='{$baseFolder}images/icon_delete.gif' width='12' height='12' alt='Delete Service' class='icon'></a>":"";
			$emailLink = $isAdmin?"&nbsp;<a title='Email Service Order to team' href='#' onClick='emailService(".$dbrow["svcID"].",\"$svcLDesc\");'><img src='{$baseFolder}images/icon_email.gif' width='15' height='12' alt='Email Service Details to Team' class='icon'></a>":"";
			$songbookLink = $songsExist?"&nbsp;<a href='createSongSheet.php?sid=".$dbrow["svcID"]."&c=1' target='_blank' title='Print Songbook'><img src='/images/songbook.gif' width='12' height='12' class='icon' /></a>":"";
			$addToCal = "&nbsp;<a href='/expService.php?id=".$dbrow["svcID"]."&act=sel' title='Add To Local Calendar' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', contentID: 'divAddCal', headingText: 'Add Service to Local Calendar' } )\"><img src='/images/cal.jpg' width='12' height='11' class='icon' /></a>";
			
			
			echo "\", WIDTH, 600,HAUTO, VAUTO)' onmouseout='return nd();'>$evtTime - $svcDesc</a></span>$delLink$editLink$emailLink$songbookLink$addToCal<br />";
			$dbrow=mysqli_fetch_array($result);
		}
		echo "</td></tr></table></td>\n";
	}
	/* terminate row if we're at on the last day of the week */
	$iDayNumOfW++;
	if ( $iDayNumOfW > 6 ) {
		$iDayNumOfW = 0;
		printf("</tr>\n");
		if ( $iDay < $iLastDayThisMonth ) {
			printf("<tr>\n");
		}
	}
}

/* fill in the remainder of the row with spaces */
if ( $iDayNumOfW > 0 ) {
	$iDayNumOfW = 7 - $iDayNumOfW;
}
if ( $iDayNumOfW > 0 ) echo "	<td colspan='$iDayNumOfW'> &nbsp; </td>";

echo "	</tr>\n";
echo "</table>\n";

echo "</body>\n</html>\n";



function dateadd($serialdate,$units,$changevalue) {
	if ($units =='Days') {
		$difference = mktime( 0, 0, 0, date("m",$serialdate), date("d",$serialdate), date("Y",$serialdate)) +(24*60*60*$changevalue);
	}
	return $difference;
}

// Add To Calendar
echo "<div id='divAddCal' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";
?>
