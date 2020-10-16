<?php
$aPage = array("AboutTeamWorship.html",		// 0
		"Support.html",			// 1
		"Login.html",			// 2
		"HomePageUser.html",		// 3
		"MyScheduleUser.html",		// 4
		"ChangeRequests.html",		// 5
		"WorshipCalendarUser.html",	// 6
		"WorshipServiceUser.html",	// 7
		"SongsUser.html",		// 8
		"EditProfileUser.html",		// 9
		"TeamDirectoryUser.html",	// 10
		"TeamAvailabilityUser.html",	// 11
		"Communications.html",		// 12
		"HomePageAdmin.html",		// 13
		"MyScheduleAdmin.html",		// 14
		"WorshipCalendarAdmin.html",	// 15
		"WorshipServiceAdmin.html",	// 16
		"SongsAdmin.html",		// 17
		"EditProfileAdmin.html",	// 18
		"TeamDirectoryAdmin.html",	// 19
		"TeamAvailabilityAdmin.html",	// 20
		"EditHomePageAdmin.html",	// 21
		"ManageRolesAdmin.html",	// 22
		"ReviewRequestsAdmin.html",	// 23
		"ManageFilesAdmin.html",	// 24
		"RequestServiceChange.html",	// 25
		"UpdateMyProfile.html",		// 26
		"UpdateMyAvailability.html",	// 27
		"SendTeamMessages.html",	// 28
		"AddNewWorshipService.html",	// 29
		"AddNewSongs.html",		// 30
		"AddNewTeamMembers.html",	// 31
		"ChangeTheHomePage.html");	// 32
$goToPage = isset($_REQUEST["id"])?$aPage[$_REQUEST["id"]]:"";

?>
<html>
<head>
<title>TeamWorship</title>
  </head>
  <NULL TAG FOR NETSCAPES LITTLE HICKUP><SCRIPT LANGUAGE="JavaScript">
  <!--
  function myError(msg, url, line) { return true; }
  window.onerror = myError; key = "";
  UniqueID = top.name = "JS_TreeView_docu";
  window.defaultStatus = "Documentation";

  if ((top.name != UniqueID) && (navigator.appName == "Netscape")
   && (navigator.appVersion.charAt(0) == "2")) {
    opts = "location,menubar,status,resizable,toolbar,scrollbars";
    remote = window.open("index.htm"+ self.location.hash, UniqueID, opts);
    if (remote != null) setTimeout("self.close();", 10); }
  if (top.frames.length > 0) { // ensure full-screen
   if (window.stop) window.stop();
   if (document.images) top.location.replace(self.location.href);
   else top.location.href = self.location.href;
  }
  function setFrameContent() { prm = " "+ self.location.href;
   pos = prm.indexOf("href="); if (pos > -1 && top.main) {
    var newPage = prm.substring(pos + 5, prm.length);
    if (document.images) top.main.location.replace(newPage);
    else top.main.location.href = newPage;
  }}
  function resizeReload() {
   if (document.layers && self.frames.index)
    setTimeout("self.frames.index.location.reload();", 500);
  } // -->
  </SCRIPT></HEAD>
  <frameset rows="103, *">
  <frame name="header" src="helphead.php">
  <frameset cols="20%,*" frameborder="1" onload="setFrameContent();" onresize="resizeReload();">
  <frame name="index" src="left.php">
  <?php
  if($goToPage!="") {
  	echo "<frame name=\"main\" src=\"$goToPage\">\n";
  } else {
  	echo "<frame name=\"main\" src=\"AboutTeamWorship.html\">\n";
  }
  ?>
  </frameset>
  </frameset>
  <noframes>
  This website requires Frames.
  </noframes>
</html>
