<?php
require('../lr/config.php');
// connect to local database 'test' on localhost
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

echo "<select id=\"mbrSelect\" name=\"newMbr\" onChange=\"setMbrButtonMbr();\">";
echo "		<option value=\"0\">-- Select Member to Add --</option>\n";
$qry = 'SELECT memberID, CONCAT(mbrFirstName," ",mbrLastName) AS mbrName FROM members WHERE mbrStatus="A" AND CONCAT(",",roleArray,",") LIKE "%,'.$_REQUEST["id"].',%" ORDER BY mbrLastName,mbrFirstName';
$results = $db->query($qry);
while ($results && $row = mysqli_fetch_array($results)) {
	echo "		<option value=\"".$row["memberID"]."\">".$row["mbrName"]."</option>";
}
echo "</select>";
exit;
?>