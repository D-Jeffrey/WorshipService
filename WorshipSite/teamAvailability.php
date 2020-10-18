<?php
/*******************************************************************
 * teamAvailability.php
 * Display availability of team members
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
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

$intYear = isset($_REQUEST["y"])? intval($_REQUEST["y"]):0;
$intMonth = isset($_REQUEST["m"])?intval($_REQUEST["m"]):0;
$memberID = isset($_REQUEST["mbr"])?$_REQUEST["mbr"]:"*";

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


include ('fnNicetime.php');

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Team Availability', $_SERVER['REQUEST_URI'], 1);

$isAdmin = (allow_access(Administrators)=="yes");

// Retrieve availability status
$q = "SELECT availStatus FROM siteconfig LIMIT 1";
$resCfg = $db->query($q);
$dbCfg=mysqli_fetch_array($resCfg);
$availStatus = $dbCfg["availStatus"];

/* Month Names */
$gaMonthTitles = array("January","February","March", "April","May","June", "July", "August", "September", "October", "November","December");

/* if month and/or year not set, change to current month and year */
if ($intMonth < 1) {
	$intMonth = date( "n" );
}
if ($intYear < 1) {
	$intYear = date( "Y" );
}

/* determine total days in month */
//$lintTotalDays = 0;
$lintTotalDays = date("t",strtotime("$intYear-$intMonth-01"));
//while ( checkdate( $intMonth, $lintTotalDays + 1, $intYear ) ) $lintTotalDays++;

$intPreMonth = $intMonth-1;
$intPreYear = $intYear;
if ($intPreMonth<1) {
	$intPreMonth = 12;
	$intPreYear = $intYear-1;
}
$intNxtMonth = $intMonth+1;
$intNxtYear = $intYear;
if ($intNxtMonth>12) {
	$intNxtMonth = 1;
	$intNxtYear = $intYear+1;
}
$preMonth = $gaMonthTitles[ $intPreMonth-1 ];
$nxtMonth = $gaMonthTitles[ $intNxtMonth-1 ];
$strTitle = $gaMonthTitles[ $intMonth-1 ] . "&nbsp;" . $intYear;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $siteTitle; ?> - Team Availability</title>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/overlibmws/overlibmws.js"></script>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/overlibmws/overlibmws_exclusive.js"></script>

<?php
if($availStatus=="O") {
	echo "\n<script>\n";
	echo "function delAway(mid,aid,desc) {\n";
	echo "	if(confirm('Do you wish to delete the away schedule for: '+desc+'?')) {\n";
	echo "		document.location='editAvailability.php?act=del&mid='+mid+'&aid='+aid+'&dte=$intYear-$intMonth-01';\n";
	echo "	}\n";
	echo "	return false;\n";
	echo "}\n";
	echo "function addAway(indte) {\n";
	echo "	document.frmAvail.memberID.value=".$_SESSION['user_id'].";\n";
	echo "	document.frmAvail.action.value='add';\n";
	echo "	document.frmAvail.inDate.value=indte;\n";
	echo "	document.frmAvail.submit();\n";
	echo "}\n";
	echo "function editAway(mid,aid) {\n";
	echo "	document.frmAvail.memberID.value=mid;\n";
	echo "	document.frmAvail.awayID.value=aid;\n";
	echo "	document.frmAvail.action.value='edit';\n";
	echo "	document.frmAvail.inDate.value='$intYear-$intMonth-01';\n";
	echo "	document.frmAvail.submit();\n";
	echo "}\n";
	echo "</script>\n";
}

$hlpID = 0;
$title = "Worship Team Availability";
include("header.php");

echo "<br /><div style='font-size:12pt;font-weight:bold;font-style:italic;text-align:center;'>";
if($availStatus=="O") {
	echo "List dates you are NOT available for service on the worship team";
} else {
	echo "<font color='#cc0000'>Availability updates are now closed. Contact the worship coordinator with specific concerns</font>";
}
echo "</div><br />\n";

