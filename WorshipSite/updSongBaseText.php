<?php
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
if (allow_access(Users) != "yes") { 
	exit;
}

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


/* Retrieve Song for specified id */
$sql = "SELECT * FROM songs";
$resSong = $db->query($sql);
while($dbSong=mysqli_fetch_array($resSong)) {
	$baseText = stripSong($dbSong["songText"]);

	$q = "UPDATE songs SET baseText='".$db->real_escape_string($baseText) . "' WHERE songID=".$dbSong["songID"];
	$resUpdSong = $db->query($q);
}

function stripSong($inText) {
	$oldTxt = nl2br(str_replace("\r\n","<br />",$inText));
	
	/* Prepare song text */
	$aSText = explode("<br />",nl2br(str_replace("\r\n","<br />",$oldTxt)));
	$aNewSText = array();
	for($i=0;$i<count($aSText);$i++) {
		if(substr($aSText[$i],0,1)!="~" && trim($aSText[$i])!="") {
			$tmpText = $aSText[$i];
			echo $tmpText."XXX<br />";
			$songTemp = "";
			$j = 0;
			while($j<strlen($tmpText)) {
				if($tmpText[$j]==" ") {
					$songTemp .= " ";
					$j++;
				} elseif($tmpText[$j]=="[") {
					$j++;
					while($tmpText[$j]!="]" && $j<strlen($tmpText)) {
						while($tmpText[$j]!="-" && $tmpText[$j]!="]" && $j<strlen($tmpText)) {
							$j++;
						}
						if($tmpText[$j]=="/" || $tmpText[$j]=="-") {
							$j++;
						}
					}
					if($tmpText[$j]=="]") {
						$j++;
					}
				} elseif($tmpText[$j]=="{") {
					$j++;
					while($tmpText[$j]!="}" && $j<strlen($tmpText)) {
						$j++;
					}
					if($tmpText[$j]=="}") {
						$j++;
					}
				} else {
					$songTemp .= $tmpText[$j];
					$j++;
				}
			}
			$aNewSText[] = $songTemp!=""?trim($songTemp)." ":"";
		}
	}
	
	return addslashes(trim(implode("",str_replace("<br />","",$aNewSText))));
}
?>