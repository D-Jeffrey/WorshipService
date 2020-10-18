<?php
/*******************************************************************
 * editResources.php
 * Add Service worship resource information
 *******************************************************************/
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

require($_SERVER["DOCUMENT_ROOT"]."/lr/config.php");
require('lr/functions.php'); 
//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Administrators) != "yes") { 
	exit;
}

//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


$serviceID = $_REQUEST["id"];
$resourceID = isset($_REQUEST["rid"])?$_REQUEST["rid"]:0;
$action = $_REQUEST["act"];

$upload_dir = $_SERVER["DOCUMENT_ROOT"]."/UserFiles/File"; // Directory for file storing
$web_upload_dir = "/UserFiles/File"; 	// Directory for file storing
// remove these lines if you're shure 
// that your upload dir is really writable to PHP scripts
$tf = $upload_dir.'/'.md5(rand()).".test";
$f = @fopen($tf, "w");
if ($f == false) 
    die("Fatal error! {$upload_dir} is not writable. Set 'chmod 777 {$upload_dir}'
        or something like this");
fclose($f);
unlink($tf);
// end up upload dir testing 

// Save New Resource
if (isset($_POST['fileframe'])) {
    $result = 'ERROR';
    $result_msg = 'No FILE field found';

	// file was sent from browser
    if (isset($_FILES['file'])) {
        if ($_FILES['file']['error'] == UPLOAD_ERR_OK) { // no error
            $filename = time().$_FILES['file']['name']; // file name 
            $text = $_POST["text"];
            move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir.'/'.stripslashes($filename));
            // main action -- move uploaded file to $upload_dir 
            $result = 'OK';
        }
        elseif ($_FILES['file']['error'] == UPLOAD_ERR_INI_SIZE)
            $result_msg = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        else 
            $result_msg = 'Unknown error';
    }

    echo '<html><head><title>-</title></head><body>';
    echo '<script language="JavaScript" type="text/javascript">'."\n";
    echo 'var parDoc = window.parent.document;';
    // this code is outputted to IFRAME (embedded frame)
    // main page is a 'parent'

    if ($result == 'OK') {
		// Add resource reference to the database
		if(isset($_POST["catEveryone"])) {
			$catArray = "*";
		} else if(isset($_POST["catArray"])) {
			$catArray = implode(",",$_POST["catArray"]);
		} else {
			$catArray = "";
		}
		$sql = "INSERT INTO serviceresources VALUES(".$_POST["serviceID"].",0,'".$db->real_escape_string($_POST["rscDescription"])."','/UserFiles/File/$filename','$catArray')";
		$resRsc = $db->query($sql);
		echo 'parDoc.write("\<script\>parent.window.dspResource(true);parent.window.hs.close();\</script\>");';
    } else {
        echo 'parDoc.getElementById("upload_status").innerHTML = "ERROR: '.$result_msg.'";';
    }
    echo "\n".'</script></body></html>';

    exit(); // do not go futher 
}

// Save Resource Changes
if (isset($_POST['saveEdit'])) {
	// Add resource reference to the database
	if(isset($_POST["catEveryone"])) {
		$catArray = "*";
	} else if(isset($_POST["catArray"])) {
		$catArray = implode(",",$_POST["catArray"]);
	} else {
		$catArray = "";
	}
	$sql = "UPDATE serviceresources SET rscDescription='".$db->real_escape_string($_POST["rscDescription"])."',rscCategories='$catArray' WHERE serviceID=$serviceID AND resourceID=$resourceID";
	$resRsc = $db->query($sql);
	echo '<script>parent.window.dspResource(false);parent.window.hs.close();</script>");';
	exit;
}
 
// retrieving message from cookie 
if (isset($_COOKIE['msg']) && $_COOKIE['msg'] != '') {
    if (get_magic_quotes_gpc()) 
        $msg = stripslashes($_COOKIE['msg']); 
    else
        $msg = $_COOKIE['msg'];
    // clearing cookie, we're not going to display same message several times
    setcookie('msg', ''); 
} 
?>
<!-- Beginning of main page -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteTitle; ?> - Add Service Resource</title>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/ajax_req.js"></script>
<script type="text/javascript" src="<?php echo $baseFolder; ?>scripts/ajax_req.js"></script>
<link rel="stylesheet" href="<?php echo $baseFolder; ?>css/tw.css" type="text/css">
<style>
form {
	padding:0px;
}
</style>
</head>
<body style='background-color:#ffffff'>

