<?php
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
session_start();


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

require($_SERVER["DOCUMENT_ROOT"].'/lr/config.php');
require('lr/functions.php'); 
require($baseDir.'/classes/PhpSpreadsheet/Bootstrap.php');



if (allow_access(Users) != "yes") { 
	exit;
}

$action = $_REQUEST["act"];

if($action=="sel") {
	echo "<html><head>\n";
	echo "<link rel=\"stylesheet\" href=\"{$baseFolder}css/tw.css\" type=\"text/css\"></head>\n";
	echo "<body><form name='frmSel' action='expMySchedule.php?&act=out' method='post' onSubmit='parent.window.hs.close();'>\n";
	echo "<b>Select Export Format</b><br />\n";
	echo "<table><tr><td colspan='2'><input type='radio' name='fmtOut' value='xls' checked />&nbsp;Excel</td></tr>\n";
	echo "<tr bgcolor='#ebebeb'><td nowrap><input type='radio' name='fmtOut' value='ics' />&nbsp;iCalendar</td><td style='text-align:center;font-size:9pt;' rowspan='2'>For import to calendar programs (Outlook/iCal/Entourage...)</td></tr>\n";
	echo "<tr bgcolor='#ebebeb'><td nowrap><input type='radio' name='fmtOut' value='vcs' />&nbsp;vCalendar</td></tr>\n";
	echo "</table>\n";
	echo "<p align='center'><input type='submit' name='save' value='Submit' />&nbsp;<input type='button' name='cancel' value='Cancel' onClick='parent.window.hs.close();' /></p>\n";
	echo "</form></body></html>\n";
	exit;
}

$outFormat = $_POST["fmtOut"];

$memberID = isset($_REQUEST["id"])?$_REQUEST["id"]:$_SESSION['user_id'];

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


if($outFormat=="xls") {
	expExcel();
} else {
	expCalendar($outFormat);
}

function expCalendar($fmt) {
	global $db, $memberID;

	// Initialize headers
	header("Content-Type: text/x-vCalendar");
	if($fmt=="ics") {
		header("Content-Disposition: inline; filename=\"MyvCalFile.ics\"");
	} else {
		header("Content-Disposition: inline; filename=\"MyvCalFile.vcs\"");
	}
	
	// Begin schedule export
	echo "BEGIN:VCALENDAR\n";
	echo $fmt=="ics"?"VERSION:2.0\n":"VERSION:1.0\n";
	
	$q="SELECT services.serviceID as svcID, svcTeamNotes, date_format(svcPractice, '%W %M %D') as svcPDATE, date_format(svcPractice, '%H:%i') as svcPTIME, date_format(svcDateTime, '%a %b %D') as svcDATE, date_format(svcDateTime, '%H:%i') as svcTIME,roleIcon,roleDescription FROM services INNER JOIN serviceteam ON services.serviceID = serviceteam.serviceID INNER JOIN roles on serviceteam.roleID = roles.roleID WHERE (serviceteam.memberID = $memberID OR concat(',',serviceteam.memberID,',') LIKE concat('%,','".$_SESSION['groups']."',',%')) AND svcDateTime>='".date("Y-m-d")."' ORDER BY svcDateTime, roleDescription";
	$resSched = $db->query($q);
	// Retrieve service Information
	$oldSvcID = 0;
	while($row=mysqli_fetch_array($resSched)){
		if($row["svcID"]!=$oldSvcID) {
			$aSvcDesc = genServiceDesc($row["svcID"],false);
			$oldSvcID = $row["svcID"];
			// Generate calendar output
			expiCalendar($aSvcDesc,$_POST["fmtOut"]);
		}
	}
	
	// End schedule export
	echo "END:VCALENDAR\n";
}

// Export Calendar in ics/vcs format
function expiCalendar($aSvcDesc,$fmt) {
	$strDateTime = date("Ymd\THis",strtotime($aSvcDesc[1]));
	$endDateTime = date("Ymd\THis",strtotime($aSvcDesc[1])+3600);
	echo "BEGIN:VEVENT\n";
	echo "DTSTART:$strDateTime\n";
	echo "DTEND:$endDateTime\n";
	echo "LOCATION:South Calgary Community Church\n";
	echo "SUMMARY:".$aSvcDesc[0]."\n";
	echo "DESCRIPTION;ENCODING=QUOTED-PRINTABLE:".$aSvcDesc[2]."\n";
	echo "END:VEVENT\n";
}

