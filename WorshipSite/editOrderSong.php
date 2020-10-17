<?php
/*******************************************************************
 * editOrder.php
 * Update Service worship order information (Song)
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
if (allow_access(Administrators) != "yes") { 
	exit;
}

$serviceID = $_REQUEST["id"];
$action = $_REQUEST["act"];
$orderID = isset($_REQUEST["oid"])?$_REQUEST["oid"]:-1;
$subaction = isset($_POST["subact"])?$_POST["subact"]:"";
$songID = "";
$songKey = "";
$songNumber = 99999;
$songName = "";
$sLink = "";
$songText = "";

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Add new song to the service
if($subaction=="addsong") {
	$sql = "INSERT INTO serviceorder VALUES($serviceID,0,'S',999,".$_POST["nsID"].",'".$_POST["newSongKey"]."','','".$db->real_escape_string($_POST["newSongLink"])."','','".$db->real_escape_string($_POST["songText"])."')";
	$resSong = $db->query($sql);
	echo "<script>parent.window.dspOrder();parent.window.hs.close();</script>";
	exit;
}

// Update song
if($subaction=="editsong") {
	$sql = "UPDATE serviceorder SET songNumber=".$_POST["newSongNumber"].",songID=".$_POST["nsID"].",songKey='".$_POST["newSongKey"]."',songLink='".$db->real_escape_string($_POST["newSongLink"])."',orderDetails='".$db->real_escape_string($_POST["songText"])."' WHERE serviceID=$serviceID AND orderID=$orderID";
	$resSong = $db->query($sql);
	echo "<script>parent.window.dspOrder();parent.window.hs.close();</script>";
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Edit Service Order (Song)</title>
<link rel="stylesheet" href="<?php echo $baseFolder; ?>css/tw.css" type="text/css">
<style>
form {
	padding:0px;
}
</style>
<script type="text/javascript" src="scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript" src="scripts/scriptaculous/src/scriptaculous.js"></script>
<script type="text/javascript">
function init() {
	new Ajax.Autocompleter('newSong', 'songBox', 'acGetSongs.php',{tokens:[','],minChars:2,afterUpdateElement:setSongID});
}

function setSongID(text,li) {
	var liParts = li.id.split("#%");
	document.frmOrder.nsID.value=liParts[0];
	document.frmOrder.newSongLink.value=liParts[1];
	document.frmOrder.songText.value=liParts[2];
	if(liParts.length > 0 && liParts[0] > 0) {
		document.frmOrder.addSng.disabled=false;
	}
}

function setSongAdd() {
	document.frmOrder.addSng.disabled=!(document.frmOrder.nsID.value>0);
}

function editSong() {
	if(document.frmOrder.newSong.value=="") {
		alert('Please enter a song name.');
		document.frmOrder.newSong.focus();
		return false;
	}
	document.frmOrder.subact.value="<?php echo $action; ?>song";
	document.frmOrder.submit();
}

function extractLink() {
	var lnk = document.frmOrder.newSongLink.value;
	var sp = lnk.indexOf("http");
	var ep = lnk.indexOf('"',sp);
	if(sp > 0 && ep > sp) {
		lnk = lnk.substring(sp,ep);
	}
	lnk = lnk.replace("embed","v");
	document.frmOrder.newSongLink.value = lnk;
}
</script>

<style>
div.autocomplete {
	border: 1px solid #999;
	background-color: #fff;
	max-height:200px;
	overflow-y:scroll;
}
div.autocomplete ul {
	list-style: none;
	margin:0;
	padding:0;
}
div.autocomplete li { 
	padding: 2px 3px;
}
div.autocomplete strong { 
	font-weight: bold;
	text-decoration: underline;
}
div.autocomplete li.selected { 
	color: #fff;
	background-color: #8c1000;
	cursor:pointer;
}
</style>

<?php
echo "</head>\n";

/* Retrieve Song */
if($action=="edit") {
	$q = "SELECT songKey, songNumber, songName, serviceorder.songLink as sLink, orderDetails, songText, serviceorder.songID as sID FROM serviceorder INNER JOIN songs ON serviceorder.songID = songs.songID WHERE serviceID = $serviceID AND serviceorder.orderID=$orderID";
	$resSong = $db->query($q);
	$i = 1;
	if($dbsong=mysqli_fetch_array($resSong)) {
		$songID = $dbsong["sID"];
		$songKey = $dbsong["songKey"];
		$songNumber = $dbsong["songNumber"];
		$songName = $dbsong["songName"];
		$sLink = $dbsong["sLink"];
		$songText = $dbsong["orderDetails"];
		if ($songText =="") {
      $songText = $dbsong["songText"];
		}
	} else {
		$songID = "";
		$songKey = "";
		$songNumber = 999;
		$songName = "";
		$sLink = "";
		$songText = "";
	}
}

// Set onload action
echo "<body onLoad='init();' style='background-color:#ffffff'>\n";
echo "<h2 align=\"center\">Edit Worship Order Element (Song)</h2>\n";
echo "<form style='margin:0px;' name='frmOrder' method='post' action='editOrderSong.php?id=$serviceID&act=$action&oid=$orderID'>\n";
echo "<input name=\"subact\" type=\"hidden\">\n";
echo "<input name=\"orderID\" type=\"hidden\" value=$orderID>\n";
echo "<input name=\"newSongNumber\" type=\"hidden\" id=\"newSongNumber\" value='$songNumber'>";
echo "<input name=\"nsID\" type=\"hidden\" value=$songID>\n";
echo "<table class='serviceDetails' border='1' align='center'>\n";
echo "<table>\n";
// Song Section
echo "<tr id='songSection'><td><table>\n";
echo "	<tr>\n";
echo "		<td><b>Song Key:</b></td>\n";
echo "		<td><input title=\"Song Key\" name=\"newSongKey\" type=\"text\" id=\"newSongKey\" size=\"1\" maxlength=\"10\" value='$songKey'></b></td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td><b>Song Name:</b></td>\n";
if($action=="edit") {
	echo "		<td>$songName\n";
	echo "		<input name=\"newSong\" type=\"hidden\" id=\"newSong\" value=\"$songName\" /></td>\n";
} else {
	echo "		<td><input title=\"Song Name - List will pop up \nafter typing in several characters\" name=\"newSong\" type=\"text\" id=\"newSong\" size=\"45\" maxlength=\"100\" autocomplete=\"off\" onKeyUp=\"setSongAdd();\" onChange=\"setSongAdd();\" value=\"$songName\" /><div id=\"songBox\" class=\"autocomplete\" style=\"left: 0px; top: 0px; position: absolute; z-index: 1;display:none\">&nbsp;</div></td>\n";
}
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td><b>YouTube Link:&nbsp;</b></td>\n";
echo "		<td><input title=\"YouTube embed link\" name=\"newSongLink\" type=\"text\" id=\"newSongLink\" size=\"40\" onChange=\"extractLink();\" value=\"$sLink\"></td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td colspan='2'><b>Lyrics:</b></td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td colspan='2'><textarea name='songText' id='songText' cols='50' rows='15'>$songText</textarea></td>\n";
echo "	</tr>\n";
$disabled = $action=="add"?" disabled":"";
echo "	<tr><td colspan='2' align='center'><input type='button' name='addSng' value='Save'$disabled onClick='editSong();'><input type='button' name='cancel' value='Cancel' onClick='parent.window.hs.close();'></td></tr>\n";
echo "</table></td></tr>\n";
echo "</td></tr>\n";

echo "</table>\n";
echo "</form>\n";
echo "</body>\n</html>\n";
?>