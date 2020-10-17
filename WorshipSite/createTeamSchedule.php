<?php
session_start();
require('lr/config.php');
require($baseDir.'/lr/functions.php'); 
//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Users) != "yes") { 
	exit;
}
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

include 'vendor/autoload.php';


$pageNumber = 1;

$pdf = new Cezpdf("letter","landscape");

outputSchedule();

$pdfcode = $pdf->output();
header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="TeamSchedule_'.date("Ymd").'.pdf"');
echo $pdfcode; 

function pageTop($pdf,$headDesc) {
	$pdf->addJpegFromFile('images/PDF/bannerblank.jpg',29,520);
	$pdf->selectFont('Helvetica-Bold');
	$pdf->setColor(255,255,255);
	$pdf->addTextWrap(600,550,10,$headDesc,0,"right");
	$pdf->setColor(0,0,0);
}

function pageBottom($pdf) {
	global $pageNumber;
	
	$pdf->selectFont('Helvetica-Bold');
	$pdf->setColor(0,0,0);
	$pdf->addText(35,35,10,"Page: $pageNumber");
//	$d = date("M j-y");
//	$pdf->selectFont('Helvetica');
//	$pdf->addText(800,35,10,$d);
	$pageNumber++;
}

function addNewPage($pdf,$headDesc) {
	pageBottom($pdf);
	$pdf->newPage();
	pageTop($pdf,$headDesc);
}

function outputSchedule() {
	global $db, $pdf, $pageNumber;
	
	// width = 792, margin left = 29, right = 30
	$xPos = 29;
	$yPos = 510;

	$pdf->selectFont('Helvetica-Bold');

	$schStart = time();
	for($mth=1;$mth<=6;$mth++) {
		$madd = $mth-1;
		$mthComp = date("Y-m",mktime(0,0,0,date("n",$schStart)+$madd,1,date("Y",$schStart)));
		// Load Service schedule for month
		$q = "SELECT s.serviceID AS svcID, svcDescription, roleID, t.memberID AS mbrID, date_format(s.svcDateTime,'%Y-%m-%d') AS svcDate, concat(mbrFirstName,' ',mbrLastName) AS mbrName FROM services s INNER JOIN serviceteam t ON s.serviceID=t.serviceID INNER JOIN members m ON t.memberID=m.memberID WHERE date_format(s.svcDateTime,'%Y-%m') = '$mthComp' ORDER BY svcDateTime, roleID, t.memberID";
		$resSched = $db->query($q);
		if($resSched && mysqli_num_rows($resSched)>0) {
			$headDesc = "Worship Team Schedule - ".date("M Y",mktime(0,0,0,date("n",$schStart)+$madd,1,date("Y",$schStart)));
			if($pageNumber>1) {
				$pdf->newPage();
			}
			pageTop($pdf,$headDesc);

			$aServices = array();
			$aSchedule = array();
			$oldSvcID = 0;
			$pdf->selectFont('Helvetica');
			While($dbSched=mysqli_fetch_array($resSched)) {
				$key = $dbSched["svcID"]."r".$dbSched["roleID"];
				if($dbSched["svcID"]!=$oldSvcID) {
					$aServices[] = array("id" => $dbSched["svcID"], "desc" => $dbSched["svcDescription"], "date" => $dbSched["svcDate"]);
					$oldSvcID = $dbSched["svcID"];
				}
				$aSchedule[$key][] = array("id" => $dbSched["mbrID"], "name" => $dbSched["mbrName"]);
			}
			$aColHead = array();
			$aScheduleTable = array();
			$aColHead[] = "Roles";
			for($i=0;$i<count($aServices);$i++) {
				$aColHead[] = $aServices[$i]["desc"]."\n".$aServices[$i]["date"];
			}
			$oldType = "";
			$q = "SELECT * FROM roletypes a INNER JOIN roles b ON a.typeID=b.typeID ORDER BY typeSort, typeDescription, roleDescription";
			$resRoles = $db->query($q);
			$row = 0;
			While($dbRoles=mysqli_fetch_array($resRoles)) {
				$col = 0;
				$aScheduleTable[$row][$col] = $dbRoles["roleDescription"];
				for($i=0;$i<count($aServices);$i++) {
					$col++;				
					$key = $aServices[$i]["id"]."r".$dbRoles["roleID"];
					$mbrNames = "";
					$mbrIDs = "";
					if (isset($aSchedule[$key])) {
						for($m=0;$m<count($aSchedule[$key]);$m++) {
						$mbrNames .= $mbrNames!=""?"\n":"";
						$mbrNames .= $aSchedule[$key][$m]["name"];
						}
					}
					$aScheduleTable[$row][$col] = $mbrNames;
				}
				$row++;
			}
			$pdf->ezSetY(510);
			$pdf->ezTable($aScheduleTable,$aColHead,'',array("showLines"=>2,"showHeadings"=>1,"fontSize"=>9,"colGap"=>2,"rowGap"=>1));
			pageBottom($pdf);
		}
	}
}
?>