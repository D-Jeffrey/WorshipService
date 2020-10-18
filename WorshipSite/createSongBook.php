<?php
require('lr/config.php');
require('lr/functions.php'); 
//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Users) != "yes") { 
//	exit;
}


include 'vendor/autoload.php';
global $pageNumber;

include ('fnNicetime.php');
include ('fnPDFSongs.php');

error_reporting(0);

if(isset($_REQUEST["id"])) {
	$bookID = $_REQUEST["id"];
} else {
	exit;
}

$showChords = 1;

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

$aTOC[]= array();


$pageNumber = 3;

$pdf = new Cezpdf("letter","portrait");


// Loop through all songs in songbook
$q = "SELECT songID, bookTitle FROM songbook INNER JOIN songbooksongs ON songbook.bookID=songbooksongs.bookID WHERE songbook.bookID=$bookID ORDER BY songOrder";
$resSng = $db->query($q);
$notFirst = false;

// Output Book Cover page

$pdf->addJpegFromFile('images/PDF/SongBookCover.jpg',30,212);
$pdf->selectFont('./fonts/Helvetica-Bold');
$pdf->setColor(0,0,0);
$pdf->addTextWrap(30,600,24,$svcDescription,550, 0, "center");
$pdf->addTextWrap(30,100,18,"Printed on: ".date("l F j, Y"),550, 0,"center");
$tp = $pdf->newPage();
$pdf->setColor(0,0,0);
$pdf->newPage();



while($dbSng=mysqli_fetch_array($resSng)) {
	$svcDescription = $dbSng["bookTitle"];
	if($notFirst) {
		addNewPage($pdf,$svcDescription);
	} else {
		pageTop($pdf,$svcDescription);
	}
	outputSong($dbSng["songID"],$showChords, 0);
	$notFirst = true;
}
pageBottom($pdf);


// Output table of Contents
$pdf->newPage(true,$tp,"after");

pageTop($pdf,$svcDescription);
$pdf->setColor(0,0,0);
$pdf->selectFont('./fonts/Helvetica');
$pdf->addTextWrap(30,656,24,"Table Of Contents",0, 550,"center");
$ypos = 620;
for($i=0;$i<count($aTOC);$i++) {
	$j = $aTOC[$i]["id"];
	$pdf->addTextWrap(35,$ypos,10,"<c:ilink:toc$j>".$aTOC[$i]["song"]."</c:ilink>",500,0,"left");
	$pdf->addTextWrap(535,$ypos,10,"<c:ilink:toc$j>".$aTOC[$i]["page"] ."</c:ilink>",30,0,"right");
	
	$ypos -= 10;
}

// Output songbook text
$pdfcode = $pdf->output();

header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="songbook.pdf"');
echo $pdfcode; 


