<?php
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

require('lr/config.php');
require('lr/functions.php'); 

if (allow_access(Users) != "yes") { 
	echo "<html>\n<body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

$serviceID = $_REQUEST["sid"];
$resourceID = $_REQUEST["rid"];

$isAdmin = allow_access(Administrators) == "yes";

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


$q = "SELECT * FROM serviceresources WHERE serviceID=$serviceID AND resourceID=$resourceID";
$resRsc = $db->query($q);
if(!$resRsc || mysqli_num_rows($resRsc)<=0) {
	echo "<html><body>\n";
	echo "<h1 style='color:#cc0000'>Invalid Request</h1>\n";
	echo "<h2>Resource Not Found</h2>\n";
	echo "</body></html>\n";
	exit;
}
$dbRsc=mysqli_fetch_array($resRsc);
$rscCats = $dbRsc["rscCategories"];

/* Retrieve member Categories */
$q = "SELECT typeID FROM roles WHERE CONCAT(',','".$_SESSION['roles']."',',') LIKE CONCAT('%,',roleID,',%') ORDER BY typeID";
$resCats = $db->query($q);
$catArray = "";
$oldCat = "";
while($dbCats=mysqli_fetch_array($resCats)) {
	if($oldCat!=$dbCats["typeID"]) {
		$catArray .= ",".$dbCats["typeID"];
		$oldCat = $dbCats["typeID"];
	}
}
$catArray .= ",";

if(!chkResourceAccess($rscCats,$catArray)) {
	echo "<html><body>\n";
	echo "<h1 style='color:#cc0000'>Invalid Request</h1>\n";
	echo "<h2>You do not have access to this resource.</h2>\n";
	echo "</body></html>\n";
	exit;
}

// Download File
$fileParts = explode("/",$dbRsc["rscLink"]);
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/force-download");
header("Content-Disposition: inline; filename=\"".$fileParts[count($fileParts)-1] ."\"");
$fh = fopen($_SERVER["DOCUMENT_ROOT"].$dbRsc["rscLink"], 'r');
$data = fread($fh, filesize($_SERVER["DOCUMENT_ROOT"].$dbRsc["rscLink"]));
fclose($fh);
echo $data;
exit;

// Check to see if current member has access to this resource
function chkResourceAccess($cats,$mbrCat) {
	global $isAdmin;
	
	$aCat = explode(",",$cats);
	$aMbrCat = explode(",",$mbrCat);
	if($isAdmin) return true;
	if($cats == "*") return true;
	$catMatch = array_intersect($aMbrCat,$aCat);
	if(count($catMatch)>0) return true;
	return false;
}
?>