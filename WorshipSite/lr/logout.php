<?php
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();

include ("config.php");

//destroys the session, the variables are not longer set
session_start();
	setcookie("lr_user", $username, $duration, "/", $domain);
	setcookie("lr_pass", "", 1, "/", $domain);

session_destroy();

if ($use_phpBB3) {
	// Logout from phpBB forum
	include("../phpBB3/includes/extBridge.php");
	$phpbb = new PhpBB3Component;
	$phpbb->startup();
	$phpbb->logout();
}
?>
<html>
<meta http-equiv="refresh" content="0;url=https://<?php echo $domain.$loginPage; ?>">
</html>


