<?php

// PHP error logging level
// Default should be E_ALL & ~E_NOTICE
error_reporting(E_ALL & ~E_NOTICE);
//set up the names of the database and table
$db_name ="southcal_worship";
$table_name ="members";

// need SSL (force SSL)  Assuming 'Lets encrypt' is turned on
define ("NEEDSSL", true);
if (NEEDSSL) {
	define ("HTTPPREFIX", "https");
	} 
 else {
	define ("HTTPPREFIX", "http");	
}

//connect to the server and select the database
define("DB_SERVER", "localhost");
define("DB_USER", "southcal_worship");
define("DB_PASS", "mysqlpasswordgoeshere");
define("DB_NAME", "southcal_worship");

// field names for session and table references
define("user_name", "user_name");
define("email", "email");

// this is a str_rot13 version of the smtp password
// TODO improve security
define ("smtpword", "complexpasswordforsmtpgoeshere");

// Set this to disable the site
define("MAINTENANCE_MODE",false);

// Send e-mails to the Corridator (or set to false for testing)
// Normal mode is  true 
define("EMAILCOORD_ON", true);

// Disable e-mail for testing
// default is TRUE
define("ENABLEMAIL", true);
//
// Normal is 0 (off)
// 1 = Insert or Update, 2 other Insert/Update/Delete, 3 - Select, 4 debug, 5 verbose
define ('debugloglevel', 3);

//domain information
$domain = "worship.southcalgary.org";


$baseDir = "/home/southcal/public_html/worship/";
$baseFolder = "/";
$loginPage = $baseFolder."lr/login.php";

// Website name
$siteTitle = "TeamWorship";

//Change to 0 to turn off the login log
define ("LOG_LOGON", false);

//base_dir is the location of the files, ie http://www.yourdomain/login
$base_dir = HTTPPREFIX . "://".$_SERVER["HTTP_HOST"].$baseFolder."lr";

//length of time the cookie is good for - 7 is the days and 24 is the hours
//if you would like the time to be short, say 1 hour, change to 60*60*1
$duration = time()+(60*60*24*30);

//the site administrator\'s email address
$adminemail = "worshipcorridnator@southcalgary.org";
$adminname = "Leader";

//sets the time to MST
$zone=3600*-7;

//do you want the verify the new user through email if the user registers themselves?
//yes = "P" :  no = "A"
$verify = "P";

//default redirect, this is the URL that all self-registered users will be redirected to
// $default_url = HTTPPREFIX . "://".$_SERVER["HTTP_HOST"].$baseFolder."index.php";
$default_url = "/index.php";

//minimum and maximum password lengths
$min_pass = 5;
$max_pass = 30;

// turn off BB3 references
// It has been disabled and not tested since 2015
$use_phpBB3 = 0;

$num_groups = 0+2;
$group_array = array("Users","Administrators");

?>