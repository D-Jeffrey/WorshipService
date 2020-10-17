<?php
require('lr/config.php');

//Will Store all the songs retrieved from the database.
$suggestions = array();

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

$value = isset($_REQUEST['newSong']) ? $_REQUEST['newSong'] : "";

/*Query to the songs table. It retrieves all the names of the songs*/
$sql = "SELECT songID,songName,songLink,songText FROM songs WHERE songName LIKE '%$value%'";

$res = $db->query($sql) or die(mysqli_error());
echo "<ul>";
if(mysqli_num_rows($res)>0) {
	while($row = mysqli_fetch_assoc($res)) {
		$match = preg_replace('/' .$value. '/i',"<strong>$0</strong>", $row["songName"], 1);
		echo "<li id=\"".$row["songID"]."#%".$row["songLink"]."#%".htmlentities($row["songText"],ENT_QUOTES)."\">$match</li>";
	}
}
echo "</ul>";

// Frees up all the resources consumed by mysql connection.
mysqli_free_result($res);
mysqli_close($db);
?>