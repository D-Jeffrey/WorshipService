<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require('lr/config.php');

if(isset($_REQUEST['sid'])) {
	$serviceID = $_REQUEST['sid'];
	$roleID = $_REQUEST['rid'];
	$memberID = $_REQUEST['mbr'];
} else {
	exit;
}

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Delete requested record
$q = "DELETE FROM serviceteam WHERE serviceID=$serviceID AND roleID=$roleID AND memberID=$memberID";

$resSched = $db->query($q);

// Load Schedule Role
$q = "SELECT *, serviceteam.memberID AS mbrID, concat(mbrFirstName,' ',mbrLastName) AS mbrName FROM serviceteam INNER JOIN members ON serviceteam.memberID=members.memberID WHERE serviceID=$serviceID AND roleID=$roleID ORDER BY mbrLastName, mbrFirstName";
$resSched = $db->query($q);
While($dbSched=mysqli_fetch_array($resSched)) {
	$key = $dbSched["serviceID"]."r".$dbSched["roleID"];
	$aSchedule[$key][] = array("id" => $dbSched["mbrID"], "name" => $dbSched["mbrName"]);
}

$key = $serviceID."r".$roleID;
$mbrNames = "";
for($m=0;$m<count($aSchedule[$key]);$m++) {
	$key = $serviceID."r".$roleID;
	$mbrNames .= "<a href='#' onClick='delMember(\"f{$serviceID}r{$roleID}\",$roleID,".$aSchedule[$key][$m]["id"].")'>".$aSchedule[$key][$m]["name"]."</a><br />";
}
echo $mbrNames;
?>