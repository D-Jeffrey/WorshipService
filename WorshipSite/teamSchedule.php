<?php
/*******************************************************************
 * teamschedule.php
 * Team Worship Schedule Calendar
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
if (!isset($_REQUEST["id"])) {
	$_REQUEST["id"] = 0;
	}
//   $valUser = $_SESSION["user_id"] == $_REQUEST["id"];
if (allow_access(Users) != "yes") { 
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

$isAdmin = allow_access(Administrators) == "yes";

$editVersion = $isAdmin;

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Team Schedule', $_SERVER['REQUEST_URI'], 3);

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
<title><?php echo $siteTitle; ?> - Team Schedule</title>
<script type="text/javascript" src="scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript" src="scripts/scriptaculous/src/scriptaculous.js"></script>
<script type="text/javascript">

function delMember(key,sid,rid,mbr,desc) {
	if(confirm("Are you sure you wish to remove "+desc+"?")) {
		var oUpdater = new Ajax.Updater({ success:key }, '/ajDspTeamSch.php', { 
			method: "get",
			parameters: { sid: sid, rid: rid, mbr: mbr }
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

$hlpID = 0;
$title = "Team Schedule";
include("header.php");

$hintPlanner = false;
// Save Schedule
echo "<div id='divSave'></div>\n";

echo "<div class='tabber'>\n";
for($mth=1;$mth<=6;$mth++) {
	$madd = $mth-1;
	$mthComp = date("Y-m",mktime(0,0,0,date("n",$schStart)+$madd,1,date("Y",$schStart)));
	// Load Service schedule for month
	$q = "SELECT s.serviceID AS svcID, svcDescription, roleID, t.memberID AS mbrID, date_format(s.svcDateTime,'%Y-%m-%d') AS svcDate, concat(mbrFirstName,' ',mbrLastName) AS mbrName FROM services s INNER JOIN serviceteam t ON s.serviceID=t.serviceID INNER JOIN members m ON t.memberID=m.memberID WHERE date_format(s.svcDateTime,'%Y-%m') = '$mthComp' ORDER BY svcDateTime, roleID, t.memberID";
	$resSched = $db->query($q);
	if($resSched && mysqli_num_rows($resSched)>0) {
		echo "	<div class=\"tabbertab\">\n";
		echo "		<h2>".date("M Y",mktime(0,0,0,date("n",$schStart)+$madd,1,date("Y",$schStart)))."</h2><p>\n";
		$aServices = array();
		$aSchedule = array();
		$oldSvcID = 0;
		While($dbSched=mysqli_fetch_array($resSched)) {
			$key = $dbSched["svcID"]."r".$dbSched["roleID"];
			if($dbSched["svcID"]!=$oldSvcID) {
				$aServices[] = array("id" => $dbSched["svcID"], "desc" => $dbSched["svcDescription"], "date" => $dbSched["svcDate"]);
				$oldSvcID = $dbSched["svcID"];
			}
			$aSchedule[$key][] = array("id" => $dbSched["mbrID"], "name" => $dbSched["mbrName"]);
		}
		echo "			<table class='sched' border='1' width='100%'>\n";
		echo "				<tr>\n";
		echo "					<th>&nbsp;</th>\n";
		echo "					<th>&nbsp;</th>\n";
		for($i=0;$i<count($aServices);$i++) {
			echo "					<th colspan='2'>".$aServices[$i]["desc"]."<br />".$aServices[$i]["date"]."</th>\n";
		}
		echo "				</tr>\n";
		$oldType = "";
		$q = "SELECT * FROM roletypes a INNER JOIN roles b ON a.typeID=b.typeID ORDER BY typeSort, typeDescription, roleDescription";
		$resRoles = $db->query($q);
		While($dbRoles=mysqli_fetch_array($resRoles)) {
			echo "				<tr>\n";
			if($dbRoles["typeDescription"]!=$oldType) {
				echo "					<td width='100' style='font-weight:bold;background-color:#ebebeb' nowrap>".$dbRoles["typeDescription"]."</td>\n";
				$oldType = $dbRoles["typeDescription"];
			} else {
				echo "					<td width='100' style='border-top:none;border-bottom:none' nowrap>&nbsp;</td>\n";
			}
			echo "					<td width='100' style='padding-right:3px;font-weight:bold;background-color:#ebebeb' nowrap>".$dbRoles["roleDescription"]."</td>\n";
			for($i=0;$i<count($aServices);$i++) {
				$key = $aServices[$i]["id"]."r".$dbRoles["roleID"];
				$mbrNames = "";
				$mbrIDs = "";
				if ($editVersion) {

					if (isset($aSchedule[$key])) {

						for ($m=0;$m<count($aSchedule[$key]);$m++) {
							$mbrNames .= "<a href='#' onClick='delMember(\"f".$aServices[$i]["id"]."r".$dbRoles["roleID"]."\",\"".$aServices[$i]["id"]."\",".$dbRoles["roleID"].",".$aSchedule[$key][$m]["id"].",\"".$aSchedule[$key][$m]["name"]."\")'>".$aSchedule[$key][$m]["name"]."</a><br />";
						}
					}
					$fldName = "f".$aServices[$i]["id"]."r".$dbRoles["roleID"];
					echo "					<td title='".$dbRoles["roleDescription"]."' nowrap style='width:20px;border-right:0px'><a href='ajTeamSchedMbr.php?sd=".$aServices[$i]["date"]."&sid=".$aServices[$i]["id"]."&rid=".$dbRoles["roleID"]."&rd=".$dbRoles["roleDescription"]."' title='Add Member' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divAddMbr', headingText: 'Add Member', width: 200 });\"><img src='/images/icon_add.gif' border='0' /></a></td><td title='".$dbRoles["roleDescription"]."' style='border-left:0px'><div style='width:150px;' id='$fldName'>$mbrNames</div></td>\n";
				} else {
					if (isset($aSchedule[$key]))
						for($m=0;$m<count($aSchedule[$key]);$m++) {
						$mbrNames .= $aSchedule[$key][$m]["name"]."<br />";

					}

					echo "					<td title='".$dbRoles["roleDescription"]."' nowrap colspan='2' style='background-color:#efefef;'>$mbrNames</td>\n";
				}
			}
			echo "				</tr>\n";
		}
		echo "			</table>\n";
		echo "		</p>\n";
		echo "	</div>\n";
	} else {
		$hintPlanner = true;
	}
}
echo "</div>\n";

echo "<div style='position:absolute;top:128px;right:10px'><a title='Download printable PDF version' href='createTeamSchedule.php'><img border='0' src='/images/PDF_Download.gif' /></a></div>\n";

if ($hintPlanner and $editVersion) {
	echo "<div style='position:absolute;top:135px;right:100px'><a title='Time to use the Planner?' href='/editSchedule.php'>Time for Worship Planner?</a></div>\n";
}
// Add Members
echo "<div id='divAddMbr' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";

echo "</body>\n</html>\n";
?>