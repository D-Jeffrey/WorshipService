<?php 
/*******************************************************************
 * dspTeam.php
 * Display Team Member information
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
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='/lr/login.php' name='frmLogin' method='post'>\n";
	echo "<input type='hidden' name='ref' value='".$_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."' />\n";
	echo "</form></body></html>\n";
	exit;
}

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

// $memberID is the contents of the search string form
// if it was not passed as a search then blank start value

$memberID = isset($memberID) ? $memberID : "";

include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Team Directory', $_SERVER['REQUEST_URI'], 1);

$isAdmin = (allow_access(Administrators)=="yes");

if(isset($_POST["pageNum"]) && $_POST["pageNum"] > 0)
	$pageNum = $_POST["pageNum"];
else
	$pageNum = 1;
if(isset($_POST["isActive"]) && $_POST["isActive"] > 0)
	$isActive = 1;
else
	$isActive = 0;
if(isset($_POST["txtSearch"]))
	$txtSearch = $_POST["txtSearch"];
else
	$txtSearch = "";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Team Directory</title>
<script type="text/javascript" src="scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript">
function memberProfile(id) {
	var url = 'getMember.php';
	var pars = 'memberID='+id;

	var myAjax = new Ajax.Updater(
		'memberDiv', 
		url, {
			method: 'get', 
			parameters: pars
		});
}
function mbrGroupProfile(id,desc) {
	var url = 'getMemberGroup.php';
	var pars = 'memberID='+id+'&desc='+desc;

	var myAjax = new Ajax.Updater(
		'memberDiv', 
		url, {
			method: 'get', 
			parameters: pars
		});
}
function memberSchedule(id) {
	var url = 'getMbrSched.php';
	var pars = 'memberID='+id;

	var myAjax = new Ajax.Updater(
		'memberDiv', 
		url, {
			method: 'get', 
			parameters: pars
		});
}
function valSearch() {
	if(document.frmTeam.txtSearch.value!="<?php echo $txtSearch; ?>") {
		document.frmTeam.pageNum.value = 1;
	}
	return true;
}

<?php
if ($isAdmin ) { 

	// Including Administrator only JavaScript functions
?>
function delMember(id,uname,name) {
	if(confirm("Delete member: "+name+"?")) {
		document.frmTeam.action="editMember.php";
		document.frmTeam.mbract.value="del";
		document.frmTeam.memberID.value=id;
		document.frmTeam.mbrUName.value=uname;
		document.frmTeam.submit();
	}
}

function delMbrGroup(id,name) {
	if(confirm("Delete member group: "+name+"?")) {
		document.frmTeam.action="editMbrGroup.php";
		document.frmTeam.mbract.value="del";
		document.frmTeam.memberID.value=id;
		document.frmTeam.submit();
	}
}

function addMember() {
	document.frmTeam.action="editMember.php";
	document.frmTeam.mbract.value="add";
	document.frmTeam.submit();
}

function addMbrGroup() {
	document.frmTeam.action="editMbrGroup.php";
	document.frmTeam.mbract.value="add";
	document.frmTeam.submit();
}

function editMember(id) {
	document.frmTeam.action="editMember.php";
	document.frmTeam.mbract.value="edit";
	document.frmTeam.memberID.value=id;
	document.frmTeam.submit();
}

function editMbrGroup(id) {
	document.frmTeam.action="editMbrGroup.php";
	document.frmTeam.mbract.value="edit";
	document.frmTeam.memberID.value=id;
	document.frmTeam.submit();
}
function toggleActive() {
	if (document.frmTeam.isActive.checked) {
	    document.frmTeam.isActive.value = 1; 
	} else
	{ 
	   document.frmTeam.isActive.value = 0;
	}
	document.frmTeam.submit();
}

<?php

// Included Administrator only functions
}
?>
</script>
<?php

$hlpID = $isAdmin?19:10;
$title = "Team Directory";
include("header.php");

$pageSize = 25;
echo "	<form name=\"frmTeam\" action=\"dspTeam.php\" method=\"post\" onSubmit=\"valSearch();\">\n";
echo "	<input name=\"mbract\" type=\"hidden\">\n";
echo "	<input name=\"memberID\" type=\"hidden\" value=\"$memberID\">\n";
echo "	<input name=\"mbrUName\" type=\"hidden\">\n";
echo "	<input type=\"hidden\" name=\"pageNum\" id=\"pageNum\" value=$pageNum>\n";
echo "	<table border=\"0\" width=\"100%\" cellspacing=\"1\" cellpadding=\"2\">\n";
echo "		<tr>\n";
if ($isAdmin) { 
	echo "			<td valign=\"middle\" align=\"left\">\n";
	echo "				<a onClick=\"addMember();\" href='#' title='Add New Member'><img src=\"images/icon_new.gif\" style='vertical-align:middle'>New Member</a>&nbsp;&nbsp;&nbsp;\n";
	echo "				<a onClick=\"addMbrGroup();\" href='#' title='Add New Group'><img src=\"images/icon_new.gif\" style='vertical-align:middle'>New Group</a>\n";
	echo "			</td>\n";
}
if ($isActive>0) {
$isActiveChk = "checked";
  $isActive = 1;  
}
else {
  $isActiveChk = "";
  $isActive = 0;  
}
echo "			<td align='right'>";
if($isAdmin) {
  echo "<b>Include inactive</b>&nbsp;   ";
  echo "<input type='checkbox' name='isActive' value='$isActive' $isActiveChk onClick=\"toggleActive();\" >&nbsp; &nbsp; &nbsp; &nbsp;";
}
echo "<b>Members with role:</b>&nbsp;\n";
echo "			<select id=\"roleSelect\" name=\"selRole\" onChange=\"document.frmTeam.pageNum.value = 1;document.frmTeam.submit();\">\n";
/* Retrieve role list */
$q = "SELECT roleID, roleDescription FROM roles INNER JOIN roletypes ON roles.typeID = roletypes.typeID ORDER BY typeSort, roleDescription";
$resRole = $db->query($q);
logit(4, __FILE__ . ":" .  __LINE__ . " Q: ". $q . " R: " . mysqli_num_rows($resRole) ." E:". $db->error);


