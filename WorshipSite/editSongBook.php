<?php
/*******************************************************************
 * editSongBook.php
 * Update/Display Song Book information
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

if(isset($_REQUEST["id"])) {
	$bookID = $_REQUEST["id"];
} else {
	header("Location: index.php");
}
if(isset($_REQUEST["ac"])) {
	$action = $_REQUEST["ac"];
} else {
	header("Location: index.php");
}

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Delete song book
if($action=="del") {
	$sql = "DELETE FROM songbooksongs WHERE bookID=$bookID";
	$resSong = $db->query($sql);
	$sql = "DELETE FROM songbook WHERE bookID=$bookID";
	$resSong = $db->query($sql);
	header("Location: dspSongBooks.php");
	exit;
}


include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
if($action=="dsp") {
	$acDesc = "Display";
} else if($action=="edit") {
	$acDesc = "Edit";
} else if($action=="add") {
	$acDesc = "Create";
}
$trail->add($acDesc.' Song Book', $_SERVER['REQUEST_URI'], 2);

// Delete song from the service
if($_POST["subact"]=="delsong") {
	$sql = "DELETE FROM songbooksongs WHERE bookID=$bookID AND songID=".$_POST["nsID"];
	$resSong = $db->query($sql);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle." - ".$acDesc; ?> Song Book</title>
<link href="<?php echo $baseFolder; ?>scripts/lightloader/upload.css" type="text/css" rel="stylesheet"/>
<link rel="stylesheet" href="<?php echo $baseFolder; ?>css/tw.css" type="text/css">
<style>
form {
	padding:0px;
}
</style>
<script type="text/javascript" src="scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript" src="scripts/scriptaculous/src/scriptaculous.js"></script>
<script type="text/javascript">

function delSong(sid,name) {
	if(confirm('Do you wish to remove '+name+' from this book?')) {
		document.frmSong.subact.value="delsong";
		document.frmSong.nsID.value=sid;
		document.frmSong.submit();
	}
}

function dspSong(id) {
	var oUpdater = new Ajax.Updater({ success:'divSongs', failure:'divSongError' }, '/ajSongBookAdd.php', { 
		method: "get",
		parameters: { 	bid: '<?php echo $bookID; ?>', sid: id, act: 'dsp' }
	});
}

function addSong(id) {
	var oUpdater = new Ajax.Updater({ success:'divSongs', failure:'divSongError' }, '/ajSongBookAdd.php', { 
		method: "get",
		parameters: { 	bid: '<?php echo $bookID; ?>', sid: id, act: 'add' },
		onSuccess: addOK,
		onFailure: addError
	});
}

function addOK(response) {
	if(response.responseText.substr(0,2)=="<!") {
		alert("Song already exists in this songbook");
	}
}

function addError() {
	alert("Song already exists in this songbook");
}

function editBookTitle() {
	var title = addslashes(document.frmTitle.bookTitle.value);
	var prv = document.frmTitle.private.checked?1:0;
	var oUpdater = new Ajax.Updater({ success:'divBookTitle', failure:'divBookError' }, '/ajSongBookAdd.php', { 
		method: "get",
		parameters: { 	bid: '<?php echo $bookID; ?>', sid: 0, t: title, p: prv, act: 'edittitle' },
		onSuccess: editBOK,
		onFailure: editBError
	});
}

function editBOK(response) {
	alert("Book title has been updated.");
}

function editBError(response) {
	alert(response.responseText);
}

function valSearch() {
	if(document.frmSong.txtSearch.value!="<?php echo $_POST["txtSearch"]; ?>") {
		document.frmSong.pageNum.value = 1;
	}
	return true;
}

function addslashes( str ) {
    return (str+'').replace(/([\\"'])/g, "\\$1").replace(/\u0000/g, "\\0");
}

</script>
<?php
// " 
$hlpID = 0;
$title = "$acDesc Song Book";
include("header.php");

echo "<div id=\"divSongError\"></div>\n";
echo "<div id=\"divBookError\"></div>\n";

$q = "SELECT * FROM songbook WHERE bookID = $bookID";
$resBook = $db->query($q);
$dbBook=mysqli_fetch_array($resBook);

echo "<table style=\"width:100%;padding:15px\">\n";
if($action=="dsp") {
	$prvText = $dbBook["private"]==1?"&nbsp;(Private)":"&nbsp;(Shared)";
	echo "<tr><td nowrap><b><u>Song Book:</u> <div id='divBookTitle'>".$dbBook["bookTitle"]."$prvText</div></b></td>\n";
} else {
	echo "<tr><td nowrap>\n";
	echo "<div id='divBookTitle'>\n";
	echo "<form name='frmTitle' method='post'>\n";
	echo "	<b>Book Title:</b>&nbsp;\n";
	echo "	<input type='text' name='bookTitle' maxlength='100' size='25' value='".htmlentities(stripslashes($dbBook["bookTitle"]),ENT_QUOTES)."' />&nbsp;&nbsp;\n";
	echo "	<b>Private</b>&nbsp;\n";
	$chk = $dbBook["private"]==1?" checked":"";
	echo "	<input type='checkbox' name='private' value='1'$chk />&nbsp;&nbsp;\n";
	echo "	<input type='button' name='updBook' value='Update' onClick='editBookTitle();'>\n";
	echo "	</form>\n";
	echo "	</div>\n";
}
$editInfo = $action!="dsp"?"Select (<img src='/images/icon_accept.gif' border='0'>) the songs to include in your book":"&nbsp;";
echo "	<td nowrap style='text-align:center'>$editInfo</td><td nowrap style='text-align:right'><a href='createSongBook.php?id=$bookID&c=1' target='_blank'>Print Songbook</a></td></tr></table>";
echo "<form name=\"frmSong\" action=\"editSongBook.php?id=$bookID&ac=$action\" method=\"post\" onSubmit=\"valSearch();\">\n";
echo "<table width=\"100%\" border='0' align='center'><tr><td valign='top'>\n";
echo "<table class='serviceDetails' border='0' align='left'><tr><td valign='top'>\n";
if($action!="dsp") echo "<a id='hsEditOrder' href='ajUpdSongBookOrder.php?bid=$bookID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', contentID: 'divEditSongOrder', headingText: 'Update Song Order' } )\">Update Song Order</a>\n";
echo "<div id='divSongs'>\n";

/* Retrieve Song List */
$q = "SELECT bookID, songOrder, songOrder, songName, songArtist, sb.songID AS sID FROM songbooksongs sb LEFT JOIN songs ON sb.songID = songs.songID WHERE bookID = $bookID ORDER BY songOrder, songName";
$resSong = $db->query($q);
$songDesc = "<table style='border-collapse:collapse;' border='0' width='450' align='left'>\n";
$i = 1;

