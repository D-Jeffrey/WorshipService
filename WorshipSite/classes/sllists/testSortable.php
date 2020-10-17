<?php
/*
This file is a simple example of how you can create a relatively generic script that will load
records from the database, allow the user to drag drop the items to change the order, and then
save the new order to the database.

This example works with the following database table:

CREATE TABLE categories (
  catid int(11) NOT NULL auto_increment,
  category varchar(255) NOT NULL default '',
  orderid smallint(6) NOT NULL default '100',
  PRIMARY KEY  (catid)
) TYPE=MyISAM AUTO_INCREMENT=9 ;

sample data:

INSERT INTO categories VALUES (1, 'Galleries', 5);
INSERT INTO categories VALUES (2, 'Art', 2);
INSERT INTO categories VALUES (3, 'Roadtrip', 6);
INSERT INTO categories VALUES (4, 'Yosemite', 8);
INSERT INTO categories VALUES (5, 'Animals', 1);
INSERT INTO categories VALUES (6, 'Australia', 3);
INSERT INTO categories VALUES (7, 'Wesleyan', 7);
INSERT INTO categories VALUES (8, 'California', 4);
*/

require('SLLists.class.php');
$sortableLists = new SLLists('scriptaculous');	// points to path of scriptaculous JS files
$conn = mysql_connect('localhost', 'dbuser', 'dbpass');
mysql_select_db('dbname');

// information about the database fields and table
$dbTable = 'categories';		// the database table
$idField = 'catid';				// the numeric primary key
$displayField = 'category';		// the field whose text will be shown
$orderField = 'orderid';		// the field that will hold the sort order for the row


// formatting for the list - using a ul as a container and li tags for list items
$sortableTag = 'li';			// the type of tag that should be sortable
$listFormat = '<ul id="sortableList">%s</ul>';	// argument is the contents of the list
$listItemFormat = '<li id="item_%s">%s</li>';  // two arguments are the idField and the displayField

// formatting for the list - using a div as the container and divs as the list items
// $sortableTag = 'div';
// $listFormat = '<div id="sortableList">%s</div>';	// argument is the contents of the list
// $listItemFormat = '<div id="item_%s">%s</div>';  // two arguments are the idField and the displayField

// formatting for the list - using a div as the container and images as the list items
// $sortableTag = 'img';
// $listFormat = '<div id="sortableList">%s</div>';	// argument is the contents of the list
// $listItemFormat = '<img id="item_%s" src="images/%s">';  // two arguments are the idField and the displayField


//
// No need to change any of the sections below
//


$sortableLists->addList('sortableList','sortableListOrder',$sortableTag);
$sortableLists->debug = false;

if (isset($_POST['sortableListsSubmitted'])) {
	$orderArray = SLLists::getOrderArray($_POST['sortableListOrder'],'sortableList');
	foreach($orderArray as $item) {
		$sql = "UPDATE $dbTable set $orderField=".$item['order']." WHERE $idField=".$item['element'];
		$db->query($sql);
	}
}

$sql = "SELECT $idField, $displayField, $orderField from $dbTable order by $orderField";
$recordSet = $db->query($sql);
$listArray = array();
while ($record = mysqli_fetch_assoc($recordSet)) {
	$listArray[] = sprintf($listItemFormat,$record[$idField],$record[$displayField]);
}
mysqli_free_result($recordSet);
$listHTML = implode("\n",$listArray);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>Test DB Example for Sortable Lists</title>
	<?php
	$sortableLists->printTopJS();
	?>
<style>
body,div,li {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
}
ul#sortableList {
	list-style-type: none;
	padding: 0px;
	margin: 0px;
	width: 300px;
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
	width: 400px;
}
div#sortableList img {
	cursor: move;
	display: block;
	margin: 5px 0px;
	border: 1px solid #000000;
}
</style>
</head>

<body>
Drag and drop to change the order of the following items then click 'Save' to save the new order:<br><br>
<?php
printf($listFormat, $listHTML);
$sortableLists->printForm($_SERVER['PHP_SELF'], 'POST', 'Save', 'button');
$sortableLists->printBottomJS();
?>
</body>
</html>