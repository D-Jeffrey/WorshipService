<?php
/*******************************************************************
 * editSvcSchedule.php
 * Update Service schedule information
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
	exit;
}

$isAdmin = allow_access(Administrators) == "yes";

$serviceID = $_REQUEST["id"];
$action = $_REQUEST["act"];
$scheduleID = isset($_POST["scheduleID"])?$_POST["scheduleID"]:(isset($_REQUEST["sid"])?$_REQUEST["sid"]:0);

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

if (!isset($_POST["subact"])) {
	$_POST["subact"] ="";
	}
// Add new text to the service
if($_POST["subact"]=="add") {
	$schCancel = isset($_POST["schCancel"])?1:0;
	if(isset($_POST["catEveryone"])) {
		$catArray = "*";
	} else if(isset($_POST["catArray"])) {
		$catArray = implode(",",$_POST["catArray"]);
	} else {
		$catArray = "";
	}
	$sql = "INSERT INTO serviceschedule VALUES($serviceID,0,'".$_POST["schType"]."','".$_POST["schDateTime"]."',".$_POST["schDuration"].",'$catArray','".$_POST["schDescription"]."',$schCancel)";
	$resSong = $db->query($sql);
	echo "<script>parent.window.dspSchedule();parent.window.hs.close();</script>";
	exit;
}

// Update text
if($_POST["subact"]=="edit") {
	$schCancel = isset($_POST["schCancel"])?1:0;
	if(isset($_POST["catEveryone"])) {
		$catArray = "*";
	} else if(isset($_POST["catArray"])) {
		$catArray = implode(",",$_POST["catArray"]);
	} else {
		$catArray = "";
	}
	$sql = "UPDATE serviceschedule SET schType='".$_POST["schType"]."',schDateTime='".$_POST["schDateTime"]."',schDuration=".$_POST["schDuration"].",schCategories='$catArray',schDescription='".$_POST["schDescription"]."',schCancel=$schCancel WHERE serviceID=$serviceID AND scheduleID=".$_POST["scheduleID"];
	$resSong = $db->query($sql);
	echo "<script>parent.window.dspSchedule();parent.window.hs.close();</script>";
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Edit Service Schedule</title>
<link rel="stylesheet" href="<?php echo $baseFolder; ?>css/tw.css" type="text/css">
<style>
form {
	padding:0px;
}
</style>

<script type="text/javascript">
function editSchedule() {
	document.frmSchedule.subact.value="<?php echo $action; ?>";
	document.frmSchedule.submit();
}
</script>

<?php
echo "</head>\n";

/* Retrieve Schedule Item */
if($action=="edit") {
	$q = "SELECT * FROM serviceschedule WHERE serviceID=$serviceID AND scheduleID=$scheduleID";
	$resSch = $db->query($q);
	$i = 1;
	if($dbSch=mysqli_fetch_array($resSch)) {
		$schDescription = $dbSch["schDescription"];
		$schDateTime = $dbSch["schDateTime"];
		$schType = $dbSch["schType"];
		$schDuration = $dbSch["schDuration"];
		$catArray = explode(",",$dbSch["schCategories"]);
		$schCancel = $dbSch["schCancel"];
	} else {
		$schDescription = "";
		$schDateTime = date("Y-m-d")." 12:00:00";
		$schType = "P";
		$schDuration = 1;
		$catArray = array();
		$schCancel = 0;
	}
} else {
	$schDescription = "";
	$schDateTime = date("Y-m-d")." 12:00:00";
	$schType = "P";
	$schDuration = 1;
	$catArray = array();
	$schCancel = 0;
}

