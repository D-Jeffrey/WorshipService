<?php
$isAdmin = (allow_access(Administrators)=="yes");
$showMenu = !isset($hideMenu) || !$hideMenu;
$curUser = $_SESSION["first_name"]." ".$_SESSION["last_name"];
$nav = "<br /><span style='font-size:10pt;'>Member: $curUser</span>&nbsp;<a href='{$baseFolder}lr/logout.php' style='color:aqua;'>Logout</a>&nbsp;";
echo "<table class='topbar'><tr><td align='right'>$title&nbsp;<a href='/help/index.php?id=$hlpID' title='Help'><img src='/images/icon_help.png' border='0' title='Help' /></a><br />$nav</td></tr></table>\n";

if(method_exists($trail,'output')) {
	$trail->output();
}
$curMember = $_SESSION["user_id"];
if(!isset($db)) {
	$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());
	
}
$q = "SELECT count(messageID) AS msgCount FROM sitemessages WHERE toID=$curMember AND msgStatus='U'";
$resCNTMBR = $db->query($q);
$dbCntMbr=mysqli_fetch_array($resCNTMBR);
if($dbCntMbr["msgCount"]>0) {
	echo "<div style=\"position:absolute;top:3px;left:3px;background-color:#cc0000;color:#ffffff;border 1px solid #ffffff;font-weight:bold;padding:2px;\"><a style=\"color:#ffffff;\" href='dspMessages.php' title='View messages'>You have ".$dbCntMbr["msgCount"]." unread messages. Click here to view</a></div>\n";
}
if($showMenu) {
	$q = "SELECT services.serviceID as svcID, svcTeamNotes, date_format(svcPractice, '%W %M %D') as svcPDATE, date_format(svcPractice, '%H:%i') as svcPTIME, date_format(svcDateTime, '%a %b %D') as svcDATE, date_format(svcDateTime, '%H:%i') as svcTIME,roleIcon,roleDescription FROM services INNER JOIN serviceteam ON services.serviceID = serviceteam.serviceID INNER JOIN roles on serviceteam.roleID = roles.roleID WHERE serviceteam.memberID = ".$_SESSION["user_id"]." AND svcDateTime>='".date("Y-m-d")."' ORDER BY svcDateTime, roleDescription";
	$resSched = $db->query($q);
	$schedExists = mysqli_num_rows($resSched)>0;
	echo "<div style=\"width:99.8%\" class=\"chromestyle\" id=\"chromeMenu\">\n";
	echo "<ul>\n";
	echo "<li><a href=\"/index.php\">Home</a></li>\n";
	echo "<li><a href=\"#\" rel=\"dropCalendar\">Calendar</a></li>\n";
	echo "<li><a href=\"#\" rel=\"dropSongs\">Songs</a></li>\n";
	echo "<li><a href=\"#\" rel=\"dropTeam\">Team</a></li>\n";
	echo "<li><a href=\"/dspMessages.php\">Messages</a></li>\n";
	if($isAdmin) {
		echo "<li><a href=\"#\" rel=\"dropAdmin\">Admin</a></li>\n";
	}
	echo "<li><a href=\"#\" rel=\"dropResources\">Resources</a></li>\n";
	echo "</ul>\n";
	echo "</div>\n";
	echo "<div id=\"dropCalendar\" class=\"dropmenudiv\">\n";
	echo "<a href=\"/calendar.php\">Service Calendar</a>\n";
	echo "<a href=\"/teamSchedule.php\">Team Schedule</a>\n";
	if($schedExists) {
		echo "<a href='/expMySchedule.php?act=sel' onClick=\"return hs.htmlExpand(this, { objectType: 'iframe',contentID: 'divExpSched',headingText: 'Export Schedule' });\">Export My Schedule</a>\n";
	}
	echo "</div>\n";
	echo "<div id=\"dropSongs\" class=\"dropmenudiv\">\n";
	echo "<a href=\"/listSongRating.php\">New Song Rating</a>\n";
	echo "<a href=\"/listSongs.php\">Song Database</a>\n";
	echo "<a href=\"/dspSongBooks.php\">Song Books</a>\n";
	echo "</div>\n";
	echo "<div id=\"dropTeam\" class=\"dropmenudiv\">\n";
	echo "<a href=\"/editMember.php?action=edit&id=".$_SESSION["user_id"]."&rtn=1\">My Profile</a>\n";
	echo "<a href=\"/dspTeam.php\">Team Directory</a>\n";
	echo "<a href=\"/teamAvailability.php\">Team Availability</a>\n";
	# echo "<a href='http://www.facebook.com/group.php?gid=156422186342' target='_blank'>Facebook Group <img src='/images/facebook.jpg' border='0' align='top' /></a>\n";
	# echo "<a href='/phpBBGo.php'>Member Forum</a>\n";
	echo "</div>\n";
	if($isAdmin) {
		echo "<div id=\"dropAdmin\" class=\"dropmenudiv\">\n";
		echo "<a href=\"/editSchedule.php\">Worship Team Planner</a>\n";
		echo "<a href=\"/editHome.php\">Edit Home Page</a>\n";
		echo "<a href=\"/dspRoles.php\">Manage Roles</a>\n";
		echo "<a href='dspRoleTypes.php'>Manage Role Categories</a>\n";
		echo "<a href=\"/dspRequests.php\">Review Requests</a>\n";
		echo "<a href='editActMessage.php'>Activation Template</a>\n";
		echo "<a href=\"/editChgMessage.php\">Change Request Template</a>\n";
		echo "<a href=\"/editSvcMessage.php\">Service Order Template</a>\n";
		echo "<a href=\"/editSiteConfig.php\">Site Configuration</a>\n";
		echo "<a href=\"/scripts/tinyfilemanager.php\">Manage Files</a>\n";
        echo "<a href=\"/editPurge.php\">Purge Services</a>\n";
		echo "</div>\n";
	}

	echo "<div id=\"dropResources\" class=\"dropmenudiv\">\n";
	echo "<a href='dspTeamResources.php?id=5'>Worhip Band</a>\n";
	echo "<a href='dspTeamResources.php?id=4'>Worship Singers</a>\n";
	echo "<a href='dspTeamResources.php?id=1'>Sound Team</a>\n";
	echo "<a href='dspTeamResources.php?id=2'>Media Team</a>\n";
	echo "<a href='dspTeamResources.php?id=3'>Lighting Team</a>\n";
	echo "</div>\n";

	echo "<script>\n";
	
	echo "cssdropdown.startchrome(\"chromeMenu\")\n";
	
	echo "</script>\n";
}
?>