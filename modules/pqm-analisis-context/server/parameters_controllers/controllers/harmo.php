<?php

include_once("utils/file_reader.php");
include_once("utils/parameters_utils.php");
include_once("parameter_controller.php");
include_once("../../pqm-projects-management/server/pqm_projects_manager.php");

class ParameterController_HARMO extends ParameterController {

	public function loadData($project_id, $device_id, $datablock_id, $scope) {
		$indicators = array("THD-F", "1", "3", "5", "7", "9", "11", "13", "15", "17", "19", "21", "23", "25", "27", "29", "31");
		$magnitudes = array("%", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "");
		
		$contents = $this->getFilesContentByType($datablock_id, "HARMO");
		$content = $contents[$scope];
		$data = PQMFileReader::readParametersInFile($content, $indicators, $magnitudes);
		
		// Include normalization data
		$powerContents = $this->getFilesContentByType($datablock_id, "3P4W");
		$content = $powerContents["ALL"];
		$powerData = PQMFileReader::readParametersInFile($content, array($scope), array("A"));
		
		// Find device in DB
		$proMgr = new PQMProjectsManager();
		$aggreg = $proMgr->getAggregate($project_id, $device_id);
		$result = $aggreg["result"];
		
		$device = $result[0]["device"];
		
		$thresholdTHD = $this->calculateThresholdTHD($device, $powerData["data"], $scope);
		
		$data["analisis"] = array("main"=>array(
									"chart"=>array("type"=>"multiGraphStockChart", "options"=>array("disabled"=>array(0,6,7,8,9,10,11,12,13,14,15,16))), 
									"events"=>array(), 
									"indicators"=>array("1", "3", "5", "7", "9", "11", "13", "15", "17", "19", "21", "23", "25", "27", "29", "31")),
								  "THD"=>array(
									"chart"=>array("type"=>"multiDatasetStockChart"), 
									"events"=>array("max","min"), 
									"indicators"=>array("THD-F"),
									"normalization"=>array("type"=>"threshold-line", "value"=>$thresholdTHD, "label"=>"THD threshold")));
									
		//$data["reports"] = $device["Isc"];
		
		return $data;
	}
	
	private function calculateThresholdTHD($deviceData, $indicatorData, $indicator) {
		if($indicator == "I1" || $indicator == "I2" || $indicator == "I3") return $this->getThresholdTHD_I($deviceData, $indicatorData, $indicator);
		else if($indicator == "V1" || $indicator == "V2" || $indicator == "V3") return $this->getThresholdTHD_V($deviceData, $indicatorData, $indicator);
	}
	
	private function getThresholdTHD_I($deviceData, $indicatorData, $indicator) {
		$Isc = $deviceData["Isc"];
		
		$Irms = 0;
		foreach($indicatorData as $k=>$ind) {
			$Irms += floatval($ind[$indicator]);
		}
		$Irms /= count($indicatorData);
		
		$rate = $Isc/$Irms;
		if($rate < 20) return 5;
		else if($rate >= 20 && $rate < 50) return 8;
		else if($rate >= 50 && $rate < 100) return 12;
		else if($rate >= 100 && $rate < 1000) return 15;
		else if($rate >= 1000) return 20;

		return -1;
	}
	
	private function getThresholdTHD_V($deviceData, $indicatorData, $indicator) {
		$rate = $deviceData["voltage"]/1000;
		if($rate <= 69) return 5;
		else if($rate > 69 && $rate <= 161) return 2.5;
		else if($rate > 161) return 1.5;
		
		return -1;
	}
}

?>
