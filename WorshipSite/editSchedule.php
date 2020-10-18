<?php
/*******************************************************************
 * editSchedule.php
 * Edit Worship Schedule
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
//   $valUser = $_SESSION["user_id"] == $_REQUEST["id"];
if (allow_access(Administrators) != "yes") { 
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Worship Schedule Planner', $_SERVER['REQUEST_URI'], 3);

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Default schedule start to today
$schStart = time();
$schDOW = "Sunday";

if(isset($_POST["action"])) {
	// Change Start Date
	if($_POST["action"]=="setdate") {
		$schStart = mktime(0,0,0,substr($_POST["schStart"],5,2),substr($_POST["schStart"],8,2),substr($_POST["schStart"],0,4));
		$schDOW = $_POST["schDOW"];
	}
	// Save changes
	if($_POST["action"]=="save") {
		header("Location: index.php");
		exit;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title><?php echo $siteTitle; ?> - Worship Schedule Planner</title>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/highslide/highslide-full.js"></script>
<script type="text/javascript">
	// Apply the Highslide settings
	hs.graphicsDir = '<?php echo $baseFolder; ?>scripts/highslide/graphics/';
	hs.align = 'center';
	hs.transitions = ['expand', 'crossfade'];
	hs.outlineType = 'rounded-white';
	hs.fadeInOut = true;
	hs.dimmingOpacity = 0.75;
	hs.outlineWhileAnimating = true;
	hs.allowSizeReduction = false;
	// always use this with flash, else the movie will be stopped on close:
	hs.preserveContent = false;
	hs.wrapperClassName = 'draggable-header no-footer';
</script>
<script type="text/javascript" src="scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript" src="scripts/scriptaculous/src/scriptaculous.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $baseFolder; ?>scripts/highslide/highslide.css" />
<script type="text/javascript">

function delMember(key,sd,rid,mbr) {
	var oUpdater = new Ajax.Updater({ success:key }, '/ajDspSchRole.php', { 
		method: "get",
		parameters: { sd: sd, rid: rid, mbr: mbr }
	});
}

function saveSchedule(start,dow) {
	if(confirm("Create Services From Schedule\n\nThis will create services for each month on the current\nschedule which has team members defined.\n\nAre you sure you wish to proceed?")) {
		var oUpdater = new Ajax.Updater({ success:'divSave' }, '/ajSaveSchedule.php', { 
			method: "get",
			parameters: { sd: start, dw: dow }
		});
	}
}
</script>

<script type="text/javascript" src="scripts/tabber/tabber.js"></script>
<link rel="stylesheet" href="scripts/tabber/example.css" TYPE="text/css" MEDIA="screen">
<link rel="stylesheet" href="scripts/tabber/example-print.css" TYPE="text/css" MEDIA="print">

<script type="text/javascript">

/* Temporarily hide the "tabber" class so it does not "flash"
   on the page as plain HTML. After tabber runs, the class is changed
   to "tabberlive" and it will appear. */
document.write('<style type="text/css">.tabber{display:none;}<\/style>');
</script>
<style>
.sched td {
	border:1px solid #000000;
	font-size:9pt;
}
</style>
<?php

// Load Schedule
$q = "SELECT *, teamschedule.memberID AS mbrID, concat(mbrFirstName,' ',mbrLastName) AS mbrName FROM teamschedule INNER JOIN members ON teamschedule.memberID=members.memberID ORDER BY svcDate, roleID, teamschedule.memberID";
$resSched = $db->query($q);
While($dbSched=mysqli_fetch_array($resSched)) {
	$key = $dbSched["svcDate"].$dbSched["roleID"];
	$aSchedule[$key][] = array("id" => $dbSched["mbrID"], "name" => $dbSched["mbrName"]);
}

$hlpID = 0;
$title = "Worship Schedule Planner";
include("header.php");

// Save Schedule
echo "<div id='divSave'></div>\n";

echo "<form style='margin:0px;' name='frmComm' method='post' action='editSchedule.php'>\n";
echo "<input type='hidden' name='action' value=''>\n";
// Weekday
echo "<div style='width:100%;text-align:right''><b>Day of Week:</b>&nbsp;\n";
echo "	<select id=\"schDOW\" name=\"schDOW\" onChange='document.frmComm.action.value=\"setdate\";document.frmComm.submit();'>\n";
$aDOW = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
for($i=0;$i<7;$i++) {
	$sel = $schDOW==$aDOW[$i]?" selected":"";
	echo "		<option$sel>".$aDOW[$i]."</option>\n";
}
echo "	</select>&nbsp;&nbsp;\n";

// Start Month
echo "<b>Schedule Start:</b>&nbsp;\n";
echo "	<select id=\"schStart\" name=\"schStart\" onChange='document.frmComm.action.value=\"setdate\";document.frmComm.submit();'>\n";
for($i=0;$i<12;$i++) {
	$sel = date("Y-m-d",mktime(0,0,0,date("n")+$i,1,date("Y")))==date("Y-m-d",$schStart)?" selected":"";
	echo "		<option value=\"".date("Y-m-d",mktime(0,0,0,date("n")+$i,1,date("Y")))."\"$sel>".date("M Y",mktime(0,0,0,date("n")+$i,1,date("Y")))."</option>\n";
}
echo "	</select>&nbsp;&nbsp;\n";
echo "<input onClick='saveSchedule(\"".date("Y-m-d",$schStart)."\",\"$schDOW\")' type='button' name='sbmSched' value='Create Services From Schedule'>\n";
echo "</div>";

