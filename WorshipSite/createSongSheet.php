<?php
require('lr/config.php');
require($baseDir.'/lr/functions.php'); 
//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.

include ('fnNicetime.php');

include ('fnPDFSongs.php');

if (allow_access(Users) != "yes") { 
//	exit;
}
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

include 'vendor/autoload.php';

$pageNumber = 0;
$aTOC[]= array();

$serviceID = isset($_REQUEST["sid"])?$_REQUEST["sid"]:0;
$songID = isset($_REQUEST["id"])?$_REQUEST["id"]:0;
$showChords = $_REQUEST["c"];

error_reporting(0);

// include("class.pdf.php");
$pdf = new Cpdf();
pageTop($pdf,"Song Sheet");


// assume Service ID set or Song ID set
if($serviceID==0) {
	pageTop($pdf,"Song Sheet");
	$fileName = outputSong($songID,$showChords,false);
	pageBottom($pdf);
} else {
	$q = "SELECT svcDateTime FROM services WHERE serviceID = $serviceID";
	$resSvc = $db->query($q);
	$dbSvc=mysqli_fetch_array($resSvc);
    $fileName = "ServiceSongs_".$dbSvc["svcDateTime"];
	$q = "SELECT orderID,orderType,songID, date_format(svcDateTime, '%W %M %D') as svcDATE, date_format(svcDateTime, '%H:%i') as svcTIME FROM serviceorder INNER JOIN services on serviceorder.serviceID=services.serviceID WHERE serviceorder.serviceID = $serviceID AND orderType = 'S' ORDER BY songNumber";
	$resSvc = $db->query($q);
	$notFirst = false;
	while($dbSvc=mysqli_fetch_array($resSvc)) {
		$svcDescription = $dbSvc["svcDATE"]." ".nicetime($dbSvc["svcTIME"]);
		if($notFirst) {
			addNewPage($pdf,$svcDescription);
		} else {
			pageTop($pdf,$svcDescription);
		}
		outputSong($dbSvc["orderID"],$showChords,true);
		$fileName = $svcDescription;
		$notFirst = true;
	}
	
	pageBottom($pdf);
}

$pdfcode = $pdf->output();

header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="'.urlencode($fileName).'.pdf"');
echo $pdfcode; 


