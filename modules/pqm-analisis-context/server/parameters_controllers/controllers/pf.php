<?php

include_once("utils/file_reader.php");
include_once("utils/files_reaper.php");
include_once("utils/parameters_utils.php");
include_once("parameter_controller.php");

class ParameterController_PF extends ParameterController {

	public function loadData($project_id, $device_id, $datablock_id, $scope) {
		// 
		$indicators = array("PF_SYS", "PFH_SYS");
		$magnitudes = array("", "H");
		
		$contents = $this->getFilesContentByType($datablock_id, "3P4W");
		$powerContent = $contents[$scope];
		$data = PQMFileReader::readParametersInFile($powerContent, $indicators, $magnitudes);
		
		$definitions = array();
		$definitions[0] = array("fileContent"=>$powerContent, 
							   "fromIndicators"=>array(), 
							   "magnitudes"=>array());
		
		// Calculate data from multiple files
		$harmoContents = $this->getFilesContentByType($datablock_id, "HARMO");
		
		// Validate the existence of all the i-harmo files for each phase
		$harmoFileExist = false;
		$indicators_THD = array();
		if(isset($harmoContents["I1"])) {
			// Add an entry to find PF1 in power file
			$definitions[0]["fromIndicators"][] = "PF1";
			$definitions[0]["magnitudes"][] = "";
			
			// Add an entry to find THD-F1 in harmo file
			$indicators_THD[] = "THD-F1";
			$definitions[] = array("fileContent"=>$harmoContents["I1"], 
								   "fromIndicators"=>array("THD-F"), 
								   "toIndicators"=>array("THD-F1"), 
								   "magnitudes"=>array("%"));
			
			$harmoFileExist = true;
		} 
		if(isset($harmoContents["I2"])) {
			// Add an entry to find PF2 in power file
			$definitions[0]["fromIndicators"][] = "PF2";
			$definitions[0]["magnitudes"][] = "";
			
			// Add an entry to find THD-F2 in harmo file
			$indicators_THD[] = "THD-F2";
			$definitions[] = array("fileContent"=>$harmoContents["I2"], 
								   "fromIndicators"=>array("THD-F"), 
								   "toIndicators"=>array("THD-F2"), 
								   "magnitudes"=>array("%"));
			
			$harmoFileExist = true;
		} 
		if(isset($harmoContents["I3"])) {
			// Add an entry to find PF3 in power file
			$definitions[0]["fromIndicators"][] = "PF3";
			$definitions[0]["magnitudes"][] = "";
			
			// Add an entry to find THD-F3 in harmo file
			$indicators_THD[] = "THD-F3";
			$definitions[] = array("fileContent"=>$harmoContents["I3"], 
								   "fromIndicators"=>array("THD-F"), 
								   "toIndicators"=>array("THD-F3"), 
								   "magnitudes"=>array("%"));
			
			$harmoFileExist = true;
		}

		// Add 'PF_SYS_NO_HARMO' entry to the data objects (if possible)
		if($harmoFileExist) {
			$dataNoHarmo = PQMFilesReaper::reapAs($definitions);
			
			if($this->dataCompatible($data["data"], $dataNoHarmo["data"], $definitions[0]["fromIndicators"], $indicators_THD)) {
				// Add 'PF_SYS_NO_HARMO' entry to the data objects
				ParametersUtils::addPowerFactorNoHarmoEntry($data["data"], $dataNoHarmo["data"], 
														$definitions[0]["fromIndicators"], $indicators_THD);
			
				$indicators[] = "PF_SYS_NO_HARMO";
			}
		}
		
		$data["analisis"] = array("main"=>array(
									"chart"=>array("type"=>"multiDatasetStockChart"), 
									"events"=>array("max", "min"), 
									"indicators"=>$indicators));
		
		return $data;
	}
	
	private function dataCompatible($data1, $data2, $indicators_PF, $indicators_THD) {
		// Check if all entries contain all indicators
		foreach($data1 as $i=>$obj) {
			foreach($indicators_PF as $j=>$ind) {
				if(!isset($data2[$i][$indicators_PF[$j]]) ||
					!isset($data2[$i][$indicators_THD[$j]]))
					return false;
			}
		}
		return true;
	}
}

?>
