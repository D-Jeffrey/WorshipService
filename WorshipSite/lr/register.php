<?php

// disabled and untested

//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();


session_start();

//require the config file
require ("config.php");
require ("functions.php");

//checks password length
if (password_check($min_pass, $max_pass, $_POST['password']) == "no")
{
?>
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<meta http-equiv="refresh" content="0; url=javascript:history.go(-1)">
<title>Registration</title>
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

//make the connection to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die($db->error);

//make query to database
$sql ="SELECT * FROM $table_name WHERE mbrUName = '$_POST[username]'";
$result = @$db->query($sql) or die($db->error);

//get the number of rows in the result set
$num = mysqli_num_rows($result);

//checks it see if that username already exists
if ($num != 0){

echo "<P>Sorry, that username already exists.</P>";
echo "<P><a href=\"#\" onClick=\"history.go(-1)\">Try Another Username.</a></p>";
exit;

}else{
	$sql = "INSERT INTO $table_name  (`memberID`, `mbrType`,  `mbrFirstName`, `mbrLastName`, `mbrUName`, `mbrEmail1`, `mbrEmail2`, `mbrHPhone`, `mbrWPhone`, `mbrCPhone`, `mbrPassword`, `mbrGroup1`, `last_login`, `mbrStatus`, `redirect`, `pchange`, `roleArray`, `groupArray`,  `mbrGroup2`, `mbrGroup3`) VALUES (0,'I', '". $_POST['firstname'] ."', '". $_POST['lastname'] ."', '" . $_POST['username'] . "', '" . $_POST['email'] . "', '" . $_POST['email2'] . "', '" . $_POST['hphone'] . "', '" . $_POST['wphone'] . "', '" . $_POST['cphone'] . "', md5('" . $_POST['password'] . "'), 'Users', CURRENT_DATE(), 'X', '$default_url', '0', '', '', '','')";
// echo "<pre> $sql </pre>";
$result = @$db->query($sql) or die($db->error);
}

//checks to see if the user needs to verify their email address before accessing the site
if ($verify == "P")
{
	$mailheaders = "From: $domain\n";
	$mailheaders .= "Your account has been created.\n";
	$mailheaders .= "Please activate your account now by visiting this page:\n";
	$mailheaders .= "$base_dir/activate.html\n";


	$to = "$_POST[email]";
	$subject = "Please activate your account";

mail($to, $subject, $mailheaders, "From: No Reply <$adminemail>\n");

}else{
	header('Location:login.php');
}



?>

<HTML>
<HEAD>
<TITLE>Add a User</TITLE>
</HEAD>
<BODY>
<H1>Please check your email to activate your account.</H1>
</BODY>
</HTML>