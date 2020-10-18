<?php
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

require('../lr/config.php');
require('lr/functions.php'); 
$isAdmin = (allow_access(Administrators)=="yes");

?>
<HTML>
<HEAD><TITLE>Change The Home Page</TITLE>
<LINK REL=StyleSheet TYPE="text/css" HREF="treeview.css">
<META NAME="robots" CONTENT="noindex,follow"></HEAD>
<BODY BGCOLOR="#FFFFFF" TEXT="#000000" LINK="#CC0000" VLINK="#990066"
 ALINK="#CC0000" ONLOAD="window.onerror=myError; loaded=true;">
<DIV STYLE="position:absolute; bottom:30px;" ALIGN="center"><img src='under_constructionSM.jpg' alt='Under Construction' /></div>
<DIV ID="preamble" STYLE="position:absolute; top:auto;" ALIGN="center">

</DIV><NULL SPACE TAG (NS2 BUGFIX)><SCRIPT LANGUAGE="JavaScript"> /*
This page requires a JavaScript- enabled browser. Yours isn't.
(F&uuml;r diese Seite muss JavaScript einsetzbar sein!) */ <!-- hide
function myError(msg, url, line) { return true; } // error 'handler'
window.onerror = myError; loaded = false; xImgs = new Array(10);

/* This is TreeView, Copyright (c) Simon Harston <jSh@jSh.de>
 * It may be used as freeware, but please give credit. Please
 * also tell me an URL where I can look at what you made with
 * it. Get the documentation at <http://www.jsh.de/treeview/>
 */

/* ### LOCAL DEFINITIONS ### */
UniqueID = "JS_TreeView_docu";
DocRoot = "./";
ImgRoot = "grafix/hlp/";
FrameSet = "index.html";
ImgWidth = 14;
ImgHeight = 18;
EntryHeight = ImgHeight;
InitialKey = "d+0";
CurrPageBG = "#000099";
CurrPageFG = "#000000";
LinkCurrPage = true;
TreeRootHint = "";
NormalPageHint = "";
LinkedPageHint = "";
OpenBookHint = "close"; // "schlie&szlig;en";
ClosedBookHint = "open"; // "&ouml;ffnen";
OpenBookStatus = "Close sub-list"; // "Ebene ausblenden";
ClosedBookStatus = "Open sub-list"; // "Ebene einblenden";
window.defaultStatus = "JavaScript TreeView documentation";
navExplain = "\nThis page normally belongs inside a navigation"
 +" frame.\n\nIs it OK to reload the page as designed?";
// navExplain = "Wenn ihr Browser Frames und JavaScript unterst"
//  +unescape("%FC")+"tzt,\nempfehlen wir die Nutzung der deutlich "
//  +"komfortableren\nFrameset-Version dieser Homepage."
waitText = "Rendering tree, please wait...";
// waitText = "Baumstruktur wird aktualisiert. Bitte warten ...";
FontFace = "'Garamond Condensed','Arial',Times,serif";
cellStyle = "font-size:9pt";
compactTree = false;
viewMatchCnt = 0;
singleBranch = false;
checkFrames = true;
linkTarget = "main";
leftMargin = 10;
preambleHeight = 10;

/* ### ENTER YOUR TREEVIEW INDEX HERE ###
 * Note for TEXT: Use \" for quotes, NOT &quot; ! */

