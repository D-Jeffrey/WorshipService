<?php
/*******************************************************************
 * editSong.php
 * Edit Song Information
 *******************************************************************/
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

require('lr/config.php');
require($baseDir.'/lr/functions.php'); 
//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Users) != "yes") { 
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


if(isset($_POST["sngact"])) {
	$sngact = $_POST["sngact"];
	$songID = $_POST["songID"];
	$pageNum = $_POST["pageNum"];
	$srchDesc = isset($_POST["srchDesc"])?$_POST["srchDesc"]:0;
	$txtSearch = $_POST["txtSearch"];
} else {
	echo "Invalid Request.";
	exit;
}

$errMsg = "";

// Delete Song
if($sngact=="del") {
    $q = "SELECT svcDateTime FROM serviceorder INNER JOIN services ON serviceorder.serviceID=services.serviceID WHERE songID=$songID ORDER BY svcDateTime";
    $songRes = $db->query($q);
    if($songRes && mysqli_num_rows($songRes)>0) {
        $errMsg = "Cannot delete. Song is defined in the following services:\\n";
        while($dbSong=mysqli_fetch_array($songRes)) {
            $errMsg .= "    ".$dbSong["svcDateTime"]."\\n";
        }
    } else {
	   $q = "DELETE FROM songs WHERE songID=".$songID;
	   $songRes = $db->query($q);
	}
}

// Save changes
if(isset($_POST["save"])) {
	$baseText = stripSong($_POST["rawSong"]);


	if($sngact=="add") {
		$q = "INSERT INTO songs VALUES(0,'".$db->real_escape_string($_POST["songName"])."','".$db->real_escape_string($_POST["songArtist"])."','".$db->real_escape_string($_POST["rawSong"])."','".$_POST["songCCLI"]."','".$db->real_escape_string($_POST["songCopyright"])."','".$db->real_escape_string($_POST["songLink"])."','".$db->real_escape_string($_POST["iTunesLink"])."','.$db->real_escape_string($baseText).')";
	} else {
		$q = "UPDATE songs SET songName='".$db->real_escape_string($_POST["songName"])."',songArtist='".$db->real_escape_string($_POST["songArtist"])."',songText='".$db->real_escape_string($_POST["rawSong"])."',songCCLI='".$_POST["songCCLI"]."',songCopyright='".$db->real_escape_string($_POST["songCopyright"])."',songLink='".$db->real_escape_string($_POST["songLink"])."',iTunesLink='".$db->real_escape_string($_POST["iTunesLink"])."',baseText='.$db->real_escape_string($baseText).' WHERE songID=$songID";
	}
	$resSong = $db->query($q);
	$sngact = 'saved';
}

if($sngact=="edit") {
	/* Retrieve Song for specified id */
	$sql = "SELECT *,songLink as sLink FROM songs WHERE songs.songID=$songID";
	$resSong = $db->query($sql);
	$dbSong=mysqli_fetch_array($resSong);
	$rawTxt = $dbSong["songText"];
	$songName = htmlentities($dbSong["songName"],ENT_QUOTES);
	$songArtist = htmlentities($dbSong["songArtist"],ENT_QUOTES);
	$songCCLI = $dbSong["songCCLI"];
	$songCopyright = htmlentities($dbSong["songCopyright"],ENT_QUOTES);
	$songLink = $dbSong["songLink"];
	$iTunesLink = $dbSong["iTunesLink"];
} else if($sngact=="chgKey") {
	/* Retrieve Song for specified id */
	$sql = "SELECT *,songLink as sLink FROM songs WHERE songs.songID=$songID";
	$resSong = $db->query($sql);
	$dbSong=mysqli_fetch_array($resSong);
	include_once("transpose2.php");
	$tp = new Transposer;
	$rawTxt = $tp->transpose(str_replace("\r\n","<br />",$dbSong["songText"]), $_POST["key"],$_POST["semitone"]);
	$songName = htmlentities($dbSong["songName"],ENT_QUOTES);
	$songArtist = htmlentities($dbSong["songArtist"],ENT_QUOTES);
	$songCCLI = $dbSong["songCCLI"];
	$songCopyright = htmlentities($dbSong["songCopyright"],ENT_QUOTES);
	$songLink = $dbSong["songLink"];
	$iTunesLink = $dbSong["iTunesLink"];
	$sngact = "edit";
} else if($sngact=="add") {
	$rawTxt = "";
	$songName = "";
	$songArtist = "";
	$songCCLI = "";
	$songCopyright = "";
	$songLink = "";
    $iTunesLink = "";
} else {
	// Return to song list
	echo "<html><head>\n";
	echo "</head><body onLoad='document.frmSong.submit();'>\n";
	echo "<form style='margin:0px;' name=\"frmSong\" action=\"listSongs.php\"method=\"post\">\n";
	echo "<input type='hidden' name='pageNum' id='pageNum' value=$pageNum>\n";
	echo "<input type='hidden' name='srchDesc' id='srchDesc' value=$srchDesc>\n";
	echo "<input type='hidden' name='txtSearch' id='txtSearch' value='$txtSearch'>\n";
	echo "<input type='hidden' name='errMsg' id='txtSearch' value='$errMsg'>\n";
	echo "</form>\n";
	exit;
}

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Edit Song', $_SERVER['REQUEST_URI'], 3);

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
	document.frmSong.sngact.value = "chgKey";
	document.frmSong.submit();
}
</script>
<script type="text/javascript" src="scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript">
function songText() {
	var url = 'getSong2.php';

	var myAjax = new Ajax.Updater(
		'songDiv', 
		url, {
			method: 'post', 
			onComplete: chkChords,
			parameters: {rawSong: document.frmSong.rawSong.value}
		});
}

