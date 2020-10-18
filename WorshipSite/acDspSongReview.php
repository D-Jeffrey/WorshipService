<?php
/*******************************************************************
 * acDspSongReview.php
 * Display Song Review
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
if (allow_access(Coordinator) != "yes") {
	exit;
}

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


if(isset($_REQUEST["sid"])) {
	$songID = $_REQUEST["sid"];
} else {
	echo "Invalid Request.";
	exit;
}

/* Retrieve Song for specified id */
$sql = "SELECT *,songLink as sLink FROM songrating WHERE songID=$songID";
$resSong = $db->query($sql);
$dbSong=mysqli_fetch_array($resSong);
$songName = htmlentities($dbSong["songName"],ENT_QUOTES);
$songArtist = htmlentities($dbSong["songArtist"],ENT_QUOTES);
$songAlbum = htmlentities($dbSong["songAlbum"],ENT_QUOTES);
$songLink = $dbSong["songLink"];

// Retrieve rating information
$sql = "SELECT AVG(ratingLyrics) AS avgLyrics, AVG(ratingMusic) AS avgMusic, AVG(ratingSing) AS avgSing, AVG(ratingOverall) AS avgOverall FROM songratingbymember WHERE songID=$songID";
$resRate = $db->query($sql);
$dbRate=mysqli_fetch_array($resRate);
$ratingLyrics = round($dbRate["avgLyrics"]);
$ratingMusic = round($dbRate["avgMusic"]);
$ratingSing = round($dbRate["avgSing"]);
$ratingOverall = round($dbRate["avgOverall"]);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Display Song Review</title>
<?php
echo "<link rel=\"stylesheet\" href=\"{$baseFolder}css/tw.css\" type=\"text/css\">\n";
echo "</head><body>\n";
$aRating = array("0","1 - Poor","2","3","4","5 - Moderate","6","7","8","9","10 - Excellent");
echo "<form style='margin-top:0px;border-bottom:2px ridge;border-top:2px ridge;' name=\"frmSong\" method=\"post\">\n";
echo "<table class='songhead'>\n";
echo "	<tr><td width='180'>Song Title:$mand </td><td>$songName</td></tr>";
echo "	<tr><td width='180'>Artist: </td><td>$songArtist</td></tr>";
echo "	<tr><td width='180'>Album: </td><td>$songAlbum</td></tr>";
echo "	<tr><td colspan='2'><hr /></td></tr>";
echo "	<tr><td colspan='2'><b>The following reflects the average of all reviews for this song:</b></td></tr>";
echo "	<tr><td width='180'>Lyrics: </td><td>".$aRating[$ratingLyrics]."/10</td></tr>\n";
echo "	<tr><td width='180'>Music: </td><td>".$aRating[$ratingMusic]."/10</td></tr>\n";
echo "	<tr><td width='180'>Singability: </td><td>".$aRating[$ratingSing]."/10</td></tr>\n";
echo "	<tr><td width='180'>Overall: </td><td>".$aRating[$ratingOverall]."/10</td></tr>\n";
echo "	<tr><td colspan='2' align='right'><input name=\"cancel\" type=\"button\" value=\"Close\" onClick=\"parent.window.hs.close();\" class=\"button\"></td></tr></table>\n";
echo "</table>\n";
echo "</body>\n</html>\n";
?>