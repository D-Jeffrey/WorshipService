<?php
/*******************************************************************
 * editActMessage.php
 * Edit Activation Message
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
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Edit New Member Activation Message', $_SERVER['REQUEST_URI'], 1);

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Save values
if(isset($_POST["save"])) {
	$sql = "UPDATE siteconfig SET actMessage='".$_POST["actMessage"]."'";
	$resCfg = $db->query($sql);
	echo mysqli_error();
//	header("Location: index.php");
//	exit;
}

/* Retrieve Page Text */
$sql = "SELECT * FROM siteconfig";
$resCfg = $db->query($sql);
$dbCfg=mysqli_fetch_array($resCfg);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Edit New Member Activation Message</title>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/overlibmws/overlibmws.js"></script>

<!-- TinyMCE -->
<script type="text/javascript" src="/scripts/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
      tinymce.init({
	selector: "textarea",
	plugins: [
			"advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
			"searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
			"save table contextmenu directionality emoticons textcolor paste  textcolor colorpicker template"
	],
	add_unload_trigger: true,
	autosave_ask_before_unload: true,
	content_css : "<?php echo $baseFolder; ?>css/tw.css",
		toolbar: "fullscreen  | undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | searchreplace | link unlink  code | preview | forecolor backcolor | spellchecker | visualblocks restoredraft ",
		menubar: true,
		toolbar_items_size: 'small',
	  browser_spellcheck : true,
	  templates : [
	   {title: 'Member First Name', description: '{%memberFirstName%}', content: '{%memberFirstName%}'},
	   {title: 'Member Last Name', description: '{%memberLastName%}', content: '{%memberLastName%}'},
	   {title: 'User Name', description: '{%userName%}', content: '{%userName%}'},
	   {title: 'User Password', description: '{%userPassword%}', content: '{%userPassword%}'}
	  ] 
	
	});
</script>
<!-- /TinyMCE -->

<script type="text/javascript">
window.onbeforeunload = function() {
	if(!(tinymce.editors[0].isNotDirty)) {
			return "You have not saved your changes. If you continue, your work will not be saved.";
	}
}
</script>
<?php

$hlpID = 21;
$title = "Edit Activation Message";
include("header.php");

echo "<h3>See Insert -> Template for template items</h3>";
echo "<form align='center' style='width:100%;margin-top:0px;border-bottom:2px ridge;border-top:2px ridge;' name=\"frm\" action=\"editActMessage.php\" method=\"post\">\n";
echo "<textarea name='actMessage' id='actMessage' rows='15' style='width: 100%'>".$dbCfg["actMessage"]."</textarea>\n";

echo "<input name=\"cancel\" type=\"button\" value=\"Cancel\" onClick=\"document.location='index.php';\" class=\"button\"><input name=\"save\" type=\"submit\" value=\"Save\" class=\"button\"></form>\n";

echo "</body>\n</html>\n";
?>
