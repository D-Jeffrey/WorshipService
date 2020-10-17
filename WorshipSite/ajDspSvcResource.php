<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_cache_limiter();
session_start();

require('lr/config.php');
require($baseDir.'/lr/functions.php'); 

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

$isAdmin = allow_access(Administrators) == "yes";

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Delete resource from the service
if(isset($_REQUEST['act']) && $_REQUEST['act']=="del") {
	$sql = "SELECT rscLink FROM serviceresources WHERE serviceID=$serviceID AND resourceID=".$_REQUEST["rsc"];
	$resRsc = $db->query($sql);
	if($dbRsc=mysqli_fetch_array($resRsc)) {
		unlink($_SERVER["DOCUMENT_ROOT"].$dbRsc["rscLink"]);
		$sql = "DELETE FROM serviceresources WHERE serviceID=$serviceID AND resourceID=".$_REQUEST["rsc"];
		$resRsc = $db->query($sql);
	}
}

/* Retrieve member Categories */
$q = "SELECT typeID FROM roles WHERE CONCAT(',','".$_SESSION['roles']."',',') LIKE CONCAT('%,',roleID,',%') ORDER BY typeID";
$resRsc = $db->query($q);
$catArray = ",";
$oldCat = "";
while($dbRsc=mysqli_fetch_array($resRsc)) {
	if($oldCat!=$dbRsc["typeID"]) {
		$catArray .= ",".$dbRsc["typeID"];
		$oldCat = $dbRsc["typeID"];
	}
}
$catArray .= ",";

/* Retrieve Resources */
$q = "SELECT * FROM serviceresources WHERE serviceID = $serviceID ORDER BY rscDescription";
$resRsc = $db->query($q);
$rscDesc = "<table>\n";
while($dbRsc=mysqli_fetch_array($resRsc)) {
	if(chkResourceAccess($dbRsc["rscCategories"],$catArray)) {
		$rscDesc .= "	<tr>";
		$editRscLink = "<a id='hsEditResource' href='editResource.php?id=$serviceID&rid=".$dbRsc["resourceID"]."&act=Edit' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 530, contentID: 'divAddSong', headingText: 'Edit Resource' } )\"><img src='images/edit.png'></a>";
		$rscDesc .= $isAdmin?"<td width='42'><a href='#' onClick=\"delResource(".$dbRsc["resourceID"].",'".addslashes($dbRsc["rscDescription"])."');\" title='Remove Resource from Service'><img src='images/icon_delete.gif'></a>&nbsp;$editRscLink</td>\n":"";
		$rscDesc .= "		<td><a href='/getResource.php?sid=$serviceID&amp;rid=".$dbRsc["resourceID"]."' target='_blank'>".$dbRsc["rscDescription"]."</a></td>\n";
		$rscDesc .= "	</tr>\n";
	}
}
echo $rscDesc;
echo $isAdmin?"	<tr><td colspan='2' height='30'><span id='btnLink'><a id='hsEditResource' href='editResource.php?id=$serviceID&act=Add' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 530, contentID: 'divAddSong', headingText: 'Add Resource' } )\">Add Resource</a></span></td></tr>\n":"";

echo "</table>\n";


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