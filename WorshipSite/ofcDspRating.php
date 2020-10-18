<?php 
/*******************************************************************
 * ofcDspRating.php
 * Song Rating
 *******************************************************************/
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
if (allow_access(Coordinator) != "yes" && !isset($_REQUEST["id"])) {
	echo "<html><body>\n";
	echo "<h3>Invalid Request.</h3>\n";
	echo "</body></html>\n";
	exit;
}
$idLink = isset($_REQUEST["id"])?"?id=".$_REQUEST["id"]:"";


echo "<html><body onLoad='document.redirect.submit();'><form action='/ofcNewSongs.php' name='redirect' method='post'>\n";
$line = isset($_REQUEST["msg"]) ? "<input type='hidden' name='id' value='$idLink' />":"";
echo $line . "\n";
echo "</form></body></html>\n";
exit;

/* 

<html>
<head>
<script type="text/javascript" src="/classes/OFC/js/swfobject.js"></script>
<script type="text/javascript">
swfobject.embedSWF("/classes/OFC/open-flash-chart.swf", "my_chart", "750", "500", "9.0.0","",{"data-file":"/ofcNewSongs.php<?php echo $idLink; ?>"});
</script>
<script type="text/javascript">
function save_image() {
	var imageData = document.getElementById('my_chart').get_img_binary();
	document.getElementById('image_data').value = imageData;
	document.getElementById('hidden_form').submit();
}

</script>
</head>
<body>
<div id="my_chart"></div>
<!-- Hidden form to post the image data to a php download script -->
<form id="hidden_form" action="/classes/OFC/php-ofc-library/DownloadImage.php" method="post">
<input type="hidden" id="image_data" name="image_data" />
<input type=button onClick="save_image();" value="Save as Image" />
</form>
</body>
</html>
*/