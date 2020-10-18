<?php
/*******************************************************************
 * showMessage.php
 * Display User Message
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
if (allow_access(Users) != "yes") { 
	echo "<form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form>\n";
	echo "<script>document.frmLogin.submit();</script>\n";
	exit;
}

$messageID = $_REQUEST["id"];

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Update message
$sql = "UPDATE sitemessages SET msgStatus='R' WHERE messageID=$messageID";
$resMsg = $db->query($sql);

// Retrieve message
$sql = "SELECT *,CONCAT(mbrFirstName,' ',mbrLastName) AS mbrName FROM sitemessages INNER JOIN members ON fromID=memberID WHERE messageID=$messageID";
$resMsg = $db->query($sql);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Display Message</title>
<link rel="stylesheet" href="<?php echo $baseFolder; ?>css/tw.css" type="text/css">
<style>
form {
	padding:0px;
}
</style>

<?php
echo "</head>\n";

$dbMsg=mysqli_fetch_array($resMsg);
echo "<body>\n";
echo "<h2 align=\"center\">Display Message</h2>\n";
echo "<form style='margin:0px;' >\n";
echo "	<tr>\n";
echo "<table  style='width: 680px'>\n";
echo "		<td><b>Message From:</b></td>\n";
echo "		<td>".$dbMsg["mbrName"]."</td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td><b>Received:</b></td>\n";
echo "		<td>".$dbMsg["msgTime"]."</td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td><b>Message SubJect:</b></td>\n";
echo "		<td>".$dbMsg["msgSubject"]."</td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td colspan='2'><b>Message Body:</b></td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td colspan='2' style='border:2px inset'>".$dbMsg["msgBody"]."</td>\n";
echo "	</tr>\n";
echo "	<tr><td colspan='2' align='center'><input type='button' name='done' value='Done' onClick='parent.window.hs.close();'></td></tr>\n";
echo "</table>\n";

echo "</form>\n";
echo "</body>\n</html>\n";
?>