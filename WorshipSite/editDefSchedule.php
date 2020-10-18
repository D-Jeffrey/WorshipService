<?php
/*******************************************************************
 * editDefSchedule.php
 * Update default schedule information
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

$action = $_REQUEST["act"];
$scheduleID = (isset($_REQUEST["sid"])?$_REQUEST["sid"]:0);

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

// is this a post back update?  (first is act, 2nd is subact)
$subact=isset($_POST["subact"])?$_POST["subact"]:"";
// Add new text to the service
if($subact=="add") {
	
	if(isset($_POST["catEveryone"])) {
		$catArray = "*";
	} else if(isset($_POST["catArray"])) {
		$catArray = implode(",",$_POST["catArray"]);
	} else {
		$catArray = "";
	}
	$sql = "INSERT INTO scheduledefaults VALUES(0,'".$_POST["schDateOffset"]."','".$_POST["schTime"]."',".$_POST["schDuration"].",'$catArray','".$_POST["schDescription"]."')";
	$resSong = $db->query($sql);
	echo "<script>parent.window.dspSchedule();parent.window.hs.close();</script>";
	exit;
}

// Update text
if($subact=="edit") {
	
	if(isset($_POST["catEveryone"])) {
		$catArray = "*";
	} else if(isset($_POST["catArray"])) {
		$catArray = implode(",",$_POST["catArray"]);
	} else {
		$catArray = "";
	}
	$sql = "UPDATE scheduledefaults SET schDateOffset='".$_POST["schDateOffset"]."',schTime='".date("Y-m-d"). " ". $_POST["schTime"].":00',schDuration=".$_POST["schDuration"].",schCategories='$catArray',schDescription='".$_POST["schDescription"]."' WHERE scheduleID=".$_POST["scheduleID"];
	$resSong = $db->query($sql);
	logit(2, __FILE__ . ":" . __LINE__ . " Q: ". $sql . " E:". $db->error);
	echo "<script>parent.window.dspSchedule();parent.window.hs.close();</script>";
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Edit Schedule Defaults</title>
<link rel="stylesheet" href="<?php echo $baseFolder; ?>css/tw.css" type="text/css">
<link rel="stylesheet" href="css/tail.datetime.css">
<script src="scripts/tail.datetime-full.min.js"></script>


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

	$schDescription = "";
	$schTime = date("Y-m-d")." 12:00:00";
	$schDuration = 1;
	$catArray = array();


$aDOW = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");

// TODO need to fix query to use from EditSiteConfig .... SELECT *,date_format(schTime,'%l:%i%p') AS schTIME,date_format(schTime+INTERVAL schDuration*60 MINUTE,'%l:%i%p') AS schENDTIME FROM scheduledefaults
/* Retrieve Schedule Item */
if($action=="edit") {
	$q = "SELECT *, date_format(schTime,'%H:%i') as schFTime FROM scheduledefaults WHERE  scheduleID=$scheduleID";
	$resSch = $db->query($q);
	$i = 1;
	if($dbSch=mysqli_fetch_array($resSch)) {
		$schDescription = $dbSch["schDescription"];
		$schTime = $dbSch["schFTime"];
		
		$schDuration = $dbSch["schDuration"];
		$catArray = explode(",",$dbSch["schCategories"]);
		$schDateOffset = $dbSch["schDateOffset"];
		logit(4, __FILE__ . ":" . __LINE__ . " Q: ". $q . " E:". $db->error);

	} else {
		$schDescription = "";
		$schTime = " 12:00:00";
		
		$schDuration = 1;
		$catArray = array();
		$schDateOffset = 0;
	}
}
if(!isset($subact)) {

/* Retrieve Service Schedule */

$q = "SELECT *,date_format(schTime,'%l:%i%p') AS schTIME,date_format(schTime+INTERVAL schDuration*60 MINUTE,'%l:%i%p') AS schENDTIME FROM scheduledefaults LEFT JOIN roletypes ON CONCAT(',',schCategories,',') LIKE CONCAT('%,',typeID,',%') ORDER BY schDateOffset DESC,schTime,typeSort";
$resSch = $db->query($q);
$schDesc = "<div id='divSvcSchedule'><table width='100%'>\n";
$i = 1;
$saveSchDateOffset = "";
$saveSchID = 0;
$schLines = "";
$schCategories = "";
if($resSch && mysqli_num_rows($resSch)>0) {
	while($dbSch=mysqli_fetch_array($resSch)) {
		if($saveSchDateOffset=="") {
			$saveSchDateOffset = $dbSch["schDateOffset"];
			$saveSchID = $dbSch["scheduleID"];
			$saveschTIME = $dbSch["schTIME"]."-".$dbSch["schENDTIME"];
		}
		if($saveSchID!=$dbSch["scheduleID"]) {
			$schAdmLink = "<td width='40' nowrap>";
			$schAdmLink .= "<a href='#' onClick=\"delSchedule($saveSchID,'$saveschDateOffset - $saveschTIME');\" title='Remove Schedule Item'><img src='images/icon_delete.gif'></a>&nbsp;";
			$schAdmLink .= "<a id='hsEditTeam' href='editDefSchedule.php?act=edit&sid=$saveSchID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 450, contentID: 'divAddSch', headingText: 'Update Schedule Defaults' } )\"><img src='images/edit.png'></a>";
			$schAdmLink .= "</td>\n";
			$schLines .= "<tr>$schAdmLink<td nowrap$schStyle>$saveschTIME&nbsp;</td><td$schStyle>$schCategories</td><td$schStyle>$schDescription</td></tr>";
			$saveschTIME = $dbSch["schTIME"]."-".$dbSch["schENDTIME"];
			$schCategories = "";
			$saveSchID = $dbSch["scheduleID"];
			$do = $saveSchDateOffset==0?0:7+$saveSchDateOffset;
			$schDesc .= "<tr><td nowrap>".$aDOW[$do]."</td><td><table>$schLines</table></td></tr>";
			$schDesc .= "<tr><td colspan='2'><hr style='margin:0px;' /></td></tr>";
			$schLines = "";
			$saveSchDateOffset = $dbSch["schDateOffset"];
		}
		$saveschDateOffset = $dbSch["schDateOffset"];
		$schStyle = "";
		if($dbSch["schCategories"]=="*") {
			$schCategories = "Everyone";
		} else {
			$schCategories .= $schCategories!=""?", ":"";
			$schCategories .= $dbSch["typeDescription"];
		}
		$schDescription = $dbSch["schDescription"]!=""?$dbSch["schDescription"]:"";
	}
	$schAdmLink = "<td width='40' nowrap>";
	$schAdmLink .= "<a href='#' onClick=\"delSchedule($saveSchID,'$saveschDateOffset - $saveschTIME');\" title='Remove Schedule Item'><img src='images/icon_delete.gif'></a>&nbsp;";
	$schAdmLink .= "<a id='hsEditTeam' href='editDefSchedule.php?act=edit&sid=$saveSchID' onclick=\"return hs.htmlExpand(this, { objectType: 'iframe',width: 450, contentID: 'divAddSch', headingText: 'Update Schedule Defaults' } )\"><img src='images/edit.png'></a>";
	$schAdmLink .= "</td>\n";
	$schLines .= "<tr>$schAdmLink<td nowrap>$saveschTIME&nbsp;</td><td>$schCategories</td><td>$schDescription</td></tr>";
	$do = $saveSchDateOffset==0?0:7+$saveSchDateOffset;
	$schDesc .= "<tr><td nowrap>".$aDOW[$do]."</td><td><table>$schLines</table></td></tr>";
}	
echo $schDesc."</table></div>\n";
} else {



echo "<body style='background-color:#ffffff'>\n";




echo "<h2 align=\"center\">Edit Service Schedule</h2>\n";
echo "<form style='margin:0px;' name='frmSchedule' method='post' action='editDefSchedule.php?sid=$scheduleID&act=edit'>\n";
echo "<input name=\"subact\" type=\"hidden\">\n";
echo "<input name=\"scheduleID\" type=\"hidden\" value=$scheduleID>\n";
echo "<table border='0' align='center'>\n";
echo "<table>\n";
echo "	<tr>\n";
echo "		<td><b>Time:</b>&nbsp;</td>\n";
echo "		<td><input type=\"text\" id=\"schTime\" maxlength=\"25\" size=\"25\" name=\"schTime\" value=\"". $schTime."\">\n";
// echo "			<a href=\"javascript:void(0)\" onclick=\"tail.DateTime(\"#schTime\", {timeFormat: \"hh:ii\", position: \"#datetime-holder\", time12h: false});return false;\" ><img align=\"top\" src=\"{$baseFolder}scripts/popcal/calbtn.gif\" alt=\"Pick a date\"></a>\n";
	echo "<div id='time-holder' class='datetime-folder'></div>";
	
// echo "			<a href=\"javascript:void(0)\" onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.frmSchedule.schTime);return false;\" ><img align=\"top\" src=\"{$baseFolder}scripts/popcal/calbtn.gif\" alt=\"Pick a date\"></a>\n";
echo "		</td>\n";
echo "	<tr>\n";
echo "	<tr>\n"; ?>

<script>
tail.DateTime("#schTime", {
time12h: true,
dateFormat: false,
timeStepMinutes: 5,
timeStepSeconds: 0,

timeFormat: "HH:ii",
 zeroSeconds: false,
 
static: "#time-holder",    
startOpen: false,    
stayOpen: false } ); 

</script>
<?php 
echo "		<td><b>Duration:</b></td>\n";
echo "		<td><select name=\"schDuration\">\n";
for($i=.25;$i<=4;$i=$i+.25) {
	$sel = $i==$schDuration?" selected":"";
	echo "			<option$sel>".number_format($i,2)."</option>\n";
}
echo "		</select>&nbsp;(Hours)\n</td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td><b>Offset:</b></td>\n";
echo "		<td><select name=\"schDateOffset\">\n";
for($i=-6;$i<=0;$i++) {
	$do  = $i==0?0:7+$i;
	$sel = $i==$schDateOffset?" selected":"";
	echo "			<option$sel value=\"$i\">". $aDOW[$do] ."</option>\n";
}
echo "		</select>&nbsp;\n</td>\n";
echo "	</tr>\n";

echo "	<tr>\n";
echo "		<td><b>Description:</b></td>\n";
echo "		<td><input title=\"Description\" name=\"schDescription\" type=\"text\" id=\"schDescription\" size=\"45\" maxlength=\"255\" value=\"$schDescription\" /></td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
$chk = $catArray[0] == "*"?" checked":"";
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
	}
?>