$songDesc .= "	<tr bgcolor='#e6e6e6'>\n";
if($action!="dsp") $songDesc .= "		<td style='border:1px solid #000000;' width='30'>&nbsp;</td>\n";
$songDesc .= "		<td style='border:1px solid #000000;font-weight:bold'>Song Name</td>\n";
$songDesc .= "	</tr>\n";
$shade = false;
while($dbsong=mysqli_fetch_array($resSong)) {
	$bgcolor = $shade?"#efefef":"";
	$shade = !$shade;
	$songDesc .= "	<tr bgcolor='$bgcolor'>\n";
	if($action!="dsp") {
		$songDesc .= "		<td style='text-align:center;border:1px solid #e1e1e1' width='30'>\n";
		$songDesc .= "			<a href='#' onClick=\"delSong(".$dbsong["sID"].",'".addslashes($dbsong["songName"])."');\" title='Remove Song from Song Book'><img src='images/icon_delete.gif'></a>&nbsp;\n";
		$songDesc .= "		</td>\n";
	}
	$songDesc .= "		<td style='border:1px solid #e1e1e1' title='Song Name'><a href='getSong.php?songID=".$dbsong["sID"]."&chords=0' onclick=\"return hs.htmlExpand(this, { objectType: 'ajax', contentID: 'divSongDisplay', headingText: 'Display Song',height: 500 } )\">".$dbsong["songName"]."</a></td>\n";
	$songDesc .= "	</tr>\n";
	$i++;
}
echo $songDesc."\n</table>\n";

echo "</div><br /><input name=\"nsID\" type=\"hidden\" value=$songID>\n";
echo "</div><br /><input name=\"subact\" type=\"hidden\">\n";

echo "</table></td>\n";


