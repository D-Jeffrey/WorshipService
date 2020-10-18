<?php
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
session_start();

require('lr/config.php');
require('lr/functions.php'); 
if (allow_access(Users) != "yes") {
	exit;
}

$serviceID = isset($_REQUEST["id"])?$_REQUEST["id"]:0;
$action = isset($_REQUEST["act"])?$_REQUEST["act"]:"";
if($serviceID==0 || $action=="") {
	exit;
}

if($action == "sel") {
	selFormat($serviceID);
} else {
	//Setup database connection
	$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());
	
	
	// Retrieve service Information
	$aSvcDesc = genServiceDesc($serviceID);
	
	// Generate calendar output
	expiCalendar($aSvcDesc,$_POST["fmtOut"]);
}

// Select format
function selFormat($serviceID) {
	echo "<html><head>\n";
	echo "<link rel=\"stylesheet\" href=\"css\tw.css\" type=\"text/css\"></head>\n";
	echo "<body><form name='frmSel' action='expService.php?id=$serviceID&act=out' method='post' onSubmit='parent.window.hs.close();'>\n";
	echo "<b>Select Calendar Type</b><br />\n";
	echo "<input type='radio' name='fmtOut' value='ics' checked />&nbsp;iCalendar<br />\n";
	echo "<input type='radio' name='fmtOut' value='vcs' />&nbsp;vCalendar<br />\n";
	echo "<p align='center'><input type='submit' name='save' value='Submit' />&nbsp;<input type='button' name='cancel' value='Cancel' onClick='parent.window.hs.close();' /></p>\n";
	echo "</form></body></html>\n";
}

// Export Calendar in ics/vcs format
function expiCalendar($aSvcDesc,$fmt) {
	global $siteTitle,$db;
	
	header("Content-Type: text/x-vCalendar");
	if($fmt=="ics") {
		header("Content-Disposition: inline; filename=\"MyvCalFile.ics\"");
	} else {
		header("Content-Disposition: inline; filename=\"MyvCalFile.vcs\"");
	}

	$strDateTime = date("Ymd\THis",strtotime($aSvcDesc[1]));
	$endDateTime = date("Ymd\THis",strtotime($aSvcDesc[1])+3600);
	echo "BEGIN:VCALENDAR\n";
	echo $fmt=="ics"?"VERSION:2.0\n":"VERSION:1.0\n";
	echo "BEGIN:VEVENT\n";
	echo "DTSTART:$strDateTime\n";
	echo "DTEND:$endDateTime\n";
	echo "LOCATION:South Calgary Community Church\n";
	echo "SUMMARY:".$aSvcDesc[0]."\n";
	echo "DESCRIPTION;ENCODING=QUOTED-PRINTABLE:".$aSvcDesc[2]."\n";
	echo "END:VEVENT\n";
	echo "END:VCALENDAR\n";
}

// Build message Body
function genServiceDesc($serviceID) { 
	global $siteTitle,$db;
	
	// Process Email Service Request
	$sql = "SELECT serviceID AS svcID, svcDateTime, date_format(svcDateTime, '%W %M %D, %Y') as svcDATE, date_format(svcPractice, '%W %M %D') as svcPDATE, date_format(svcPractice, '%l:%i%p') as svcPTIME, svcDescription, date_format(svcDateTime, '%e') as svcDOM, date_format(svcDateTime, '%l:%i%p') as svcTime,svcNotes,svcPNotes FROM services WHERE serviceID=$serviceID";
	$result = $db->query($sql);
	if($dbrow=mysqli_fetch_array($result)) {
		$subject="$siteTitle Service for ".$dbrow["svcDATE"]." at ".$dbrow["svcTime"];
		$msgText = "";
	
		/* Retrieve team members */
		$q = "SELECT serviceteam.memberID AS mbrID, serviceteam.roleID as rID,roleDescription,roleIcon,concat(mbrFirstName,' ',mbrLastName) as mbrName,mbrEmail1,mbrEmail2,mbrHPhone,mbrWPhone,mbrCPhone,soundNotes,roles.typeID as rType FROM serviceteam INNER JOIN members ON serviceteam.memberID = members.memberID INNER JOIN roles on serviceteam.roleID = roles.roleID INNER JOIN roletypes on roles.typeID = roletypes.typeID WHERE serviceID = $serviceID ORDER BY typeSort, roleDescription";
		$resTeam = $db->query($q);
		$msgText .= "WORSHIP TEAM:\n";
		while($dbteam=mysqli_fetch_array($resTeam)) {
			$msgText .= "    ".$dbteam["roleDescription"]." - ".$dbteam["mbrName"]." (".$dbteam["mbrEmail1"].")\n";
		}
	
		/* Retrieve Song List */
		$q = "SELECT songNumber, songKey, serviceorder.songLink as LinkA, ifnull(songs.songLink,'') as LinkB, ifnull(songName,orderDescription) as orderText FROM serviceorder LEFT JOIN songs ON serviceorder.songID = songs.songID WHERE serviceID = ".$dbrow["svcID"]." ORDER BY songNumber";
		$resSong = $db->query($q);
		$wOrder = "";
		while($dbsong=mysqli_fetch_array($resSong)) {
			$wOrder .= "    ".$dbsong["orderText"]."=0D=0A";
		}
		$msgText .= $wOrder!=""?"=0D=0A=0D=0AWORSHIP ORDER:\n":"";
	
		$msgText .= $dbrow["svcNotes"]!=""?"=0D=0A=0D=0ASERVICE NOTES:=0D=0A":"";
		$msgText .= $dbrow["svcNotes"]!=""?str_replace("\n","=0D=0A",$dbrow["svcNotes"])."\n":"";
		$msgText .= $dbrow["svcPDATE"]!=""?"\nPRACTICE DATE/TIME: ".$dbrow["svcPDATE"]." / ".$dbrow["svcPTIME"]."\n":"";
		$msgText .= $dbrow["svcPNotes"]!=""?"\nPRACTICE NOTES:\n":"";
		$msgText .= $dbrow["svcPNotes"]!=""?str_replace("\n","=0D=0A",$dbrow["svcPNotes"]):"";

		return array($subject,$dbrow["svcDateTime"],str_replace("\r","",str_replace("\n","=0D=0A",$msgText)));
	} else {
		return false;
	}
} 
?>
