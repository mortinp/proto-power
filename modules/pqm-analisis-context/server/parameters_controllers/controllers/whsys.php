<?php

include_once("utils/file_reader.php");
include_once("parameter_controller.php");

class ParameterController_WHSYS extends ParameterController {

	public function loadData($project_id, $device_id, $datablock_id, $scope) {
		$indicators = array("WH_SYS");
		$magnitudes = array("KWH|MWH(1000)");
		
		$contents = $this->getFilesContentByType($datablock_id, "3P4W");
		$content = $contents[$scope];
		$data = PQMFileReader::readParametersInFile($content, $indicators, $magnitudes);
		
		$data["analisis"] = array("main"=>array(
									"chart"=>array("type"=>"multiDatasetStockChart", "options"=>array("fillAlphas"=>0.3)), 
									"events"=>array(), 
									"indicators"=>$indicators));
		
		return $data;
	}
}

?>
