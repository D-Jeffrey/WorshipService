<?php
//prevents caching
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: text/plain");
header("Content-Type: application/force-download");
header("Content-Disposition: attachment;filename=\"songDB".date("Ymd").".txt\"");
header("Content-Transfer-Encoding: binary ");

session_start();

require('lr/config.php');
require('lr/functions.php'); 
//this is group name or username of the group or person that you wish to allow access to
// - please be advise that the Administrators Groups has access to all pages.
if (allow_access(Users) != "yes") { 
	exit;
}

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

$songNum = 0;
$outDB = "";

/* Retrieve Songs */
$sql = "SELECT *,songLink as sLink FROM songs ORDER BY songName";
$resSong = $db->query($sql);
while($dbSong=mysqli_fetch_array($resSong)) {
    $oldTxt = clean_song($dbSong["songText"]);
    $songNum++;
    $outSong = "Title: ".$dbSong["songName"]."\r";
    $outSong .= "Author: ".$dbSong["songArtist"]."\r";
    $outSong .= "Copyright: ".$dbSong["songName"];
    $outSong .= rtrim($dbSong["songArtist"])!=""?" (".$dbSong["songArtist"].")":"";
    $outSong .= rtrim($dbSong["songCopyright"])!=""?"  ©".$dbSong["songCopyright"]."\r":"\r";
    $outSong .= "Song ID: W-".sprintf("%05u",$dbSong["songID"])."\r";
    /* Prepare song text */
    $aSText = explode("<br />",nl2br(str_replace("\r\n","<br />",$oldTxt)));
    $vn = 1;
    $vValid = false;
    for($i=0;$i<count($aSText);$i++) {
   		$tmpText = rtrim(substr($aSText[$i],0,200));
    	if(substr($tmpText,0,1)=="~") { /* Song Part */
       		$outSong .= rtrim(str_replace(":","",substr($tmpText,1))).":\r";
            if($vn > 1 && $vValid) {
        		$vValid = false;
            }
       		$vn++;
    	} else if($tmpText!="") { /* Skip Blank lines */
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
    			} else {
    				$songTemp .= $tmpText[$j];
    				$j++;
    			}
    		}
    		if(rtrim($songTemp)!="") {
        		$outSong .= $songTemp."\r";
        		$vValid = true;
        	}
    	}
    }
    $outDB .= $outSong;
}
header("Content-Length: ".strlen($outDB));
echo $outDB;
exit;

function clean_song($string) {
    $search = array(chr(145),
                    chr(146),
                    "â€™",
                    chr(147),
                    chr(148),
                    chr(151),
                    "\t",
                    "\r\n");
    $replace = array("'",
                    "'",
                    "'",
                    '"',
                    '"',
                    '-',
                    "",
                    "<br />");
    $out = nl2br(str_replace($search, $replace, $string));
    return $out;
}
?>