<?php /*******************************************************************
 * index.php
 * Display Member home page
 *******************************************************************/
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

require('../lr/config.php');
require('lr/functions.php'); 
//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Users) != "yes") { 
	header("Location: /lr/login.php"); 
	exit;
}

include($baseDir."/classes/class.breadcrumb.php");
$trail = new Breadcrumb();
$trail->add('Help', $_SERVER['REQUEST_URI'], 4);
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<title><?php echo $siteTitle; ?> - Help</title>
<?php
echo "<link rel=\"stylesheet\" href=\"{$baseFolder}css/tw.css\" type=\"text/css\">";
?>
</head>
<body>

<?php
		$curUser = isset($_SESSION['first_name'])?$_SESSION['first_name']:"". " " 
				. isset($_SESSION['last_name'])?$_SESSION['last_name']:"";
$nav = "<br /><span style='font-size:10pt;'>Member: $curUser</span>&nbsp;<a target='_top' href='{$baseFolder}lr/logout.php'>Logout</a>&nbsp;";
echo "<table class='topbar'><tr><td align='right'>Home Page for $curUser&nbsp;<br />$nav</td></tr></table>\n";
$trail->output();
?>
</body>
</html>