echo "<body style='background-color:#ffffff'>\n";
echo "<h2 align=\"center\">Edit Service Schedule</h2>\n";
echo "<form style='margin:0px;' name='frmSchedule' method='post' action='editSvcSchedule.php?id=$serviceID&action=edit'>\n";
echo "<input name=\"subact\" type=\"hidden\">\n";
echo "<input name=\"scheduleID\" type=\"hidden\" value=$scheduleID>\n";
echo "<table border='0' align='center'>\n";
echo "<table>\n";
echo "	<tr>\n";
echo "		<td><b>Type:</b></td>\n";
$chk = $schType=="P"?" checked":"";
echo "		<td><input name=\"schType\" type=\"radio\" id=\"schType\" value=\"P\"$chk />&nbsp;Practice&nbsp;&nbsp;&nbsp;\n";
$chk = $schType=="S"?" checked":"";
echo "			<input name=\"schType\" type=\"radio\" id=\"schType\" value=\"S\"$chk />&nbsp;Service</td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td><b>Date/Time:</b>&nbsp;</td>\n";
echo "		<td><input type=\"Text\" id=\"schDateTime\" maxlength=\"25\" size=\"25\" name=\"schDateTime\" value=\"".substr($schDateTime,0,16)."\">\n";
echo "			<a href=\"javascript:void(0)\" onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.frmSchedule.schDateTime);return false;\" ><img align=\"top\" src=\"{$baseFolder}scripts/popcal/calbtn.gif\" alt=\"Pick a date\"></a>\n";
echo "		</td>\n";
echo "	<tr>\n";
echo "	<tr>\n";
echo "		<td><b>Duration:</b></td>\n";
echo "		<td><select name=\"schDuration\">\n";
for($i=.25;$i<=4;$i=$i+.25) {
	$sel = $i==$schDuration?" selected":"";
	echo "			<option$sel>".number_format($i,2)."</option>\n";
}
echo "		</select>&nbsp;(Hours)\n";
$chk = $schCancel?" checked":"";
echo "			&nbsp;- OR -&nbsp;<input name=\"schCancel\" type=\"checkbox\" id=\"schCancel\" value=\"S\"$chk />&nbsp;Cancel</td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td><b>Description:</b></td>\n";
echo "		<td><input title=\"Description\" name=\"schDescription\" type=\"text\" id=\"schDescription\" size=\"45\" maxlength=\"255\" value=\"$schDescription\" /></td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
$chk = count($catArray)>0?($catArray[0] == "*"?" checked":""):"";
echo "		<td colspan='2'><b>Service Categories:</b>&nbsp;<input id='catEveryone' name='catEveryone' type='checkbox' value='*'$chk />&nbsp;Everyone</td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td align='left' colspan='2'><table align='center'>\n";

// Setup Category checkboxes
$sql = "SELECT * FROM roletypes ORDER BY typeSort, typeDescription";
$resType = $db->query($sql);
$i=0;
while($dbType=mysqli_fetch_array($resType)) {
	echo "		<tr>\n";
	$chk = array_search($dbType["typeID"],$catArray)===FALSE?"":" checked";
	echo "			<td><input id='catArray[$i]' name='catArray[$i]' type='checkbox' value=".$dbType["typeID"]."$chk />&nbsp;".$dbType["typeDescription"]."&nbsp;</td>\n";
	$i++;
	if($dbType=mysqli_fetch_array($resType)) {
		$chk = array_search($dbType["typeID"],$catArray)===FALSE?"":" checked";
		echo "			<td><input id='catArray[$i]' name='catArray[$i]' type='checkbox' value=".$dbType["typeID"]."$chk />&nbsp;".$dbType["typeDescription"]."&nbsp;</td>\n";
		$i++;
		if($dbType=mysqli_fetch_array($resType)) {
			$chk = array_search($dbType["typeID"],$catArray)===FALSE?"":" checked";
			echo "			<td><input id='catArray[$i]' name='catArray[$i]' type='checkbox' value=".$dbType["typeID"]."$chk />&nbsp;".$dbType["typeDescription"]."&nbsp;</td>\n";
			$i++;
		} else {
			echo "			<td>&nbsp;</td>\n";
		}
	} else {
		echo "			<td>&nbsp;</td>\n";
		echo "			<td>&nbsp;</td>\n";
	}
	$i++;
	echo "		</tr>\n";
}
echo "		</table></td>\n";
echo "	</tr>\n";
echo "	<tr><td colspan='2' align='center'><input type='button' name='addSch' value='Save' onClick='editSchedule();'>&nbsp;<input type='button' name='cancel' value='Cancel' onClick='parent.window.hs.close();'></td></tr>\n";
echo "</table>\n";
echo "</td></tr>\n";

echo "</table>\n";
echo "</form>\n";
echo "<iframe width=188 height=166 name=\"gToday:datetime:/scripts/popcal/agenda.js:gfPop:/scripts/popcal/plugins_time.js\" id=\"gToday:datetime:/scripts/popcal/agenda.js:gfPop:/scripts/popcal/plugins_time.js\" src=\"/scripts/popcal/ipopeng.htm\" scrolling=\"no\" frameborder=\"0\" style=\"visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;\">\n";
echo "</iframe>\n";
echo "</body>\n</html>\n";
?>