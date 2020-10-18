<?php
include ("config.php");

//destroys the session, the variables are not longer set
session_start();
session_destroy();

?>
<html>

<head>
<meta http-equiv="refresh" content="0;url=https://<?php echo $domain.$loginPage; ?>">
<title>Account Not Activated</title>
</head>

<body>

<p>Your account must be activated before you can log in, please visit the 
activation page that was included in the email we sent you.</p>

</body>

</html>
