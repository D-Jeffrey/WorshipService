<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_cache_limiter();
session_start();

require('lr/config.php');
require($baseDir.'/lr/functions.php'); 

//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Administrators) != "yes") { 
	header("Location: /lr/login.php"); 
	exit;
}

$isAdmin = allow_access(Administrators) == "yes";

if(isset($_REQUEST['id'])) {
	$serviceID = $_REQUEST['id'];
} else {
	exit;
}

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Delete song/text from the service order
if(isset($_REQUEST['act']) && $_REQUEST['act']=="del") {
	$sql = "DELETE FROM serviceschedule WHERE serviceID=$serviceID AND scheduleID=".$_REQUEST["sid"];
	$resMbr = $db->query($sql);
}


/* Retrieve Service Schedule */
$q = "SELECT *,date_format(schDateTime,'%b %D, %Y') AS schDATE,date_format(schDateTime,'%l:%i%p') AS schTIME,date_format(schDateTime+INTERVAL schDuration*60 MINUTE,'%l:%i%p') AS schENDTIME FROM serviceschedule LEFT JOIN roletypes ON CONCAT(',',schCategories,',') LIKE CONCAT('%,',typeID,',%') WHERE serviceID=$serviceID ORDER BY schType,schDateTime,typeSort";
$resSch = $db->query($q);
$schDesc = "<table width='100%'>\n";
$i = 1;
$saveSchDATE = "";
$saveSchID = 0;
$schLines = "";
$schCategories = "";
$saveSchType = "";
if($resSch && mysqli_num_rows($resSch)>0) {
	while($dbSch=mysqli_fetch_array($resSch)) {
		if($saveSchDATE=="") {
			$saveSchDATE = $dbSch["schDATE"];
			$saveSchID = $dbSch["scheduleID"];
		}
		if($saveSchID!=$dbSch["scheduleID"]) {
			if($isAdmin) {
				$schAdmLink = "<td width='40' nowrap>";
				$schAdmLink .= "<a href='#' onClick=\"delSchedule($saveSchID,'$saveSchDATE - $schTIME');\" title='Remove Schedule Item from Service'><img src='images/icon_delete.gif'></a>&nbsp;";
				$schAdmLink .= "<a id='hsEditTeam' href='editSvcSchedule.php?id=$serviceID&act=edit&sid=$saveSchID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 450, contentID: 'divAddSong', headingText: 'Update Service Schedule' } )\"><img src='images/edit.png'></a>";
				$schAdmLink .= "</td>\n";
			} else {
				$schAdmLink = "";
			}
			$schLines .= "<tr>$schAdmLink<td nowrap>$schTIME&nbsp;</td><td>$schCategories</td><td>$schDescription</td></tr>";
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
		if($dbSch["schCancel"]) {
			$schTIME = $dbSch["schTIME"]."-Cancelled";
		} else {
			$schTIME = $dbSch["schTIME"]."-".$dbSch["schENDTIME"];
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
		$schAdmLink .= "<a href='#' onClick=\"delSchedule($saveSchID,'$saveSchDATE - $schTIME');\" title='Remove Schedule Item from Service'><img src='images/icon_delete.gif'></a>&nbsp;";
		$schAdmLink .= "<a id='hsEditTeam' href='editSvcSchedule.php?id=$serviceID&act=edit&sid=$saveSchID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 450, contentID: 'divAddSong', headingText: 'Update Service Schedule' } )\"><img src='images/edit.png'></a>";
		$schAdmLink .= "</td>\n";
	} else {
		$schAdmLink = "";
	}
	$schLines .= "<tr>$schAdmLink<td nowrap>$schTIME&nbsp;</td><td>$schCategories</td><td>$schDescription</td></tr>";
	$schDesc .= "<tr><td nowrap>$saveSchDATE</td><td><table>$schLines</table></td></tr>";
}	
echo $schDesc."</table>\n";
?>