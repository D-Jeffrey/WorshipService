<?php
// Obsoleted
die("This function is no longer value please contact SCCC IT Support, if you need this\n")

//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();

session_start();

require('../config.php');
require('../functions.php');

//check for administrative rights
if (allow_access(Administrators) != "yes")
{
	include ('../login.php?msg=1');
	exit;
}

include($_SERVER["DOCUMENT_ROOT"]."/classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('User Admin', $_SERVER['REQUEST_URI'], 4);

//make connection to dbase
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());


//build and issue the query
$sql ="SELECT * FROM $table_name";
$result = @$db->query($sql) or die(mysqli_error());

?>

<head>
<meta http-equiv="Content-Language" content="en-us">
<link rel="stylesheet" type="text/css" href="adminpage.css">
<link rel="stylesheet" href="/css/tw.css" type="text/css">
</head>

<body topmargin="0" leftmargin="0" rightmargin="0" bottommargin="0">
<?php
$curUser = $_SESSION[first_name]." ".$_SESSION[last_name];
echo "<table class='topbar'><tr><td>&nbsp;User Administration</td><td align='right'>TeamWorship&nbsp;<br /><br /><span style='font-size:10pt;'>Member: $curUser</span>&nbsp;&nbsp;&nbsp;<a href='/lr/logout.php'>Logout</a>&nbsp;</td></tr></table>\n";
$trail->output();
?>
<table border="0" width="100%" id="table1">
	<tr>
		<td align="center">
		<table border="0" width="800" id="table2" cellspacing="0" cellpadding="0">
			<tr>
				<td width="280">&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td width="280"><b><font size="2">Login - Redirect Control Panel</font></b></td>
				<td align="right"><i><a href="../logs/index.php">View Access Logs</a></i></td>
			</tr>
			<tr>
				<td width="280">Logged in as <?php echo $_SESSION[user_name]; ?></td>
				<td align="right"><i><a href="../logout.php">Logout</a></i></td>
			</tr>
			<tr>
				<td width="280">&nbsp;</td>
				<td>
				<p align="right"><i><a href="mail_all.htm">Email All Users</a></i></td>
			</tr>
			<tr>
				<td width="280" valign="top">
				
				<table border="0" width="100%" id="table3" cellspacing="0" cellpadding="0">
					<tr>
						
						<td width="38%" valign="top">
						<table border="0" width="100%" id="table38" cellspacing="0" cellpadding="0">
							<tr>
								<td>