/*
function pageTop($pdf,$svcDescription) {
	$pdf->addJpegFromFile('images/PDF/banner.jpg',30,700);
	$pdf->selectFont('./fonts/Helvetica-Bold');
	$pdf->setColor(255,255,255);
	$pdf->addTextWrap(223,720,350,10,$svcDescription,"right");
	$pdf->setColor(0,0,0);
}

function pageBottom($pdf) {
	global $pageNumber;
	
	$pdf->selectFont('./fonts/Helvetica-Bold');
	$pdf->setColor(0,0,0);
	$pdf->addText(35,35,10,"Page: $pageNumber");
	$pageNumber++;
}

function addNewPage($pdf,$svcDescription) {
	pageBottom($pdf);
	$pdf->newPage();
	pageTop($pdf,$svcDescription);
}

function outputSong($songID,$showChords) {
	global $db, $pdf, $svcDescription, $pageNumber, $aTOC;

	/// Retrieve Song for specified id 
	$sql = "SELECT *,songLink as sLink FROM songs WHERE songs.songID=$songID";
	$resSong = $db->query($sql);
	if(!$resSong) exit;
	$dbSong=mysqli_fetch_array($resSong);
	$oldTxt = nl2br(str_replace("\r\n","<br />",$dbSong["songText"]));

	$chordsExist = strpos($oldTxt,"[")!==FALSE;

	// Add entry to Table of Contents
	$aTOC[] = array("page" => $pageNumber, "song" => $dbSong["songName"]." (".$dbSong["songArtist"].")");

	$pdf->selectFont('./fonts/Helvetica-Bold');
	$remTxt = $pdf->addTextWrap(35,685,520,14,$dbSong["songName"]." - ".$dbSong["songArtist"],"left");
	$pdf->addText(35,670,14,$remTxt);
	$pdf->addDestination("toc$pageNumber",'FitH',800);
	$pdf->selectFont('./fonts/Courier');
	// Prepare song text 
	$aSText = explode("<br />",nl2br(str_replace("\r\n","<br />",$oldTxt)));
	$cn = 1;
	$vn = 1;
	$tn = 1;
	$ypos = 656;
	for($i=0;$i<count($aSText);$i++) {
		$chordLen = 0;
		if(substr($aSText[$i],0,1)=="~") { // Song Part
			if($ypos<65) {
				addNewPage($pdf,$svcDescription);
				$pdf->selectFont('./fonts/Helvetica-Bold');
				$pdf->addText(35,685,14,"Song Name: ".$dbSong["songName"]." (Continued...)");
				$pdf->selectFont('./fonts/Courier');
				$ypos = 595;
			}
			$pdf->selectFont('./fonts/Helvetica-Bold');
			$ypos -= 5;
			$pdf->addText(35,$ypos,11,trim(substr($aSText[$i],1,200)));
			$pdf->selectFont('./fonts/Courier');
			$ypos -= 10;
			$vn++;
		} else { //  Song Text 
			$tmpText = rtrim(substr($aSText[$i],0,200));
			$songTemp = "";
			$chordTemp = "";
			$lineChords = 0;
			$j = 0;
			while($j<strlen($tmpText)) {
				if($tmpText[$j]==" ") {
					$songTemp .= " ";
					$chordTemp .= " ";
					$j++;
				} elseif($tmpText[$j]=="[") {
					$j++;
					while($tmpText[$j]!="]" && $j<strlen($tmpText)) {
						$chord = "";
						while($tmpText[$j]!="-" && $tmpText[$j]!="]" && $j<strlen($tmpText)) {
							$chord .= $tmpText[$j];
							$chordLen++;
							$j++;
						}
						$chordTemp .= $chord;
						$lineChords++;
						if($tmpText[$j]=="/" || $tmpText[$j]=="-") {
							$chordTemp .= $tmpText[$j];
							$chordLen++;
							$j++;
						}
					}
					if($tmpText[$j]=="]") {
						$j++;
					}
				} else {
					$songTemp .= $tmpText[$j];

					if($chordLen==0) {
						$chordTemp .= " ";
					} else {
						$chordLen--;
					}
					$j++;
				}
			}
			if($chordsExist && $showChords==1 && $lineChords>0) {
				$pdf->setColor(204,0,0);
				$pdf->addText(35,$ypos,10,$chordTemp);
				$pdf->setColor(0,0,0);
				$ypos -= 8;
			}
			$cn++;
			$pdf->addText(35,$ypos,10,$songTemp);
			$ypos -= 10;
			$tn++;
		}
		if($ypos<45) {
			addNewPage($pdf,$svcDescription);
			$pdf->selectFont('./fonts/Helvetica-Bold');
			$pdf->addText(35,640,14,"Song Name: ".$dbSong["songName"]." (Continued...)");
			$pdf->selectFont('./fonts/Courier');
			$ypos = 595;
		}
	}
	// Print Song copyright/ccli info
	$pdf->selectFont('./fonts/Times-Italic');
	$pdf->addTextWrap(30,35,550,10,"CCLI: ".$dbSong["songCCLI"],"center");
	$pdf->addTextWrap(30,22,550,10,"Copyright: ".$dbSong["songCopyright"],"center");
	
}

*/
?>