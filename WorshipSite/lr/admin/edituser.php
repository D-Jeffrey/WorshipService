<?php

//
// NO LONGER USED
//
/*******************************************************************
 * calendar.php
 * Main script for calendar EDIT function - used to edit events
 * of calendar.
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
if (allow_access(Users) != "yes")
{ 
	include ('../login.php?msg=1'); 
	exit;
}

include($_SERVER["DOCUMENT_ROOT"]."/classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('User Admin', $_SERVER['REQUEST_URI'], 4);

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>TeamWorship - Edit Account</title>
<?php
echo "<link rel=\"stylesheet\" href=\"/css/tw.css\" type=\"text/css\">";
echo "</head>\n";
echo "<body>\n";
$curUser = $_SESSION[first_name]." ".$_SESSION[last_name];
echo "<table class='topbar'><tr><td>&nbsp;Edit User Account</td><td align='right'>TeamWorship&nbsp;<br /><br /><span style='font-size:10pt;'>Member: $curUser</span>&nbsp;&nbsp;&nbsp;<a href='/lr/logout.php'>Logout</a>&nbsp;</td></tr></table>\n";
$trail->output();

//build and issue the query
$sql ="SELECT * FROM $table_name WHERE mbrUName = '$_SESSION[user_name]'";
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
<table><tr>
<td>
<form method="POST" action="mod_user.php">
	<input type="hidden" name="username" value="<?php echo $_SESSION[user_name]; ?>">
	<input type="hidden" name="mod_group1" value="<?php echo $g_1; ?>">
	<input type="hidden" name="mod_group2" value="<?php echo $g_2; ?>">
	<input type="hidden" name="mod_group3" value="<?php echo $g_3; ?>">
	<input type="hidden" name="mod_redirect" value="<?php echo $direct; ?>">
	
	<table class="bodyText" border="1" width="100%" cellspacing="0" cellpadding="0" bordercolorlight="#C0C0C0" bordercolordark="#FFFFFF">
		<tr>
			<td width="140">First Name:</td>
			<td>
				<input type="text" name="mod_first" value="<?php echo $m_first; ?>" size="20">
			</td>
		</tr>
		<tr>
			<td width="140">Last Name:</td>
			<td><input type="text" name="mod_last" value="<?php echo $m_last; ?>" size="20"></td>
		</tr>
		<tr>
			<td width="140">Password:</td>
			<td><input type="text" name="mod_pass" size="20" value="Same as Old"></td>
		</tr>
		<tr>
			<td width="140">E-Mail Address:</td>
			<td><input type="text" name="mod_email" value="<?php echo $m_email; ?>" size="20"></td>
		</tr>
		<tr>
			<td width="140">
				Change Password Next Logon:</td>
						<td>
				<select size="1" name="mod_chng">
				<option value="0" selected>No</option>
				<option value="1">Yes</option>
			</select></td>
		</tr>
		<tr>
			<td width="140">
				E-Mail User Account Information:</td>
						<td>
				<select size="1" name="mod_send">
				<option value="No" selected>No</option>
				<option value="Yes">Yes</option>
			</select></td>
		</tr>
		<tr>
			<td align="center" colspan="2">
				<input type="submit" value="Submit" name="B5">&nbsp;<input type="button" value="Cancel" name="cancel" onClick="history.go(-1);">
			</td>
		</tr>
	</table>

</form>