function chkChords() {
	if(document.frmSong.chordsExist.value=="true") {
		document.frmSong.semitone.disabled = false;
	} else {
		document.frmSong.semitone.disabled = true;
	}
}

function extractLink() {
	var lnk = document.frmSong.songLink.value;
	var sp = lnk.indexOf("http");
	var ep = lnk.indexOf('"',sp);
	if(sp > 0 && ep > sp) {
		lnk = lnk.substring(sp,ep);
	}
	lnk = lnk.replace("embed","v");
	document.frmSong.songLink.value = lnk;
	slnk = lnk.replace("/v/","/embed/");
	document.getElementById("aSongLink").href = slnk;
}

function valSong() {
	var frmSong = document.frmSong;
	if(frmSong.songName.value=="") {
		alert("Please enter a value for the Song Title");
		frmSong.songName.focus();
		return false;
	}
	return true;
}

function cancelEdit() {
	document.frmSong.action = "listSongs.php";
	document.frmSong.sngact.value='cancel';
	document.frmSong.submit();
}
</script>
<link rel="stylesheet" type="text/css" href="<?php echo $baseFolder; ?>scripts/highslide/highslide.css" />
<?php

/* Retrieve Service for specified id */
$hlpID = 17;
$title = "Edit Song Details";
include("header.php");

/* Prepare song text */
$noChordsExist = strpos($rawTxt,"[")===FALSE;