echo "<table class=\"calhead\"><tr><td class=\"calheadleft\"><a href=\"teamAvailability.php?y=$intPreYear&amp;m=$intPreMonth&amp;mbr=*\">&lt;$preMonth</a></td><td class=\"calheadtitle\">$strTitle</td><td class=\"calheadright\"><a href=\"teamAvailability.php?y=$intNxtYear&amp;m=$intNxtMonth&amp;mbr=*\">$nxtMonth&gt;</a></td></tr></table>\n";
echo "<table class=\"calendar\">\n";
echo "	<tr><th class=\"daynames\" abbr=\"Sunday\">Sun</th><th class=\"daynames\" abbr=\"Monday\">Mon</th><th class=\"daynames\" abbr=\"Tuesday\">Tue</th><th class=\"daynames\" abbr=\"Wednesday\">Wed</th><th class=\"daynames\" abbr=\"Thursday\">Thu</th><th class=\"daynames\" abbr=\"Friday\">Fri</th><th class=\"daynames\" abbr=\"Saturday\">Sat</th></tr>\n";

// Build availability array from member information
$iYM = $intMonth*12+ $intYear;
// add this line to the SQL below to list everyone who is blocked off
$showall = "OR (((month(awayFrom)+year(awayFrom)*12) >  '" . $iYM . "' AND (month(awayTo)*12 + year(awayTo))< '" . $iYM . "'))"; 


$sql = "SELECT memberavailability.memberID,awayID,mbrFirstName,mbrLastName,month(awayFrom) as mFrom,day(awayFrom) as dFrom,year(awayFrom) as yFrom, day(awayTo) as dTo,month(awayTo) as mTo, year(awayTo) as yTo, awayFrom, awayTo, awayDescription FROM memberavailability INNER JOIN members USING(memberID) WHERE awayFrom <= '$intYear-$intMonth-$lintTotalDays' AND awayTo >= '$intYear-$intMonth-1' ORDER BY awayFrom";

// echo "<pre>$sql \n";
$result = $db->query($sql);
// echo "$db->error</pre>";
while($dbrow=mysqli_fetch_array($result)) {
	// is this for this month? if not then use the 1 day of month
	$start = $dbrow["mFrom"]!=$intMonth?1:$dbrow["dFrom"];
	if ($dbrow["yFrom"] < $intYear)
		$start = 1;
	// is this for this month? if not then use last day of the month
	$end = $dbrow["mTo"]!=$intMonth?$lintTotalDays:$dbrow["dTo"];
	if ($dbrow["yTo"] > $intYear)
		$end = $lintTotalDays;
	
	for ($i=$start;$i<=$end;$i++) {
		$aAvail[$i][] = array("mbrID" => $dbrow["memberID"],
				"awayID" => $dbrow["awayID"],
				"mbrFN" => $dbrow["mbrFirstName"],
				"mbrLN" => $dbrow["mbrLastName"],
				"aDesc" => htmlentities($dbrow["awayDescription"],ENT_QUOTES));
	}
}

/* ensure that enough blanks are put in so that the first day of the month lines up with the proper day of the week */
$lintOffset = date( "w", mktime( 0, 0, 0, $intMonth, 1, $intYear ) );
echo "	<tr>\n";
if($lintOffset > 0) {
	echo "		<td colspan='$lintOffset'>&nbsp;</td>\n";
}

$today = mktime( 0, 0, 0, date("n"), date("j"), date("Y") );