// Build message Body
function genServiceDesc($serviceID, $isXLSX) { 
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


// Export Excel listing of schedule

function expExcel() {
	global $siteTitle, $db, $memberID;
	


	// Create new Spreadsheet object
	$spreadsheet = new Spreadsheet();
	$spreadsheet = new Spreadsheet();

	// Set document properties
	$spreadsheet->getProperties()->setCreator("SCCC - Worship")
	->setLastModifiedBy("SCCC - Worship")
	->setTitle("Service Schedule")
	// ->setSubject("Office 2007 XLSX Test Document")
	->setDescription("Service Schedule For ".$_SESSION['first_name']." ".$_SESSION['last_name']);
	// ->setKeywords("office 2007 openxml php")
	//->setCategory("Test result file")



		// Rename worksheet
	$spreadsheet->getActiveSheet()->setTitle('Schedule');
	$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(40);
	$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(21);
	$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(21);
	$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
	
	$spreadsheet->getActiveSheet()->getStyle('A1:D2')->getFill()
	->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
	$spreadsheet->getActiveSheet()->getStyle('A1:D2')->getFill()->getStartColor()->setARGB('7F0070C0');
	
	$spreadsheet->getActiveSheet()->getStyle('A1:D2')->getFont()->getColor()
	->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
	
	



	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$spreadsheet->setActiveSheetIndex(0);
	
	

	
	// Get data records from table. 
	$q="SELECT svcDescription as Description, svcDateTime as 'Service Time',svcPractice as 'Practice Time',roleDescription as Role FROM services INNER JOIN serviceteam ON services.serviceID = serviceteam.serviceID INNER JOIN roles on serviceteam.roleID = roles.roleID WHERE (serviceteam.memberID = $memberID OR concat(',',serviceteam.memberID,',') LIKE concat('%,','".$_SESSION['groups']."',',%')) AND svcDateTime>='".date("Y-m-d")."' ORDER BY svcDateTime, roleDescription";
//	$q="SELECT svcDescription as Description, svcDateTime as DateTime,svcPractice as Practice,roleDescription as Role FROM services INNER JOIN serviceteam ON services.serviceID = serviceteam.serviceID INNER JOIN roles on serviceteam.roleID = roles.roleID WHERE serviceteam.memberID = $memberID AND svcDateTime>='".date("Y-m-d")."' ORDER BY svcDateTime, roleDescription";
	$resSched = $db->query($q);
	
	/*
	Make a top line on your excel sheet at line 1 (starting at 0).
	The first number is the row number and the second number is the column, both are start at '0'
	*/

	$spreadsheet->setActiveSheetIndex(0)->setCellValue('A1', 
		"$siteTitle: Service Schedule For ".$_SESSION['first_name']." ".$_SESSION['last_name']);

	
	$excelDateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel( time() );
	$spreadsheet->setActiveSheetIndex(0)->setCellValue('D1', $excelDateValue);
	$spreadsheet->getActiveSheet()->getStyle('D1')->getNumberFormat()
			->setFormatCode('mmm d, yyyyy');


		
	$spreadsheet->getActiveSheet()->mergeCells('A1:C1');
	$spreadsheet->getActiveSheet()->getStyle('A1:C1')->getFont()->setSize(16); 
		
	// Make column labels. (at line 2)
	$count = mysqli_num_fields($resSched);
	
	
	for ($i = 0; $i < $count; $i++) {
		$qr = $resSched->fetch_field_direct($i);
		$spreadsheet->setActiveSheetIndex(0)->setCellValue(chr(65+$i)."2",$qr->name);
		$isDateFld = strstr( $qr->name, 'Time');
		if ($isDateFld == 'Time' ) {
			$cellDate[$i] = true;
		} else {
			$cellDate[$i] = false;
			}
		
	}
	
	$xlsRow = 3;
	// Put data records from mysql by while loop.
	while ($row=mysqli_fetch_array($resSched)) {
		for ($i = 0; $i < $count; $i++) {
			$cell = chr(65+$i) . $xlsRow;
			$spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $row[$i]);
			// if it is one of our dates, then set and format it differently
			if ($cellDate[$i]) {
				$excelDateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel( $row[$i] );
				$spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $excelDateValue);
				$spreadsheet->getActiveSheet()->getStyle($cell)
				->getNumberFormat()
				->setFormatCode('mmm d, yyyyy h:mm AM/PM');


				}
			}
			
		
			if ($xlsRow % 2 == 0) {
//			$spreadsheet->getActiveSheet()->getStyle($cell)->getFill()
//			->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_PATTERN_LIGHTGRAY);
				$spreadsheet->getActiveSheet()->getStyle("A$xlsRow:D$xlsRow")->getFill()
					->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
				$spreadsheet->getActiveSheet()->getStyle("A$xlsRow:D$xlsRow")->getFill()->getStartColor()
					->setARGB('0085DFFF');
				
		
			}
			$xlsRow++;
		} 
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header("Content-Disposition: attachment;filename=\"mySchedule".date("Ymd").".xlsx\" ");
	// If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');

	// If you're serving to IE over SSL, then the following may be needed
	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header ('Pragma: public'); // HTTP/1.0

	$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
	$objWriter->save('php://output');
	// PHPExcel_Settings::setZipClass(PHPExcel_Settings::ZIPARCHIVE);

}
?>
