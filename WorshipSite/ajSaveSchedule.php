<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_cache_limiter();
session_start();

require('lr/config.php');
require('lr/functions.php'); 
if (allow_access(Administrators) != "yes") { 
	exit;
}

$schStart = $_REQUEST['sd'];
$schDOW = $_REQUEST['dw'];

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// Retrieve Service Defaults
$q = "SELECT svcContact,svcDescription,svcTime,svcPracticeOffset,svcPracticeTime FROM siteconfig LIMIT 1";
$resSched = $db->query($q);
$dbDefaults=mysqli_fetch_array($resSched);

// Find All valid services
$q = "SELECT svcDate,roleID,memberID,DATE_FORMAT(svcDate,'%W') as dow FROM teamschedule LEFT JOIN services ON svcDate=date(svcDateTime) WHERE svcDate >= '$schStart' AND DATE_FORMAT(svcDate,'%W') = '$schDOW' AND isnull(svcDateTime) ORDER BY svcDate";
$resSched = $db->query($q);
$oldSvcDate = "";
While($dbSched=mysqli_fetch_array($resSched)) {
	if($dbSched["svcDate"]!=$oldSvcDate) {
			// TODO fixup NULL datetime
		// Create Service
		$svcPractice = $dbDefaults["svcPracticeTime"]=="00:00:00"?"0000-00-00 00:00:00":strftime("%G-%m-%d ".$dbDefaults["svcPracticeTime"],strtotime($dbSched["svcDate"]." ".$dbDefaults["svcPracticeOffset"]." days"));
		$q = "INSERT INTO services VALUES(0,'".$dbSched["svcDate"]." ".$dbDefaults["svcTime"]."','$svcPractice','".$dbDefaults["svcDescription"]."','','',".$dbDefaults["svcContact"].",0)";
		$resSvc = $db->query($q);
		logit(2, __FILE__ . ":" . __LINE__ . " Q: ". $q . " ID:" . $db->insert_id ." E:". $db->error);
		
		$serviceID = $db->insert_id;
		$oldSvcDate = $dbSched["svcDate"];
		// Create Default Schedule
		$qs = "SELECT * FROM scheduledefaults";
		$resDSch = $db->query($qs);
		While($dbDSch=mysqli_fetch_array($resDSch)) {
			$defDateTime = add_date($dbSched["svcDate"]." ".substr($dbDSch["schTime"],11,8),$dbDSch["schDateOffset"]);
			$qX = "INSERT INTO serviceschedule VALUES($serviceID,0,'P','$defDateTime',".$dbDSch["schDuration"].",'".$dbDSch["schCategories"]."','".$dbDSch["schDescription"]."',0)";
			$resDSvc = $db->query($qX);
			logit(2, __FILE__ . ":" . __LINE__ . " Q: ". $qX . " E:". $db->error);
		
		}
	}
	$q = "INSERT INTO serviceteam VALUES($serviceID,".$dbSched["memberID"].",".$dbSched["roleID"].",'','')";
	$resSvc = $db->query($q);
	logit(2, __FILE__ . ":" . __LINE__ . " Q: ". $q . " ID:"  ." E:". $db->error);
		
}
echo "<span style='background-color:green;color:#ffffff;padding:3px;border:1px solid #000000'>The services have been created for the current service schedule.</span>";

function add_date($givendate,$day=0) {
	$cd = strtotime($givendate);
	$newdate = date('Y-m-d H:i:s', mktime(date('H',$cd),
	date('i',$cd), date('s',$cd), date('m',$cd),
	date('d',$cd)+$day, date('Y',$cd)));
	return $newdate;
}

?>