echo "<div class='tabber'>\n";
for($mth=1;$mth<=6;$mth++) {
	$madd = $mth-1;
	echo "	<div class=\"tabbertab\">\n";
	echo "		<h2>".date("M Y",mktime(0,0,0,date("n",$schStart)+$madd,1,date("Y",$schStart)))."</h2><p>\n";

	$firstSunday = strtotime("first $schDOW ", mktime(0,0,0,date("n",$schStart)+$madd,0,date("Y",$schStart)));
	$aSundays = array();
	$aSvcDates = array();
	$aAllowEdit = array();
	$nxtSunday = $firstSunday;
	while(date("n",$nxtSunday)==date("n",$firstSunday)) {
		$q = "SELECT serviceID FROM services WHERE substr(svcDateTime,1,10) = '".date("Y-m-d",$nxtSunday)."'";
		$resScv = $db->query($q);
		$aAllowEdit[] = !(mysqli_num_rows($resScv) > 0 && $resScv) && $nxtSunday > time();
		$aSundays[] = date("M j, Y",$nxtSunday);
		$aSvcDates[] = date("Y-m-d",$nxtSunday);
		$nxtSunday = mktime(0,0,0,date("n",$nxtSunday),date("j",$nxtSunday)+7,date("Y",$nxtSunday));
	}
	echo "			<table class='sched' border='1' width='100%'>\n";
	echo "				<tr>\n";
	echo "					<th>&nbsp;</th>\n";
	echo "					<th>&nbsp;</th>\n";
	for($i=0;$i<count($aSundays);$i++) {
		echo "					<th colspan='2'>".$aSundays[$i]."</th>\n";
	}
	echo "				</tr>\n";
	$oldType = "";
	$q = "SELECT * FROM roletypes a INNER JOIN roles b ON a.typeID=b.typeID ORDER BY typeSort, typeDescription, roleDescription";
	$resRoles = $db->query($q);
	While($dbRoles=mysqli_fetch_array($resRoles)) {
		echo "				<tr>\n";
		if($dbRoles["typeDescription"]!=$oldType) {
			echo "					<td style='font-weight:bold;background-color:#ebebeb' nowrap>".$dbRoles["typeDescription"]."</td>\n";
			$oldType = $dbRoles["typeDescription"];
		} else {
			echo "					<td style='border-top:none;border-bottom:none' nowrap>&nbsp;</td>\n";
		}
		echo "					<td style='font-weight:bold;background-color:#ebebeb' nowrap>".$dbRoles["roleDescription"]."</td>\n";
		for($i=0;$i<count($aSundays);$i++) {
				$key = $aSvcDates[$i].$dbRoles["roleID"];
				$mbrNames = "";
				$mbrIDs = "";
			if ($aAllowEdit[$i]) {
				if (isset($aSchedule[$key])) {
					for ($m=0;$m<count($aSchedule[$key]);$m++) {
						$mbrNames .= "<a href='#' onClick='delMember(\"f".str_replace("-","",$aSvcDates[$i].$dbRoles["roleID"])."\",\"".$aSvcDates[$i]."\",".$dbRoles["roleID"].",".$aSchedule[$key][$m]["id"].")'>".$aSchedule[$key][$m]["name"]."</a><br />";
					}
				}
				$fldName = "f".str_replace("-","",$aSvcDates[$i].$dbRoles["roleID"]);
				echo "					<td title='".$dbRoles["roleDescription"]."' nowrap style='width:20px;border-right:0px'><a href='addSchedMbr.php?&sd=".$aSvcDates[$i]."&rid=".$dbRoles["roleID"]."&rd=".$dbRoles["roleDescription"]."' title='Add Member' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divAddMbr', headingText: 'Add Member', width: 200 });\"><img src='/images/icon_add.gif' border='0' /></a></td><td title='".$dbRoles["roleDescription"]."' style='border-left:0px'><div style='width:150px;' id='$fldName'>$mbrNames</div></td>\n";
			} else {
         //             <td title='".$dbRoles["roleDescription"]."' nowrap colspan='2' style='background-color:#efefef;'>&nbsp;</td>
		 	if (isset($aSchedule[$key])) {
		 		for($m=0;$m<count($aSchedule[$key]);$m++) {
		 		$mbrNames .= $aSchedule[$key][$m]["name"]."<br />";
		 		}
		 	}
				$fldName = "f".str_replace("-","",$aSvcDates[$i].$dbRoles["roleID"]);
				echo "					<td title='".$dbRoles["roleDescription"]."' nowrap colspan='2' style='border-left:0px'><div style='width:150px;' id='$fldName'>$mbrNames</div></td>\n";
			}
		}
		echo "				</tr>\n";
	}
	echo "			</table>\n";
	echo "		</p>\n";
	echo "	</div>\n";
}
echo "</div>\n";
echo "</form>\n";

// Add Members
echo "<div id='divAddMbr' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";

echo "</body>\n</html>\n";
?>