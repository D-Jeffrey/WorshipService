<?php

//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();

session_start();

//include config and functions files
include ('config.php');
include ('functions.php');

//if user tries to access this page without logging in, this will send the user back to login.html
if (!$_SESSION["user_name"])
{
	header('Location:login.php');
	exit;
}

//checks password length
if (password_check($min_pass, $max_pass, $_POST["p_word"]) == "no")
{
?>
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<meta http-equiv="refresh" content="0; url=pass_change.html">
<title>Password Change</title>
<script language="JavaScript">
<!--
function FP_popUpMsg(msg) {//v1.0
 alert(msg);
}
// -->
</script>
</head>

<body onload="FP_popUpMsg('Your password must be between <?php echo $min_pass; ?> & <?php echo $max_pass; ?> characters.')">

</body>

</html>
<?php exit;
}

//make connection to dbase
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());

// Update phpBB password
	if ($use_phpBB3) {

		include("../phpBB3/includes/extBridge.php");
		$phpbb = new PhpBB3Component;
		$phpbb->startup();
		$phpbb->changePassword($_SESSION["user_name"], $_POST["p_word"]);
	}
//updates the table with the new password		
    $p = $_POST["p_word"];
    $u = $_SESSION["user_name"];
	$sql = "UPDATE $table_name SET 
			mbrPassword = md5('$p') 
			WHERE mbrUName = '$u'";
	$result = @$db->query($sql) or die(mysqli_error());
	$_SESSION["password"] = $_POST["p_word"];

//resets the password change required to no	
	$set_chng = "UPDATE $table_name SET	pchange = '0' WHERE mbrUName = '$u'";
	$result1 = @$db->query($set_chng) or die(mysqli_error());			

//gets that users redirect to	
	$get_redir = "SELECT * FROM $table_name WHERE mbrUName = '$u'";
	$result2 = @$db->query($get_redir) or die(mysqli_error());
	while ($get_redir = mysqli_fetch_object($result2)) 
		{	
			$_SESSION['redirect'] = $get_redir -> redirect;
		}

//sends the user to their redirect to
	header("Location:". $_SESSION['redirect']);
	exit();
?>