/* start filling in the days of the month */
for ( $lintDay = 1; $lintDay <= $lintTotalDays; $lintDay++ ) {
	$calendarDay = mktime( 0, 0, 0, $intMonth, $lintDay, $intYear );
	$newCalIcon = $availStatus=="O"?"<a href=\"editAvailability.php?act=add&mid=".$_SESSION['user_id']."&aid=0&dte=$intYear-".sprintf("%02s",$intMonth)."-".sprintf("%02s",$lintDay)."\" onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdAvail',headingText: 'Add Away Schedule', width: 550, height: 250 });\"><span class='newbutton'><img src='{$baseFolder}images/new.png' alt='Add Away Schedule' style='border:0;'></span><span class='newbutton'></a>":"";
	$txtDate = date("D, M jS",strtotime("$intYear-$intMonth-$lintDay"));

	$aAvail[$lintDay] = isset($aAvail[$lintDay])?$aAvail[$lintDay]:array();
	if(count($aAvail[$lintDay]) == 0) {
		$dayclass = $calendarDay==$today?"caltoday":"calday";
		echo "		<td><table class='$dayclass'><tr><th align='left'>$lintDay</th><th class='calbuttons' align='right'>$newCalIcon</th></tr><tr><td class='$dayclass' colspan='2'>&nbsp;</td></tr></table></td>\n";
	} else {
		echo "		<td>";
		$dayclass = $calendarDay==$today?"caltoday":"fullcalday";
		echo "<table class='$dayclass'><tr><th align='left'>$lintDay</th><th class='calbuttons' align='right'>$newCalIcon</th></tr><tr><td colspan='2'>\n";
		for($i=0;$i<count($aAvail[$lintDay]);$i++) {
			$availDesc = addslashes(htmlentities($aAvail[$lintDay][$i]["mbrFN"]." ".substr($aAvail[$lintDay][$i]["mbrLN"],0,1),ENT_COMPAT));
			$edetails = "";
			$delLink = ($isAdmin || $aAvail[$lintDay][$i]["mbrID"] == $_SESSION['user_id']) && $availStatus=="O"?"&nbsp;<a href='#' onClick='delAway(".$aAvail[$lintDay][$i]["mbrID"].",".$aAvail[$lintDay][$i]["awayID"].",\"".$aAvail[$lintDay][$i]["mbrFN"]." ".substr($aAvail[$lintDay][$i]["mbrLN"],0,1).". on $txtDate\");'><img src='{$baseFolder}images/icon_delete.gif' alt='Delete Away Schedule' style='border:0;'></span></a>":"";
			$editLink = ($isAdmin || $aAvail[$lintDay][$i]["mbrID"] == $_SESSION['user_id']) && $availStatus=="O"?"&nbsp;<a href='editAvailability.php?act=edit&mid=".$aAvail[$lintDay][$i]["mbrID"]."&aid=".$aAvail[$lintDay][$i]["awayID"]."&dte=$intYear-".sprintf("%02s",$intMonth)."-".sprintf("%02s",$lintDay)."' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divUpdAvail',headingText: 'Edit Away Schedule', width: 550, height: 250 });\"><img src='{$baseFolder}images/edit.png' alt='Edit Away Schedule' style='border:0;'></span></a>":"";
			echo "<a href='#' onmouseover='return overlib(\"<strong>".$aAvail[$lintDay][$i]["mbrFN"]." ".$aAvail[$lintDay][$i]["mbrLN"]."</strong>";
			echo $aAvail[$lintDay][$i]["aDesc"]!=""?" - ".$aAvail[$lintDay][$i]["aDesc"]:"";
			echo "\", WIDTH, 200)' onmouseout='return nd();' class='eventitle'>$availDesc</a>$delLink$editLink<br />";
			$dbrow=mysqli_fetch_array($result);
		}
		echo "</td></tr></table></td>\n";
	}
	/* terminate row if we're at on the last day of the week */
	$lintOffset++;
	if ( $lintOffset > 6 ) {
		$lintOffset = 0;
		printf("</tr>\n");
		if ( $lintDay < $lintTotalDays ) {
			printf("<tr>\n");
		}
	}
}

/* fill in the remainder of the row with spaces */
if ( $lintOffset > 0 ) {
	$lintOffset = 7 - $lintOffset;
}
if ( $lintOffset > 0 ) echo "	<td colspan='$lintOffset'> &nbsp; </td>";

echo "	</tr>\n";
echo "</table>\n";
echo "<form name='frmAvail' method='post' action='editAvailability.php' style='margin:0px;'>\n";
echo "<input type='hidden' name='action'>\n";
echo "<input type=\"hidden\" name=\"memberID\">\n";
echo "<input type=\"hidden\" name=\"awayID\">\n";
echo "<input type=\"hidden\" name=\"inDate\">\n";
echo "</form>\n";

// Update Availability
echo "<div id='divUpdAvail' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";
echo "</body>\n</html>\n";


function dateadd($serialdate,$units,$changevalue) {
	if ($units =='Days') {
		$difference = mktime( 0, 0, 0, date("m",$serialdate), date("d",$serialdate), date("Y",$serialdate)) +(24*60*60*$changevalue);
	}
	return $difference;
}
?>
