<?php


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="/scripts/scriptaculous/lib/prototype.js"></script>
<script src="/scripts/scriptaculous/src/scriptaculous.js"></script>
<script src="/scripts/highslide/highslide-full.js"></script>
<script>
	// Apply the Highslide settings
	hs.graphicsDir = '<?php echo $baseFolder; ?>scripts/highslide/graphics/';
	hs.align = 'center';
	hs.transitions = ['expand', 'crossfade'];
	hs.outlineType = 'rounded-white';
	hs.fadeInOut = true;
	hs.dimmingOpacity = 0.75;
	hs.outlineWhileAnimating = true;
	hs.allowSizeReduction = false;
	// always use this with flash, else the movie will be stopped on close:
	hs.preserveContent = false;
	hs.wrapperClassName = 'draggable-header no-footer';
</script>
<link rel="stylesheet" type="text/css" href="<?php echo $baseFolder; ?>scripts/highslide/highslide.css" />

<script>


function setMemberID(text,li) {
	var liParts = li.id.split(";");
	var eml = "";
	if(li.id && liParts[0] > 0) {
		document.frmComm.memberID.value=liParts[0];
		document.frmComm.mbrType.value=liParts[1];
		document.frmComm.sbmComm.disabled=false;
		if(liParts[2]!="") eml += liParts[2];
		if(liParts[3]!="") eml += ";"+liParts[3];
		document.getElementById("mbrEmail").innerHTML=eml;
	} else {
		document.frmComm.memberID.value=0;
		document.frmComm.mbrType.value="";
		document.frmComm.sbmComm.disabled=true;
		document.getElementById("mbrEmail").innerHTML="";
	}
}

function setSbmComm() {
	document.frmComm.sbmComm.disabled=!(document.frmComm.memberID.value>0);
}

function setCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}





function dspSchedule() {
	for (var i=1;i<10;i++) {
		var oUpdater = new Ajax.Updater({ success:'divSvcSchedule' }, '/ajSendEmail.php', { 
			method: "get",
			insertion: Insertion.Top,
			parameters: { Subject: "Subject<b> text</b> " + i.toString() }
		});
	}
}


</script>
</head>
<body>
<p></p>
<?php


echo "<div id='divSvcSchedule' style='width:800px'>123456789 123456789 123456789 123456789 123456789 123456789\n</div>\n";

echo "<a href='#' onClick='dspSchedule();' >Click on </a>";




?>
</body><</html>