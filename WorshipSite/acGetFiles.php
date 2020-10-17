<?php
require('lr/config.php');

$dir = $baseDir . "UserFiles/File/";



//Will Store all the members retrieved from the database.
$suggestions = array();

$songName = isset($_REQUEST['Name']) ? $_REQUEST['Name'] : "";

	// open pointer to directory and read list of files
    $d = @dir($dir) or die("getFileList: Failed opening directory {$dir} for reading");
    while(FALSE !== ($entry = $d->read())) {
      // skip hidden files
      if($entry{0} == "." || $entry{0} == "..") continue;
      if(is_dir("{$dir}{$entry}")) {
      	// TODO need to re sub-directories 
//        $retval[] = [
//          'name' => "{$dir}{$entry}/",
//          'type' => filetype("{$dir}{$entry}"),
//        ];
      } elseif(is_readable("{$dir}{$entry}")) {
        
          $fname = "{$dir}{$entry}";
          $name = preg_match('/(1[0-9]{9,9})(.+)\.([A-Za-z][A-Za-z0-9]{2,3})/i', $fname ,  $matches);
          $name = preg_match('/(1[0-9]{9,9}).+' . $songName .'/i', $fname );
        }
        if ($name) { 
        	$suggestions[$matches[2]] = [ 
        		'name' => $fname, 
        		'desc' => $matches[2], 
        		'song' => $matches[3] ];
			}
	
    }
    $d->close();
echo "<ul>";
sort($suggestions);
foreach ($suggestions as $result) {
	echo "<li id=\"". $result['name']."\"" . "\">" . $result['desc'] . "</li>";
}
echo "</ul>\n";

?>