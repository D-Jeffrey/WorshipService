<?php
/*******************************************************************
 * ajUpdSongRate.php
 * Edit Song Information (Box on home page)
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
	echo "Not Allowed!";
	exit;
}

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

// Save changes
if(isset($_POST["save"])) {
	$q = "INSERT INTO songratingbymember VALUES(".$_POST["songID"].", ".$_SESSION['user_id'].",".$_POST["ratingLyrics"].",".$_POST["ratingMusic"].",".$_POST["ratingSing"].",".$_POST["ratingOverall"].")";
	$resSong = $db->query($q);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> Rate Song</title>
<script type="text/javascript" src="scripts/StarryWidget2/prototype.lite.js"></script>
<script type="text/javascript" src="scripts/StarryWidget2/starsSM.js"></script>
<link rel="stylesheet" href="/css/tw.css" type="text/css">
<link rel="stylesheet" href="scripts/StarryWidget2/stars.css" type="text/css" />
<style>
.songRate {
	width:170px;
	margin:0px;
}
.songButton {
	font-size:8pt;
}
.songRate td {
	font-size:9pt;
	color:#ffffff;
	font-family: Arial, Helvetica, Verdana;
}
</style>
</head>
<body bgcolor="#000000">   /* height='235' width="170" */
<?php
echo getNewSong();
echo "</body></html>\n";

function getNewSong() {
	global $db;
	/* Retrieve Songs */
	$sql = "SELECT * FROM songrating ORDER BY songName, songArtist";
	$resSong = $db->query($sql);
	$dbSong=mysqli_fetch_array($resSong);
	$notRated = false;
	while($dbSong && !$notRated) {
		// Retrieve rating information
		$sql = "SELECT * FROM songratingbymember WHERE songID=".$dbSong["songID"]." AND memberID=".$_SESSION['user_id'];
		$resRate = $db->query($sql);
		$notRated = !$resRate || mysqli_num_rows($resRate)==0;
		if(!$notRated) $dbSong=mysqli_fetch_array($resSong);
	}
	if($notRated) {
		$songName = htmlentities($dbSong["songName"],ENT_QUOTES);
		$songArtist = htmlentities($dbSong["songArtist"],ENT_QUOTES);
		$songAlbum = htmlentities($dbSong["songAlbum"],ENT_QUOTES);
		$songLink = $dbSong["songLink"];
		$out = "<form style='margin:0px;' action='ajUpdSongRate.php' name=\"frmSongRate\" method=\"post\">\n";
		$out .= "<input type='hidden' name='songID' value=".$dbSong["songID"].">\n";
		$out .= "<table class='songRate'>\n";
		$out .= "	<tr><td colspan='2'><b>Please rate this song:</b></td></tr>";
		$out .= "	<tr><td>Title:</td><td>$songName</td></tr>";
		if($songArtist!="") $out .= "	<tr><td>Artist:</td><td>$songArtist</td></tr>";
		if($songAlbum!="") $out .= "	<tr><td>Album:&nbsp;</td><td>$songAlbum</td></tr>";
		if($songLink!="") $out .= "	<tr><td>&nbsp;</td><td><input type='button' class='songButton' title=\"Play Music Video\" onClick='window.open(\"$songLink\");' value='Play Video' /></td></tr>";
		$out .= "</table>\n";
		$out .= "<table class='songRate'>\n";
		$out .= "	<tr><td>Lyrics: </td><td>\n";
		$out .= "		<script>new Starry('starLyrics', {name:'ratingLyrics', maxLength:10, startAt:10});</script>\n";
		$out .= "	</td></tr>";
		$out .= "	<tr><td>Music: </td><td>\n";
		$out .= "		<script>\n";
		$out .= "		new Starry('starMusic', {name:'ratingMusic', maxLength:10, startAt:10});\n";
		$out .= "		</script>\n";
		$out .= "	</td></tr>";
		$out .= "	<tr><td>Singability: </td><td>\n";
		$out .= "		<script>\n";
		$out .= "		new Starry('starSing', {name:'ratingSing', maxLength:10, startAt:10});\n";
		$out .= "		</script>\n";
		$out .= "	</td></tr>";
		$out .= "	<tr><td>Overall: </td><td>\n";
		$out .= "		<script>\n";
		$out .= "		new Starry('starOverall', {name:'ratingOverall', maxLength:10, startAt:10});\n";
		$out .= "		</script>\n";
		$out .= "	</td></tr>";
		$out .= "	<tr height='27'><td colspan='2' align='center'><input title='Save rating' class='songButton' type='submit' name='save' value='Submit' /></td></tr></table>\n";
		$out .= "	<tr><td colspan='2'><hr /></td></tr>";
		$out .= "</table>\n";
		$out .= "</form>\n";
	} else {
		$out = "<div class='songRate' align='center'><a href='listSongRating.php' target='_top'>Review song rating</a></div>\n";
	}
	return $out;
}
?>