initTree("TeamWorship","","") // BEGIN
sub_Page("About TeamWorship","A","AboutTeamWorship.html"); //D2
sub_Page("Support","B","Support.html"); //D2
sub_Book("TeamWorship Features","C"); //F3
sub_Page("Login","CA","Login.html"); //D2
sub_Page("Page Header","CB","page-header.html"); //D2
sub_Page("My Schedule","CC","MyScheduleUser.html"); //D2
sub_Page("Change Requests","CD","ChangeRequests.html"); //D2
sub_Page("Edit Profile","CE","EditProfileUser.html"); //D2
sub_Page("Facebook Group","CF","facebook-group.html"); //D2
sub_Page("Send Mail","CG","Communications.html"); //D2
sub_Page("View Mail","CH","view-mail.html"); //D2
sub_Book("User View","CI"); //F3
sub_Page("Home Page","CIA","HomePageUser.html"); //D2
sub_Page("Worship Calendar","CIB","WorshipCalendarUser.html"); //D2
sub_Page("Worship Service","CIC","WorshipServiceUser.html"); //D2
sub_Page("New Song Rating","CID","new-song-rating.html"); //D2
sub_Page("Songs","CIE","SongsUser.html"); //D2
sub_Page("Song Details","CIF","song-details.html"); //D2
sub_Page("Song Books","CIG","song-books.html"); //D2
sub_Page("Team Directory","CIH","TeamDirectoryUser.html"); //D2
lastPage("Team Availability","CII","TeamAvailabilityUser.html"); //D1
end_Book(); //D1A
sub_Book("Administrator View","CJ"); //F3
sub_Page("Home Page","CJJ","HomePageAdmin.html"); //D2
sub_Page("Worship Calendar","CJK","WorshipCalendarAdmin.html"); //D2
sub_Page("Worship Service","CJL","WorshipServiceAdmin.html"); //D2
sub_Page("New Song Rating","CJM","new-song-rating-admin.html"); //D2
sub_Page("Songs","CJN","SongsAdmin.html"); //D2
sub_Page("Song Details","CJO","song-details-admin.html"); //D2
sub_Page("Team Directory","CJP","TeamDirectoryAdmin.html"); //D2
lastPage("Team Availability","CJQ","TeamAvailabilityAdmin.html"); //D1
end_Book(); //D1A
lastBook("Admin Links","CK"); //F4
sub_Page("Worship Team Planner","CKR","worship-team-planner.html"); //D2
sub_Page("Manage Roles","CKS","ManageRolesAdmin.html"); //D2
sub_Page("Manage Role Categories","CKT","manage-role-categories.html"); //D2
sub_Page("Review Requests","CKU","ReviewRequestsAdmin.html"); //D2
sub_Page("Edit Home Page","CKV","EditHomePageAdmin.html"); //D2
sub_Page("Activation Template","CKW","activation-template.html"); //D2
sub_Page("Change Req. Template","CKX","change-req-template.html"); //D2
sub_Page("Service Order Template","CKY","service-order-template.html"); //D2
sub_Page("Site Configuration","CKc","site-configuration.html"); //D2
lastPage("Manage Files","CKd","ManageFilesAdmin.html"); //D1
end_Book(); //D1A
end_Book(); //D1A
sub_Book("How To - Users","D"); //F3
sub_Page("Request Service Change","DL","RequestServiceChange.html"); //D2
sub_Page("Update My Profile","DM","UpdateMyProfile.html"); //D2
sub_Page("Update My Availability","DN","UpdateMyAvailability.html"); //D2
lastPage("Send Team Messages","DO","SendTeamMessages.html"); //D1
end_Book(); //D1A
lastBook("How To - Administrators","E"); //F4
sub_Page("Add New Worship Service","EP","AddNewWorshipService.html"); //D2
sub_Page("Add New Songs","EQ","AddNewSongs.html"); //D2
sub_Page("Add New Team Members","ER","AddNewTeamMembers.html"); //D2
lastPage("Change The Home Page","ES","ChangeTheHomePage.html"); //D1
end_Book(); //D1A
end_Book(); //D1A
end_Tree();

/* ############################################################ *
 * Note: You won't need to change anything below here, I think. */
//
// Version 3.3 (currently still BETA) introduces:
// * compactTree switch (added ages ago, was undocumented)
//   (for very 'flat' trees: does not draw any plus/minus symbols)
// * viewMatchCnt switch (added 2000-05-10)
//   (limit visible subtrees to those matching n chars of viewKey)
// * closeAll / openAll functions (added 2000-05-29)
//   (to open and close all subtrees)
// * hourglass while waiting for tree re-render (added 2000-08-16)
// * singleBranch switch (added 2000-08-18)
//   (allows only one branch to be open at a time)
// * speed increase in Internet Explorer for tree change.
// * IE 5.5 viewPage-update-bug squashed (2000-12-02),
//   resulting openBook-bug (oops...) squashed (2001-02-26)
// * checkFrames switch (added 2001-04-01, thanks ERIC)
// * possibly solved preamble-height problem with 'top:auto'? (2002-05-16)
// * added leftMargin configuration property (2002-10-13, thanks Adi)
// * added cellStyle configuration property (2003-03-14, thx Martin Maedler)
// * added linkTarget configuration property (2003-03-14, thx Martin Maedler)
// * added previewHeight and use getElemById to fix Opera bug (2003-11-06)
// * inofficial release of TreeView with WXP and INV symbol set (2004-02-26)
// * switched from using CSS-property 'display' to 'visibility', killing a
//   display-bug in Netscape>7 and Opera>6 (2004-05-13, thx Andreas Eltzner)

