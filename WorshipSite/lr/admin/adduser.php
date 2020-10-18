<?php 

// OLD CODE
//
// Obsoleted
die("This function is no longer value please contact SCCC IT Support, if you need this\n")

//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();

session_start();

//require the config file
require ("../config.php");
require ("../functions.php");

if ($page == ""){$page = 1;}
if ($max_results == ""){$max_results = 5;}
	
	
//check required fields
if (($_POST['username'] == ""))
{
	echo "The Username Field can not be left blank.  Please <a href=\"adminpage.php?page=$page&max_results=$max_results\">Go Back</a> and re-enter the information";
	exit;
}



//check the password length
$pass_len = password_check($min_pass, $max_pass, $_POST['password']);
if ($pass_len == "no")
{


	echo "<p>You must use a password between $min_pass and $max_pass characters in length, please <a href=\"adminpage.php?page=$page&max_results=$max_results\">Go Back </a>and try again.</p>";
	exit;
}

//check for authority to view this page
if (allow_access(Administrators) != "yes")
{
	echo "not authorized to perform this function";
}

//make the connection to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());

//make query to database
$sql ="SELECT * FROM $table_name WHERE username= '$_POST['username']'";
$result = @$db->query($sql) or die(mysqli_error());

//get the number of rows in the result set
$num = mysqli_num_rows($result);

//check if that username already exists
if ($num != 0){

echo "<P>Sorry, that username already exists.</P>";
echo "<P><a href=\"#\" onClick=\"history.go(-1)\">Try Another Username.</a></p>";
echo "$_POST['username']";
exit;

}else{

//or add it to the database
$sql_add = "INSERT INTO $table_name (firstname, lastname, username, password, group1, group2, group3, 
			pchange, email, redirect, verified, last_login) VALUES
			('$_POST['firstname']', '$_POST['lastname']', '$_POST['username']', password('$_POST['password']'), 
			'$_POST['group1']', '$_POST['group2']', '$_POST['group3']', '$_POST['pchange']', '$_POST['email']',
			'$_POST['redirect']', '1', 'last_login()')";

$result = @$db->query($sql_add) or die(mysqli_error());
}

if ($_POST['email_user'] == "Yes")
{
	$mailheaders = "From: $domain\n";
	$mailheaders .= "Dear $_POST['firstname'] $_POST['lastname'],\n";
	$mailheaders .= "\n";
	$mailheaders .= "An account has been created for you at $domain.\n";
	$mailheaders .= "Please log in with the following account information:\n";
	$mailheaders .= "Username: $_POST['username']\n";
	$mailheaders .= "Password: $_POST['password']\n";
	$mailheaders .= "\n";
	$mailheaders .= "Please login at:\n";
	$mailheaders .= "$base_dir/login.php\n";
	$mailheaders .= "Should you have any complications, please email the System Administrator at:\n";
	$mailheaders .= "$adminemail\n";

	$to = "$_POST['email']";
	$subject = "Your account has been created !!";

	mail($to, $subject, $mailheaders, "From: No Reply <$adminemail>\n");

}
?>

<html>

<head>
<meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<link rel="stylesheet" type="text/css" href="adminpage.css">
<meta http-equiv="refresh" content="5; url=adminpage.php">
<title>Add User</title>
</head>

<body>

<p><b><font face="Tahoma">User Added:</font></b></p>
<table border="1" id="table1">
	<tr>
		<td width="154"><font face="Tahoma" size="2">First Name:</td>
		<td><?php echo $_POST['firstname']; ?></font></td>
	</tr>
	<tr>
		<td width="154"><font face="Tahoma" size="2">Last Name:</td>
		<td><?php echo $_POST['lastname']; ?></font></td>
	</tr>
	<tr>
		<td width="154"><font face="Tahoma" size="2">Username:</td>
		<td><?php echo $_POST['username']; ?></font></td>
	</tr>
	<tr>
		<td width="154"><font face="Tahoma" size="2">Password:</td>
		<td><?php echo $_POST['password']; ?></font></td>
	</tr>
	<tr>
	<tr>
		<td width="154"><font face="Tahoma" size="2">E-Mail:</td>
		<td><?php echo $_POST['email']; ?></font></td>
	</tr>
		<td width="154"><font face="Tahoma" size="2">Group Memberships:</td>
		<td><?php echo $_POST['group1']; ?>&nbsp;</td>
	</tr>
	<tr>
		<td width="154">&nbsp;</td>
		<td><?php echo $_POST['group2']; ?>&nbsp;</td>
	</tr>
	<tr>
		<td width="154">&nbsp;</td>
		<td><?php echo $_POST['group3']; ?>&nbsp;</font></td>
	</tr>
	<tr>
		<td width="154"><font face="Tahoma" size="2">Redirect to:</font></td>
		<td><?php echo $_POST['redirect']; ?></font></td>
	</tr>
	<tr>
		<td width="154"><font face="Tahoma" size="2">Password Change Req'd:</td>
		<td><?php if($_POST['pchange'] == "1"){$ans1="Yes";}else{$ans1="No";} echo $ans1; ?></td>
	</tr>
	<tr>
		<td width="154"><font face="Tahoma" size="2">User E-Mailed:</td>
		<td><?php echo $_POST['email_user']; ?></font></td>
	</tr>
</table>

</body>

</html>
