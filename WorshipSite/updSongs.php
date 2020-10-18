<?php
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

require('lr/config.php');
require('lr/functions.php'); 
//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Users) != "yes") { 
	exit;
}

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

$songID = $_REQUEST["songID"];
$showChords = $_REQUEST["chords"];
/* Retrieve Song for specified id */
$sql = "SELECT *,songLink as sLink FROM songs WHERE songs.songID=$songID";
$resSong = $db->query($sql);
$dbSong=mysqli_fetch_array($resSong);
$oldTxt = nl2br(str_replace("\r\n","<br />",$dbSong["songText"]));

$chordsExist = strpos($oldTxt,"[")!==FALSE;

echo "<table class='songhead'>\n";
echo "	<tr><td width='90'>Song Title: </td><td>".$dbSong["songName"]."</td></tr>";
echo "	<tr><td width='90'>Artist: </td><td>".$dbSong["songArtist"]."</td></tr>";
$cpyr = strlen($dbSong["songCopyright"])>50?substr($dbSong["songCopyright"],0,50)."...":$dbSong["songCopyright"];
echo "	<tr><td width='90'>Copyright: </td><td title='".$dbSong["songCopyright"]."'>$cpyr</td></tr>";
echo "	<tr><td width='90'>CCLI #: </td><td>".$dbSong["songCCLI"]."</td></tr>\n";
echo "</table>\n";
echo "<table align='left' cellpadding='0' cellspacing='0'>\n";
/* Prepare song text */
$aSText = explode("<br />",nl2br(str_replace("\r\n","<br />",$oldTxt)));
$aNewSText = array();
$cn = 1;
$vn = 1;
$tn = 1;
for($i=0;$i<count($aSText);$i++) {
	$chordLen = 0;
	if(substr($aSText[$i],0,1)=="~") { /* Song Part */
		$aNewSText[] = "	<tr class='versehead' id='versehead$vn'><td><br />".trim(substr($aSText[$i],1,200))."</td></tr>\n";
		$vn++;
	} else { /* Song Text */
		$tmpText = rtrim(substr($aSText[$i],0,200));
		$songTemp = "";
		$chordTemp = "";
		$j = 0;
		$lineChords = 0;
		while($j<strlen($tmpText)) {
			if($tmpText[$j]==" ") {
				$songTemp .= "&nbsp;";
				$chordTemp .= "&nbsp;";
				$j++;
			} elseif($tmpText[$j]=="[") {
				$j++;
				while($tmpText[$j]!="]" && $j<strlen($tmpText)) {
					$chordTemp .= "<a href='#'";
					$chord = "";
					while($tmpText[$j]!="-" && $tmpText[$j]!="]" && $j<strlen($tmpText)) {
						$chord .= $tmpText[$j];
						$chordLen++;
						$j++;
					}
					$lineChords++;
					$chordTemp .= ">$chord</a>";
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
					$chordTemp .= "&nbsp;";
				} else {
					$chordLen--;
				}
				$j++;
			}
		}
		if($chordsExist && $showChords==1 && $lineChords>0) $aNewSText[] = "	<tr class='chords' id='chordline$cn'><td>$chordTemp</td></tr>\n";
		$cn++;
		$aNewSText[] = "	<tr id='songtext'><td>".$songTemp."</td></tr>\n";
		$tn++;
	}
}

$sText = implode("",$aNewSText);

echo $sText;
echo "</table>\n";
?>