function TVversion() { /* print version info */
 return "TreeView v.3.3 BETA (2004-05-13) [http://www.jSh.de/treeview/]"; }

/* read params, split key and viewKey etc. */
function initTreeView() { if (self.TVinitd) return;
 if (self.checkFrames && (""+window.innerWidth != "0")) { // not printing
  tmpTopName = top.name; cutPos = UniqueID.length;
  if (tmpTopName.length > cutPos)
   tmpTopName = tmpTopName.substring(0, cutPos);
  if ((tmpTopName == UniqueID && top.frames.length == 0)
   || (tmpTopName != UniqueID)) // check we're feeling at home ...
   if (confirm(navExplain)) { if (window.stop) window.stop();
    if (document.images) top.location.replace(FrameSet);
    else top.location.href = FrameSet; }}
 isOpera = (myIndexOf(navigator.userAgent, "Opera") > -1);
 if ((navigator.appName == "Netscape")
  && (navigator.appVersion.charAt(0) == "2")) // Doesn't know
  CurrPageFG = '#339933"><B><CurrPage="YES'; // TD with BGCOLOR
 isDHTML = (document.all || document.layers || document.getElementById);
 if ((navigator.appName == "Netscape") // Mac display refresh
  && (navigator.appVersion.charAt(0) == "4") // bug workaround
  && (myIndexOf(navigator.userAgent, "Macintosh") > -1)) isDHTML = false;
 if (document.layers && document.preamble)
  TVtop = document.preamble.clip.bottom;
 else if (document.getElementById && document.getElementById('preamble'))
  TVtop = document.getElementById('preamble').height;
 else if (document.all && document.all.preamble)
  TVtop = document.all.preamble.offsetHeight;
 if (!self.preambleHeight) preambleHeight = 125;
 if (isNaN(TVtop)) TVtop = preambleHeight;
 if (!self.waitText) waitText = "Rendering tree, please wait...";
 if (!self.leftMargin) leftMargin = 0; currPosY = TVtop;
 if (!self.cellStyle) cellStyle = '';
 else cellStyle = ' STYLE="'+ cellStyle +'"';
 TVentries = new Array(); TVkeys = new Array(); TVcount = 0;
 showKey = printBuffer = ""; splitPrm(); TVinitd = true; }

/* split input to prm and viewKey */
function splitPrm() { input = ""; if (top.key) input = ""+ top.key;
 if ((input == "") || (myIndexOf(input, "<object") > -1)) input = InitialKey;
 pos = myIndexOf(input, "+"); if (pos <0) viewKey = "";
 else { viewKey = input.substring(pos+1); input = input.substring(0, pos); }
 if (input == "") input = ".+."; prm = input; dontVKey = false; }

