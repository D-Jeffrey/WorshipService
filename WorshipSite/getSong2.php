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
$oldTxt = nl2br(str_replace("\r\n","<br />",$_POST["rawSong"]));

$chordsExist = strpos($oldTxt,"[")!==FALSE;

echo "<table align='center' style='border:0px' class='songdetails' cellpadding='0' cellspacing='0'>\n";
/* Prepare song text */
$aSText = explode("<br />",str_replace("\n","",stripslashes($oldTxt)));
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
		$lineChordsExist = strpos($tmpText,"[")!==FALSE;
		$j = 0;
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
					// Build chord image file name
					$chordName = str_replace("#","sharp",$chord);
					$chordName = str_replace("/","slash",$chordName);
					$chordName = str_replace("+","plus",$chordName);
					$chordName = str_replace("(","",$chordName);
					$chordName = str_replace(")","",$chordName);
					$chordName .= "chord.png";
					$chordTemp .= "onMouseOver = \"showChordHover('images/chords/$chordName');\" onMouseOut=\"nd();\"";
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
		if($chordsExist && $lineChordsExist) $aNewSText[] = "	<tr class='chords' id='chordline$cn'><td>$chordTemp</td></tr>\n";
		$cn++;
		$aNewSText[] = "	<tr id='songtext'><td>".$songTemp."</td></tr>\n";
		$tn++;
	}
}

$sText = implode("",$aNewSText);

echo $sText;
echo "</table>\n";
$ce = $chordsExist?"true":"false";
echo "<input type='hidden' name='chordsExist' value='$ce'>\n";
?>