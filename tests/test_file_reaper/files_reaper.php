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
	public static function reap($fileDefinitions) {
		// Get the data of each file header
		$definitionResult = array();
		foreach($fileDefinitions as $i=>$definitionObj) {
			$filePath = $definitionObj["filePath"];
			$indicators = $definitionObj["fromIndicators"];
			$toIndicators = isset($definitionObj["toIndicators"])?$definitionObj["toIndicators"]:NULL;
			$magnitudes = $definitionObj["magnitudes"];
			
			$fh = fopen($_SERVER["DOCUMENT_ROOT"]."/$filePath", "r") or die("could not open.");
			$nline = 0;
			$indexes = array(); //empty indexes
			$options = array(); //empty options
			while(true) {
				if($s = fgets($fh,1048576) ) {
					if(PQMFilesReaper::processHeaderLine($nline, $s, $indicators, $indexes, $options)) {
						$response["options"] = $options;
						break;
					}
					$nline++;
				}
			}
			$definitionResult[$i]["handler"] = $fh;
			$definitionResult[$i]["options"] = $options;
			$definitionResult[$i]["indexes"] = $indexes;
			$definitionResult[$i]["fromIndicators"] = $indicators;
			$definitionResult[$i]["toIndicators"] = $toIndicators;
			$definitionResult[$i]["magnitudes"] = $magnitudes;
		}
		
		// Mix each file's data in one dataset
		foreach($definitionResult as $k=>$def) {
			$fh = $def["handler"];
			$options = $def["options"];
			$indexes = $def["indexes"];
			$indicators = $datasetIndicators = $def["fromIndicators"];
			$toIndicators = $def["toIndicators"];
			$magnitudes = $def["magnitudes"];
			
			if($toIndicators) $datasetIndicators = $toIndicators;
			$nentry = 0;
			while($s = fgets($fh,1048576)) {
				$words = preg_split('/\s+/',$s,-1,PREG_SPLIT_NO_EMPTY);
				// Calculate parameters offset (Harmonics file has an offset between the header and the actual parameter column)
				$off = 0;
				if($options["TYPE"] == "HARMO") $off = 2;
				foreach($datasetIndicators as $i=>$ind) {
					$offset = 0;// this is a FIX for Harmonics file
					if($i > $off) $offset = $off;
					$word_to_value = str_replace(",", ".", $words[$indexes[$i] + $offset]);
					$ntrunc = strlen($magnitudes[$i])==0?1000:-strlen($magnitudes[$i]);
					$response["data"][$nentry][$ind] = substr($word_to_value ,0, $ntrunc);
				}
				$nentry++;
			}
			fclose($fh) or die("could not close.");
		}
		
		return $response;
	}
	
	private static function processHeaderLine($nline, $line, $parametersNames, &$indexes, &$options/*, &$title*/) {
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
		} elseif($nline == 1) { // Find each indicator's column index
			$words = preg_split('/\s+/',$line,-1,PREG_SPLIT_NO_EMPTY);
			for($i=0,$l=count($words);$i<$l;$i++) {
				foreach($parametersNames as $name) {
					if($words[$i] == $name) $indexes[] = $i;
				}
			}
			$is_header =  true;
		}
		$nline++;
		return $is_header;
	}
}

?>