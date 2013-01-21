<?php

class PQMFileReader {
	public static function readParametersInFile(array $fileContent, $parametersNames, $magnitudes) {
		// Add DATE and TIME entries
		$parametersNames = array_merge(array("DATE", "TIME"), $parametersNames);
		$magnitudes = array_merge(array("", ""), $magnitudes);
		$line = 0;
		while(true) {
			if($line < count($fileContent) && $s = $fileContent[$line] ) {
				$line++;
				if(PQMFileReader::processHeaderLine($s, $parametersNames, $indexes, $options)) {
					break;
				}
			}
		}
		$response["options"] = $options;

		$nentry = 0;
		while($line < count($fileContent) && $s = $fileContent[$line]) {
			$line++;
			$words = preg_split('/\s+/',$s,-1,PREG_SPLIT_NO_EMPTY);
			// Calculate parameters offset (Harmonics file has an offset between the header and the actual parameter column)
			$off = 0;
			if($options["TYPE"] == "HARMO") $off = 2;
			foreach($parametersNames as $i=>$ind) {
				// Make some fixes
				$offset = 0;// this is a FIX for Harmonics file
				if($i > $off) $offset = $off;
				
				// Get the value (and make some fixes)
				$word_to_value = str_replace(",", ".", $words[$indexes[$i] + $offset]); // Fix comma/point discordance
				
				// Split value and magnitude
				$n = 0;
				$val = "";
				$magn = "";
				if($magnitudes[$i] == "") $val = $word_to_value; // No need to split
				else {
					while(is_numeric($word_to_value[$n]) || $word_to_value[$n] == "." || $n == count($word_to_value)) {
						$val .= $word_to_value[$n];
						$n++;
					}
					$magn = substr($word_to_value ,$n);
				}
			
				// Deal with magnitudes conversions
				$conversionFactor = 1;
				$mag = explode("|", $magnitudes[$i]);
				if(count($mag) > 1) {
					// Find out which magnitude the current value is using
					foreach($mag as $m) {
						if(substr($m, 0, strlen($magn)) == $magn && strlen($m) > strlen($magn) && $m[strlen($magn)] == "(") {
							$conversionFactor = floatval(substr($m, strlen($magn) + 1, -1));
						}
					}
				}
			
				if(is_numeric($val)) $val = floatval($val) * $conversionFactor;
				
				$response["data"][$nentry][$ind] = $val;
			}
			$nentry++;
		}

		return $response;
	}
	
	private function processHeaderLine($line, $parametersNames, &$indexes, &$options/*, &$title*/) {
		static $nline = 0;
		$is_header = false;
		if($nline == 0) { // Read some configrations
			//$title = $line;
			$words = preg_split('/\s+/',$line,-1,PREG_SPLIT_NO_EMPTY);
			for($i=0,$l=count($words);$i<$l;$i++) {
				$parts = explode("=", $words[$i]);
				if($parts[0] == "SEC") {
					if((int)$parts[1] >= 60) $options["MINTIME"] = "mm";
					else $options["MINTIME"] = "ss";
				} else if($parts[0] == "3P4W") {
					$options["TYPE"] = "POWER";
				} else if($parts[0] == "HARMO") {
					$options["TYPE"] = "HARMO";
				}
			}
			$is_header =  false;
		} elseif($nline == 1) {
			$words = preg_split('/\s+/',$line,-1,PREG_SPLIT_NO_EMPTY);
			for($i=0,$l=count($words);$i<$l;$i++) {
				foreach($parametersNames as $name) {
					if($words[$i] == $name) $indexes[] = $i;
				}
			}
			$is_header =  true;
		}
		$is_header?$nline=0:$nline++;
		return $is_header;
	}
}

?>