if($action!="dsp") {
	echo "<td valign=\"top\" align='center'>\n";


	// List Songs
	$pageNum = isset($_POST["pageNum"])?$_POST["pageNum"]:1;
	if($pageNum > 1) {
		$limit = " LIMIT ".($pageNum * 20 - 20).",20";
	} else {
		$limit = " LIMIT 20";
	}
	$srchDesc = isset($_POST["srchDesc"])?" OR songText like \"%".$_POST["txtSearch"]."%\"":"";
	if(isset($_POST["txtSearch"]) && $_POST["txtSearch"] <> "") {
		$q = "SELECT songName, songLink as sLink, songText, songID as sID, songArtist FROM songs WHERE songArtist like \"%".$_POST["txtSearch"]."%\" OR songName like \"%".$_POST["txtSearch"]."%\"$srchDesc ORDER BY songName";
	} else {
		$q = "SELECT songName, songLink as sLink, songText, songID as sID, songArtist FROM songs ORDER BY songName";
	}
	$useqry = $db->query($q);
	$numPages = ceil(mysqli_num_rows($useqry)/20);

	if(isset($_POST["txtSearch"]) && $_POST["txtSearch"] <> "") {
		$q = "SELECT songName, songLink as sLink, songText, songID as sID, songArtist FROM songs WHERE songArtist like \"%".$_POST["txtSearch"]."%\" OR songName like \"%".$_POST["txtSearch"]."%\"$srchDesc ORDER BY songName".$limit;
	} else {
		$q = "SELECT songName, songLink as sLink, songText, songID as sID, songArtist FROM songs ORDER BY songName".$limit;
	}
	$resSong = $db->query($q);

	echo "	<input type=\"hidden\" name=\"pageNum\" id=\"pageNum\" value=$pageNum>\n";
	echo "<table style='border-collapse:collapse' border='0'>\n";
	echo "		<tr>\n";
	echo "			<td style='padding-left:3px;padding-right:5px;border:1px solid #000000;font-weight:bold' align=\"left\">\n";
	echo "				Song Database\n";
	echo "			</td>\n";
	echo "			<td style='border:1px solid #000000;' align=\"right\">\n";
	$chk = isset($_POST["srchDesc"])?" checked":"";
	echo "				<input type=\"checkbox\" name=\"srchDesc\" value=\"1\"$chk>\n";
	echo "				Search song text&nbsp;&nbsp;\n";
	echo "				<strong>Search:</strong>&nbsp;\n";
	echo "				<input type=\"text\" name=\"txtSearch\" size=\"20\" value=\"".stripslashes($_POST["txtSearch"])."\">&nbsp;\n";
	echo "				<a href='#' onClick=\"document.frmSong.submit();\"><img src=\"/images/search.gif\"></a>\n";
	echo "			</td>\n";
	echo "		</tr>\n";
	if($resSong && (mysqli_num_rows($resSong) > 0)){
		if($numPages > 1) {
			$pageTxt = "<tr>\n";
			$pageTxt .= "	<td style='border:1px solid #000000;background-color:#ebebeb;font-size:7pt;font-weight:normal' colspan=\"2\" align=\"center\">\n";
			$pageFrom = (intval(($pageNum-1)/10)*10)+1;
			if($pageFrom > 1) {
				$pageTxt .= "		<a href=\"#\" onClick=\"frmSong.pageNum.value=1;frmSong.submit();\"><<</a>&nbsp;&nbsp;\n";
			}
			if($pageNum > 1) {
				$pageTxt .= "		<a href=\"#\" onClick=\"frmSong.pageNum.value=Number(frmSong.pageNum.value)-1;frmSong.submit();\"> Prev</a>&nbsp;&nbsp;\n";
			}
			$pageTo = $pageFrom+9>$numPages?$numPages:$pageFrom+9;
			for($i=$pageFrom;$i<=$pageTo;$i++) {
				if($i==$pageNum) {
					$pageTxt .= "		<span style='color:#933100;font-weight:bold;'>$i</span>&nbsp;\n";
				} else {
					$pageTxt .= "		<a href=\"#\" onClick=\"frmSong.pageNum.value=$i;frmSong.submit();\">$i</a>&nbsp;\n";
				}
			}
			if($pageNum < $numPages) {
				$pageTxt .= "		<a href=\"#\" onClick=\"frmSong.pageNum.value=Number(frmSong.pageNum.value)+1;frmSong.submit();\">Next</a>\n";
			}
			if($numPages > $pageFrom+9) {
				$pageTxt .= "		&nbsp;&nbsp;<a href=\"#\" onClick=\"frmSong.pageNum.value=$numPages;frmSong.submit();\">>></a>\n";
			}
			$pageTxt .= "	</td>\n";
			$pageTxt .= "</tr>\n";
		}
		echo $pageTxt."<tr><td colspan=\"2\" valign='top'>";
		$songDesc = "<table border='0' style='border:1px solid #000000;border-collapse:collapse;width:100%;min-width:200px'>\n";
		$shade = false;
		$songDesc .= "	<tr bgcolor='#e6e6e6'>\n";
		$songDesc .= "		<td style='border:1px solid #000000'>&nbsp;</td>\n";
		$songDesc .= "		<td style='text-align:left;border:1px solid #000000;font-weight:bold' nowrap>Song Name</td>\n";
		$songDesc .= "	</tr>\n";
		while($dbsong=mysqli_fetch_array($resSong)) {
			$bgcolor = $shade?"#efefef":"";
			$shade = !$shade;
			$songDesc .= "	<tr bgcolor='$bgcolor'>\n";
			$songDesc .= "		<td style='border:1px solid #e1e1e1'><a href=\"#\" onClick=\"addSong(".$dbsong["sID"].");\"><img src=\"/images/icon_accept.gif\" border=\"0\"></a></td>\n";
			$songDesc .= "		<td style='text-align:left;border:1px solid #e1e1e1' nowrap><div style='min-width:300px;overflow:hidden'><a href='getSong.php?songID=".$dbsong["sID"]."&chords=0' onclick=\"return hs.htmlExpand(this, { objectType: 'ajax', contentID: 'divSongDisplay', headingText: 'Display Song from database',height: 500 } )\" >".$dbsong["songName"]."</a></div></td>\n";
			$songDesc .= "	</tr>\n";
		}
		echo $songDesc."</table></td>\n";
		echo "</tr>\n";
		echo $pageTxt;
	}
	echo "</table></td>\n";
}

echo "</tr></table></form>\n";


// Edit Book Title Section
echo "<div id='divEditTitle' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";

// Edit Song Order
echo "<div id='divEditSongOrder' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";

// Display Song Section
echo "<div id='divSongDisplay' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";

echo "</body>\n</html>\n";