<?php
/*******************************************************************
 * editAvailability.php
 * Edit Member Availability
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
	exit;
}
$isAdmin = allow_access(Administrators) == "yes";

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Prevent hacking
if(!isset($_REQUEST["act"]) && !isset($_POST["action"])) {
	echo "Invalid Request...";
	exit;
}

$action = isset($_POST["action"])?$_POST["action"]:$_REQUEST["act"];
$awayID = isset($_POST["awayID"])?$_POST["awayID"]:$_REQUEST["aid"];
$memberID = isset($_POST["memberID"])?$_POST["memberID"]:$_REQUEST["mid"];
$inDate = isset($_POST["inDate"])?$_POST["inDate"]:$_REQUEST["dte"];
$rtnScript = "teamAvailability.php?y=".substr($inDate,0,4)."&m=".substr($inDate,5,2);
$err ="";
// Delete Availability
if($action=="del") {
	$q = "DELETE FROM memberavailability WHERE memberID=$memberID AND awayID=$awayID";
	$mbrRes = $db->query($q);
	header("Location: $rtnScript");
	exit;
}

// Save changes
if(isset($_POST["save"])) {
	$q = "SELECT services.serviceID FROM services INNER JOIN serviceteam ON services.serviceID = serviceteam.serviceID WHERE memberID=$memberID AND LEFT(svcDateTime,10) >= '".$_POST["awayFrom"]."' AND LEFT(svcDateTime,10) <= '".$_POST["awayTo"]."'";
	$resSched = $db->query($q);
	if($resSched && mysqli_num_rows($resSched)>0) {
		$err = "You are currently scheduled for this date. Please use the swap/change feature.";
		$awayFrom = $_POST["awayFrom"];
		$awayTo = $_POST["awayTo"];
		$awayDescription = $_POST["awayDescription"];
	} else {
		// Retrieve member groups
		$q = "SELECT groupArray FROM members WHERE memberID=$memberID";
		$resMbr = $db->query($q);
		$dbMbr=mysqli_fetch_array($resMbr);
		$mbrGroups = $dbMbr["groupArray"]!=""?",".$dbMbr["groupArray"].",":"";
		if($mbrGroups!="") {
			$q = "SELECT services.serviceID FROM services INNER JOIN serviceteam ON services.serviceID = serviceteam.serviceID WHERE '$mbrGroups' LIKE concat('%,',memberID,',%') AND LEFT(svcDateTime,10) >= '".$_POST["awayFrom"]."' AND LEFT(svcDateTime,10) <= '".$_POST["awayTo"]."'";
			$resSched = $db->query($q);
			if($resSched && mysqli_num_rows($resSched)>0) {
				$err = "You are currently scheduled for this date. Please use the swap/change feature.";
				$awayFrom = $_POST["awayFrom"];
				$awayTo = $_POST["awayTo"];
				$awayDescription = $_POST["awayDescription"];
			} else {
				if($action=="add") {
					$q = "INSERT INTO memberavailability VALUES($memberID,0,'".$_POST["awayFrom"]."','".$_POST["awayTo"]."','".$_POST["awayDescription"]."')";
				} else {
					$q = "UPDATE memberavailability SET awayFrom='".$_POST["awayFrom"]."',awayTo='".$_POST["awayTo"]."',awayDescription='".$_POST["awayDescription"]."' WHERE memberID=$memberID AND awayID=$awayID";
				}
				$resMbr = $db->query($q);
				echo "<script>parent.window.location.reload();</script>";
				exit;
			}
		} else {
			if($action=="add") {
				$q = "INSERT INTO memberavailability VALUES($memberID,0,'".$_POST["awayFrom"]."','".$_POST["awayTo"]."','".$_POST["awayDescription"]."')";
			} else {
				$q = "UPDATE memberavailability SET awayFrom='".$_POST["awayFrom"]."',awayTo='".$_POST["awayTo"]."',awayDescription='".$_POST["awayDescription"]."' WHERE memberID=$memberID AND awayID=$awayID";
			}
			$resMbr = $db->query($q);
			echo "<script>parent.window.location.reload();</script>";
			exit;
		}
	}
} else {
	if($action=="edit") {
		/* Retrieve availability for specified id */
		$sql = "SELECT * FROM memberavailability WHERE memberID=$memberID AND awayID=$awayID";
		$resMbr = $db->query($sql);
		$dbMbr=mysqli_fetch_array($resMbr);
		$awayFrom = $dbMbr["awayFrom"];
		$awayTo = $dbMbr["awayTo"];
		$awayDescription = htmlentities($dbMbr["awayDescription"],ENT_QUOTES);
	} else {
		$awayFrom = $inDate;
		$awayTo = $inDate;
		$awayDescription = "";
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Edit Member Availability</title>

<script type="text/javascript">
function valAvailability() {
	var frm = document.frmAvailability;
	if(frm.awayFrom.value=="" || !isDate(frm.awayFrom.value,"yyyy-MM-dd")) {
		alert("Please enter a valid date for Away From");
		frm.awayFrom.focus();
		return false;
	}
	if(frm.awayTo.value=="" || !isDate(frm.awayTo.value,"yyyy-MM-dd")) {
		alert("Please enter a valid date for Away To");
		frm.awayTo.focus();
		return false;
	}
	if(frm.awayTo.value<frm.awayFrom.value) {
		alert("Away To must be on or after Away From");
		frm.awayTo.focus();
		return false;
	}

	return true;
}
function dspErrMsg(msg)
{
	alert(msg);
	frm.awayFrom.focus();
}
</script>



<link rel="stylesheet" href="<?php echo $baseFolder; ?>css/tw.css" type="text/css">
</head>
<?php
	if ($err!="") {
		echo "<body onLoad=\"dspErrMsg('". $err . "')\">\n";
	} 
	else {
		echo "<body>\n";
	}

echo "<form style='margin-top:0px;border-bottom:2px ridge;border-top:2px ridge;' name=\"frmAvailability\" action=\"editAvailability.php\" onSubmit=\"return valAvailability();\" method=\"post\">\n";
echo "<input type='hidden' name='action' value='$action'>\n";
echo "<input name=\"memberID\" type=\"hidden\" value='$memberID'>\n";
echo "<input name=\"awayID\" type=\"hidden\" value='$awayID'>\n";
echo "<input type=\"hidden\" name=\"inDate\" value='$inDate'>\n";
echo "<table>\n";
$mand = "<span style='font-weight:bold;color:#cc0000;'>*</span>";
echo "	<tr><td width='240'>Away From:$mand </td><td><input type=\"Text\" id=\"awayFrom\" maxlength=\"25\" size=\"25\" name=\"awayFrom\" value=\"$awayFrom\"><a href=\"javascript:void(0)\" onclick=\"if(self.gfPop)gfPop.fStartPop(document.frmAvailability.awayFrom,document.frmAvailability.awayTo);return false;\" ><img align=\"top\" src=\"{$baseFolder}scripts/popcal/calbtn.gif\" alt=\"Pick a date\"></a>&nbsp;(YYYY-MM-DD)</td></tr>";
echo "	<tr><td width='240'>Away To:$mand </td><td><input type=\"Text\" id=\"awayTo\" maxlength=\"25\" size=\"25\" name=\"awayTo\" value=\"$awayTo\"><a href=\"javascript:void(0)\" onclick=\"if(self.gfPop)gfPop.fEndPop(document.frmAvailability.awayFrom,document.frmAvailability.awayTo);return false;\" ><img align=\"top\" src=\"{$baseFolder}scripts/popcal/calbtn.gif\" alt=\"Pick a date\"></a>&nbsp;(YYYY-MM-DD)</td></tr>";
echo "	<tr><td width='240'>Description</td><td><input type='text' name='awayDescription' size='60' maxlength='255' value='$awayDescription' /></td></tr>";
echo "	<tr><td colspan='2' align='right'><input name=\"cancel\" type=\"button\" value=\"Cancel\" onClick=\"parent.window.hs.close();\" class=\"button\">&nbsp;<input name=\"save\" type=\"submit\" value=\"Save\" class=\"button\"></td></tr>\n";
echo "</table>\n";
echo "</form>\n";

echo "<iframe width=188 height=166 name=\"gToday:datetime:/scripts/popcal/agenda.js:gfPop:/scripts/popcal/plugins_date.js\" id=\"gToday:datetime:/scripts/popcal/agenda.js:gfPop:/scripts/popcal/plugins_date.js\" src=\"/scripts/popcal/ipopeng.htm\" scrolling=\"no\" frameborder=\"0\" style=\"visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;\">\n";
echo "</iframe>\n";

echo "</body>\n</html>\n";
?>