/* set visibility if isDHTML */
function DHTMLTreeView(currKey) { // must return true ...
 if (!isDHTML) return false; // ... only if display handled.
// TVentries[count](status{0=final,1=redraw}, text, key, link, TreePfx,
//  prefix, code, isCurrVisible, currTop); TVkeys[key](showSubs);
 TVkeys[currKey] = newVis = (!TVkeys[currKey]);
 if (self.singleBranch) for (var i = 1; i <= TVcount; i++)
  if (TVkeys[TVentries[i][2]] && (myIndexOf(currKey, TVentries[i][2]) != 0))
   TVkeys[TVentries[i][2]] = TVentries[i][0] = false;
 currPosY = TVtop; TVelemTop = TVelemBtm = 0;
 for (var j = 1; j <viewKey.length; j++) if (!dontVKey) {
  var viewSub = viewKey.substring(0, j);
  for (var i = 1; i <= TVcount; i++) if (!TVkeys[viewSub])
   TVentries[i][0] &= (TVentries[i][2] != viewSub);
  TVkeys[viewSub] = true; }
 if (TVkeys[currKey] != newVis) dontVKey = true;
 TVkeys[currKey] = newVis;
 for (var i = 1; i <= TVcount; i++) {
  var tmpKey = TVentries[i][2]; var isVisible = true;
  for (var j = 1; j <tmpKey.length; j++)
   isVisible &= TVkeys[tmpKey.substring(0, j)];
  if (self.viewMatchCnt && tmpKey != "*") isVisible
   &= (tmpKey.substring(0, viewMatchCnt)
   == viewKey.substring(0, viewMatchCnt));
  if (isVisible) {
   TVentries[i][0] &= ((tmpKey != currKey) && (tmpKey != viewKey));
   if (TVentries[i][8] != currPosY) { TVentries[i][8] = currPosY;
    if (document.layers) document.layers["TV"+i].top = currPosY;
    else if (document.all) document.all["TV"+i].style.top = currPosY;
    else document.getElementById("TV"+i).style.top = currPosY; }
   if (tmpKey == showKey) TVelemTop = TVelemBtm = currPosY;
   if ((tmpKey.substring(0, showKey.length) == showKey)
    && (currPosY > TVelemBtm)) TVelemBtm = currPosY;
   currPosY += EntryHeight;
   if (!TVentries[i][0]) { treePfx = TVentries[i][4];
    prm = (TVkeys[tmpKey] ? tmpKey : tmpKey.substring(0, tmpKey.length-1));
    var retVal = wrtIdx(TVentries[i][1], tmpKey,
     TVentries[i][3], TVentries[i][5], TVentries[i][6]);
    if (document.getElementById) document.getElementById("TV"+i).innerHTML = retVal;
    else if (document.all) document.all["TV"+i].innerHTML = retVal;
    else with (document.layers["TV"+i].document) { clear(); write(retVal); close(); }
    TVentries[i][0] = (tmpKey != viewKey); }}
  if (TVentries[i][7] != isVisible) { TVentries[i][7] = isVisible;
   if (document.layers)
    document.layers["TV"+i].visibility = (isVisible ? "show" : "hide");
   else if (document.getElementById)
    document.getElementById("TV"+i).style.visibility = (isVisible ? "visible" : "hidden");
   else document.all["TV"+i].style.visibility = (isVisible ? "visible" : "hidden");
 }} // scroll new entry into view
 if (TVelemTop > 0) { TVelemBtm += EntryHeight;
  if (document.layers) { var ScreenTop = window.pageYOffset;
   var ScreenBtm = ScreenTop + window.innerHeight; }
  else { var ScreenTop = document.body.scrollTop;
   var ScreenBtm = ScreenTop + document.body.clientHeight; }
  if ((TVelemBtm > ScreenBtm) || (TVelemTop <ScreenTop)) {
   var scrollTo = ScreenTop + TVelemBtm - ScreenBtm;
   if (TVelemTop <scrollTo) scrollTo = TVelemTop;
   window.scrollTo(0, scrollTo); }
 } return true; }

/* expands an image */
function img(image, hint) { return '<IMG SRC="'
 + ImgRoot +'ix_'+ image +'.gif" ALT="'+ hint +'" BORDER="0"'
 +' WIDTH="'+ ImgWidth +'" HEIGHT="'+ ImgHeight +'">'; }

/* expands a tree-code */
function tree(code) { var ret = "";
 if (myIndexOf(code, "null") > -1) return "";
 for (var i = 0; i <code.length; i++) { var c = code.charAt(i);
  if (c == '.') ret += img("space",""); if (c == '/') ret += img("line","");
  if (c >= '0' && c <= '9') ret += img(xImgs[c],""); if (!self.compactTree) {
   if (c == 'l') ret += img("list",""); if (c == 'L') ret += img("end", "");
   if (c == '+') ret += img("listp",ClosedBookHint);
   if (c == '*') ret += img("endp", ClosedBookHint);
   if (c == '-') ret += img("listm",OpenBookHint);
   if (c == '_') ret += img("endm", OpenBookHint); }
  if (c == 'r') ret += img("open", TreeRootHint);
  if (c == 'R') ret += img("link", TreeRootHint);
  if (c == '#') ret += img("leaf", NormalPageHint);
  if (c == 'x') ret += img("link", LinkedPageHint);
  if (c == 'b') ret += img("book", ClosedBookHint);
  if (c == 'o') ret += img("open", OpenBookHint);
 } return ret; }

