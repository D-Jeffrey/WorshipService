<?php
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
//   $valUser = $_SESSION["user_id"] == $_REQUEST["id"];
if (allow_access(Administrators) != "yes") { 
	exit;
}

$svcDate = $_REQUEST["sd"];
$serviceID = $_REQUEST["sid"];
$roleID = $_REQUEST["rid"];
$fldName = "f".$serviceID."r".$roleID;
$errMsg = "";

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


if(isset($_POST["sbmMbr"])) {
	// Verify that member is available
	$q = "SELECT memberID from memberavailability WHERE memberID=".$_POST["memberID"]." AND awayFrom <= '".$_POST["svcDate"]."' AND awayTo >= '".$_POST["svcDate"]."'";
	$resMbr = $db->query($q);
	if(mysqli_num_rows($resMbr) > 0 && $resMbr) {
		$errMsg = "Member is not available for the selected date.";
		$svcDate = $_POST["svcDate"];
		$serviceID = $_POST["serviceID"];
		$roleID = $_POST["roleID"];
		$memberID = $_POST["memberID"];
		$memberName = $_POST["mbrName"];
	} else {
		$q = "INSERT INTO serviceteam VALUES(".$_POST["serviceID"].",".$_POST["memberID"].",".$_POST["roleID"].",'','')";
		$resMbr = $db->query($q);
		echo "<script>\n";
		$fldName = "f".$_POST["serviceID"]."r".$_POST["roleID"];
		echo "parent.document.getElementById('$fldName').innerHTML+='<a href=\"#\" onClick=\"delMember(&quot;$fldName&quot;,".$_POST["roleID"].",".$_POST["memberID"].")\">".$_POST["mbrName"]."\\n<br />';\n";
		echo "parent.window.hs.close();\n";
		echo "</script>";
		exit;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" version="XHTML 1.2" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle." - Add Member"; ?></title>
<script type="text/javascript" src="/scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript" src="/scripts/scriptaculous/src/scriptaculous.js"></script>
<script type="text/javascript">

function selMember(id,mname) {
	document.frmComm.memberID.value=id;
	document.frmComm.mbrName.value=mname;
	document.frmComm.submit();
}
</script>
<?php
echo "<link rel=\"stylesheet\" href=\"/css/tw.css\" type=\"text/css\">\n";
echo "</head><body style='background-color:#ffffff'>\n";
echo "<form style='margin:0px;' name='frmComm' method='post' action='ajTeamSchedMbr.php'>\n";
$memberID = isset($memberID)? $memberID:"";
echo "<input name=\"memberID\" type=\"hidden\" valuer=\"$memberID\">\n";
echo "<input name=\"serviceID\" type=\"hidden\" value=\"$serviceID\">\n";
echo "<input name=\"svcDate\" type=\"hidden\" value=\"$svcDate\">\n";
echo "<input name=\"roleID\" type=\"hidden\" value=\"$roleID\">\n";
echo "<input name=\"mbrName\" type=\"hidden\">\n";
echo "<input name=\"sbmMbr\" type=\"hidden\" value=\"save\">\n";

/*Query to the members table. It retrieves all the names of the members*/
$sql = "SELECT memberID,concat(mbrFirstName,' ',mbrLastName) as mbrName FROM members WHERE mbrStatus='A' AND concat(',',roleArray,',') LIKE '%,$roleID,%' ORDER BY mbrName";

$res = $db->query($sql) or die(mysqli_error());
echo "<div style='background-color:#ffffff;width:175px;max-height:250px;overflow:auto'>";
if(mysqli_num_rows($res)>0) {
	while($row = mysqli_fetch_assoc($res)) {
		echo "<a href='#' title='Click on name to select' onClick='selMember(".$row["memberID"].",&quot;".$row["mbrName"]."&quot;);'>".$row["mbrName"]."</a><br />";
	}
} else {
	echo "<p align='center'><b>No members associated with this role.</b></p>\n";
	echo "<p align='center'>From the <a href='/dspTeam.php' target='_top'>Team Directory</a> you can edit members and assign them to this role.</p>\n";
}
echo "</div>";
echo "<p align='center'><input name=\"cancel\" type=\"button\" value=\"Cancel\" onClick=\"parent.window.hs.close();\" class=\"button\"></p>\n";
echo "</form>\n";
if($errMsg!="") {
	echo "<script>alert('Sorry, $memberName is not available for this date.');</script>\n";
}
echo "</body></html>";
?>