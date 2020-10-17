<?php
require('lr/config.php');

//Will Store all the members retrieved from the database.
$suggestions = array();

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


$value = isset($_REQUEST['selMember']) ? $_REQUEST['selMember'] : "";
$roleSel = isset($_REQUEST['roleID']) ? " AND concat(',',roleArray,',') LIKE '%,".$_REQUEST['roleID'].",%'" : "";

/*Query to the members table. It retrieves all the names of the members*/
$sql = "SELECT memberID,mbrEmail1,mbrEmail2,concat(mbrFirstName,' ',mbrLastName) as mbrName FROM members WHERE mbrStatus='A' AND concat(mbrFirstName,' ',mbrLastName) LIKE '%$value%'$roleSel ORDER BY mbrName";

$res = $db->query($sql) or die(mysqli_error());
echo "<ul>";
if(mysqli_num_rows($res)>0) {
	while($row = mysqli_fetch_assoc($res)) {
		$match = preg_replace('/' .$value. '/i',"<strong>$0</strong>", $row["mbrName"], 1);
		echo "<li id=\"".$row["memberID"].";".$row["mbrEmail1"].";".$row["mbrEmail2"]."\">$match</li>";
	}
}
echo "</ul>";

// Frees up all the resources consumed by mysql connection.
mysqli_free_result($res);
mysqli_close($db);
?>