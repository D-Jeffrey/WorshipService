<?php


function pageTop($pdf, $svcDescription)
{

	$pdf->addJpegFromFile('images/PDF/banner.jpg',30,700);

	$pdf->selectFont('Helvetica-Bold');
	$pdf->setColor(255,255,255);
	$pdf->addTextWrap(223,720,14,$svcDescription,350,0, "right");

	$pdf->setColor(0,0,0);
}

function pageBottom($pdf)
{
	global $pageNumber;

	

	$pdf->selectFont('Helvetica-Bold');
	$pdf->setColor(0,0,0);
	$pdf->addText(300,35,10,"Page: " . $pageNumber);
}

function addNewPage($pdf, $svcDescription)
{
	global $pageNumber;
	
	pageBottom($pdf);
	$pdf->newPage();
	$pageNumber++;
	pageTop($pdf,$svcDescription);
}

function outputSong($songID, $showChords, $svcSong)
{
	global $db, $pdf, $svcDescription, $serviceID,  $aTOC, $pageNumber;

	$topOfPage = 656;

/* Retrieve Song for specified id */
	if ($svcSong) {
		$sql = "SELECT *,serviceorder.songLink as sLink, orderDetails AS songLyrics FROM serviceorder INNER JOIN songs ON serviceorder.songID=songs.songID WHERE serviceID=$serviceID AND orderID=$songID";
	} else {
		$sql = "SELECT *,songLink as sLink,songText AS songLyrics FROM songs WHERE songs.songID=$songID";
	}
	$resSong = $db->query($sql);
	if (!$resSong)
		exit;
	$dbSong=mysqli_fetch_array($resSong);
	$oldTxt = nl2br(str_replace("\r\n","<br />",$dbSong["songLyrics"]));

	$chordsExist = strpos($oldTxt,"[")!==FALSE;

	// Add entry to Table of Contents
	$aTOC[] = array("page" => ($pageNumber ), "song" => $dbSong["songName"]." (".$dbSong["songArtist"].")", "id"=> $songID);

	$pdf->selectFont('Helvetica-Bold');
	$remTxt = $pdf->addTextWrap(35,685,14,$dbSong["songName"]." - ".$dbSong["songArtist"],520,0, "left");
	$pdf->addText(35,670,14,$remTxt);
	$pdf->addDestination("toc$songID",'FitH',800);
	$pdf->selectFont('Courier');
/* Prepare song text */
	$aSText = explode("<br />",nl2br(str_replace("\r\n","<br />",$oldTxt)));
	$cn = 1;
	$vn = 1;
	$tn = 1;
	$ypos = $topOfPage;
	for ($i=0;$i<count($aSText);$i++) {
		$chordLen = 0;
		if (substr($aSText[$i],0,1)=="~") {
			 /* Song Part */
			if ($ypos<55) {
				addNewPage($pdf,$svcDescription);
				$pdf->selectFont('Helvetica-Bold');
				$pdf->addText(35,670,14,$dbSong["songName"]." (Continued...)");
				$pdf->selectFont('Courier');
				$ypos = $topOfPage;
			}
			$pdf->selectFont('Helvetica-Bold');
			$ypos -= 5;
			$pdf->addText(35,$ypos,11,trim(substr($aSText[$i],1,200)));
			$pdf->selectFont('Courier');
			$ypos -= 10;
			$vn++;
		} else {
			 /* Song Text */
			$tmpText = rtrim(substr($aSText[$i],0,200));
			$songTemp = "";
			$chordTemp = "";
			$lineChords = 0;
			$j = 0;
			while ($j<strlen($tmpText)) {
				if ($tmpText[$j]==" ") {
					$songTemp .= " ";
					$chordTemp .= " ";
					$j++;
				} elseif($tmpText[$j]=="[") {
					$j++;
					while ($tmpText[$j]!="]" && $j<strlen($tmpText)) {
						$chord = "";
						while ($tmpText[$j]!="-" && $tmpText[$j]!="]" && $j<strlen($tmpText)) {
							$chord .= $tmpText[$j];
							$chordLen++;
							$j++;
						}
						$chordTemp .= $chord;
						$lineChords++;
						if ($tmpText[$j]=="/" || $tmpText[$j]=="-") {
							$chordTemp .= $tmpText[$j];
							$chordLen++;
							$j++;
						}
					}
					if ($tmpText[$j]=="]") {
						$j++;
					}
				} else {
					$songTemp .= $tmpText[$j];

					if ($chordLen==0) {
						$chordTemp .= " ";
					} else {
						$chordLen--;
					}
					$j++;
				}
			}
			if ($chordsExist && $showChords==1 && $lineChords>0) {
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
		if ($ypos<55) {
			//TODO fixed page overflow when this fires
			addNewPage($pdf,$svcDescription);
			$pdf->selectFont('Helvetica-Bold');
			$pdf->addText(35,670,14,$dbSong["songName"]." (Continued....)");
			$pdf->selectFont('Courier');
			$ypos = $topOfPage;
		}
	}
	// Print Song copyright/ccli info
	$pdf->selectFont('Times-Italic');
	$pdf->addTextWrap(30,35,10,"CCLI: ".$dbSong["songCCLI"], 550,0,"center");
	$pdf->addTextWrap(30,22,10,"Copyright: ".$dbSong["songCopyright"],550,0,"center");

	return $dbSong["songName"];
}
?>