<?php 
if (isset($msg)) // this is special section for outputing message 
    echo '<p style="font-weight: bold;">'.$msg.'</p>';

/* Retrieve Resource Item */
if($action=="Edit") {
	$q = "SELECT * FROM serviceresources WHERE serviceID=$serviceID AND resourceID=$resourceID";
	$resRsc = $db->query($q);
	$i = 1;
	if($dbRsc=mysqli_fetch_array($resRsc)) {
		$rscDescription = $dbRsc["rscDescription"];
		$catArray = explode(",",$dbRsc["rscCategories"]);
	} else {
		$rscDescription = "";
		$catArray = array();
	}
} else {
	$rscDescription = "";
	$catArray = array();
}

echo "<h2>$siteTitle - $action Service Resource</h2>\n";

if($action=="Edit") {
	echo "<form name=\"frmUpLoad\" action=\"editResource.php?id=$serviceID&rid=$resourceID&act=$action\" method=\"post\">\n";
} else {
	echo "<form name=\"frmUpLoad\" action=\"".$_SERVER["PHP_SELF"]."\" target=\"upload_iframe\" method=\"post\" enctype=\"multipart/form-data\">\n";
	echo "<input type=\"hidden\" name=\"fileframe\" value=\"true\">\n";
	// Target of the form is set to hidden iframe
	// Form will send its post data to fileframe section of this PHP script (see above)
}
echo "<input name=\"serviceID\" type=\"hidden\" value=\"$serviceID\">\n";
echo "<input name=\"subact\" type=\"hidden\" value=\"editrsc\">\n";
echo "<table>\n";
if($action!="Edit") {
	echo "	<tr>\n";
	echo "		<td>Resource File:</td>\n";
	echo "		<td><input type=\"file\" name=\"file\" id=\"file\" size=\"37\"></td>\n";
	echo "	</tr>\n";
}
echo "	<tr>\n";
echo "		<td>Description</td>\n";
echo "		<td><input title=\"Resource Description\" name=\"rscDescription\" type=\"text\" id=\"rscDescription\" size=\"50\" maxlength=\"100\" value=\"$rscDescription\"></td>\n";
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
echo "	<tr>\n";
if($action=="Edit") {
	echo "		<td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"saveEdit\" value=\"Save\" /><input type=\"button\" name=\"cancel\" value=\"Cancel\" onClick=\"parent.window.hs.close();\" /></td>\n";
} else {
	echo "		<td colspan=\"2\" align=\"center\"><input type=\"button\" name=\"save\" value=\"Save\" onClick=\"jsUpload(document.frmUpLoad.file)\" /><input type=\"button\" name=\"cancel\" value=\"Cancel\" onClick=\"parent.window.hs.close();\" /></td>\n";
}
echo "	</tr>\n";
echo "</table>\n";
echo "</form>\n";
if($action!="Edit") {
?> 
	<script type="text/javascript">
	/* This function is called when user selects file in file dialog */
		
	function jsUpload(upload_field)
	{
	    // var re_text = /\.doc|\.gif|\.jpg|\.pdf|\.txt|\.xml|\.zip|\.wma|\.wmv|\.mp3/i; 
	
	    var re_text = /\./i; 
	    var filename = upload_field.value;
		/* Checking for some file name */
	    if (filename.search(re_text) == -1)
		{
			// alert("File does not have appropriate extension ("+filename+")"); 
			alert("Please select a file to attach.");
			upload_field.form.reset();
			return false;
		}
	
	    upload_field.form.submit();
	    document.getElementById('upload_status').value = "uploading file...";
	    upload_field.disabled = true;
	    return true;
	}
	</script>
	<iframe name="upload_iframe" style="width: 400px; height: 50px; display: none;">
	</iframe>
	<br>
	<div style=\"color:red\" id=\"upload_status\"></div>
	<?php

}
echo "</body>\n";
echo "</html>\n";
?>