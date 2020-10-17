<?php 
/*******************************************************************
 * editService.php
 * Update Service information
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

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Song Database', $_SERVER['REQUEST_URI'], 1);

$isAdmin = allow_access(Administrators) == "yes";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Song Database</title>
<script type="text/javascript" src="scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript">
function songText(id,chords) {
	var url = 'getSong.php';
	var pars = 'songID='+id+"&chords="+chords;

	var myAjax = new Ajax.Updater(
		'songDiv', 
		url, {
			method: 'get', 
			parameters: pars
		});
}
function valSearch() {
			if (document.frmSong.txtSearch.value!="<?php echo isset($_POST["txtSearch"])?$_POST["txtSearch"]:''; ?>") {
		document.frmSong.pageNum.value = 1;
	}
	return true;
}

<?php
if (allow_access(Administrators) == "yes") { 
?>
function delSong(id,name) {
	if(confirm("Delete song: "+name+"?")) {
		document.frmSong.action="editSong.php";
		document.frmSong.sngact.value="del";
		document.frmSong.songID.value=id;
		document.frmSong.submit();
	}
}

function addSong() {
	document.frmSong.action="editSong.php";
	document.frmSong.sngact.value="add";
	document.frmSong.submit();
}

function editSong(id) {
	document.frmSong.action="editSong.php";
	document.frmSong.sngact.value="edit";
	document.frmSong.songID.value=id;
	document.frmSong.submit();
}
<?php
}
if(isset($_POST["errMsg"]) && $_POST["errMsg"]!="") {
    echo "window.onload = function() {\n";
    echo "  alert('".stripslashes($_POST["errMsg"])."');\n";
    echo "}\n";
}
?>
</script>
<?php

$hlpID = $isAdmin?17:8;
$title = "Display Song Database";
include("header.php");

$pageNum = (isset($_POST["pageNum"]) && $_POST["pageNum"] > 0)?$_POST["pageNum"]:1;
$pageTxt = "";

echo "	<form name=\"frmSong\" action=\"listSongs.php\" method=\"post\" onSubmit=\"valSearch();\">\n";
echo "	<input name=\"sngact\" type=\"hidden\">\n";
echo "	<input name=\"songID\" type=\"hidden\" value=\"$songID\">\n";
echo "	<input type=\"hidden\" name=\"pageNum\" id=\"pageNum\" value=$pageNum>\n";
echo "	<table border=\"0\" width=\"100%\" cellspacing=\"1\" cellpadding=\"2\">\n";
echo "		<tr>\n";
if (allow_access(Administrators) == "yes") { 
	echo "			<td valign=\"middle\" align=\"left\">\n";
	echo "				<a onClick=\"addSong();\" href='#' title='Add New Song'><img src=\"images/icon_new.gif\" style='vertical-align:middle'>New Song</a>\n";
	echo "			</td>\n";
}
echo "			<td align=\"right\">\n";
$chk = isset($_POST["srchDesc"]) && $_POST["srchDesc"]=="1"?" checked":"";
echo "				<input type=\"checkbox\" name=\"srchDesc\" value=\"1\"$chk>\n";
echo "				Search song text&nbsp;&nbsp;&nbsp;\n";
echo "				<strong>Search:</strong>&nbsp;\n";
echo "				<input type=\"text\" name=\"txtSearch\" size=\"20\" value=\"".stripslashes($_POST["txtSearch"])."\">&nbsp;\n";
echo "				<a href='#' onClick=\"document.frmSong.submit();\"><img src=\"/images/search.gif\"></a>\n";
echo "			</td>\n";
echo "		</tr>\n";
echo "	</table></form>\n";

if($pageNum > 1) {
	$limit = " LIMIT ".($pageNum * 20 - 20).",20";
} else {
	$limit = " LIMIT 20";
}

$srchDesc = isset($_POST["srchDesc"]) && $_POST["srchDesc"]=="1"?" OR baseText LIKE \"%".$_POST["txtSearch"]."%\"":"";
if(isset($_POST["txtSearch"]) && $_POST["txtSearch"] <> "") {
	$q = "SELECT songName, songLink as sLink, songText, songID as sID, songArtist FROM songs WHERE songArtist LIKE \"%".$_POST["txtSearch"]."%\" OR songName LIKE \"%".$_POST["txtSearch"]."%\"$srchDesc ORDER BY songName";
} else {
	$q = "SELECT songName, songLink as sLink, songText, songID as sID, songArtist FROM songs ORDER BY songName";
}
$useqry = $db->query($q);
$numPages = ceil(mysqli_num_rows($useqry)/20);

if(isset($_POST["txtSearch"]) && $_POST["txtSearch"] <> "") {
	$q = "SELECT songName, songLink as sLink, songText, songID as sID, songArtist FROM songs WHERE songArtist LIKE \"%".$_POST["txtSearch"]."%\" OR songName LIKE \"%".$_POST["txtSearch"]."%\"$srchDesc ORDER BY songName".$limit;
} else {
	$q = "SELECT songName, songLink as sLink, songText, songID as sID, songArtist FROM songs ORDER BY songName".$limit;
}
$resSong = $db->query($q);

echo "<table style='border-collapse:collapse;border:2px ridge;width:100%'>\n";
if($resSong && (mysqli_num_rows($resSong) > 0)){
	if($numPages > 1) {
		$pageTxt = "<tr>\n";
		$pageTxt .= "	<td style='border:2px inset;background-color:#ebebeb;font-size:7pt;font-weight:normal' colspan=\"2\" align=\"center\">\n";
		if($pageNum > 1) {
			$pageTxt .= "		<a href=\"#\" onClick=\"frmSong.pageNum.value=Number(frmSong.pageNum.value)-1;frmSong.submit();\">[<< Prev]</a>&nbsp;&nbsp;\n";
		}
		for($i=1;$i<=$numPages;$i++) {
			if($i==$pageNum) {
				$pageTxt .= "		<span style='color:#933100;font-weight:bold;font-size:9pt;'>$i</span>&nbsp;&nbsp;\n";
			} else {
				$pageTxt .= "		<a href=\"#\" onClick=\"frmSong.pageNum.value=$i;frmSong.submit();\">$i</a>&nbsp;&nbsp;\n";
			}
		}
		if($_POST["pageNum"] < $numPages) {
			$pageTxt .= "		<a href=\"#\" onClick=\"frmSong.pageNum.value=Number(frmSong.pageNum.value)+1;frmSong.submit();\">[Next >>]</a>\n";
		}
		$pageTxt .= "	</td>\n";
		$pageTxt .= "</tr>\n";
	}
	echo $pageTxt."<tr><td valign='top'>";
	$songDesc = "<table style='border-right:2px inset;border-collapse:collapse;width:100%;min-width:650px'>\n";
	$songDesc .= "<tr style='border-bottom:1px solid #000000;background-color:#e0e0e0;font-weight:bold'><td>&nbsp;</td><td>Song Title</td><td>Song Artist</td></tr>\n";
	$shade = false;
	while($dbsong=mysqli_fetch_array($resSong)) {
		$bgcolor = $shade?"#efefef":"";
		$shade = !$shade;
		$chordsExist = strpos($dbsong["songText"],"[")!==FALSE;
		$sLink = $dbsong["songText"]==""?"&nbsp;":"<a href='dspSong.php?id=".$dbsong["sID"]."'>";
		$sLink2 = $dbsong["songText"]==""?"":"</a>";
		if($dbsong["sLink"]=="") {
			$sLinkPlay = "";
		} else {
			// Fixed up any http links for youtube to https: youtube to avovd a security error
			$linkSrc = str_replace(array("/v/","http://www.youtube"),array("/embed/","https://www.youtube"),$dbsong["sLink"]);
			$sLinkPlay = "<a href='".$linkSrc."' title='Play music video' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',  Height: 482, width: 600  } )\"><img src='images/play.png' border='0' alt='Play in new window'></a>";
				if (strpos(strtolower($linkSrc), "youtube.com/embed/")) {
				    preg_match("/\/embed\/([a-z0-9A-Z\-]+)/", $linkSrc, $matches);
				    // change http youtube to https: youtube to avovd a security error
					$linkSrcn = "https://www.youtube.com/watch?v=" . $matches[1] . "&feature=player_embedded";
				    $sLinkPlay = $sLinkPlay . "<a href='".$linkSrcn."' title='Play music video in new tab' Target='_blank' \"><img src='images/playr.png' border='0' alt='Play in new window'></a>";
				}
				
		}
		$pdfLink = $dbsong["songText"]==""?"":"<a title=\"Create PDF Song Sheet\" href='createSongSheet.php?id=".$dbsong["sID"]."&c=1' target='_blank'><img src='images/icon_pdf.gif' border='0' alt='Download PDF Version'></a>";
		$songDesc .= "	<tr bgcolor='$bgcolor'><td nowrap width='122'>\n";
		if (allow_access(Administrators) == "yes") { 
			$songDesc .= "		<a onClick=\"editSong(".$dbsong["sID"].");\" href='#' title='Edit Song Info'><img src=\"images/edit.png\"></a>\n";
			$songDesc .= "		<a onClick=\"delSong(".$dbsong["sID"].",'".addslashes($dbsong["songName"])."');\" href='#' title='Delete Song'><img src=\"images/icon_delete.gif\"></a>\n";
		}
		$songDesc .= "			$pdfLink <a onClick='songText(".$dbsong["sID"].",0)' href='#' title='Display Lyrics - no chords'><img src=\"images/icon_lyricsX.gif\"></a>\n";
		$songDesc .= $chordsExist?"			<a onClick='songText(".$dbsong["sID"].",1)' href='#' title='Display Lyrics with chords'><img src=\"images/icon_lyrics.gif\"></a>\n":"<img src=\"images/space16.gif\">\n";
		$songDesc .= "			$sLinkPlay</td>\n";
		$songDesc .= "		<td nowrap><div style='min-width:300px;overflow:hidden'>".$dbsong["songName"]."</div></td>\n";
		$songDesc .= "		<td nowrap><div style='overflow:hidden;width:200px'>".$dbsong["songArtist"]."</div></td>\n";
		$songDesc .= "	</tr>\n";
	}
	echo $songDesc."</table></td>\n";
	echo "<td width='431' align='right' valign='top'><div style='text-align:left;border:2px inset;height:430px;width:425px;padding:3px;overflow-x:scroll;overflow-y:scroll;' id='songDiv'></div></td>\n";
	echo "</tr>\n";
	echo $pageTxt;
} else {
	echo "<tr><td><h2 align='center'>No songs found.</h2></td></tr>\n";
}
echo "</table>\n";
echo "</body>\n</html>\n";
