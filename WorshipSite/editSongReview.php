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
	exit;
}

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


if(isset($_REQUEST["act"])) {
	$sngact = $_REQUEST["act"];
	$songID = $_REQUEST["sid"];
} else {
	echo "Invalid Request.";
	exit;
}

// Save changes
if(isset($_POST["save"])) {
	if($sngact=="add") {
		$q = "INSERT INTO songrating VALUES(0,'".$db->real_escape_string($_POST["songName"])."','".$db->real_escape_string($_POST["songArtist"])."','".$db->real_escape_string($_POST["songAlbum"])."','".$db->real_escape_string($_POST["songLink"])."',".$_SESSION['user_id'].")";
	} else if($sngact=="rate") {
		$q = "INSERT INTO songratingbymember VALUES($songID, ".$_SESSION['user_id'].",".$_POST["ratingLyrics"].",".$_POST["ratingMusic"].",".$_POST["ratingSing"].",".$_POST["ratingOverall"].")";
	} else if($sngact=="ratu") {
		$q = "UPDATE songratingbymember SET ratingLyrics=".$_POST["ratingLyrics"].",ratingMusic=".$_POST["ratingMusic"].",ratingSing=".$_POST["ratingSing"].",ratingOverall=".$_POST["ratingOverall"]." WHERE songID=$songID AND memberID=".$_SESSION['user_id'];
	} else {
		$q = "UPDATE songrating SET songName='".$db->real_escape_string($_POST["songName"])."',songArtist='".$db->real_escape_string($_POST["songArtist"])."',songAlbum='".$db->real_escape_string($_POST["songAlbum"])."',songLink='".$db->real_escape_string($_POST["songLink"])."' WHERE songID=$songID";
	}
	$resSong = $db->query($q);
	echo "<script>parent.window.dspSongs();parent.window.hs.close();</script>";
	exit;
}

if($sngact=="add") {
	$songName = "";
	$songArtist = "";
	$songAlbum = "";
	$songLink = "";
} else {
	/* Retrieve Song for specified id */
	$sql = "SELECT * FROM songrating WHERE songID=$songID";
	$resSong = $db->query($sql);
	$dbSong=mysqli_fetch_array($resSong);
	$songName = htmlentities($dbSong["songName"],ENT_QUOTES);
	$songArtist = htmlentities($dbSong["songArtist"],ENT_QUOTES);
	$songAlbum = htmlentities($dbSong["songAlbum"],ENT_QUOTES);
	$songLink = $dbSong["songLink"];
	$ratingLyrics = 5;
	$ratingMusic = 5;
	$ratingSing = 5;
	$ratingOverall = 5;
}