/* removes quotes and HTML-Tags in status-text. */
function unquote(text) {
 var pos = myIndexOf(text, '"');
 while (pos > -1) { text = text.substring(0, pos) +"`"+
  text.substring(pos+1); pos = myIndexOf(text, '"'); }
 var pos = myIndexOf(text, "'");
 while (pos > -1) { text = text.substring(0, pos) +"`"+
  text.substring(pos+1); pos = myIndexOf(text, "'"); }
 var pos = myIndexOf(text, "<"); var pos2 = myIndexOf(text, ">");
 while ((pos > -1) && (pos2 > -1) && (pos <pos2)) {
  text = text.substring(0, pos) + text.substring(pos2+1);
  pos = myIndexOf(text, "<"); pos2 = myIndexOf(text, ">");
 } return text; }

/* expands a link */
function lnk(xHref, onOver, misc, xText) { return '<A H'+'REF="'
 + xHref +'" ONMOUSEOVER="window.status=\''+ onOver +'\'; return true" '
 +'ONMOUSEOUT="window.status=\'\'; return true"'+ misc +'>'+ xText +'<\/A>'; }

/* writes tree code, marks active doc, adds link and text */
function wrtEntry(tree, key, link, text) {
 var split = myIndexOf(text, "|"); // split text and status
 if (split <0) { var statusText = unquote(text); var tipText = ""; }
 else { var statusText = unquote(text.substring(split+1));
  var tipText = ' TITLE="'+ statusText +'"';
  text = text.substring(0, split); } tipText += cellStyle;
 var pos = myIndexOf(text, " "); // make text non-breaking
 while (pos > -1) { text = text.substring(0, pos) +"&#160;"+
  text.substring(pos+1); pos = myIndexOf(text, " "); }
 var isCurr = (viewKey == key); if (link)
  link = (link.charAt(0) == "|" ? link.substring(1) : DocRoot + link);
 if (link && !(isCurr && (isOpera || !LinkCurrPage))) text = lnk(link,
  statusText, (isCurr ? ' STYLE="color:'+ CurrPageFG +';"' : '') + tipText,
  (isCurr ? '<FONT COLOR="'+ CurrPageFG +'">'+ text +'<\/FONT>' : text));
 tableBeg = '<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="0"><TR>';
 return tableBeg +'<TD><FONT SIZE="1">&#160;<\/TD><TD NOWRAP><NOBR>'+ tree
 +'<\/NOBR><\/TD><TD><FONT SIZE="1">&#160;<\/TD><TD NOWRAP>'+ tableBeg
 +'<TD NOWRAP'+ (isCurr ? ' BGCOLOR="'+ CurrPageBG +'"' : '') + cellStyle
 +'><NOBR><FONT SIZE="-1"'+ (isCurr ? ' COLOR="'+ CurrPageFG +'"' : '')
 + cellStyle +' FACE="'+ FontFace +'">&#160;'+ text +'&#160;<\/FONT>'
 +'<\/NOBR><\/TD><\/TR><\/TABLE><\/TD><\/TR><\/TABLE>'; }

/* performs a reload-index-instruction with the new key */
function index(newKey, currKey, doneMouse) { window.status = waitText;
 if (document.all && document.all.waitMsg && !doneMouse) {
  document.all.waitMsg.style.top = document.body.scrollTop + 5;
  document.all.waitMsg.style.visibility = "visible";
  window.setTimeout("index('"+newKey+"','"+currKey+"','true');", 50);
  return; }
 else if (document.getElementById && document.getElementById('waitMsg')
  && !doneMouse) {
  document.getElementById('waitMsg').style.top = document.body.scrollTop + 5;
  document.getElementById('waitMsg').style.visibility = "visible";
  window.setTimeout("index('"+newKey+"','"+currKey+"','true');", 50);
  return; }
 if (!self.currKey) showKey = ""; else showKey = currKey;
 if ((!self.currKey && (""+ currKey == "undefined")) || !isDHTML) {
  var pos = myIndexOf(newKey, "+");
  if (pos <0) newHash = newKey +"+"+ viewKey; // missing viewKey
  else { if (pos > 0) newHash = newKey; // new prm & viewKey
   else { // keep prm, new viewKey
    var KeyAdd = newKey.substring(1); showKey = KeyAdd;
    if (myIndexOf(":"+prm+":", ":"+KeyAdd+":") > -1) newHash = prm + newKey;
    else // newKey needs to be added to prm
     newHash = ((prm == ".+.") ? "" : prm +":") + KeyAdd + newKey;
  }} top.key = newHash; splitPrm(); currKey = ""; TVkeys[viewKey] = true; }
 if (!DHTMLTreeView(currKey)) { // need to redisplay
  if (isOpera) location.reload(); else
   if (document.images) location.replace(location.href);
   else location.href = location.href;
 } else if (document.all && document.all.waitMsg)
  document.all.waitMsg.style.visibility = "hidden";
  else if (document.getElementById && document.getElementById('waitMsg'))
  document.getElementById('waitMsg').style.visibility = "hidden";
 window.status = ""; }

