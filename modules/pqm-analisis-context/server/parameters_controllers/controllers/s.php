<?php

include_once("utils/file_reader.php");
include_once("parameter_controller.php");

class ParameterController_S extends ParameterController {

	public function loadData($project_id, $device_id, $datablock_id, $scope) {
		$indicators = array("S1", "S2", "S3");
		$magnitudes = array("KVA", "KVA", "KVA");
		
		$contents = $this->getFilesContentByType($datablock_id, "3P4W");
		$content = $contents[$scope];
		$data = PQMFileReader::readParametersInFile($content, $indicators, $magnitudes);
		
		$data["analisis"] = array("main"=>array(
									"chart"=>array("type"=>"multiDatasetStockChart"), 
									"events"=>array("max", "min"), 
									"indicators"=>$indicators));
		
		return $data;
	}
}

?>
