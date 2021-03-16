<?php

define ('Administrators', 'Administrators');
define ('Users', 'Users');
define ('Coordinator', 'Coordinator');


//function to get the date
function last_login() {
	$date = gmdate("Y-m-d");
	return $date;
}

//function that sets the session variable
function sess_vars($base_dir, $server, $dbusername, $dbpassword, $db_name, $table_name, $user, $pass) {
	//make connection to dbase
	$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());
	// echo mysqli_connect_error() . "\n";
	$thisuser = mysqli_real_escape_string($db, $user);
	$thispass = mysqli_real_escape_string($db, $pass);
	$sql = "SELECT * FROM $table_name WHERE mbrUName = '$thisuser' and mbrPassword = md5('$thispass')";
	// echo $sql . "\n";
	// echo md5($pass) . "\n";
	$result = $db -> query($sql) or die(mysqli_error());
	$log ="----";
	//get the number of rows in the result set
	$num = mysqli_num_rows($result);
	// echo 'NUM =' . $num . '\n';
	//set session variables if there is a match
	if ($num != 0) {
		while ($sql = mysqli_fetch_object($result)) {
			if ($sql->mbrUName != $user) {
				$x = $sql -> mbrUName;
				$log  = date("Y.m.d H:i")." HackUser: `". $user. "` Pass: `". $pass. "` IP:" . $_SERVER['REMOTE_ADDR']. PHP_EOL;

				//Write action to txt log
				file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/logs/logon_'.date("Y.m").'.txt', $log, FILE_APPEND);

				die("Something is wrong with your user name '$user' - stop the hack from " . $_SERVER['REMOTE_ADDR'] );
			}
			$_SESSION["user_id"]	 	= $sql -> memberID;
			$_SESSION["roles"]		= $sql -> roleArray;
			$_SESSION["groups"]		= $sql -> groupArray;
			$_SESSION["first_name"] 	= $sql -> mbrFirstName;
			$_SESSION["last_name"] 	= $sql -> mbrLastName;
			$_SESSION["user_name"] 	= $sql -> mbrUName;
			// $_SESSION["password"] 		= $pass;
			$_SESSION["group1"]	 	= $sql -> mbrGroup1;
			$_SESSION["group2"]	 	= $sql -> mbrGroup2;
			$_SESSION["group3"] 		= $sql -> mbrGroup3;
			$_SESSION["pchange"]		= $sql -> pchange;  
			$_SESSION["email"] 		= $sql -> mbrEmail1;
			$_SESSION["email2"] 		= $sql -> mbrEmail2;
			$_SESSION["redirect"]		= $sql -> redirect;
			$_SESSION["mbrStatus"]	= $sql -> mbrStatus;
			$_SESSION["last_login"]	= $sql -> last_login;
		}
		$log  = date("Y.m.d H:i")." User : `". $user. "` \tIP: " .$_SERVER['REMOTE_ADDR'] . PHP_EOL;

		
		mysqli_free_result($result);
		

	} else {
		$_SESSION["redirect"] = "$base_dir/login.php?msg=0";
		$log  = date("Y.m.d H:i")." BadID: `". $user. "` \tIP: " .$_SERVER['REMOTE_ADDR'] . PHP_EOL;
	}
	mysqli_close($db);
	//Write action to txt log
	file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/logs/logon_'.date("Y.m").'.txt', $log, FILE_APPEND);

}

//functions that will determine if access is allowed
function allow_access($group) {
	$allowed = "no";
	if (isset($_SESSION["group1"]) || isset($_SESSION["user_name"])) {
		if ($_SESSION["group1"] == "$group" || $_SESSION["group2"] == "$group" || $_SESSION["group3"] == "$group" ||
			//	$_SESSION["group1"] == "Administrators" || $_SESSION["group2"] == "Administrators" || 
			// 	$_SESSION["group3"] == "Administrators" ||
			$_SESSION["user_name"] == "$group") {
		$allowed = "yes";
	} else {
		$allowed = "no";
	}
	}
	return $allowed;
}

//function to check the length of the requested password
function password_check($min_pass, $max_pass, $pass) {
	$valid = "yes";
	if ($min_pass > strlen($pass) || $max_pass < strlen($pass)) {
		$valid = "no";
	}

	return $valid;
}

function logit($level, $say) {
	
	if ($level <= debugloglevel ) { file_put_contents('./logs/debug_'.date("Y.m").'.txt', $say . "\n", FILE_APPEND);}
}
?>
