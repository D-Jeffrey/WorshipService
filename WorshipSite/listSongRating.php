<?php 
/*******************************************************************
 * listSongRating.php
 * Song Rating
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

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Delete Song
if(isset($_POST["sngact"]) && $_POST["sngact"]=="del") {
	$q = "DELETE FROM songratingbymember WHERE songID = ".$_POST["songID"];
	$resSong = $db->query($q);
	$q = "DELETE FROM songrating WHERE songID = ".$_POST["songID"];
	$resSong = $db->query($q);
}

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('New Song Rating', $_SERVER['REQUEST_URI'], 1);

$isAdmin = allow_access(Administrators) == "yes";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Song Rating</title>

<script type="text/javascript" src="scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript">

function delSong(id,name) {
	if(confirm("Delete song: "+name+"?")) {
		document.frmSong.sngact.value="del";
		document.frmSong.songID.value=id;
		document.frmSong.submit();
	}
}

function dspSongs() {
	var url = 'ajDspSongReview.php';

	var myAjax = new Ajax.Updater(
		'songsDiv', 
		url, {
			method: 'get'
		});
}
</script>
<?php

$hlpID = $isAdmin?17:8;
$title = "New Song Rating";
include("header.php");

$pageNum = (isset($_POST["pageNum"]) && $_POST["pageNum"] > 0)?$_POST["pageNum"]:1;
$pagetxt = "";

echo "<h3 style='margin-left:30px;margin-right:30px;'>Use this page to review and rate new songs. You may add new songs to be rated by the rest of the worship team or review songs that have been added by other team members.  Please note that you will only be able to see your own reviews.</h3>\n";
echo "	<form name=\"frmSong\" action=\"listSongRating.php\" method=\"post\" onSubmit=\"valSearch();\">\n";
echo "	<input name=\"sngact\" type=\"hidden\">\n";
echo "	<input name=\"songID\" type=\"hidden\" value=\"$songID\">\n";
echo "	<table border=\"0\" width=\"100%\" cellspacing=\"1\" cellpadding=\"2\">\n";
echo "		<tr>\n";
echo "			<td valign=\"middle\" align=\"left\">\n";
echo "				<a href='editSongReview.php?&act=add&sid=0' title='Add New Song' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdReview',headingText: 'Add New Song',width: 540 });\"><img src=\"images/icon_new.gif\" style='vertical-align:middle'>Add New Song</a>\n";
echo "			</td>\n";
echo "		</tr>\n";
echo "	</table></form>\n";


$q = "SELECT addedBy, songName, songLink as sLink, songrating.songID as sID, songArtist, songAlbum, IFNULL(memberID,0) AS newRating, ratingLyrics, ratingMusic, ratingSing, ratingOverall FROM songrating LEFT JOIN songratingbymember on songrating.songID=songratingbymember.songID AND memberID=".$_SESSION['user_id']." ORDER BY songName";
$resSong = $db->query($q);
echo "<div id='songsDiv'><table style='border-collapse:collapse;border:2px ridge;width:100%'>\n";
if($resSong && (mysqli_num_rows($resSong) > 0)){
	echo $pageTxt."<tr><td valign='top'>";
	$songDesc = "<table style='border-collapse:collapse;width:100%;min-width:650px'>\n";
	$songDesc .= "<tr style='border-bottom:1px solid #000000;background-color:#e0e0e0;font-weight:bold'><td>&nbsp;</td><td>Song Title</td><td>Song Artist</td><td>Song Album</td><td>Lyrics</td><td>Music</td><td>Singability</td><td>Overall</td></tr>\n";
	$shade = false;
	while($dbsong=mysqli_fetch_array($resSong)) {
		$bgcolor = $shade?"#efefef":"";
		$shade = !$shade;
		if($dbsong["sLink"]=="") {
			$sLinkPlay = "";
		} else {
			$sLinkPlay = "&nbsp;<a title=\"Play Music Video\" href='".$dbsong["sLink"]."' target='_blank'>(Play)</a>";
		}
		$songDesc .= "	<tr bgcolor='$bgcolor'><td nowrap>\n";
		if (allow_access(Coordinator) == "yes") { 
			$songDesc .= "		<a href='acDspSongReview.php?sid=".$dbsong["sID"]."' title='Display Song Rating Results' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdReview',headingText: 'Display Song Rating Results',width: 540 });\"><img src='images/icon_preview.gif'></a>&nbsp;\n";
		}
		if (allow_access(Administrators) == "yes" || $dbsong["addedBy"]==$_SESSION['user_id']) { 
			$songDesc .= "		<a href='editSongReview.php?act=edit&sid=".$dbsong["sID"]."' title='Edit Song Info' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdReview',headingText: 'Edit Song Info',width: 540 });\"><img src='images/edit.png'></a>&nbsp;\n";
			$songDesc .= "		<a onClick=\"delSong(".$dbsong["sID"].",'".addslashes($dbsong["songName"])."');\" href='#' title='Delete Song'><img src=\"images/icon_delete.gif\"></a>&nbsp;\n";
		}
		$songDesc .= "			</td>\n";
		$songDesc .= "		<td nowrap>".$dbsong["songName"]."$sLinkPlay</td>\n";
		$songDesc .= "		<td nowrap>".$dbsong["songArtist"]."</td>\n";
		$songDesc .= "		<td nowrap>".$dbsong["songAlbum"]."</td>\n";
		if($dbsong["newRating"]==0) {
			$songDesc .= "		<td nowrap colspan='4'><a href='editSongReview.php?&act=rate&sid=".$dbsong["sID"]."' title='Rate Song' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdReview',headingText: 'Rate Song',width: 340 });\">(Rate)</a></td>\n";
		} else {			
			$songDesc .= "		<td nowrap><a href='editSongReview.php?&act=ratu&sid=".$dbsong["sID"]."' title='Rate Song' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdReview',headingText: 'Rate Song',width: 340 });\">".genStars($dbsong["ratingLyrics"])."</a></td>\n";
			$songDesc .= "		<td nowrap><a href='editSongReview.php?&act=ratu&sid=".$dbsong["sID"]."' title='Rate Song' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdReview',headingText: 'Rate Song',width: 340 });\">".genStars($dbsong["ratingMusic"])."</a></td>\n";
			$songDesc .= "		<td nowrap><a href='editSongReview.php?&act=ratu&sid=".$dbsong["sID"]."' title='Rate Song' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdReview',headingText: 'Rate Song',width: 340 });\">".genStars($dbsong["ratingSing"])."</a></td>\n";
			$songDesc .= "		<td nowrap><a href='editSongReview.php?&act=ratu&sid=".$dbsong["sID"]."' title='Rate Song' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdReview',headingText: 'Rate Song',width: 340 });\">".genStars($dbsong["ratingOverall"])."</a></td>\n";
		}
		$songDesc .= "	</tr>\n";
	}
	echo $songDesc."</table></td>\n";
	echo "</tr>\n";
} else {
	echo "<h3 align='center'>No songs currently listed for Rating.</h3>\n";
}
echo "</table></div>\n";
echo "<br /><div style='text-align:center'>\n";

// Allow download of info
if (allow_access(Coordinator) == "yes") { 
	echo "<span id='btnLink'><a href='/expSongRating.php'>Export Rating Info</a></span>\n";
	echo "&nbsp;&nbsp;<span id='btnLink'><a href='ofcDspRating.php' title='Display Results Graph' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdReview',headingText: 'Display Results Graph', width:970, height: 580 });\">Display Results Graph</a></span>\n";
}
echo "&nbsp;&nbsp;<span id='btnLink'><a href='ofcDspRating.php?id=".$_SESSION['user_id']."' title='Your Rating Graph' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdReview',headingText: 'Your Rating Graph', width:970, height: 580 });\">Your Rating Graph</a></span></div>\n";

// Update Review
echo "<div id='divUpdReview' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";
echo "</body>\n</html>\n";

function genStars($num) {
	$out = "";
	for($i=1;$i<=10;$i++) {
		if($i<=$num) {
			$out .= "<img width='10' src='images/star.gif' />";
		} else {
			$out .= "<img width='10' src='images/star_no.gif' />";
		}
	}
	return $out;
}
?>