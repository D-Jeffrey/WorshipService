<?php 
/*******************************************************************
 * dspTeamResources.php
 * Display Team Resource Page
 *******************************************************************/
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

require('lr/config.php');
require($baseDir.'/lr/functions.php'); 
//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Users) != "yes") { 
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

$isAdmin = allow_access(Administrators) == "yes";

$teamID = $_REQUEST["id"];

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


$q = "SELECT * FROM teamresources WHERE teamID=$teamID";
$resRsc = $db->query($q);
if(!$resRsc || (mysqli_num_rows($resRsc) <= 0)){
	header("Location: /index.php");
	exit;
} else {
	$dbRsc=mysqli_fetch_array($resRsc);	
}

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add("Display ".$dbRsc["teamName"]." Team Resources", $_SERVER['REQUEST_URI'], 3);



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle." - ".$dbRsc["teamName"]; ?> Team Resources</title>
<?php
$hlpID = 0;
$title = "Display Messages";
include("header.php");

echo "	<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"5\">\n";
echo "		<tr>\n";
echo "			<td>\n";
echo "				<h2 align=\"center\">".$dbRsc["teamDescription"]."</h2>";
echo $dbRsc["pageLayout"];
echo "			</td>\n";
echo "		</tr>\n";
echo "	</table>\n";
if($isAdmin) echo "<div style='position:absolute;top:148px;right:10px'><a href='/editTeamResources.php?id=$teamID'>Edit Page <img src='/images/edit.png' border='0' alt='Edit Page' /></div>\n";
echo "</body>\n</html>\n";
