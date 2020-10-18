<?php
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Disposition: attachment;filename=\"songrating".date("Ymd").".xls\"");
header("Content-Transfer-Encoding: binary ");
session_start();

require($_SERVER["DOCUMENT_ROOT"].'/lr/config.php');
require('lr/functions.php'); 
if (allow_access(Coordinator) != "yes") { 
	exit;
}

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Get data records from table. 
$q="select songName,songArtist,songAlbum,songLink,concat(d.mbrFirstName,' ',d.mbrLastName) AS AddedBy,concat(c.mbrFirstName,' ',c.mbrLastName) AS memberName,ratingLyrics,ratingMusic,ratingSing,ratingOverall FROM songrating a INNER JOIN songratingbymember b ON a.songID=b.songID INNER JOIN members c ON b.memberID=c.memberID INNER JOIN members d ON a.addedBy=d.memberID ORDER BY songName, memberName";
$resSongs = $db->query($q);
xlsBOF();
/*
Make a top line on your excel sheet at line 1 (starting at 0).
The first number is the row number and the second number is the column, both are start at '0'
*/
xlsWriteLabel(0,0,"New Song Rating as of ".date("l, F j, Y"));
// Make column labels. (at line 2)
$count = mysqli_num_fields($resSongs);
for ($i = 0; $i < $count; $i++) {
	xlsWriteLabel(1,$i,mysqli_fetch_field_direct ($resSongs, $i));
}
$xlsRow = 2;
// Put data records from mysql by while loop.
while($row=mysqli_fetch_array($resSongs)){
	for ($i = 0; $i < $count; $i++) {
		if(is_numeric($row[$i])) {
			xlsWriteNumber($xlsRow,$i,$row[$i]);
		} else {
			xlsWriteLabel($xlsRow,$i,$row[$i]);
		}
	}
	$xlsRow++;
} 
xlsEOF();


// Functions for export to excel.
function xlsBOF() { 
	echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0); 
	return; 
} 
function xlsEOF() { 
	echo pack("ss", 0x0A, 0x00); 
	return; 
} 
function xlsWriteNumber($Row, $Col, $Value) { 
	echo pack("sssss", 0x203, 14, $Row, $Col, 0x0); 
	echo pack("d", $Value); 
return; 
} 
function xlsWriteLabel($Row, $Col, $Value ) { 
	$L = strlen($Value); 
	echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L); 
	echo $Value; 
	return; 
} 
?>