$mand = "<span style='font-weight:bold;color:#cc0000;'>*</span>";
echo "<form style='margin-top:0px;border-bottom:2px ridge;border-top:2px ridge;' name=\"frmSong\" action=\"editSong.php\" onSubmit=\"valSong();\" method=\"post\">\n";
echo "<input type='hidden' name='sngact' value='$sngact'>\n";
echo "<input type='hidden' name='pageNum' id='pageNum' value=$pageNum>\n";
echo "<input type='hidden' name='srchDesc' id='srchDesc' value=$srchDesc>\n";
echo "<input type='hidden' name='txtSearch' id='txtSearch' value='$txtSearch'>\n";
echo "<table class='songhead'>\n";
echo "	<tr><td width='160'>Song Title:$mand </td><td><input type='text' name='songName' size='80' maxlength='100' value='$songName' /></td></tr>";
echo "	<tr><td width='160'>Artist: </td><td><input type='text' name='songArtist' size='80' maxlength='100' value='$songArtist' /></td></tr>";
echo "	<tr><td width='160'>Copyright: </td><td><input type='text' name='songCopyright' size='80' maxlength='100' value='$songCopyright' /></td></tr>";
echo "	<tr><td width='160'>CCLI #: </td><td><input type='text' name='songCCLI' size='12' maxlength='10' value='$songCCLI' /></td></tr>\n";
echo "	<tr><td width='160'>Embed Link: </td><td><input title='Paste embed link from a video serving site' type='text' name='songLink' size='80' maxlength='255' value='$songLink' onChange=\"extractLink();\" />\n";
echo "		<a title='Play Music Video' id=\"aSongLink\" href=\"$songLink\" onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', Height: 482, width: 600  } )\"><img src='images/play.png' border='0' alt='Play in new window'></a></td></tr>\n";
echo "	<tr><td width='160'>iTunes Store Link: </td><td><input title='Paste iTunes Store link for this song' type='text' name='iTunesLink' size='80' value='$iTunesLink' />\n";
echo "		<a title='Display Link Maker to build the link' id=\"aiTunesLink\" href=\"https://linkmaker.itunes.apple.com/en-us\" target=\"_blank\">Build iTunes Link</a></td></tr>\n";
echo "</table>\n";
echo "<table width='100%'><tr><td><input type='button' onClick='setChord();' value='Show/Hide Chords'>\n";
$dis = $noChordsExist?" disabled":"";
echo "&nbsp;&nbsp;&nbsp;Semi-tone adjust:&nbsp;<select$dis name=\"semitone\" id=\"selKey\" onChange=\"changeKey();\">\n";
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
echo "<input type='hidden' name='songID' value='$songID'>\n";
echo "</td><td align='right'><input name=\"cancel\" type=\"button\" value=\"Cancel\" onClick=\"cancelEdit();\" class=\"button\">&nbsp;<input name=\"save\" type=\"submit\" value=\"Save\" class=\"button\"></td></tr></table>\n";
echo "<table align='center'><tr>\n";
echo "	<td valign='top'><textarea style='width:450px;height:300px;overflow:auto;' name='rawSong' id='rawSong' rows='15' cols='70'>".str_replace("<br />","\r\n",$rawTxt)."</textarea></td>\n";
echo "	<td><a href='#' onClick='songText();' title='Update preview with\nchanges made to raw text'>-&gt;</a></td>\n";
echo "	<td valign='top'><div id='songDiv' style='border:1px inset;width:450px;height:300px;overflow:auto;'><table style='border:0px' align='center' class='songdetails' cellpadding='0' cellspacing='0'>\n";

$aSText = explode("<br />",nl2br(str_replace("\r\n","<br />",$rawTxt)));
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
		$lineChords = 0;
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
					$chordTemp .= "&nbsp;";
				} else {
					$chordLen--;
				}
				$j++;
			}
		}
		if($lineChords>0) {
			$aNewSText[] = "	<tr class='chords' id='chordline$cn'><td>$chordTemp</td></tr>\n";
			$cn++;
		}
		$aNewSText[] = "	<tr id='songtext'><td>".$songTemp."</td></tr>\n";
		$tn++;
	}
}

$sText = implode("",$aNewSText);

echo $sText;
$ce = $noChordsExist?"false":"true";
echo "</table><input type='hidden' name='chordsExist' value='$ce'>\n</div></td></tr></table>\n";
echo "</form>\n";

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
if($noChordsExist) echo "window.onload = setChord;\n";
echo "</script>\n";
echo "</body>\n</html>\n";


// Convert song to base text (no chords and other song indicators
function stripSong($inText) {
	$oldTxt = nl2br(str_replace("\r\n","<br />",$inText));
	
	/* Prepare song text */
	$aSText = explode("<br />",nl2br(str_replace("\r\n","<br />",$oldTxt)));
	$aNewSText = array();
	for($i=0;$i<count($aSText);$i++) {
		if(substr($aSText[$i],0,1)!="~" && trim($aSText[$i])!="") {
			$tmpText = $aSText[$i];
			$songTemp = "";
			$j = 0;
			while($j<strlen($tmpText)) {
				if($tmpText[$j]==" ") {
					$songTemp .= " ";
					$j++;
				} elseif($tmpText[$j]=="[") {
					$j++;
					while($tmpText[$j]!="]" && $j<strlen($tmpText)) {
						while($tmpText[$j]!="-" && $tmpText[$j]!="]" && $j<strlen($tmpText)) {
							$j++;
						}
						if($tmpText[$j]=="/" || $tmpText[$j]=="-") {
							$j++;
						}
					}
					if($tmpText[$j]=="]") {
						$j++;
					}
				} elseif($tmpText[$j]=="{") {
					$j++;
					while($tmpText[$j]!="}" && $j<strlen($tmpText)) {
						$j++;
					}
					if($tmpText[$j]=="}") {
						$j++;
					}
				} else {
					$songTemp .= $tmpText[$j];
					$j++;
				}
			}
			$aNewSText[] = $songTemp!=""?trim($songTemp)." ":"";
		}
	}
	return addslashes(trim(implode("",str_replace("<br />","",$aNewSText))));
}

?>