<?php
if ($_POST[alter2] == "add")
{
?>

	<form method="POST" action="<?php $_SERVER["PHP_SELF"]; ?>">
		<p><input type="hidden" name="alterup2" value="up"><input type="image" img src="admin_add_up.gif" value="Submit" alt="Submit"></p>
	</form>

<?php
}else
{
?>
		<form method="POST" action="<?php $_SERVER["PHP_SELF"]; ?>">
		<p><input type="hidden" name="alter2" value="add"><input type="image" img src="admin_add.gif" value="Submit" alt="Submit"></p>
	</form>
<?php
}
?>


								</td>
							</tr>
							<tr>
								<td>
	<?php
	if ($_POST[alter2] == "add")
	{
	?>
								
								<form method="POST" action="<?php echo "adduser.php?page=$page&max_results=$max_results"; ?>">
	<font size="1" face="Tahoma">
			<table border="1" width="100%" id="table44" bordercolorlight="#C0C0C0" bordercolordark="#FFFFFF">
				<tr>
					<td width="140">First Name:</td>
					<td><font size="1" face="Tahoma">
			<input type="text" name="firstname" size="20"></font></td>
				</tr>
				<tr>
					<td width="140">Last Name:</td>
					<td><font size="1" face="Tahoma">

			<input type="text" name="lastname" size="20"></font></td>
				</tr>
				<tr>
					<td width="140">Username:</td>
					<td><font size="1" face="Tahoma">

			<input type="text" name="username" size="20"></font></td>
				</tr>
				<tr>
					<td width="140">Password:</td>
					<td><font size="1" face="Tahoma">

			<input type="text" name="password" size="20" maxlength="667"></font></td>
				</tr>
				<tr>
					<td width="140">E-Mail Address:</td>
					<td><font size="1" face="Tahoma">

			<input type="text" name="email" size="20"></font></td>
				</tr>
				<tr>
					<td width="140">Group Memberships:</td>
					<td><font size="1" face="Tahoma">
			<select size="1" name="group1">

<?php

echo "<option>Users</option>";
$i = 0;
while ($i < $num_groups)
{
	echo "<option value=\"$group_array[$i]\">$group_array[$i]</option>";
	$i++;
}
?>

			</select></font></td>
				</tr>
				<tr>
					<td width="140">&nbsp;</td>
					<td><font size="1" face="Tahoma">
			<select size="1" name="group2">

<?php
	echo "<option></option>";
$i = 0;
while ($i < $num_groups)
{
	echo "<option value=\"$group_array[$i]\">$group_array[$i]</option>";
	$i++;
}
?>

			</select></font></td>
				</tr>
				<tr>
					<td width="140">&nbsp;</td>
					<td><font size="1" face="Tahoma">
			<select size="1" name="group3">

<?php
	echo "<option></option>";
$i = 0;
while ($i < $num_groups)
{
	echo "<option value=\"$group_array[$i]\">$group_array[$i]</option>";
	$i++;
}
?>

			</select></font></td>
				</tr>
				<tr>
					<td width="140">Redirect to:</td>
					<td><font size="1" face="Tahoma">
			<input type="text" name="redirect" size="20" value="http://"></font></td>
				</tr>
				<tr>
					<td width="140">Change Password Next Login:</td>
					<td><font size="1" face="Tahoma">
			<select size="1" name="pchange">
			<option value="0" selected>No</option>
			<option value="1">Yes</option>
			</select></font></td>
				</tr>
				<tr>
					<td width="140">E-Mail User Account 
					Information:</td>
					<td><font size="1" face="Tahoma">
			<select size="1" name="email_user">
			<option value="No" selected>No</option>
			<option value="Yes">Yes</option>
			</select></font></td>
				</tr>
				<tr>
					<td width="140"><font size="1" face="Tahoma">
			<input type="submit" value="Submit" name="B4"></font></td>
					<td>&nbsp;</td>
				</tr>
			</table>

		</font>

								</td>
							</tr>
							<tr>

								</form>
<?php
}
?>

							</tr>
							<tr>
								<td><font size="1" face="Tahoma">
								
								<?php
								if ($_POST[alter1] == "modify" || $_POST[username] != "")
								{
								?>
								
								<form method="POST" action="<?php $_SERVER["PHP_SELF"]; ?>">
									<p>
									<input type="hidden" name="alter1" size="20" value="up"><input type="image" img src="admin_mod_up.gif" value="Submit" alt="Submit"></p>
								</form>
								
								<?php
								}
								else{
								?>
																
								<form method="POST" action="<?php $_SERVER["PHP_SELF"]; ?>">
									<p>
									<input type="hidden" name="alter1" size="20" value="modify"><input type="image" img src="admin_mod.gif" value="Submit" alt="Submit"></p>
								</form>

								<?php
								}
								?>
								
								
								</td>								
								<?php if (($_POST[alter1] == "modify") || ($_POST[username] != ""))
{
?>
<tr>
<td>
	<font size="1" face="Tahoma">


<form name="myform" id="myform" action="<?php $_SERVER["PHP_SELF"]; ?>" method="POST">
			<table border="1" width="100%" id="table41" cellspacing="0" cellpadding="0" bordercolorlight="#C0C0C0" bordercolordark="#FFFFFF">
				<tr>
					<td width="140">Username:</td>
					<td><font size="1" face="Tahoma">
					<select size="1" name="username" onChange="document.myform.submit()">
<?php 
//require the config file
require ("../config.php");

echo "<option></option>";

//make the connection to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());


//build and issue the query
$sql ="SELECT * FROM $table_name";
$result = @$db->query($sql) or die(mysqli_error());
	while ($sql = mysqli_fetch_object($result)) 
	{
	    $uname = $sql -> mbrUName;
	    $sel = $_POST[username]==$uname?" selected":"";
	    echo "<option value=\"$uname\"$sel>$uname</option>";
	}
?>
					</select></font></td>
				</tr>
			</table>
		</form>

		</font>

				</td>			
</tr>

<?php
if ($_POST[username] != "")
{

	//require the config file
require ("../config.php");

//make the connection to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());


//build and issue the query
$sql ="SELECT * FROM $table_name WHERE mbrUName = '$_POST[username]'";
$result = @$db->query($sql) or die(mysqli_error());

while ($sql = mysqli_fetch_object($result)) 
{
	$m_first = $sql -> mbrFirstName;
	$m_last = $sql -> mbrLastName;	
	$g_1	 = $sql -> mbrGroup1;
	$g_2	 = $sql -> mbrGroup2;
	$g_3	 = $sql -> mbrGroup3;			
	$chng	 = $sql -> pchange;
	$m_email = $sql -> mbrEmail1;
	$direct = $sql -> redirect;

}
?>
<tr>
<td>
<form method="POST" action="mod_user.php">
	
		<font size="1" face="Tahoma">
			<table class="bodyText" border="1" width="100%" id="table1" cellspacing="0" cellpadding="0" bordercolorlight="#C0C0C0" bordercolordark="#FFFFFF">
				<tr>
					<td width="140">First Name:</td>
					<td><font size="1" face="Tahoma">
			<input type="hidden" name="username" value="<?php echo $_POST[username]; ?>">
			<input type="text" name="mod_first" value="<?php echo $m_first; ?>" size="20"></font></td>
				</tr>
				<tr>
					<td width="140">Last Name:</td>
					<td><font size="1" face="Tahoma">
			<input type="text" name="mod_last" value="<?php echo $m_last; ?>" size="20"></font></td>
				</tr>
				<tr>
					<td width="140">
			Password:</td>
					<td><font size="1" face="Tahoma">
			<input type="text" name="mod_pass" size="20" value="Same as Old"></font></td>
				</tr>
				<tr>
					<td width="140">
			E-Mail Address:</td>
					<td><font size="1" face="Tahoma">
			<input type="text" name="mod_email" value="<?php echo $m_email; ?>" size="20"></font></td>
				</tr>
				<tr>
					<td width="140">
			Group Memberships:</td>
					<td><font size="1" face="Tahoma">
			<select size="1" name="mod_group1">

<?php

echo "<option>$g_1</option>";
$i = 0;
while ($i < $num_groups)
{
	echo "<option value=\"$group_array[$i]\">$group_array[$i]</option>";
	$i++;
}
?>

			</select></font></td>
				</tr>
				<tr>
					<td width="140">
			&nbsp;</td>
					<td><font size="1" face="Tahoma">
			<select size="1" name="mod_group2">

<?php

echo "<option>$g_2</option>";
$i = 0;
while ($i < $num_groups)
{
	echo "<option value=\"$group_array[$i]\">$group_array[$i]</option>";
	$i++;
}
?>

			</select></font></td>
				</tr>
				<tr>
					<td width="140">
			&nbsp;</td>
					<td><font size="1" face="Tahoma">
			<select size="1" name="mod_group3">

<?php

echo "<option>$g_3</option>";
$i = 0;
while ($i < $num_groups)
{
	echo "<option value=\"$group_array[$i]\">$group_array[$i]</option>";
	$i++;
}
?>

			</select></font></td>
				</tr>
				<tr>
					<td width="140">
			Redirect to:</td>
					<td><font size="1" face="Tahoma">
			<input type="text" name="mod_redirect" value="<?php echo $direct; ?>" size="20"></font></td>
				</tr>
				<tr>
					<td width="140">
			Change Password Next Logon:</td>
					<td><font size="1" face="Tahoma">
			<select size="1" name="mod_chng">
			<option value="0" selected>No</option>
			<option value="1">Yes</option>
			</select></font></td>
				</tr>
				<tr>
					<td width="140">
			E-Mail User Account Information:</td>
					<td><font size="1" face="Tahoma">
			<select size="1" name="mod_send">
			<option value="No" selected>No</option>
			<option value="Yes">Yes</option>
			</select></font></td>
				</tr>
				<tr>
					<td width="140">
			<font size="1" face="Tahoma">
			<input type="submit" value="Submit" name="B5"></font></td>
					<td>&nbsp;</td>
				</tr>
			</table>

		</font>
		
		</form>
		
</td>
</tr>

<?php
}
?>


<?php }
?>
								
								</td>
							</tr>
					<tr>
						<td width="38%">

