<?php 
/*******************************************************************
 * ofcSongRating.php
 * Song Rating - Chart
 *******************************************************************/
//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter();
session_start();

echo '<script src="../vendor/ejdamm/chart.js-php/js/Chart.min.js"> </script>';
echo '<script src="../vendor/ejdamm/chart.js-php/js/driver.js"> </script>';

require('lr/config.php');



require 'vendor/autoload.php';
use ChartJs\ChartJS;



//Setup database connection
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_connect_error());

$isSel = isset($_REQUEST["id"])?" WHERE memberID=".$_REQUEST["id"]:"";
$q = "SELECT addedBy, songName, songrating.songID as sngID, songArtist, songAlbum, IFNULL(memberID,0) AS newRating, ratingLyrics, ratingMusic, ratingSing, ratingOverall FROM songrating LEFT JOIN songratingbymember on songrating.songID=songratingbymember.songID$isSel ORDER BY songrating.songID";
$resSong = $db->query($q);

// include 'classes/OFC/php-ofc-library/open-flash-chart.php';


// $data = [
	// 'labels' => [],
	// 'datasets' => [] // use addDataset()
	// [
	//		'data' => [8, 7, 8, 9, 6],
	//		'backgroundColor' => '#f2b21a',
	//		'borderColor' => '#e5801d',
	//		'label' => 'Song'
	// 		'yAxisID' => 'Song'
	// ]
// ];
$colors =   ['backgroundColor' => ['blue', 'purple', 'red', 'black', 'brown', 'pink', 'green']] ;
$colorlist =   ['blue', 'purple', 'red', 'black', 'brown', 'pink', 'green'] ;
$options = ['responsive' => false, 'legend' => ['position' => 'right'], 
			'scales' => ['yAxes' => [['ticks' => ['beginAtZero' => true]]]]];
$attributes = ['id' => 'Songs', 'width' => 800, 'height' => 500];





if($resSong && (mysqli_num_rows($resSong) > 0)){
	$aSongs = array();
	$aLyrics = array();
	$aMusic = array();
	$aSing = array();
	$aOverall = array();
	$aVotes = array();
	$oldSongID = 0;
	$oldSongName = "";
	$oldSongArtist = "";
	while($dbsong=mysqli_fetch_array($resSong)) {
		if($oldSongID == 0) $oldSongID = $dbsong["sngID"];
		if ($oldSongID != $dbsong["sngID"]) {

			// $aXLabel[] = new x_axis_label("$oldSongName : $oldSongArtist",'#000000',10,310);
			$aXLabel[] = $oldSongName;
			$aXArtist[] = $oldSongArtist;
			$aSongs[] = $oldSongName;
			if($Votes>0) {
				$aLyrics[] = round($rLyrics/$Votes);
				$aMusic[] = round($rMusic/$Votes);
				$aSing[] = round($rSing/$Votes);
				$aOverall[] = round($rOverall/$Votes);
				$aVotes[] = $Votes;
			} else {
				$aLyrics[] = 0;
				$aMusic[] = 0;
				$aSing[] = 0;
				$aOverall[] = 0;
				$aVotes[] = 0;
			}
			$rLyrics = 0;
			$rMusic = 0;
			$rSing = 0;
			$rOverall = 0;
			$Votes = 0;
		}
		$oldSongName = $dbsong["songName"];
		$oldSongArtist = $dbsong["songArtist"];
		$rLyrics += $dbsong["ratingLyrics"];
		$rMusic += $dbsong["ratingMusic"];
		$rSing += $dbsong["ratingSing"];
		$rOverall += $dbsong["ratingOverall"];
		$Votes++;
		$oldSongID = $dbsong["sngID"];
	}
	// $aXLabel[] = new x_axis_label($oldSongName."\n".$oldSongArtist,'#000000',10,310);
	$aXLabel[] = $oldSongName;
	$aXArtist[] = $oldSongArtist;
	$aSongs[] = $oldSongName;
	if($Votes>0) {
		$aLyrics[] = round($rLyrics/$Votes);
		$aMusic[] = round($rMusic/$Votes);
		$aSing[] = round($rSing/$Votes);
		$aOverall[] = round($rOverall/$Votes);
		$aVotes[] = $Votes;
	} else {
		$aLyrics[] = 0;
		$aMusic[] = 0;
		$aSing[] = 0;
		$aOverall[] = 0;
		$aVotes[] = 0;
	}
}
// $title = new title( "New Song Rating as of: ".date("D M d Y") );

$c = count($aXLabel);


$data['labels'][0] = 'Lyrics';
$data['labels'][1] = 'Music';
$data['labels'][2] = 'Sing';
$data['labels'][3] = 'Overall';
// $data['labels'][4] = 'Votes';

$Line = new ChartJS('line', $data, $options, $attributes);

$c = count($aXLabel);
for ($i=0 ; $i< $c; $i++ ) {


	$datasets['data'][0] = $aLyrics[$i];
	$datasets['data'][1] = $aMusic[$i];
	$datasets['data'][2] = $aSing[$i];
	$datasets['data'][3] = $aOverall[$i];
//	$datasets['data'][4] = $aVotes[$i];
	$datasets['label']  = "$aXLabel[$i] - $aXArtist[$i] ($aVotes[$i])";
	
 	$datasets['fill'] = 'false';
	$datasets['backgroundColor'] = $colorlist[$i % count($colorlist)];
	$datasets['borderColor'] = $colorlist[$i % count($colorlist)];
	$Line->addDataset($datasets);
}


/* 
// Lyrics
$hL = new bar_glass();
$hL->colour('#003CFF');
$hL->key('Lyrics', 12);
$hL->set_values( $aLyrics );
// Music
$hM = new bar_glass();
$hM->colour('#00FF00' );
$hM->key('Music', 12);
$hM->set_values( $aMusic );
// Sing
$hS = new bar_glass();
$hS->colour('#BC7118' );
$hS->key('Singability', 12);
$hS->set_values( $aSing );
// Overall
$hO = new bar_glass();
$hO->colour('#FF0018' );
$hO->key('Overall', 12);
$hO->set_values( $aOverall );
// Votes
if($isSel=="") {
	$hV = new bar_glass();
	$hV->colour('#000000' );
	$hV->key('Votes', 12);
	$hV->set_values( $aVotes );
}

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->set_bg_colour( '#ffffff' );
$chart->add_element( $hL );
$chart->add_element( $hM );
$chart->add_element( $hS );
$chart->add_element( $hO );
if($isSel=="") {
	$chart->add_element( $hV );
}

$y = new y_axis();
$yMax = max($aVotes)>10?max($aVotes):10;
$y->set_range(0,$yMax,1);
$chart->set_y_axis($y);

$xl = new x_axis_labels();
$xl->set_labels( $aXLabel );
$x = new x_axis();
$x->set_labels( $xl );
$chart->set_x_axis( $x );

*/


		echo $Line;
		?>
		<script>
			(function() {
				loadChartJsPhp();
			})();
		</script>
 	
	</body>
</html>