// Retrieve rating information
if($sngact=="ratu") {
	$sql = "SELECT * FROM songratingbymember WHERE songID=$songID AND memberID=".$_SESSION['user_id'];
	$resRate = $db->query($sql);
	$dbRate=mysqli_fetch_array($resRate);
	$ratingLyrics = $dbRate["ratingLyrics"];
	$ratingMusic = $dbRate["ratingMusic"];
	$ratingSing = $dbRate["ratingSing"];
	$ratingOverall = $dbRate["ratingOverall"];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Calendar (Display Song)</title>
<script type="text/javascript" src="scripts/StarryWidget2/prototype.lite.js"></script>
<script type="text/javascript" src="scripts/StarryWidget2/stars.js"></script>
<link rel="stylesheet" href="scripts/StarryWidget2/stars.css" type="text/css" />
<script type="text/javascript">
function valSong() {
	var frmSong = document.frmSong;
	if(frmSong.songName.value=="") {
		alert("Please enter a value for the Song Title");
		frmSong.songName.focus();
		return false;
	}
	if(frmSong.songLink.value!="" && !isValidURL(frmSong.songLink.value)) {
		alert("Please enter a valid URL for the Song Link");
		frmSong.songLink.focus();
		return false;
	}
	return true;
}

function setURL(url){
	var turl = url.substr(0,4);
	turl = turl.toLowerCase();
	// Reference outside of the website
	if(url!="" && turl!="http") {          
		frmSong.songLink.value = "http://"+url;
	}
} 

function isValidURL(url){
	var RegExp = /^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/;
	if(RegExp.test(url)){
		return true;
	}else{
		return false;
	}
} 
</script>

<?php
echo "<link rel=\"stylesheet\" href=\"{$baseFolder}css/tw.css\" type=\"text/css\">\n";
echo "</head><body style='background-color:#ffffff'>\n";
/* Retrieve Service for specified id */
$mand = "<span style='font-weight:bold;color:#cc0000;'>*</span>";
echo "<form style='margin-top:0px;border-bottom:2px ridge;border-top:2px ridge;' name=\"frmSong\" action=\"editSongReview.php?act=$sngact&sid=$songID\" onSubmit=\"return valSong();\" method=\"post\">\n";
echo "<input type='hidden' name='sngact' value='$sngact'>\n";
echo "<table class='songhead'>\n";
if($sngact=="rate" || $sngact=="ratu") {
	echo "	<tr><td>Song Title:$mand </td><td>$songName</td></tr>";
	echo "	<tr><td>Artist: </td><td>$songArtist</td></tr>";
	echo "	<tr><td>Album: </td><td>$songAlbum</td></tr>";
	echo "	<tr><td colspan='2'><hr /></td></tr>";
	echo "	<tr><td colspan='2'><b>Please rate this song on the following:</b></td></tr>";
	echo "	<tr><td>Lyrics: </td><td>\n";
	echo "		<script>new Starry('starLyrics', {name:'ratingLyrics', maxLength:10, startAt:$ratingLyrics});</script>\n";

	echo "	</td></tr>";
	echo "	<tr><td>Music: </td><td>\n";
	echo "		<script>\n";
	echo "		new Starry('starMusic', {name:'ratingMusic', maxLength:10, startAt:$ratingMusic});\n";
	echo "		</script>\n";

	echo "	</td></tr>";
	echo "	<tr><td>Singability: </td><td>\n";
	echo "		<script>\n";
	echo "		new Starry('starSing', {name:'ratingSing', maxLength:10, startAt:$ratingSing});\n";
	echo "		</script>\n";

	echo "	</td></tr>";
	echo "	<tr><td>Overall: </td><td>\n";
	echo "		<script>\n";
	echo "		new Starry('starOverall', {name:'ratingOverall', maxLength:10, startAt:$ratingOverall});\n";
	echo "		</script>\n";

	echo "	</td></tr>";
} else {
	echo "	<tr><td>Song Title:$mand </td><td><input type='text' name='songName' size='50' maxlength='100' value='$songName' /></td></tr>";
	echo "	<tr><td>Artist: </td><td><input type='text' name='songArtist' size='50' maxlength='100' value='$songArtist' /></td></tr>";
	echo "	<tr><td>Album: </td><td><input type='text' name='songAlbum' size='50' maxlength='100' value='$songAlbum' /></td></tr>";
	echo "	<tr><td valign='top'>Video Link: </td><td><input title='Paste embed link from a video serving site' type='text' name='songLink' size='50' maxlength='255' value='$songLink' onBlur='setURL(document.frmSong.songLink.value);' /><br /><span style='font-size:9px;'>(enter the link to the&nbsp;<a href='http://www.youtube.com' target='_blank'>YouTube</a> video)</span></td></tr>\n";
}
echo "	<tr><td colspan='2' align='right'><input name=\"cancel\" type=\"button\" value=\"Cancel\" onClick=\"parent.window.hs.close();\" class=\"button\">&nbsp;<input name=\"save\" type=\"submit\" value=\"Save\" class=\"button\"></td></tr></table>\n";
echo "</table>\n";
echo "</body>\n</html>\n";
exit;
?>