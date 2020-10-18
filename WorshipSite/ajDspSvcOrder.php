<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_cache_limiter();
session_start();

require('lr/config.php');
require('lr/functions.php'); 

//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Administrators) != "yes") { 
	header("Location: /lr/login.php"); 
	exit;
}

if(isset($_REQUEST['id'])) {
	$serviceID = $_REQUEST['id'];
} else {
	exit;
}

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Delete song/text from the service order
if(isset($_REQUEST['act']) && $_REQUEST['act']=="del") {
	$sql = "DELETE FROM serviceorder WHERE serviceID=$serviceID AND orderID=".$_REQUEST["oid"];
	$resMbr = $db->query($sql);
}

/* Retrieve Song List */
$q = "SELECT orderID,orderType,songKey,iTunesLink, songNumber, songName,orderDescription,orderDetails, serviceorder.songLink as sLink, songText, serviceorder.songID as sID FROM serviceorder LEFT JOIN songs ON serviceorder.songID = songs.songID WHERE serviceID = $serviceID ORDER BY songNumber";
$resSong = $db->query($q);
$songDesc = "<table>";
$i = 1;
while($dbsong=mysqli_fetch_array($resSong)) {
	if($dbsong["orderType"]=="S") {
		$sLink = $dbsong["songText"]==""?"&nbsp;":"<a href='dspSvcSong.php?id=".$dbsong["sID"]."&sid=$serviceID'>";
		$sLink2 = $dbsong["songText"]==""?"":"</a>";
		if($dbsong["sLink"]=="") {
			$sLinkPlay = "<span style='font-size:9pt;'>&nbsp;&nbsp;&nbsp;</span>";
		} else {
			$sLinkPlay = "<a href='".$dbsong["sLink"]."' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', objectWidth: 480, objectHeight: 385, width: 480  } )\"><img src='images/play.png' border='0' alt='Play in new window'></a>";
			if (strpos(strtolower($linkSrc), "youtube.com/embed/")) {
				    preg_match("/\/embed\/([a-z0-9A-Z\-]+)/", $linkSrc, $matches);
					$linkSrcn = "https://www.youtube.com/watch?v=" . $matches[1] . "&feature=player_embedded";
				    $sLinkPlay = $sLinkPlay . "<a href='".$linkSrcn."' title='Play music video in new tab' Target='_blank' \"><img src='images/playr.png' border='0' alt='Play in new window'></a>";
				}
	
		}
		if($dbsong["iTunesLink"]=="") {
			$sLinkiTunes = "&nbsp;";
		} else {
			$sLinkiTunes = $dbsong["iTunesLink"];
		}
		$songDesc .= "	<tr><td width='40'><a href='#' onClick=\"delSong(".$dbsong["sID"].",".$dbsong["orderID"].",'".$db->real_escape_string($dbsong["songName"])."');\" title='Remove Song from Service'><img src='images/icon_delete.gif'></a>&nbsp;<a id='hsEditTeam' href='editOrderSong.php?id=$serviceID&act=edit&oid=".$dbsong["orderID"]."' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',height: 450,width: 450, contentID: 'divAddSong', headingText: 'Update Song' } )\"><img src='images/edit.png'></a></td>\n";
		$songDesc .= "		<td title='Song Key'><b>".$dbsong["songKey"]."</b></td>\n";
		$songDesc .= "		<td>$sLink".$dbsong["songName"]."$sLink2</td>\n";
		$songDesc .= "		<td>$sLinkPlay&nbsp;$sLinkiTunes</td>\n";
		$songDesc .= "	</tr>\n";
	} else {
		$songDesc .= "	<tr><td width='40'><a href='#' onClick=\"delSong(".$dbsong["sID"].",".$dbsong["orderID"].",'".addslashes($dbsong["orderDescription"])."');\" title='Remove Item from Service'><img src='images/icon_delete.gif'></a>&nbsp;<a id='hsEditTeam' href='editOrderText.php?id=$serviceID&act=edit&oid=".$dbsong["orderID"]."' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',height: 450,width: 692, contentID: 'divAddSong', headingText: 'Update Text' } )\"><img src='images/edit.png'></a></td>\n";
		$songDesc .= "		<td>--</td>\n";
		if($dbsong["orderDetails"]=="") {
			$sLinkPlay = "";
			$sLinkPlay2 = "";
			$tdiv = "";
		} else {
			$sLinkPlay = "<a title='Display Details' href='' onclick=\"return hs.htmlExpand(this, { contentId: 'hsc".$dbsong["orderID"]."' } )\">";
			$sLinkPlay2 = "</a>";
			$tdiv = "<div class=\"highslide-html-content\" id=\"hsc".$dbsong["orderID"]."\" style=\"width: 480px\">";
			$tdiv .= "    <div width='100%' align='right'><a href=\"#\" onclick=\"hs.close(this)\">";
			$tdiv .= "        Close";
			$tdiv .= "    </a></div>";
			$tdiv .= "    <div style='background-color:#efefef;overflow:auto;border:2px inset;padding:3px' class=\"highslide-body\">";
			$tdiv .= "        ".nl2br($dbsong["orderDetails"]);
			$tdiv .= "    </div>";
			$tdiv .= "</div>";
		}
		$songDesc .= "		<td colspan='2'>$sLinkPlay".$dbsong["orderDescription"]."$sLinkPlay2$tdiv</td>\n";
		$songDesc .= "	</tr>\n";
	}
	$i++;
}
echo $songDesc."</table>";
?>