<?php 
/*******************************************************************
 * dspSongBooks.php
 * Display Team Member information
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

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


include("classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Display Song Books', $_SERVER['REQUEST_URI'], 1);

$isAdmin = (allow_access(Administrators)=="yes");

if(isset($_POST["pageNum"]) && $_POST["pageNum"] > 0)
	$pageNum = $_POST["pageNum"];
else
	$pageNum = 1;
if(isset($_POST["txtSearch"]))
	$txtSearch = $_POST["txtSearch"];
else
	$txtSearch = "";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Song Books</title>
<script type="text/javascript" src="scripts/scriptaculous/lib/prototype.js"></script>
<script type="text/javascript" src="scripts/scriptaculous/src/scriptaculous.js"></script>

<script type="text/javascript">
function valSearch() {
	if(document.frmBook.txtSearch.value!="<?php echo $txtSearch; ?>") {
		document.frmBook.pageNum.value = 1;
	}
	return true;
}

function delBook(id,name) {
	if(confirm("Delete member: "+name+"?")) {
		document.frmBook.action="editSongBook.php?id="+id+"&ac=del";
		document.frmBook.bkact.value="del";
		document.frmBook.bookID.value=id;
		document.frmBook.submit();
	}
}

function editBookTitle() {
	var title = addslashes(document.frmTitle.bookTitle.value);
	var prv = document.frmTitle.private.checked?1:0;
	new Ajax.Request('/ajSongBookAdd.php', { 
		method: "get",
		parameters: { 	bid: 0, sid: 0, t: title, uid:'<?php echo $_SESSION["user_name"]; ?>', act: 'addbook',p:prv },
		onSuccess: function(response) {
			editBook(response.responseText);
		}
	});
}


function dspBook(id) {
	document.frmBook.action="editSongBook.php?id="+id+"&ac=dsp";
	document.frmBook.bkact.value="dsp";
	document.frmBook.bookID.value=id;
	document.frmBook.submit();
}

function editBook(id) {
	document.frmBook.action="editSongBook.php?id="+id+"&ac=edit";
	document.frmBook.bkact.value="edit";
	document.frmBook.bookID.value=id;
	document.frmBook.submit();
}


function addslashes( str ) {
    return (str+'').replace(/([\\"'])/g, "\\$1").replace(/\u0000/g, "\\0");
}
</script>
<?php
// " 

$hlpID = $isAdmin?19:10;
$title = "Song Books";
include("header.php");

echo "	<form name=\"frmBook\" action=\"dspSongBooks.php\" method=\"post\" onSubmit=\"valSearch();\">\n";
echo "	<input name=\"bkact\" type=\"hidden\">\n";
echo "	<input name=\"bookID\" type=\"hidden\" value=\"$bookID\">\n";
echo "	<input type=\"hidden\" name=\"pageNum\" id=\"pageNum\" value=$pageNum>\n";
echo "	<table border=\"0\" width=\"100%\" cellspacing=\"1\" cellpadding=\"2\">\n";
echo "		<tr>\n";
echo "			<td valign=\"middle\" align=\"left\">\n";
echo "				<a href='ajSongBookTitle.php?ac=add' onclick=\"return hs.htmlExpand(this, { objectType: 'ajax', contentID: 'divEditTitle', headingText: 'New Book Title' } )\" title='Add New Song Book'><img src=\"images/icon_new.gif\" style='vertical-align:middle'>New Song Book</a>\n";
echo "			</td>\n";
echo "			<td align=\"right\">\n";
echo "				<strong>Search:</strong>&nbsp;\n";
echo "				<input type=\"text\" name=\"txtSearch\" size=\"20\" value=\"$txtSearch\">&nbsp;\n";
echo "				<a href='#' onClick=\"document.frmBook.submit();\"><img src=\"/images/search.gif\"></a>\n";
echo "			</td>\n";
echo "		</tr>\n";
echo "	</table>\n";

if($pageNum > 1) {
	$limit = " LIMIT ".($pageNum * 20 - 20).",20";
} else {
	$limit = " LIMIT 20";
}
if($txtSearch <> "") {
	$q = "SELECT * FROM songbook WHERE (private=0 OR mbrUName='".$_SESSION["user_name"]."') AND bookTitle like \"%$txtSearch%\" ORDER BY bookTitle";
} else {
	$q = "SELECT * FROM songbook WHERE (private=0 OR mbrUName='".$_SESSION["user_name"]."') ORDER BY bookTitle";
}
$useqry = $db->query($q);
$numPages = ceil(mysqli_num_rows($useqry)/20);

if($txtSearch <> "") {
	$q = "SELECT *, concat(mbrFirstName,' ',mbrLastName) AS mbrName, songbook.mbrUName AS UserName FROM songbook INNER JOIN members ON songbook.mbrUName=members.mbrUName WHERE (private=0 OR members.mbrUName='".$_SESSION["user_name"]."') AND bookTitle like \"%$txtSearch%\" ORDER BY bookTitle$limit";
} else {
	$q = "SELECT *, concat(mbrFirstName,' ',mbrLastName) AS mbrName, songbook.mbrUName AS UserName FROM songbook INNER JOIN members ON songbook.mbrUName=members.mbrUName WHERE (private=0 OR members.mbrUName='".$_SESSION["user_name"]."') ORDER BY bookTitle".$limit;
}
$resBook = $db->query($q);
echo "<table width='100%'>\n";
if($resBook && (mysqli_num_rows($resBook) > 0)){
	if($numPages > 1) {
		$pageTxt = "<tr>\n";
		$pageTxt .= "	<td style='border:1px solid #000000;background-color:#ebebeb;font-size:7pt;font-weight:normal' colspan=\"2\" align=\"center\">\n";
		if($pageNum > 1) {
			$pageTxt .= "		<a href=\"#\" onClick=\"frmBook.pageNum.value=Number(frmBook.pageNum.value)-1;frmBook.submit();\">[<< Prev]</a>&nbsp;&nbsp;\n";
		}
		for($i=1;$i<=$numPages;$i++) {
			if($i==$pageNum) {
				$pageTxt .= "		<span style='color:#933100;font-weight:bold;font-size:9pt;'>$i</span>&nbsp;&nbsp;\n";
			} else {
				$pageTxt .= "		<a href=\"#\" onClick=\"frmBook.pageNum.value=$i;frmBook.submit();\">$i</a>&nbsp;&nbsp;\n";
			}
		}
		if($_POST["pageNum"] < $numPages) {
			$pageTxt .= "		<a href=\"#\" onClick=\"frmBook.pageNum.value=Number(frmBook.pageNum.value)+1;frmBook.submit();\">[Next >>]</a>\n";
		}
		$pageTxt .= "	</td>\n";
		$pageTxt .= "</tr>\n";
	}
	echo $pageTxt."<tr><td width='55%' valign='top'>";
	$bookDesc = "<table style='border:2px inset;border-collapse:collapse;width:100%'>\n";
	$bookDesc .= "	<tr style='border:1px solid; background-color:#e0e0e0'><td>&nbsp;</td>\n";
	$bookDesc .= "		<td><b>Song Book Title</b></td>\n";
	$bookDesc .= "		<td><b>Created By</b></td>\n";
	$bookDesc .= "	</tr>\n";
	$shade = false;
	while($dbBook=mysqli_fetch_array($resBook)) {
		$bgcolor = $shade?"#efefef":"";
		$shade = !$shade;
		$bookDesc .= "	<tr bgcolor='$bgcolor'>\n";
		$editLink = $dbBook["UserName"]==$_SESSION["user_name"]?"<a onClick=\"editBook(".$dbBook["bookID"].");\" href='#' title='Edit Song Book'><img src=\"images/edit.png\"></a>":"";
		$dspLink = "<a onClick=\"dspBook(".$dbBook["bookID"].");\" href='#' title='Display Song Book'><img src=\"images/icon_preview.gif\"></a>";
		$delLink = $dbBook["UserName"]==$_SESSION["user_name"]?"<a onClick=\"delBook(".$dbBook["bookID"].",'".addslashes($dbBook["bookTitle"])."');\" href='#' title='Delete Song Book'><img src=\"images/icon_delete.gif\"></a>":"";
		$bookDesc .= "		<td>$dspLink$editLink\n";
		$bookDesc .= "		$delLink&nbsp;</td>\n";
		$prvText = $dbBook["private"]==1?"&nbsp;(Private)":"&nbsp;(Shared)";
		$bookDesc .= "		<td>".$dbBook["bookTitle"].$prvText."</td>\n";
		$bookDesc .= "		<td>".$dbBook["mbrName"]."</td>\n";
		$bookDesc .= "	</tr>\n";
	}
	echo $bookDesc."</table></td>\n";
	echo "</tr>\n";
	echo $pageTxt;
} else {
	echo "<tr><td><h2 align='center'>No songbooks found.</h2></td></tr>\n";
}
echo "</table>\n";

// Edit Book Title Section
echo "<div id='divEditTitle' class='highslide-html-content'>\n";
echo "	<div class='highslide-body'></div>\n";
echo "</div>\n";

echo "</body>\n</html>\n";
