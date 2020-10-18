<?php
/*******************************************************************
 * editRoleTypes.php
 * Edit Role Category
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
//   $valUser = $_SESSION["user_id"] == $_REQUEST["id"];
if (allow_access(Administrators) != "yes") { 
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


$action = $_REQUEST["action"];
$typeID = $_REQUEST["typeID"];

// Save changes
if(isset($_POST["save"])) {
	if($action=="add") {
		$q = "INSERT INTO roletypes VALUES(0,0,'".$_POST["typeDescription"]."',".$_POST["typeContact"].")";
	} else {
		$q = "UPDATE roletypes SET typeDescription='".$_POST["typeDescription"]."',typeContact=".$_POST["typeContact"]." WHERE typeID=$typeID";
	}
	$resMbr = $db->query($q);
	echo "<script>parent.window.dspRoleTypes();parent.window.hs.close();</script>\n";
	exit;
}

if($action=="edit") {
	// Retrieve Role Type
	$sql = "SELECT *,concat(mbrFirstName,' ',mbrLastName) AS mbrName FROM roletypes LEFT JOIN members ON typeContact=memberID WHERE typeID=$typeID";
	$resMbr = $db->query($sql);
	$dbMbr=mysqli_fetch_array($resMbr);
	$typeDescription = htmlentities($dbMbr["typeDescription"],ENT_QUOTES);
	$selMember = $dbMbr["mbrName"];
	$typeContact = $dbMbr["typeContact"];
} else {
	$typeDescription = "";
	$selMember = "";
	$typeContact = 0;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Edit Role Category</title>
<script type="text/javascript" src="scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript" src="scripts/scriptaculous/src/scriptaculous.js"></script>

<script type="text/javascript">

function startContact() {
	new Ajax.Autocompleter('selMember', 'contactBox', 'acGetMembers.php',{tokens:[','],minChars:2,afterUpdateElement:setContactID});
}

function setContactID(text,li) {
	var liParts = li.id.split(";");
	if(li.id && liParts[0] > 0) {
		document.frmRole.typeContact.value=liParts[0];
	} else {
		document.frmRole.typeContact.value=0;
	}
}

function valRoleType() {
	var frm = document.frmRole;
	if(frm.typeDescription.value=="") {
		alert("Please enter a Category Description");
		frm.typeDescription.focus();
		return false;
	}

	return true;
}
</script>
<style>
div.autocomplete {
	border: 1px solid #999;
	background-color: #fff;
	max-height:200px;
	overflow-y:scroll;
}
div.autocomplete ul {
	list-style: none;
	margin:0;
	padding:0;
}
div.autocomplete li { 
	padding: 2px 3px;
}
div.autocomplete strong { 
	font-weight: bold;
	text-decoration: underline;
}
div.autocomplete li.selected { 
	color: #fff;
	background-color: #8c1000;
	cursor:pointer;
}
</style>

<?php
echo "<link rel=\"stylesheet\" href=\"{$baseFolder}css/tw.css\" type=\"text/css\">\n";
echo "<style>\n";
echo "form {\n";
echo "	padding:0px;\n";
echo "}\n";
echo "</style>\n";
echo "</head><body style='background-color:#ffffff'>\n";
echo "<form style='margin-top:0px;border-bottom:2px ridge;border-top:2px ridge;' name=\"frmRole\" action=\"editRoleTypes.php\" onSubmit=\"return valRoleType();\" method=\"post\">\n";
echo "<input type='hidden' name='action' value='$action'>\n";
echo "<input name=\"typeID\" type=\"hidden\" value='$typeID'>\n";
echo "<input name=\"typeContact\" type=\"hidden\" value='$typeContact'>\n";
echo "<table>\n";
$mand = "<span style='font-weight:bold;color:#cc0000;'>*</span>";
echo "	<tr><td>Category Description:$mand</td><td><input type='text' name='typeDescription' size='40' maxlength='50' value='$typeDescription' /></td></tr>";
echo "	<tr><td>Category Coordinator:&nbsp;</td><td><input onFocus=\"startContact();\" type=\"Text\" id=\"selMember\" maxlength=\"100\" size=\"40\" name=\"selMember\" value=\"$selMember\" autocomplete=\"off\" onChange=\"document.frmRole.typeContact.value=0;\" ><div id=\"contactBox\" class=\"autocomplete\" style=\"display:none\">&nbsp;</div></td></tr>";
echo "	<tr><td colspan='2' align='center'><br /><input name=\"cancel\" type=\"button\" value=\"Cancel\" onClick=\"parent.window.hs.close();\" class=\"button\">&nbsp;<input name=\"save\" type=\"submit\" value=\"Save\" class=\"button\"></td></tr>\n";
echo "</table>\n";
echo "</form>\n";
echo "</body>\n</html>\n";
?>