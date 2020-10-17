<?php
require('lr/config.php');

require('classes/sllists/SLLists.class.php');

$sortableLists = new SLLists('/scripts/scriptaculous/src');	// points to path of scriptaculous JS files

if(isset($_REQUEST['id'])) {
	$serviceID = $_REQUEST['id'];
} else {
	exit;
}

/*Connection to database*/
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());


// formatting for the list - using a ul as a container and li tags for list items
$sortableTag = 'li';			// the type of tag that should be sortable
$listFormat = '<ul id="sortableList">%s</ul>';	// argument is the contents of the list
$listItemFormat = '<li id="item_%s">%s</li>';  // two arguments are the idField and the displayField

// formatting for the list - using a div as the container and divs as the list items
//$sortableTag = 'div';
//$listFormat = '<div id="sortableList">%s</div>';	// argument is the contents of the list
//$listItemFormat = '<div id="item_%s">%s</div>';  // two arguments are the idField and the displayField


$sortableLists->addList('sortableList','sortableListOrder',$sortableTag);
$sortableLists->debug = false;

if (isset($_POST['sortableListsSubmitted'])) {
	$orderArray = $sortableLists->getOrderArray($_POST['sortableListOrder'],'sortableList');
	foreach($orderArray as $item) {
		$sql = "UPDATE serviceorder set songNumber=".$item['order']."*10 WHERE serviceID=$serviceID AND orderID=".$item['element'];
		$db->query($sql);
		
	}
	echo "<script>parent.window.dspOrder();parent.window.hs.close();</script>";
	
	exit;
}

$sql = "SELECT a.orderID, ifnull(b.songName,orderDescription) as itemDesc, songNumber from serviceorder a LEFT JOIN songs b ON a.songID=b.songID WHERE serviceID=$serviceID order by songNumber";
$recordSet = $db->query($sql);

$listArray = array();
while ($record = mysqli_fetch_assoc($recordSet)) {
	$listArray[] = sprintf($listItemFormat,$record['orderID'],$record['itemDesc']);
	
}
mysqli_free_result($recordSet);
$listHTML = implode("\n",$listArray);


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<?php $sortableLists->printTopJS(); ?>
<style>
body,div,li {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
}
ul#sortableList {
	list-style-type: none;
	padding: 0px;
	margin: 0px;
	width: 250px;
}
ul#sortableList li {
	cursor: move;
	padding: 2px 2px;
	margin: 2px 0px;
	border: 1px solid #000000;
	background-color: #daeda3;
}
div#sortableList div {
	cursor: move;
	padding: 2px 2px;
	margin: 2px 0px;
	border: 1px solid #000000;
	background-color: #daeda3;
	width: 250px;
}
</style>
</head>

<body>
<b>Drag and drop to set the desired order, then click 'Save':</b><br>
<?php
printf($listFormat, $listHTML);
$sortableLists->printForm($_SERVER['PHP_SELF']."?id=$serviceID", 'POST', 'Save', 'button');
$sortableLists->printBottomJS();
?>
</body>
</html>