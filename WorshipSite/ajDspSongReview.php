<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_cache_limiter();
session_start();

require('lr/config.php');
require('lr/functions.php'); 

if(isset($_SESSION['user_id'])) {
	$memberID = $_SESSION['user_id'];
} else {
	exit;
}

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


/* Display Songs */
$q = "SELECT addedBy, songName, songLink as sLink, songrating.songID as sID, songArtist, songAlbum, IFNULL(memberID,0) AS newRating, ratingLyrics, ratingMusic, ratingSing, ratingOverall FROM songrating LEFT JOIN songratingbymember on songrating.songID=songratingbymember.songID AND memberID=$memberID ORDER BY songName";
$resSong = $db->query($q);
echo "<table style='border-collapse:collapse;border:2px ridge;width:100%'>\n";
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
	echo "<h3 align='center'>No songs currently listed for review.</h3>\n";
}
echo "</table>\n";

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