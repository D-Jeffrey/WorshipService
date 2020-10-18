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

$isAdmin = allow_access(Administrators) == "yes";

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Delete song/text from the service order
if(isset($_REQUEST['act']) && $_REQUEST['act']=="del") {
	$sql = "DELETE FROM scheduledefaults WHERE scheduleID=".$_REQUEST["sid"];
	$resMbr = $db->query($sql);
}

include ("incDspDefSchedule.php");

echo $schDesc;
?>