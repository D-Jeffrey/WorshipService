<?php
/*******************************************************************
 * dspSong.php
 * Display Song
 *******************************************************************/
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
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

include ('fnNicetime.php');

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Display Song', $_SERVER['REQUEST_URI'], 3);

$isAdmin = (allow_access(Administrators)=="yes");

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


if(isset($_POST["svcid"]) || isset($_REQUEST["sid"])) {
	$dspType = "Service";
} else {
	$dspType = "Song";
}
if(isset($_POST["sngid"])) {
	$serviceID = $_POST["svcid"];
	$songID = $_POST["sngid"];
	/* Retrieve Song for specified id */
	if($dspType == "Service") {
		$sql = "SELECT *,serviceorder.songLink as sLink FROM songs LEFT JOIN serviceorder ON songs.songID=serviceorder.songID AND serviceorder.serviceID=$serviceID WHERE songs.songID=$songID";
	} else {
		$sql = "SELECT *,songLink as sLink FROM songs WHERE songs.songID=$songID";
	}
	$resSong = $db->query($sql);
	$dbSong=mysqli_fetch_array($resSong);
	include_once("transpose2.php");
	$tp = new Transposer;
	$oldTxt = $tp->transpose(str_replace("\r\n","<br />",$dbSong["songText"]), $_POST["key"],$_POST["semitone"]);
} else {
	$songID = $_REQUEST["id"];
	$serviceID = $_REQUEST["sid"];
	/* Retrieve Song for specified id */
	if($dspType == "Service") {
		$sql = "SELECT *,serviceorder.songLink as sLink FROM songs LEFT JOIN serviceorder ON songs.songID=serviceorder.songID AND serviceorder.serviceID=$serviceID WHERE songs.songID=$songID";
	} else {
		$sql = "SELECT *,songLink as sLink FROM songs WHERE songs.songID=$songID";
	}
	$resSong = $db->query($sql);
	$dbSong=mysqli_fetch_array($resSong);
	
	if(!isset($_POST["songText"])) {
		$oldTxt = nl2br(str_replace("\r\n","<br />",$dbSong["songText"]));
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Calendar (Display Song)</title>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/overlibmws/overlibmws.js"></script>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/overlibmws/overlibmws_exclusive.js"></script>

<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/overlibmws/overlibmws_shadow.js"></script>
<script>
function showChordHover(img) {
	overlib("<div style='padding:2px;background-color:#ffffff'><img src='"+img+"'></div>", FGCOLOR, "", BGCOLOR,'#ffffff', BORDER, 1, BGCOLOR, '#6A6868', SHADOW, SHADOWCOLOR,'#000000', WRAP, HAUTO, VAUTO);
}
function changeKey() {
	document.frm.submit();
}
</script>
<?php

/* Retrieve Service for specified id */
if($dspType == "Service") {
	$sql = "SELECT *, date_format(svcPractice, '%W %M %D') as svcPDATE, date_format(svcPractice, '%H:%i') as svcPTIME, date_format(svcDateTime, '%W %M %D') as svcDATE, date_format(svcDateTime, '%H:%i') as svcTIME FROM services WHERE serviceID=$serviceID";
	$resSVC = $db->query($sql);
	$dbSvc=mysqli_fetch_array($resSVC);
}
$hlpID = $isAdmin?17:8;
$title = "Song Details";
include("header.php");

if($dspType == "Service") {
	echo "<table class='songhead'>\n";
	echo "<tr><td colspan='2' style='border-bottom:2px ridge;'>".$dbSvc["svcDescription"]." on ".$dbSvc["svcDATE"]." at ".nicetime($dbSvc["svcTIME"])."</td></tr>\n";
	echo "</table>\n";
}
echo "<table class='songhead'>\n";
echo "	<tr><td width='90'>Song Title: </td><td>".$dbSong["songName"]."</td></tr>";
echo "	<tr><td width='90'>Artist: </td><td>".$dbSong["songArtist"]."</td></tr>";
$cpyr = strlen($dbSong["songCopyright"])>50?substr($dbSong["songCopyright"],0,50)."...":$dbSong["songCopyright"];
echo "	<tr><td width='90'>Copyright: </td><td title='".$dbSong["songCopyright"]."'>$cpyr</td></tr>";
echo "	<tr><td width='90'>CCLI #: </td><td>".$dbSong["songCCLI"]."</td></tr>\n";
echo "</table>\n";
echo "<form style='margin-top:0px;border-bottom:2px ridge;border-top:2px ridge;' name=\"frm\" action=\"dspSong.php\" method=\"post\"><table width='100%'><tr><td><input type='button' onClick='setChord();' value='Show/Hide Chords'>\n";

echo "&nbsp;&nbsp;&nbsp;Semi-tone adjust:&nbsp;<select id=\"selKey\" name=\"semitone\" onChange=\"changeKey();\">\n";
for($i=11;$i>0;$i--) {
	$sel = $_POST["semitone"]==$i?" selected":"";
	echo "<option value=\"$i\"$sel>$i</option>\n";
}
$sel = $_POST["semitone"]==0||!isset($_POST["semitone"])?" selected":"";
echo "<option value=\"0\"$sel>--</option>\n";
for($i=-1;$i>-12;$i--) {
	$sel = $_POST["semitone"]==$i?" selected":"";
	echo "<option value=\"$i\"$sel>$i</option>\n";
}
echo "</select>\n";

echo "<input type='hidden' name='key' value='C'>\n";
if($dspType == "Service") {
	echo "<input type='hidden' name='svcid' value='$serviceID'>\n";
}
echo "<input type='hidden' name='sngid' value='$songID'>\n";
if($dspType == "Service") {
	echo "</td><td align='right'><input name=\"back\" type=\"button\" value=\"Back\" onClick=\"document.location='dspService.php?id=$serviceID';\" class=\"button\"></td></tr></table></form>\n";
} else {
	echo "</td><td align='right'><input name=\"back\" type=\"button\" value=\"Back\" onClick=\"document.location='dspSongs.php';\" class=\"button\"></td></tr></table></form>\n";
}
echo "<table align='center' width='100%'><tr><td><div style='height:350px;overflow:auto;'><table width='100%' align='center' class='songdetails' cellpadding='0' cellspacing='0'>\n";
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
					while($tmpText[$j]!=" " && $tmpText[$j]!="-" && $tmpText[$j]!="]" && $j<strlen($tmpText)) {
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
		$aNewSText[] = "	<tr class='chords' id='chordline$cn'><td>$chordTemp</td></tr>\n";
		$cn++;
		$aNewSText[] = "	<tr id='songtext'><td>".$songTemp."</td></tr>\n";
		$tn++;
	}
}

$sText = implode("",$aNewSText);

echo $sText;
if($dbSong["sLink"]!="") {
	$sLink = "<object width=\"425\" height=\"344\"><param name=\"movie\" value=\"".$dbSong["sLink"]."\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"always\"></param><embed src=\"".$dbSong["sLink"]."\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"425\" height=\"344\"></embed></object>";
}
echo "</table></div></td><td width='425' valign='top'>$sLink</td></tr></table>\n";

if($dspType == "Service") {
	echo "<form><input type='button' onClick='setChord();' value='Show/Hide Chords'><input name=\"back\" type=\"button\" value=\"Back\" onClick=\"document.location='dspService.php?id=$serviceID';\" class=\"button\"></form>\n";
} else {
	echo "<form><input type='button' onClick='setChord();' value='Show/Hide Chords'><input name=\"back\" type=\"button\" value=\"Back\" onClick=\"document.location='dspSongs.php';\" class=\"button\"></form>\n";
}
 
echo "<script>\n";
echo "function setChord() {\n";
for($i=1;$i<$cn;$i++) {
	echo "	var row = document.getElementById(\"chordline$i\");\n";
	echo "	if (row.style.display == '')\n";
	echo "		row.style.display = 'none';\n";
	echo "	else\n";
	echo "		row.style.display = '';\n";
}
echo "}\n";
echo "</script>\n";

echo "</body>\n</html>\n";



?>