<?php
if ($_POST[alter] == "options")
{
?>


							<form method="POST" action="<?php $_SERVER["PHP_SELF"]; ?>">
							<p>
							<input type="hidden" name="alter" value="up">
							<input type="image" src="admin_options_up.gif" value="Submit" alt="Submit"></p>
						</form>

<?php
}
else
{
?>
						
						
							<form method="POST" action="<?php $_SERVER["PHP_SELF"]; ?>">
							<p>
							<input type="hidden" name="alter" value="options">
							<input type="image" src="admin_options.gif" value="Submit" alt="Submit"></p>
						</form>
<?php
}
?>



						</td>
							</tr>
						<tr>
		<td width="33%" valign="top" height="0">
<?php
if ($_POST[alter] == "options")
{
?>
		<form method="POST" action="mod_user.php">
		<table border="1" width="100%" id="table43" cellspacing="0" cellpadding="0" bordercolorlight="#C0C0C0" bordercolordark="#FFFFFF">
			<tr>
				<td width="140">Delete User</td>
				<td>
				<font size="1" face="Tahoma">
				<select size="1" name="del_user">
				<option></option>
<?php
//require the config file
require ("../config.php");

//make the connection to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());


//build and issue the query
$sql ="SELECT username FROM $table_name ORDER BY mbrUName";
$result = @$db->query($sql) or die(mysqli_error());

while ($sql = mysqli_fetch_object($result)) 
{
	$uname = $sql -> mbrUName;
	if ($uname != $_SESSION[user_name])
	{
	echo "<option value=\"$uname\">$uname</option>";
	}
}

?>
				</select></font></td>
			</tr>
			<tr>
				<td width="140">Restore User</td>
				<td>
				<font size="1" face="Tahoma">
				<select size="1" name="restore">
				<option></option>
<?php
//require the config file
require ("../config.php");

//make the connection to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());


//build and issue the query
$sql ="SELECT username FROM trash ORDER BY mbrUName";
$result = @$db->query($sql) or die(mysqli_error());

while ($sql = mysqli_fetch_object($result)) 
{
	$uname = $sql -> mbrUName;
	if ($uname != $_SESSION[user_name])
	{
	echo "<option value=\"$uname\">$uname</option>";
	}
}

?>
				</select></font></td>
			</tr>

			<tr>
				<td width="140">Ban User</td>
				<td>
				<font size="1" face="Tahoma">
				<select size="1" name="ban_user">
				<option></option>

<?php

//require the config file
require ("../config.php");

//make the connection to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());


//build and issue the query
$sql ="SELECT username FROM $table_name ORDER BY mbrUName";
$result = @$db->query($sql) or die(mysqli_error());

while ($sql = mysqli_fetch_object($result)) 
{
	$uname = $sql -> mbrUName;
	
	if ($uname != $_SESSION[user_name])
	{
	echo "<option value=\"$uname\">$uname</option>";
	}
}
?>
				</select></font></td>
			</tr>
			<tr>
				<td width="140">Block IP Address</td>
				<td>
				<font size="1" face="Tahoma">
				<input type="text" name="oct1" size="3" maxlength="3"></font>.<font size="1" face="Tahoma"><input type="text" name="oct2" size="3" maxlength="3"></font><font face="Tahoma">.</font><font size="1" face="Tahoma"><input type="text" name="oct3" size="3" maxlength="3"><font face="Tahoma">.</font><input type="text" name="oct4" size="3" maxlength="3"></font></td>
			</tr>
			<tr>
				<td width="140">Lift User Ban</td>
				<td>
				<font size="1" face="Tahoma">
				<select size="1" name="lift_user_ban">
				<option></option>
<?php
//require the config file
require ("../config.php");

//make the connection to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());


//build and issue the query
$sql ="SELECT * FROM banned WHERE type = 'user'";
$result = @$db->query($sql) or die(mysqli_error());

while ($sql = mysqli_fetch_object($result)) 
{
	$banned = $sql -> no_access;
	
	echo "<option value=\"$banned\">$banned</option>";
}
?>

				</select></font></td>
			</tr>
			<tr>
				<td width="140">Lift IP Ban</td>
				<td>
				<font size="1" face="Tahoma">
				<select size="1" name="lift_ip_ban">
				<option></option>
<?php
//require the config file
require ("../config.php");

//make the connection to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());


//build and issue the query
$sql ="SELECT * FROM banned WHERE type = 'ip'";
$result = @$db->query($sql) or die(mysqli_error());

while ($sql = mysqli_fetch_object($result)) 
{
	$banned = $sql -> no_access;
	echo "<option value=\"$banned\">$banned</option>";
}
?>				
				</select></font></td>
			</tr>
			<tr>
				<td width="140">Empty Trash</td>
				<td><font size="1" face="Tahoma">
			<select size="1" name="empt_trash">
			<option></option>
			<option value="yes">Yes</option>
			</select></font></td>
			</tr>
			<tr>
				<td width="140">Purge Accounts Inactive for</td>
				<td><select size="1" name="amt_time">
				<option></option>
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
				<option value="5">5</option>
				<option value="6">6</option>
				<option value="7">7</option>
				<option value="8">8</option>
				<option value="9">9</option>
				<option value="10">10</option>
				<option value="11">11</option>
				<option value="12">12</option>
				</select><font size="1" face="Tahoma"><select size="1" name="incr_time">
				<option></option>
				<option value="DAY">Days</option>
				<option value="MONTH">Months</option>
				<option value="YEAR">Years</option>
				</select></font></td>
			</tr>
			<tr>
				<td width="140"><font size="1" face="Tahoma">
				<input type="submit" value="Submit" name="B6"></font></td>
				<td>&nbsp;</td>
			</tr>
		</table>
		</form>
<?php
}
?>
		</td>

					</tr>
					<tr>
						<td width="38%">
						&nbsp;</td>
							</tr>
					<tr>
						<td width="38%">
						&nbsp;</td>
							</tr>
					<tr>
						<td width="38%">
						&nbsp;</td>
							</tr>
					<tr>
						<td width="38%">
						&nbsp;</td>
							</tr>
					<tr>
						<td width="38%">
						&nbsp;</td>
							</tr>
					<tr>
						<td width="38%">
						&nbsp;</td>
							</tr>
					<tr>
						<td width="38%">
						&nbsp;</td>
							</tr>
					<tr>
						<td width="38%">
						&nbsp;</td>
							</tr>
					</table>
				
				
				</td>



			</tr>
		</table>
		</td>
						<td valign="top">
	<?php if ($user_det == "")
{

if (!isset($_GET['max_results']))
{
	$max_results = 5;
}else{
	$max_results = $_GET['max_results'];
}	

if(!isset($_GET['page'])){ 
    $page = 1; 
} else { 
    $page = $_GET['page']; 
} 

if (isset($_GET['search']))
{
	$s_string = $_GET['search'];
	$explodeit = explode(" ", "$s_string");
	$c = count($explodeit);
	if ($c > 1)
	{
		for ($i=0; $i<$c; $i++)
		{
			$search = $explodeit[$i]."+";
		}
	}else{
		$search = $s_string;
	}
}




echo "Show Number of Results Per Page: <a href=\"".$_SERVER['PHP_SELF']."?page=$page&max_results=5&search=$search\">5</a>";
echo " <a href=\"".$_SERVER['PHP_SELF']."?page=$page&max_results=10&search=$search\">10</a><br>";  

// Figure out the limit for the query based 
// on the current page number. 
$from = (($page * $max_results) - $max_results); 

// Perform MySQL query on only the current page number's results 
if (!isset($_GET['search']))
{
	$set_lim = "SELECT * FROM $table_name ORDER BY mbrUName LIMIT $from, $max_results"; 
}else{
	$search = $_GET['search'];
	$set_lim = "SELECT * FROM $table_name WHERE mbrUName LIKE '%$search%' || mbrFirstName LIKE '%$search%' 
	|| mbrLastName LIKE '%$search%' || mbrGroup1 LIKE '%$search%' || mbrGroup2 LIKE '%$search%' || mbrGroup3 LIKE '%$search%' 
	|| mbrEmail1 LIKE '%$search%' ORDER BY mbrUName LIMIT $from, $max_results"; 	
}	
$set_res = @$db->query($set_lim) or die(mysqli_error());

while ($set_lim = mysqli_fetch_object($set_res)) 
{ 

$fname 		= $set_lim -> mbrFirstName;
$lname 		= $set_lim -> mbrLastName;
$uname 		= $set_lim -> mbrUName;
$p_change	= $set_lim -> pchange;
$verif_d	= $set_lim -> verified;
$last 		= $set_lim -> last_login;
$re_direct 	= $set_lim -> redirect;
$groupA		= $set_lim -> mbrGroup1;
$groupB		= $set_lim -> mbrGroup2;
$groupC		= $set_lim -> mbrGroup3;
$e_mail		= $set_lim -> mbrEmail1;


if ($p_change == 1)
{$p_change = "Yes";}else{$p_change = "No";}

if ($verif_d == "0")
{$verif_d= "No";}else{$verif_d= "Yes";}


echo "<table border=\"1\" width=\"100%\" id=\"table5\">";
echo "<tr>";
echo "<td width=\"100\"><b><a href=\"".$_SERVER['PHP_SELF']."?user_det=$uname&ret_page=$page&ret_max=$max_results&search=$search\">$uname</a></b></td>";
echo "<td width=\"80\">E-Mail:</font></td>";
echo "<td><a href=\"mailto:$e_mail\">$e_mail</a></td>";

echo "</tr>";

echo "<tr>";
echo "<td width=\"100\">&nbsp;</td>";
echo "<td width=\"100\">Name:</td>";
echo "<td>$fname $lname</td>";
echo "</tr>";

echo "<tr>";
echo "<td width=\"100\">&nbsp;</td>";
echo "<td width=\"100\">Last Login:</td>";
echo "<td>$last</td>";
echo "</tr>";

echo "</table>";


}
 

// Figure out the total number of results in DB: 
if (!isset($_GET['search']))
{
				$qcnt = $db->query("SELECT COUNT(*) as Num FROM $table_name"); 
}else{
				$qcnt = $db->query("SELECT COUNT(*) as Num FROM $table_name 
	WHERE mbrUName LIKE '%$search%' || mbrFirstName LIKE '%$search%' 
	|| mbrLastName LIKE '%$search%' || mbrGroup1 LIKE '%$search%' || mbrGroup2 LIKE '%$search%' || mbrGroup3 LIKE '%$search%' 
	|| mbrEmail1 LIKE '%$search%'"); 
}	
	$total_results = $qcnt->fetch_row();

// Figure out the total number of pages. Always round up using ceil() 
$total_pages = ceil($total_results[0] / $max_results); 
$qcnt->close();

// Build Page Number Hyperlinks 
echo "<center>Select a Page<br />"; 

// Build Previous Link 
if($page > 1){ 
    $prev = ($page - 1); 
    echo "<a href=\"".$_SERVER['PHP_SELF']."?page=$prev&max_results=$max_results&search=$search\">&lt;&lt;&nbsp;&nbsp; </a> "; 
} 

for($i = 1; $i <= $total_pages; $i++){ 
    if(($page) == $i){ 
        echo "$i "; 
        } else { 
            echo "<a href=\"".$_SERVER['PHP_SELF']."?page=$i&max_results=$max_results&search=$search\">$i</a> "; 
    } 
} 

// Build Next Link 
if($page < $total_pages){ 
    $next = ($page + 1); 
    echo "<a href=\"".$_SERVER['PHP_SELF']."?page=$next&max_results=$max_results&search=$search\">&nbsp;&nbsp; &gt;&gt;</a>"; 
} 
echo "</center>"; 
}else{
echo "<b>Details for $user_det</b><br>";

$dsql = "SELECT * FROM $table_name WHERE mbrUName = '$user_det'"; 
$dresults = @$db->query($dsql) or die(mysqli_error());
while ($dsql = mysqli_fetch_object($dresults)) 
{
$fname 		= $dsql -> mbrFirstName;
$lname 		= $dsql -> mbrLastName;
$uname 		= $dsql -> mbrUName;
$p_change	= $dsql -> pchange;
$verif_d	= $dsql -> verified;
$last 		= $dsql -> last_login;
$re_direct 	= $dsql -> redirect;
$groupA		= $dsql -> mbrGroup1;
$groupB		= $dsql -> mbrGroup2;
$groupC		= $dsql -> mbrGroup3;
$e_mail		= $dsql -> mbrEmail1;

if ($p_change == "0")
{
$p_change = "No";
}else{
$p_change = "Yes";
}

if ($verif_d == "0")
{
$verif_d = "No";
}else{
$verif_d = "Yes";
}
?>
<table border="1" width="100%" id="table1" bordercolorlight="#FFFFFF" bordercolordark="#C0C0C0">
	<tr>
		<td width="100" rowspan="10" valign="top"><?php echo $uname; ?></td>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td width="177">E-Mail:</td>
		<td><a href="<?php echo $e_mail; ?>"><?php echo $e_mail; ?></a></td>
	</tr>
	<tr>
		<td width="177">Real Name:</td>
		<td><?php echo "$fname $lname &nbsp;"; ?></td>
	</tr>
	<tr>
		<td width="177">Last Login:</td>
		<td><?php echo $last; ?>&nbsp;</td>
	</tr>
	<tr>
		<td width="177">Password Change Required:</td>
		<td><?php echo $p_change; ?>&nbsp;</td>
	</tr>
	<tr>
		<td width="177">Verified Through E-Mail:</td>
		<td><?php echo $verif_d; ?>&nbsp;</td>
	</tr>
	<tr>
		<td width="177">Group Memberships:</td>
		<td><?php echo $groupA; ?>&nbsp;</td>
	</tr>
	<tr>
		<td width="177">&nbsp;</td>
		<td><?php echo $groupB; ?>&nbsp;</td>
	</tr>
	<tr>
		<td width="177">&nbsp;</td>
		<td><?php echo $groupC; ?>&nbsp;</td>
	</tr>
	<tr>
		<td width="177">Redirect to:</td>
		<td><?php echo $re_direct; ?></td>
	</tr>
</table>

<center><a href="<?php echo $_SERVER['PHP_SELF']."?page=$ret_page&max_results=$ret_max&search=$search"; ?>">Back to List</a></center>

<?php }}
if ($search != "")
{
	if ($ret_max)
	{ $page=$ret_page; $max_results=$ret_max; }
	echo "<center><a href=\"".$_SERVER['PHP_SELF']."?page=$page&max_results=$max_results\">Exit Search</a>";
}else{
echo "<br><br>";
}
?>						
<center>
<form method="GET" action="<?php echo $_SERVER['PHP_SELF']."?page=$page&max_results=$max_results&search="; ?>"><p>Search 
<input type="text" name="search" size="20"><input type="submit" value="Submit" name="B1"></p>
</form>
</center>
</td>
</tr>
</table>
