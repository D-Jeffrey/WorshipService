<?php
/*******************************************************************
 * editSiteConfig.php
 * Update Site Configuration Settings
 *******************************************************************/
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

require('lr/config.php');
require('lr/functions.php'); 
//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Administrators) != "yes") { 
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Site Configuration', $_SERVER['REQUEST_URI'], 1);

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Process Delete Service Request
if(isset($_REQUEST["action"]) && $_REQUEST["action"]=="del" && isset($_REQUEST["year"])) {
    $sql = "SELECT serviceID FROM services WHERE Year(svcDateTime) =".$_REQUEST["year"];
    $resQ = $db->query($sql);

	while ($rows = mysqli_fetch_row($resQ)) {
        
        $id = $rows[0];
   	    $q = "DELETE FROM serviceorder WHERE serviceID=".$id;
	    $result = $db->query($q);
        $q = "DELETE FROM serviceteam WHERE serviceID=".$id;
	    $result = $db->query($q);
        $q = "DELETE FROM serviceresources WHERE serviceID=".$id;
	    $result = $db->query($q);
        $q = "DELETE FROM svcchangereq WHERE serviceID=".$id;
	    $result = $db->query($q);
        
    }
    $q = "DELETE FROM serviceschedule WHERE Year(schDateTime)=".$_REQUEST["year"];
   $result = $db->query($q);       
    $q = "DELETE FROM teamschedule WHERE Year(svcDate)=".$_REQUEST["year"];
	$result = $db->query($q);   
	$q = "DELETE FROM services WHERE Year(svcDateTime) =".$_REQUEST["year"];
	$result = $db->query($q);
    header("Location: editPurge.php"); 
}

	

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Site Configuration</title>
<link rel="stylesheet" href="<?php echo $baseFolder; ?>css/tw.css" type="text/css">
<style>
form {
	padding:0px;
}
</style>

<script type="text/javascript" src="/scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript" src="/scripts/scriptaculous/src/scriptaculous.js"></script>




<?php

$hlpID = 0;
$title = "Purge Service Calendar";
include("header.php");
echo "\n<script>\n";

echo "function delServices(svcdate) {\n";
echo "	if(confirm('Do you wish to delete all the services for :\\n<b>'+svcdate+'</b>?')) {\n";
echo "		window.location='".$_SERVER['PHP_SELF']."?year='+svcdate+'&action=del';\n";
echo "	}\n";
echo "	return false;\n";
echo "}\n";

echo "</script>\n";
/* Retrieve List of site which need to be purged*/

$sql = "SELECT COUNT(serviceID) as cID ,  date_format(svcDateTime, '%Y') as svcYear FROM services  Group by svcYear order by svcYear";
$resCat =$db->query($sql); 


echo "<br /><div style='background-color: yellow;bgcolor=;font-size: x-large;color: darkred;text-align: -webkit-center;'>WARNING This will make LARGE SCALE DELETES to your Service Schedules</div><p />\n";

echo "<br /><form style='margin:0px;' name='frmConfig' method='post' action='editPurge.php'>\n";
echo "<table style='margins:10px;' border='0' align='center'>\n";
echo "<tr><td colspan=4> <b>Purge Services by Year:</b><br></tr>\n";
echo "	<tr bgcolor='#ebebeb'>\n";
echo "		<td></td>\n";
echo "		<td><b>Year</b></td>\n";
echo "		<td></td>\n";
echo "		<td><b>Services to DELETE</b></td>\n";
echo "	</tr>\n";


while($dbrow=mysqli_fetch_array($resCat)) {
    echo "	<tr>\n";
    echo "	<td><a title='Delete Services' href='#' onClick='delServices(".$dbrow["svcYear"].
        ");'><br /><img src='{$baseFolder}images/icon_delete.gif' width='12' height='12' alt='Delete All Services for this year' class='icon'></a></td>";
    echo "		<td>". $dbrow["svcYear"]. "</td>\n";
    echo "		<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
    echo "		<td>". $dbrow["cID"]. "</td>\n";
  
    echo "	</tr>\n";
}

echo "</table>\n";
echo "</form>\n";
echo "<br /><div style='text-align: -webkit-center;'>The reason for using this is, you must clearn schedules in order to remove members who where scheduled in older services</div><p />\n";

echo "</body>\n</html>\n";
?>