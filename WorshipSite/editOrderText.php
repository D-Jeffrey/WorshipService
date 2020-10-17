<?php
/*******************************************************************
 * editOrderText.php
 * Update Service worship order information (Text Entry)
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
if (allow_access(Administrators) != "yes") { 
	exit;
}

$serviceID = $_REQUEST["id"];
$action = $_REQUEST["act"];
$orderID = $_REQUEST["oid"];
$subaction = isset($_POST["subact"])?$_POST["subact"]:"";

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Add new text to the service
if($subaction=="addtext") {
	$sql = "INSERT INTO serviceorder VALUES($serviceID,0,'T',".$_POST["songNumber"].",0,'','','','".$_POST["orderDescription"]."','".$_POST["orderDetails"]."')";
	$resSong = $db->query($sql);
	echo "<script>parent.window.dspOrder();parent.window.hs.close();</script>";
	exit;
}

// Update text
if($subaction=="edittext") {
	$sql = "UPDATE serviceorder SET songNumber=".$_POST["songNumber"].",orderDescription='".$_POST["orderDescription"]."',orderDetails='".$_POST["orderDetails"]."' WHERE serviceID=$serviceID AND orderID=".$_POST["orderID"];
	$resSong = $db->query($sql);
	echo "<script>parent.window.dspOrder();parent.window.hs.close();</script>";
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Edit Service Order (Text)</title>
<link rel="stylesheet" href="<?php echo $baseFolder; ?>css/tw.css" type="text/css">
<style>
form {
	padding:0px;
}
</style>

<script type="text/javascript">
function editText() {
	if(document.frmOrder.orderDescription.value=="") {
		alert('Please enter a description.');
		document.frmOrder.orderDescription.focus();
		return false;
	}
	document.frmOrder.subact.value="<?php echo $action; ?>text";
	document.frmOrder.submit();
}
</script>

<!-- TinyMCE -->
<script type="text/javascript" src="/scripts/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
      tinymce.init({
	selector: "textarea",
	plugins: [
			"searchreplace save  paste"
	],
	height : 200,
	width : 650,
	statusbar : false,
	add_unload_trigger: false,
	autosave_ask_before_unload: false,
	// content_css : "<?php echo $baseFolder; ?>css/tw.css",
		toolbar: " undo redo | bold italic underline backcolor | alignleft aligncenter alignright alignjustify | searchreplace  | formatselect   ",
		menubar: false,
		toolbar_items_size: 'small',
	  browser_spellcheck : true 


	});
</script>
<!-- /TinyMCE -->

<?php
echo "</head>\n";

/* Retrieve Song */
if($action=="edit") {
	$q = "SELECT orderDescription, songNumber, orderDetails FROM serviceorder WHERE serviceID=$serviceID AND orderID=$orderID";
	$resSong = $db->query($q);
	$i = 1;
	if($dbsong=mysqli_fetch_array($resSong)) {
		$orderDescription = $dbsong["orderDescription"];
		$songNumber = $dbsong["songNumber"];
		$orderDetails = $dbsong["orderDetails"];
	} else {
		$orderDescription = "";
		$songNumber = 999;
		$orderDetails = "";
	}
} else {
	$orderDescription = "";
	$songNumber = 999;
	$orderDetails = "";
}


echo "<body style='background-color:#ffffff'>\n";
echo "<h2 align=\"center\">Edit Worship Order Element (Text)</h2>\n";
echo "<form style='margin:0px;' name='frmOrder' method='post' action='editOrderText.php?id=$serviceID&action=edit'>\n";
echo "<input name=\"subact\" type=\"hidden\">\n";
echo "<input name=\"orderID\" type=\"hidden\" value=$orderID>\n";
echo "<input name=\"songNumber\" type=\"hidden\" id=\"songNumber\" value='$songNumber'>\n";
echo "<table class='serviceDetails' border='1' align='center'>\n";
echo "<table>\n";
// Text Section
echo "<tr id='textSection'><td><table>\n";
echo "	<tr>\n";
echo "		<td><b>Description:</b></td>\n";
echo "		<td><input title=\"Text Description\" name=\"orderDescription\" type=\"text\" id=\"orderDescription\" size=\"45\" maxlength=\"255\" value=\"$orderDescription\" /></td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td colspan='2'><b>Text Details:</b></td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td align='center' colspan='2'><div>\n";
echo "		<textarea name='orderDetails' id='orderDetails' rows='15'  style='width: 90%'>$orderDetails</textarea>\n";
//include("scripts/fckeditor/fckeditor.php") ;
//$oFCKeditor = new FCKeditor('orderDetails') ;
//$oFCKeditor->Height = 250;
//$oFCKeditor->Width = 580;
//$oFCKeditor->Value = $orderDetails;
//$oFCKeditor->Create();
echo "		</div></td>\n";
echo "	</tr>\n";
echo "	<tr><td colspan='2' align='center'><input type='button' name='addTxt' value='Save' onClick='editText();'>&nbsp;<input type='button' name='cancel' value='Cancel' onClick='parent.window.hs.close();'></td></tr>\n";
echo "</table>\n";
echo "</td></tr>\n";

echo "</table>\n";
echo "</form>\n";
echo "</body>\n</html>\n";
?>