/* compute the new prm for a book */
function makePrm(currPrm, add, sub) {
 if (myIndexOf(currPrm, " ") > -1) currPrm = ".+."; // catch NS2-bug
 if (add != "") // put in a key
  var newPrm = ((currPrm == ".+.") ? "" : currPrm +":") + add;
 if (sub != "") { // take out a key _and_it's_children_
  var newPrm = ":"+currPrm+":"; var cutPos = myIndexOf(newPrm, ":"+sub);
  while(cutPos > -1) { newPrm = newPrm.substring(0, cutPos) +
   newPrm.substring(myIndexOf(newPrm, ":", cutPos+1));
   cutPos = myIndexOf(newPrm, ":"+sub); } if (newPrm == ":") newPrm = ":*:";
  newPrm = newPrm.substring(1, newPrm.length-1);
 } if (myIndexOf(newPrm, " ") > -1) newPrm = currPrm;
 return newPrm; }

/* expands a reload-index-instruction with new prm */
function rld(currKey, newPrm, treecode, hint) {
 return lnk('#" ONCLICK="index(\''+ newPrm +"+"+ viewKey +"', '"
 + currKey +'\');return false" TARGET="_self', hint, "", treecode); }

/* generate the HTML tables */
function wrtIdx(text, key, link, prefix, code) { var idxRet = "";
 var pos = myIndexOf(key, " "); if (pos > -1) key = key.substring(0, pos);
 var subKey = (key.length > 1 ? key.substring(0, key.length-1) : "");
 currIsVisible = (myIndexOf(":"+prm+":", ":"+subKey) > -1);
 if (self.viewMatchCnt && subKey != "") currIsVisible
  &= (subKey.substring(0, viewMatchCnt)
  == viewKey.substring(0, viewMatchCnt));
 if (currIsVisible || isDHTML) { var codePos = myIndexOf(code, "|");
  if (codePos > -1) { var prefixPos = myIndexOf(prefix, "|"); // isBook
   if (myIndexOf(":"+prm+":", ":"+key) <0) // isCollapsed
    idxRet = tree(treePfx + (prefixPos <0 ? prefix :
    prefix.substring(prefixPos+1))) + rld(key, makePrm(prm, key, ""),
    tree(code.substring(codePos+1)), ClosedBookStatus);
   else idxRet = tree(treePfx + (prefixPos <0 ? prefix :
    prefix.substring(0, prefixPos))) + rld(key, makePrm(prm, "", key),
    tree(code.substring(0, codePos)), OpenBookStatus);
  } else idxRet = tree(treePfx + prefix + code); // isLeaf
  return wrtEntry(idxRet, key, link, text);
 } else return ""; }

/* adds the initial TreeView entries */
function idx(text, key, link, prefix, code, opts) {
 if (!key) key = "*"; if (!text) text = "";
 if (link) link += '" TARGET="'+ xTarget(opts); TVcount++;
 var retVal = wrtIdx(text, key, link, prefix, code);
 if (document.layers) retVal = '<LAYER ID="TV'+ TVcount
 +'" TOP="'+ currPosY +'" LEFT="'+ leftMargin +'" VISIBILITY="'
 + (currIsVisible ? "show" : "hide") +'">'+ retVal +"<\/LAYER>";
 else if (document.all || document.getElementById)
  retVal = '<DIV ID="TV'+ TVcount +'"'
 +' STYLE="position:absolute; top:'+ currPosY +"px; left:"
 + leftMargin +"px; visibility:"+ (currIsVisible ? "visible" : "hidden")
 +';">'+ retVal +"<\/DIV>";
 if (isDHTML) { TVkeys[key] = false; TVentries[TVcount] = new Array
  ((viewKey != key), text, key, link, treePfx, prefix, code, currIsVisible,
  currPosY); TVkeys[key.substring(0, key.length-1)] = currIsVisible; }
 wrt(retVal); if (currIsVisible) currPosY += EntryHeight; }

