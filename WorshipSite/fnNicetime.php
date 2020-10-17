<?php

function nicetime($t)
{
	$t=strtolower($t);
	if ($t == "00:00" or $t == "00:00:00" or $t == "12:00 am" or $t == "") {
		$t = "";     # Print nothing for Midnight
	} else {
		if ($t == "12:00" or $t == "12:00:00" or $t == "12:00 pm") {
			$t = "Noon";
		} else {
			$aT = explode(":",$t);
			$aT[2] = isset($aT[2])?$aT[2]:0;
			$t = date("g:ia",mktime($aT[0],$aT[1],$aT[2],0,0,0));
		}
	}
	return($t);
}

?>