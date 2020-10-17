<?php
/**
* Transpose musical notes/keys
**/
class Transposer
{
	/**
	* @var string "flats" or "sharps"
	**/
	private $type = 'flats';
	private $chords = array("C" => array("C", "Db", "D", "Eb", "E", "F", "F#", "G", "Ab", "A", "Bb", "B"), 
		"C#" => array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B"),
		"Db" => array("C", "Db", "D", "Eb", "E", "F", "Gb", "G", "Ab", "A", "Bb", "B"),
		"D" => array("C", "C#", "D", "Eb", "E", "F", "F#", "G", "G#", "A", "Bb", "B"), 
		"D#" => array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B"),	// not a common key sig
		"Eb" => array("C", "Db", "D", "Eb", "E", "F", "Gb", "G", "Ab", "A", "Bb", "B"),
		"E" => array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B"),
		"F" => array("C", "Db", "D", "Eb", "E", "F", "Gb", "G", "Ab", "A", "Bb", "B"),
		"F#" => array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B"),
		"Gb" => array("C", "Db", "D", "Eb", "E", "F", "Gb", "G", "Ab", "A", "Bb", "B"),
		"G" => array("C", "C#", "D", "D#", "E", "F", "F#", "G", "Ab", "A", "Bb", "B"),
		"G#" => array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B"), 
		"Ab" => array("C", "Db", "D", "Eb", "E", "F", "Gb", "G", "Ab", "A", "Bb", "B"),
		"A" => array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "Bb", "B"),
		"A#" => array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B"),	// not a common key sig
		"Bb" => array("C", "Db", "D", "Eb", "E", "F", "Gb", "G", "Ab", "A", "Bb", "B"),
		"B" => array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B"),
		"Am" => array("C", "Db", "D", "Eb", "E", "F", "F#", "G#", "Ab", "A", "Bb", "B"),
		"A#m" => array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B"),
		"Bbm" => array("C", "Db", "D", "Eb", "E", "F", "Gb", "G", "Ab", "A", "Bb", "B"),
		"Bm" => array("C", "C#", "D", "Eb", "E", "F", "F#", "G", "G#", "A", "A#", "B"),
		"Cm" => array("C", "Db", "D", "Eb", "E", "F", "Gb", "G", "Ab", "A", "Bb", "B"),
		"C#m" => array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B"),
		"Dbm" => array("C", "Db", "D", "Eb", "E", "F", "Gb", "G", "Ab", "A", "Bb", "B"),
		"Dm" => array("C", "C#", "D", "Eb", "E", "F", "Gb", "G", "Ab", "A", "Bb", "B"),
		"D#m" => array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B"),
		"Ebm" => array("C", "Db", "D", "Eb", "E", "F", "Gb", "G", "Ab", "A", "Bb", "B"),
		"Em" => array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B"),
		"Fm" => array("C", "Db", "D", "Eb", "E", "F", "Gb", "G", "Ab", "A", "Bb", "B"),
		"F#m" => array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "Bb", "B"),
		"Gbm" => array("C", "Db", "D", "Eb", "E", "F", "Gb", "G", "Ab", "A", "Bb", "B"),
		"Gm" => array("C", "C#", "D", "Eb", "E", "F", "Gb", "F#", "Ab", "A", "Bb", "B"),
		"G#m" => array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B"),
		"Abm" => array("C", "Db", "D", "Eb", "E", "F", "Gb", "G", "Ab", "A", "Bb", "B")) ;
	
	// got B#, Cb, E# and Fb for completeness and easier parsing of a chord. Never expect to use them
	private $chordPosition = array("B#"=>0, "C"=>0, "C#"=>1, "Db"=>1, "D"=>2, "D#"=>3, "Eb"=>3, "E"=>4, "Fb"=>4, "E#"=>5, "F"=>5, 
			"F#"=>6, "Gb"=>6, "G"=>7, "G#"=>8, "Ab"=>8, "A"=>9, "A#"=>10, "Bb"=>10, "B"=>11, "Cb"=>11) ;
	
	private $simpleTranspose = array("Db"=>"C#", "C#"=>"Db", "Eb"=>"D#", "D#"=>"Eb", "Gb"=>"F#", "F#"=>"Gb", "Ab"=>"G#", "G#"=>"Ab", "Bb"=>"A#", "A#"=>"Bb") ;
	private $fullEnharmonicSwap =  array("Db"=>"C#", "C#"=>"Db", "Eb"=>"D#", "D#"=>"Eb", "Gb"=>"F#", "F#"=>"Gb", "Ab"=>"G#", "G#"=>"Ab", "Bb"=>"A#", "A#"=>"Bb","C"=>"B#","B"=>"Cb","F"=>"E#","E"=>"Fb") ;

	private $strict = FALSE;


	function rootChordValue($t)	// given text $t, return the chord at the start of it
	{
		global $strict ;
		// fist test strict conditions if we have $strict set
		if ($strict && preg_match("/^\s*([A-G][#b]?) ?($|M|maj|m|min|dim|dom|sus|aug|add|7|9|11|13|b5|b11|b13|\+4)/", $t, $m))
			return $m[1] ;
		if ($strict) return false ;
		if (!preg_match("/^\s*([A-G][#b]?)/", $t, $m))
			return false;	// couldn't find a chord
		else return $m[1] ;
	}
	
	function baseNote($t)	// given text $t, return a root note if there is one. Root notes are defined in chords of the form
	//	chord/root  eg A7/C#, C/G, 
	{
		if (!preg_match("/\/([A-G][#b]?)$/", $t, $m))
			return false ; 	// couldn't find a base note
		else return $m[1] ;
	}

	/**
	* Transpose a note
	* @return string - transposed chord
	* @param string - chord to transpose
	* @param string - transpose key - ie. "C", "Eb", "F#", etc.
	* @param int 	- transposeValue - Half-steps to be trasponsed, -11 to 11
	**/
	public function transpose($t, $transposeKey, $transposeValue) {
		$aChord = explode("[",$t);
		$rtn = "";
		$s=0;
		$e=0;
		$found = true;
		while($found) {
			$s = strpos($t,"[",$e);
			if($s === FALSE) {
				$found = false;
			} else {
				$s++;
				$e = strpos($t,"]",$s);
				if($e === FALSE) {
					$found = false;
				} else {
					$ot = "[".substr($t,$s,$e-$s)."]";
					$t = substr_replace($t,$this->transposeChord(substr($t,$s,$e-$s), $transposeKey, $transposeValue),$s,$e-$s);
				}
			}
		}
		return $t;
	}
	
	function transposeChord($t, $transposeKey, $transposeValue) {
		if (!($bcv = $this->rootChordValue($t)))
			return "No chord passed in";	// couldn't find a chord
		// okay, if we are doing a simple transpose (trans 0, no transposeKey set) and is enharmonic
		if ($transposeValue == 0 && $transposeKey=="") {
			if (isset($this->simpleTranspose[$bcv])) 
				$t = preg_replace("/^(\s*)([A-G][#b]?)/", "\$1".$this->simpleTranspose[$bcv], $t, 1) ;
			if (($b = $this->baseNote($t)) && isset($this->simpleTranspose[$b]))	// and transpose base note if it is there and needs it
				$t = preg_replace("/\/([A-G][#b]?)$/", "/".$this->simpleTranspose[$b], $t, 1) ;
			return $t;
		}
		// okay, we're doing a transposition according to $transposeKey
		$semitones = ($this->chordPosition[$bcv] + $transposeValue + 12) % 12 ;
		$transposeKey = $transposeKey==""?"C":$transposeKey;
		$newC = $this->chords[$transposeKey][$semitones] ;
		$t = preg_replace("/^(\s*)([A-G][#b]?)/", "\$1".$newC, $t, 1) ;
		if ($b = $this->baseNote($t)) {
			$semitones = ($this->chordPosition[$b] + $transposeValue + 12) % 12 ;
			$newB = $this->chords[$transposeKey][$semitones] ;		// work out the new base, and try and match chord for # and b if necessary
			if ((preg_match("/#/", $newC) && !preg_match("/#/", $newB)) || (preg_match("/b/", $newC) && !preg_match("/b/", $newB)))
				if (isset($this->fullEnharmonicSwap[$newB]))
					$newB = $this->fullEnharmonicSwap[$newB] ;
			$t = preg_replace("/\/([A-G][#b]?)$/", "/".$newB, $t, 1) ;
		}
		return $t;
	}
}

// TEST:
/* try
{
   $tr = new Transposer();
   echo "<pre>";
   foreach(array('flats', 'sharps') as $fs)
   {
      $tr->setType($fs);
      echo "#### $fs ####\n";
      foreach(array('A', 'Bb', 'B', 'C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab') as $key)
      {
         echo "  $key:\n";
         for($n = -11; $n < 12; $n++)
         {
            printf("    %3d = %s\n", $n, $tr->transpose($key, $n));
         }
      }
   }
   echo "</pre>";
}
catch(Exception $e)
{
   echo "<pre>".print_r($e,1)."</pre>";
} */
?>