/* a 'clean' version of indexOf */
function myIndexOf(text, srch, start) {
 if (!start) start = 0; var pos = (""+ text).indexOf(srch, start);
 return (""+ pos != "" ? pos : -1); }

/* write to prnBuffer */
function wrt(text) { printBuffer += text +"\n"; }

/* writes the printBuffer */
function flush() { document.writeln(printBuffer); printBuffer = ""; }

/* test for option */
function is(opts, keyword) { return (myIndexOf(""+ opts, keyword) > -1); }

/* get custom target */
function xTarget(opts) { if (opts && is(opts, "target")) {
  opts += ","; startPos = myIndexOf(opts, "target=") + 7;
  return opts.substring(startPos, myIndexOf(opts, ",", startPos)); }
 else return "main"; }

/* get custom image */
function xImg(opts) { return (opts ? opts.substring
 (myIndexOf(opts, "img") + 3, myIndexOf(opts, "img") + 4) : ""); }

/* functions for building the tree with */
function initTree(text, key, link, opts) { initTreeView(); treePfx = "";
 idx(text, key, link, (is(opts, "cntd.") ? "/" : (is(opts, "img")
 ? xImg(opts) : (is(opts, "link") ? "R":"r") ) ), "", opts); }
function sub_Book(text, key, link, opts) {
 if (is(opts, "cntd.")) idx(text, key, link, "/|.", "|", opts);
 else { idx(text, key, link, "", (is(opts, "img") ? (is(opts, "last")
 ? "_"+xImg(opts)+"|*"+xImg(opts):"-"+xImg(opts)+"|+"+xImg(opts))
 : (is(opts, "last") ? "_o|*b":"-o|+b") ), opts );
 treePfx += (is(opts, "last") ? ".":"/"); }}
function lastBook(text, key, link, opts) {
 sub_Book(text, key, link, "last,"+ opts); }
function end_Book() { treePfx = treePfx.substring(0, treePfx.length-1); }
function sub_Page(text, key, link, opts) {
 idx(text, key, link, "", (is(opts, "cntd.") ? (is(opts, "last")
 ? "..":"/.") : (is(opts, "last") ? "L":"l") + (is(opts, "img")
 ? xImg(opts) : (is(opts, "link") ? "x":"#") ) ), opts); }
function lastPage(text, key, link, opts) {
 sub_Page(text, key, link, "last,"+ opts); }
function end_Tree() { idx(); if (document.layers) wrt('<LAYER ID="bottom"'
 +' TOP="'+ (TVtop + EntryHeight * (TVcount-1)) +'">&#160;<\/LAYER>');
 wrt('<INFO TEXT="'+ TVversion() +'">'); flush(); treePfx = ""; }

/* close all subtrees */
function closeAll() { if (isDHTML) {
 for (var i = 1; i <= TVcount; i++) if (TVkeys[TVentries[i][2]]) {
  TVkeys[TVentries[i][2]] = TVentries[i][0] = false; } index();
 if (document.layers) { ScreenTop = window.pageYOffset; scrollMax = 50
  - window.innerHeight + document.layers["TV"+TVcount].pageY;
 } else { ScreenTop = document.body.scrollTop;
  if (document.all) scrollMax = 50 - document.body.clientHeight
   + document.all["TV"+TVcount].offsetTop;
  else scrollMax = 50 - document.body.clientHeight
   + document.getElementById("TV"+TVcount).offsetTop;
 } if (ScreenTop > scrollMax) window.scrollTo(0, scrollMax); }}

/* open all subtrees */
function openAll() { if (isDHTML) { for (var i = 1; i <= TVcount; i++)
 if ((myIndexOf(TVentries[i][6], "|") > -1) && (!TVkeys[TVentries[i][2]])) {
  TVkeys[TVentries[i][2]] = true; TVentries[i][0] = false; } index(); }}

// end-hide --> </SCRIPT></DIV><DIV ID="waitMsg" STYLE="cursor:wait;
 position:absolute; left:5px; height:100%; width:100%; visibility:hidden;">
<TABLE HEIGHT="100%" WIDTH="100%"><TR><TD><BR></TD></TR></TABLE>
</DIV></BODY></HTML>



