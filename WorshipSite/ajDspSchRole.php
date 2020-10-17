<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require('lr/config.php');

if(isset($_REQUEST['sd'])) {
	$sd = $_REQUEST['sd'];
	$rid = $_REQUEST['rid'];
	$memberID = $_REQUEST['mbr'];
} else {
	exit;
}

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Delete requested record
$q = "DELETE FROM teamschedule WHERE svcDate='$sd' AND roleID=$rid AND memberID=$memberID";
$resSched = $db->query($q);

// Load Schedule Role
$q = "SELECT *, teamschedule.memberID AS mbrID, concat(mbrFirstName,' ',mbrLastName) AS mbrName FROM teamschedule INNER JOIN members ON teamschedule.memberID=members.memberID WHERE svcDate='$sd' AND roleID=$rid ORDER BY teamschedule.memberID";
$resSched = $db->query($q);
While($dbSched=mysqli_fetch_array($resSched)) {
	$key = $dbSched["svcDate"].$dbSched["roleID"];
	$aSchedule[$key][] = array("id" => $dbSched["mbrID"], "name" => $dbSched["mbrName"]);
}

$key = $sd.$rid;
$mbrNames = "";
for($m=0;$m<count($aSchedule[$key]);$m++) {
	$key = $sd.$rid;
	$mbrNames .= "<a href='#' onClick='delMember(\"f".str_replace("-","",$sd.$rid)."\",\"$sd\",$rid,".$aSchedule[$key][$m]["id"].")'>".$aSchedule[$key][$m]["name"]."</a><br />";
}
echo $mbrNames;
?>