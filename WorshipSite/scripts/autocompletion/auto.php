<?php
$dbhost = 'localhost'; //host. 99% it is localhost
$dbuser = 'rakWorship'; //username
$dbpass = 'cavalier'; //password
$database = 'rakWorship'; // your database name

//Will Store all the state names retrieved from the database.
$suggestions = array();

/*Connection to database*/
$conn = mysqli_connect($dbhost,$dbuser,$dbpass, $database);
if (!$conn) die(mysqli_error());

/*Query to the states table. It retrieves all the names of the states*/
$sql = "SELECT songName FROM songs";

$res = $db->query($sql) or die(mysqli_error());
if(mysqli_num_rows($res)>0) {
	while($row = mysqli_fetch_assoc($res)) {
	array_push($suggestions, $row['songName']);
	}
}

// Frees up all the resources consumed by mysql connection.
mysqli_free_result($res);
mysqli_close($conn);

// if the user types in anything in the textbox, $value will store that value via $_REQUEST['input name'] method
// in our case the input name = state_input ( The textbox from the HTML page )
$value = isset($_REQUEST['state_input']) ? $_REQUEST['state_input'] : "";

//Will store all the input values that matches with the database suggestions.
$matched = array();

// Foreach statement, goes through each value of the array specified individually.
foreach ($suggestions as $suggestion) {
if (stripos($suggestion, $value) !== FALSE) {
$match = preg_replace('/' .$value. '/i',"<strong>$0</strong>", $suggestion, 1);
$matched[] = "<li>$match</li>";
}
}

//Print all the suggestions. This is returned back to Ajax.Autocompleter.
echo "<ul>".join("", $matched)."</ul>";

?>