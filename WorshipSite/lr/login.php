<?php
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();

$secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 1 : 0;
if (!$secure  && NEEDSSL) { 
	$server_name = (!empty($_SERVER['HTTP_HOST'])) ? strtolower($_SERVER['HTTP_HOST']) : ((!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME'));
	$line = "";
	//$line = isset($_SERVER["QUERY_STRING"])? (!empty($_SERVER["QUERY_STRING"])? "?".$_SERVER["QUERY_STRING"] :""):"";
	echo "<html><body onLoad='document.frmLogin.submit();'><form action='https://" . $server_name . "/lr/login.php".$line . "' name='frmLogin' method='post'>\n";
	$line = isset($_REQUEST["msg"]) ? "<input type='hidden' name='msg' value='".$_REQUEST["msg"]."' />":"";
	echo $line . "\n";
	echo "</form></body></html>\n";
	exit;
}
session_start();

$aMsg = array("Username/Password combination incorrect. Please try again...",
		"Your account must be activated by an administrator befor you can login.",
		"Your profile has no access to the requested page.",
		"YOU'VE BEEN BANNED!",
		"Your account has be disabled. Contact the site administrator");
$msg = isset($_REQUEST["msg"])?$aMsg[$_REQUEST["msg"]]:"";
?>
<HTML>
<head>
<title>Login</title>
<link rel="stylesheet" href="/css/tw.css" type="text/css">
<link rel="stylesheet" href="/css/style.css" type="text/css">
</head>
<script>
function valEntry(frm) {
	if(frm.username.value=="") {
		alert("Please enter your user name");
		frm.username.focus();
		return false;
	}
	if(frm.password.value=="") {
		alert("Please enter your password");
		frm.password.focus();
		return false;
	}
	return true;
}
</script>
</head>
<body onLoad='document.frmLogin.username.focus();'>
<table class='topbar'><tr><td>&nbsp;</td><td align='right'>TeamWorship&nbsp;<br /><br />Member Login&nbsp;</td></tr></table>
<section class="loginform cf">
			<div style="margin-top:0px; font-size: 18pt">Login to Team Worship</div>
			<br />
			<div style="color:#cc0000;font-size: 10pt ;background-color:#ffff7f"><?php echo $msg; ?></div>
			<p />

<form name='frmLogin' method="POST" action="/lr/redirect.php" onSubmit="return valEntry(this);">
<input type='hidden' name='ref' value='<?php echo $_POST["ref"]; ?>' />
<table class="bodyText">
	<tr><td><strong>Username:&nbsp;</strong></td><td><input type="text" name="username" size="30" maxlength="50" placeholder="yourname" required></td></tr>
	<tr><td><strong>Password:&nbsp;</strong></td><td><input type="password" name="password" size="30" maxlength="50" placeholder="password" required></td></tr>
	<tr><td colspan="2"><input type="hidden" name="remember" value="Yes"></td></tr>
	
</table>
<div  align="right"><input type="submit" name="submit" value="Login"></div>
</form>
			<p>
				<a href="emailpass.php" title="Click here if would like your username and password to be e-mailed to the address we have on file.">Forgot your Password?</a></p>
</section>

</body>
</html>