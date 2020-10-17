<?php
/*******************************************************************
 * getMember.php
 * Format Member Information
 *******************************************************************/
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

require($_SERVER["DOCUMENT_ROOT"].'/lr/config.php');
require($_SERVER["DOCUMENT_ROOT"].'/lr/functions.php'); 
//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Users) != "yes") {
	exit;
}

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Retrieve Group Members
$q ="SELECT * FROM members WHERE concat(',',groupArray,',') like concat('%,','".$_REQUEST["memberID"]."',',%') ORDER BY mbrLastName,mbrFirstName";
$resMbr = $db->query($q);
if(!$resMbr || (mysqli_num_rows($resMbr) == 0)) {
	exit;
}
echo "<b class=\"b1h\"></b><b class=\"b2h\"></b><b class=\"b3h\"></b><b class=\"b4h\"></b>\n";
echo "	<div class=\"headh\">\n";
echo "		<h4 align='center'>".$_REQUEST["desc"]." Members</h4>\n";
echo "	</div>\n";
echo "	<div class=\"contenth\"><div>\n";
echo "<table width='100%'>\n";
$odd = false;
while($dbMbr=mysqli_fetch_array($resMbr)) {
	$shade=$odd?" style='background-color:#ebebeb'":"";
	$odd = !$odd;
	echo "	<tr><td$shade>".$dbMbr["mbrFirstName"]." ".$dbMbr["mbrLastName"]."</td></tr>\n";
}
echo "</td></tr>\n";
echo "</table>\n";
echo "	</div></div><b class=\"b4bh\"></b><b class=\"b3bh\"></b><b class=\"b2bh\"></b><b class=\"b1h\"></b>\n";
?>
