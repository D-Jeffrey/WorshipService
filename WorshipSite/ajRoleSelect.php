<?php
require('lr/config.php');
// connect to local database 'test' on localhost
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

echo "<select id=\"rscRole\" name=\"rscRole\">";
echo "		<option value=\"0\">-- Any Role --</option>\n";
$qry = 'SELECT roleID, roleDescription FROM roles WHERE typeID = '.$_REQUEST["id"].' ORDER BY roleDescription';
$results = $db->query($qry);
while ($results && $row = mysqli_fetch_array($results)) {
	echo "		<option value=\"".$row["roleID"]."\">".$row["roleDescription"]."</option>";
}
echo "</select>";
exit;
?>