echo "				<option value=\"0\">-- Any Role --</option>\n";
while($dbRole=mysqli_fetch_array($resRole)) {
	$sel = $_POST["selRole"]==$dbRole["roleID"]?" selected":"";
	echo "				<option value=\"".$dbRole["roleID"]."\"$sel>".$dbRole["roleDescription"]."</option>\n";
}
echo "	</select></td>\n";
echo "			<td align=\"right\">\n";
echo "				<strong>Search:</strong>&nbsp;\n";
echo "				<input type=\"text\" name=\"txtSearch\" size=\"20\" value=\"$txtSearch\">&nbsp;\n";
echo "				<a href='#' onClick=\"document.frmTeam.submit();\"><img src=\"/images/search.gif\"></a>\n";
echo "			</td>\n";
echo "		</tr>\n";
echo "	</table>\n";

if($pageNum > 1) {
	$limit = " LIMIT ".(($pageNum-1) * $pageSize ).",$pageSize";
} else {
	$limit = " LIMIT $pageSize";
}
$aWhere = array();
if(isset($_POST["selRole"]) && $_POST["selRole"]>0) $aWhere[] = " concat(',',roleArray,',') LIKE '%,".$_POST["selRole"].",%'";
if(!$isAdmin || $isActive==0) $aWhere[] = " mbrStatus<>'X'";
if($txtSearch <> "") $aWhere[] = " (concat(mbrLastName,', ',mbrFirstName) like \"%$txtSearch%\" OR concat(mbrFirstName,' ',mbrLastName) like \"%$txtSearch%\")";
$Where = "";
if(count($aWhere)>0) {
	$Where = count($aWhere)>1?" WHERE".implode(" AND",$aWhere):" WHERE".$aWhere[0];
}
$q = "SELECT *, concat(mbrLastName,', ',mbrFirstName) as mbrName FROM members$Where ORDER BY mbrLastName, mbrFirstName";
$useqry = $db->query($q);
$numPages = ceil(mysqli_num_rows($useqry)/$pageSize);
$pageTxt = "";
$q = "SELECT *, concat(mbrLastName,', ',mbrFirstName) as mbrName FROM members$Where ORDER BY mbrLastName, mbrFirstName$limit";
$resMbr = $db->query($q);
echo "<table style='border-collapse:collapse;border:2px ridge;width:100%'>\n";
if($resMbr && (mysqli_num_rows($resMbr) > 0)){
	if($numPages > 1) {
		$pageTxt = "<tr>\n";
		$pageTxt .= "	<td style='border:1px solid #000000;background-color:#ebebeb;font-size:7pt;font-weight:normal' colspan=\"2\" align=\"center\">\n";
		if($pageNum > 1) {
			$pageTxt .= "		<a href=\"#\" onClick=\"frmTeam.pageNum.value=Number(frmTeam.pageNum.value)-1;frmTeam.submit();\">[<< Prev]</a>&nbsp;&nbsp;\n";
		}
		for($i=1;$i<=$numPages;$i++) {
			if($i==$pageNum) {
				$pageTxt .= "		<span style='color:#933100;font-weight:bold;font-size:9pt;'>$i</span>&nbsp;&nbsp;\n";
			} else {
				$pageTxt .= "		<a href=\"#\" onClick=\"frmTeam.pageNum.value=$i;frmTeam.submit();\">$i</a>&nbsp;&nbsp;\n";
			}
		}
		
		if ($pageNum < $numPages) {
			$pageTxt .= "		<a href=\"#\" onClick=\"frmTeam.pageNum.value=Number(frmTeam.pageNum.value)+1;frmTeam.submit();\">[Next >>]</a>\n";
		}
		$pageTxt .= "	</td>\n";
		$pageTxt .= "</tr>\n";
	}
	echo $pageTxt."<tr><td valign='top'>";
	
	// Top of table of people 
	$memberDesc = "<table style='border-collapse:collapse;width:100%'>\n";
	$shade = false;
	while($dbMbr=mysqli_fetch_array($resMbr)) {
		$bgcolor = $shade?"#efefef":"";
		$shade = !$shade;
		$memberDesc .= "	<tr bgcolor='$bgcolor'><td width='16'>\n";
		if ($isAdmin) {
			if($dbMbr["mbrType"]=="G") {
				$memberDesc .= "		<a onClick=\"editMbrGroup(".$dbMbr["memberID"].");\" href='#' title='Edit Member Group Info'><img border='0' src=\"images/edit.png\"></a></td>\n";
			} else {
				$memberDesc .= "		<a onClick=\"editMember(".$dbMbr["memberID"].");\" href='#' title='Edit Member Info'><img border='0' src=\"images/edit.png\"></a></td>\n";
			}
			$qm = "SELECT memberID FROM serviceteam WHERE memberID=" .
						$dbMbr["memberID"]." OR ',".$dbMbr["groupArray"].",' LIKE concat('%,',memberID,',%')";
			$resDel = $db->query($qm);
			
			
			$memberDesc .= "		<td width='16'>";
			// Group icon to delete members 
			if($dbMbr["mbrType"]=="G") {
				$memberDesc .= !$resDel || mysqli_num_rows($resDel)==0?"<a onClick=\"delMbrGroup(".$dbMbr["memberID"].",'".addslashes($dbMbr["mbrName"])."');\" href='#' title='Delete Member Group'><img border='0' src=\"images/icon_delete.gif\"></a>\n":"&nbsp;";
			} else {
				$memberDesc .= !$resDel || mysqli_num_rows($resDel)==0?"<a onClick=\"delMember(".$dbMbr["memberID"].",'".$dbMbr["mbrUName"]."','".addslashes($dbMbr["mbrName"])."');\" href='#' title='Delete Member'><img border='0' src=\"images/icon_delete.gif\"></a>\n":"&nbsp;";
			}
			$memberDesc .= "		</td>";
		}  // IsAdmin
		
		
		$schedLink = "<a href=\"#\" onClick=\"memberSchedule(".$dbMbr["memberID"].");\" title=\"Display Member Schedule\"><img border='0' src=\"/images/icon_schedule.gif\" /></a>";
		if($dbMbr["mbrType"]=="G") {
			$memberDesc .= "		<td width='76'><a href=\"#\" onClick=\"mbrGroupProfile(".$dbMbr["memberID"].",'".$dbMbr["mbrLastName"]."');\" title=\"Display Member Group Profile\"><img src=\"/images/icon_profile.gif\" /></a>&nbsp;$schedLink</td><td>&nbsp;<b>".$dbMbr["mbrLastName"]."</b></td>\n";
		} else {
      $memStateB = "";
      $memStateE = "";
      
 		// Scratch out the name if they are disabled account
      if ($dbMbr["mbrStatus"]=="X") {
        $memStateB = "<span style='color:#E60000;text-decoration:line-through'>";
        $memStateE = "</span>";
        }
      $memberDesc .= "		<td width='76'><a href=\"#\" onClick=\"memberProfile(".$dbMbr["memberID"].");\" title=\"Display Member Profile\"><img border='0' src=\"/images/icon_profile.gif\" /></a>&nbsp;$schedLink</td><td>&nbsp;".$memStateB . $dbMbr["mbrName"].$memStateE."</td>\n";
		}
		// show last logon to Admin
		if ($isAdmin) {
			$memberDesc .= "<td align='right'>" . $dbMbr["last_login"] . "</td>";
		}
		$memberDesc .= "	</tr>\n";
	}
	echo $memberDesc."</table></td>\n";
	
	// bottom of the table of people
	echo "<td width='425' align='right' valign='top'><div style='text-align:left;border-left:1px solid;height:" .
		($pageSize * 21) ."px;width:425px;padding:3px;overflow-x:auto' id='memberDiv'></div></td>\n";
	echo "</tr>\n";
	echo $pageTxt;
} else {
	echo "<tr><td><h2 align='center'>No members found.</h2></td></tr>\n";
}
echo "</table>\n";
echo "</body>\n</html>\n";
