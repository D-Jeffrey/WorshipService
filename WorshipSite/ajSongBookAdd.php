<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require('lr/config.php');

//Will Store all the songs retrieved from the database.
if(isset($_REQUEST['bid'])) {
	$bookID = $_REQUEST['bid'];
} else {
	exit;
}

if(isset($_REQUEST['sid'])) {
	$songID = $_REQUEST['sid'];
} else {
	exit;
}

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


if($_REQUEST["act"]=="add") {
	/*Query to insert a record into the songbooksongs table */
	$sql = "INSERT INTO songbooksongs VALUES($bookID,$songID,999)";
	$res = $db->query($sql);
} else if($_REQUEST["act"]=="edittitle") {
	/*Query to update record in the songbooksongs table */
	$sql = "UPDATE songbook SET bookTitle='".stripslashes($_REQUEST["t"])."',private=".$_REQUEST["p"]." WHERE bookID=$bookID";
	$res = $db->query($sql);
} else if($_REQUEST["act"]=="addbook") {
	/*Query to update record in the songbooksongs table */
	$sql = "INSERT INTO songbook VALUES(0,'".stripslashes($_REQUEST["t"])."','".$_REQUEST["uid"]."',".$_REQUEST["p"].")";
	$res = $db->query($sql);
	$bookID = $db->insert_id;
}
if(!$res && $_REQUEST["act"]=="add") {
	echo "<!-- -->\n";
}

if($_REQUEST["act"]=="edittitle") {
	echo "<form name='frmTitle' method='post'>\n";
	echo "	<b>Book Title:</b>&nbsp;\n";
	echo "	<input type='text' name='bookTitle' maxlength='100' size='25' value='".stripslashes(htmlentities(stripslashes($_REQUEST["t"]),ENT_QUOTES))."' />&nbsp;&nbsp;\n";
	echo "	<b>Private</b>&nbsp;\n";
	$chk = $_REQUEST["p"]==1?" checked":"";
	echo "	<input type='checkbox' name='private' value='1'$chk />&nbsp;&nbsp;\n";
	echo "	<input type='button' name='updBook' value='Update' onClick='editBookTitle();'>\n";
	echo "	</form>\n";
} else if($_REQUEST["act"]=="addbook") {
	echo $bookID;
} else {
	/* Retrieve Song List */
	$q = "SELECT bookID, songOrder, songOrder, songName,sb.songID AS sID FROM songbooksongs sb LEFT JOIN songs ON sb.songID = songs.songID WHERE bookID = $bookID ORDER BY songOrder, songName";
	$resSong = $db->query($q);
	$songDesc = "<table style='border-collapse:collapse;' border='0' width='450' align='left'>\n";
	$i = 1;

	$songDesc .= "	<tr bgcolor='#e6e6e6'>\n";
	$songDesc .= "		<td style='border:1px solid #000000;' width='30'>&nbsp;</td>\n";
	$songDesc .= "		<td style='border:1px solid #000000;font-weight:bold'>Song Name</td>\n";
	$songDesc .= "	</tr>\n";
	$shade = false;
	while($dbsong=mysqli_fetch_array($resSong)) {
		$bgcolor = $shade?"#efefef":"";
		$shade = !$shade;
		$songDesc .= "	<tr bgcolor='$bgcolor'>\n";
		$songDesc .= "		<td style='text-align:center;border:1px solid #e1e1e1' width='30'>\n";
		$songDesc .= "			<a href='#' onClick=\"delSong(".$dbsong["sID"].",'".addslashes($dbsong["songName"])."');\" title='Remove Song from Song Book'><img src='images/icon_delete.gif'></a>&nbsp;\n";
		$songDesc .= "		</td>\n";
		$songDesc .= "		<td style='border:1px solid #e1e1e1' title='Song Name'><a href='getSong.php?songID=".$dbsong["sID"]."&chords=0' onclick=\"return hs.htmlExpand(this, { objectType: 'ajax', contentID: 'divSongDisplay', headingText: 'Display Song',height: 500 } )\" class='highslide'>".$dbsong["songName"]."</a></td>\n";
		$songDesc .= "	</tr>\n";
		$i++;
	}
	echo $songDesc."\n</table>\n";
}
// Frees up all the resources consumed by mysql connection.
//mysqli_free_result($resSong);
//mysqli_close($db);
?>