<?php

class PQMFilesReaper {

	/**
	 @param $fileDefinitions The array version of a json object with the following structure:
	 {{filePath: "path/to/file1", fromIndicators: [ind1, ind2,...], toIndicators: [ind1_1, ind1_2,...] magnitudes: [M1, M2,...]}, 
	  {filePath: "path/to/file2", fromIndicators: [ind1, ind2,...], magnitudes: [M1, M2,...]}}
	  
	  filePath: The path to the file to be read.
	  fromIndicators: The names of the fields to be retrieved as thay appear in the file, e.g. [I1, I2, V23, PF1].
	  toIndicators: The names of the field as you want them to appear in the dataset. If this entry is not found, the fields
					are set with the same name as in fromIndicators.
	  magnitudes: The magnitudes of each indicator.
	*/
	public static function reapAs($fileDefinitions) {
		// Get the data of each file header
		$definitionResult = array();
		foreach($fileDefinitions as $i=>$definitionObj) {
			// Get the data of current definition object
			$fileContent = $definitionObj["fileContent"];
			$indicators = $definitionObj["fromIndicators"];
			$toIndicators = isset($definitionObj["toIndicators"])?$definitionObj["toIndicators"]:NULL;
			$magnitudes = $definitionObj["magnitudes"];
			
			// Create file handler and read header
			$line = 0;
			while(true) {
				if($line < count($fileContent) && $s = $fileContent[$line] ) {
					$line++;
					if(PQMFilesReaper::processHeaderLine($s, $indicators, $indexes, $options)) {
						break;
					}
				}
			}
			$response["options"] = $options;
			
			// Create a result of the current definition to be used later
			$definitionResult[$i]["fileContent"] = $fileContent;
			$definitionResult[$i]["options"] = $options;
			$definitionResult[$i]["indexes"] = $indexes;
			$definitionResult[$i]["fromIndicators"] = $indicators;
			$definitionResult[$i]["toIndicators"] = $toIndicators;
			$definitionResult[$i]["magnitudes"] = $magnitudes;
		}
		
		// Mix each file's data in one dataset
		foreach($definitionResult as $k=>$def) {
			// Get the data of the current definition result
			$fileContent = $def["fileContent"];
			$options = $def["options"];
			$indexes = $def["indexes"];
			$indicators = $parametersNames = $def["fromIndicators"];
			$toIndicators = $def["toIndicators"];
			$magnitudes = $def["magnitudes"];
			if($toIndicators) $parametersNames = $toIndicators;
			
			// Add entries in the dataset
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