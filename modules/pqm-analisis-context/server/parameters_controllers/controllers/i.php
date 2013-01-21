<?php

include_once("utils/file_reader.php");
include_once("utils/parameters_utils.php");
include_once("parameter_controller.php");

class ParameterController_I extends ParameterController {

	public function loadData($project_id, $device_id, $datablock_id, $scope) {
		$indicators = array("I1", "I2", "I3");
		$magnitudes = array("A", "A", "A");
		
		$contents = $this->getFilesContentByType($datablock_id, "3P4W");
		$content = $contents[$scope];
		$data = PQMFileReader::readParametersInFile($content, $indicators, $magnitudes);
		
		// Add 'unbalance' entry to the data objects
		ParametersUtils::addUnbalanceEntry($data["data"], $data["data"], $indicators);
		
		$data["analisis"] = array("main"=>array(
									"chart"=>array("type"=>"multiDatasetStockChart"), 
									"events"=>array("max", "min"), 
									"indicators"=>$indicators),
								  "unbalance"=>array(
									"chart"=>array("type"=>"multiDatasetStockChart"), 
									"events"=>array("max", "min"), 
									"indicators"=>array("unbalance")));
		
		return $data;
	}
}

?>
