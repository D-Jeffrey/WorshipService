<?php
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
session_start();

require($_SERVER["DOCUMENT_ROOT"].'/lr/config.php');
require('lr/functions.php');
if (allow_access(Coordinator) != "yes") {
	exit;
}

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Get data records from table. 
$q="select mbrEmail1 FROM members WHERE mbrStatus='A'";
$resSongs = $db->query($q);
while($row=mysqli_fetch_array($resSongs)){
	echo $row["mbrEmail1"].",";
}
?>
