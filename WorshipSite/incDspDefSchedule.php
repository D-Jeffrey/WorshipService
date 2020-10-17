<?php
// This is an Inline include
$aDOW = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
/* Retrieve Service Schedule */
$q = "SELECT *,date_format(schTime,'%l:%i%p') AS schTIME,date_format(schTime+INTERVAL schDuration*60 MINUTE,'%l:%i%p') AS schENDTIME FROM scheduledefaults LEFT JOIN roletypes ON CONCAT(',',schCategories,',') LIKE CONCAT('%,',typeID,',%') ORDER BY schDateOffset DESC,schTime,typeSort";
$resSch = $db->query($q);
$schDesc = "<table width='100%'>\n";
$i = 1;
$saveSchDateOffset = "";
$saveSchID = 0;
$schLines = "";
$schCategories = "";
if($resSch && mysqli_num_rows($resSch)>0) {
	while($dbSch=mysqli_fetch_array($resSch)) {
		if($saveSchDateOffset=="") {
			$saveSchDateOffset = $dbSch["schDateOffset"];
			$saveSchID = $dbSch["scheduleID"];
			$schTIME = $dbSch["schTIME"]."-".$dbSch["schENDTIME"];
		}
		if($saveSchID!=$dbSch["scheduleID"]) {
			$schAdmLink = "<td width='40' nowrap>";
			$schAdmLink .= "<a href='#' onClick=\"delSchedule($saveSchID,'$schDateOffset - $schTIME');\" title='Remove Schedule Item'><img src='images/icon_delete.gif'></a>&nbsp;";
			$schAdmLink .= "<a id='hsEditTeam' href='editDefSchedule.php?act=edit&sid=$saveSchID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 450, contentID: 'divAddSch', headingText: 'Update Schedule Defaults' } )\"><img src='images/edit.png'></a>";
			$schAdmLink .= "</td>\n";
			$schLines .= "<tr>$schAdmLink<td nowrap$schStyle>$schTIME&nbsp;</td><td$schStyle>$schCategories</td><td$schStyle>$schDescription</td></tr>";
			$schTIME = $dbSch["schTIME"]."-".$dbSch["schENDTIME"];
			$schCategories = "";
			$saveSchID = $dbSch["scheduleID"];
			$do = $saveSchDateOffset==0?0:7+$saveSchDateOffset;
			$schDesc .= "<tr><td nowrap>".$aDOW[$do]."</td><td><table>$schLines</table></td></tr>";
			$schDesc .= "<tr><td colspan='2'><hr style='margin:0px;' /></td></tr>";
			$schLines = "";
			$saveSchDateOffset = $dbSch["schDateOffset"];
		}
		$schDateOffset = $dbSch["schDateOffset"];
		$schStyle = "";
		if($dbSch["schCategories"]=="*") {
			$schCategories = "Everyone";
		} else {
			$schCategories .= $schCategories!=""?", ":"";
			$schCategories .= $dbSch["typeDescription"];
		}
		$schDescription = $dbSch["schDescription"]!=""?"(".$dbSch["schDescription"].")":"";
	}
	$schAdmLink = "<td width='40' nowrap>";
	$schAdmLink .= "<a href='#' onClick=\"delSchedule($saveSchID,'$schDateOffset - $schTIME');\" title='Remove Schedule Item'><img src='images/icon_delete.gif'></a>&nbsp;";
	$schAdmLink .= "<a id='hsEditTeam' href='editDefSchedule.php?act=edit&sid=$saveSchID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 450, contentID: 'divAddSch', headingText: 'Update Schedule Defaults' } )\"><img src='images/edit.png'></a>";
	$schAdmLink .= "</td>\n";
	$schLines .= "<tr>$schAdmLink<td nowrap>$schTIME&nbsp;</td><td>$schCategories</td><td>$schDescription</td></tr>";
	$do = $saveSchDateOffset==0?0:7+$saveSchDateOffset;
	$schDesc .= "<tr><td nowrap>".$aDOW[$do]."</td><td><table>$schLines</table></td></tr>";
}
$schDesc .= "</table>\n";
?>