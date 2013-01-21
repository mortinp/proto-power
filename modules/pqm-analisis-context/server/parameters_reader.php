<?php
include_once("parameters_controllers/parameter_controller_locator.php");

class ParameterReader {
	public function getResponseForParameter($project_id, $device_id, $datablock_id, $type, $scope) {
		/*$config = $this->getConfigurations($type);
		$response = array("analisis"=>$config["analisis"],);*/
		
		$controller = ParameterControllerLocator::getParameterController($type);
		$fileData = $controller->loadData($project_id, $device_id, $datablock_id, $scope);
		
		$options = $fileData["options"];
		if($options["TYPE"] == "POWER") {
			$response["selectors"] = $selectors = array("I", "V", "P", "S", "PF", "W_SYS", "VA_SYS", "WH_SYS");
		} else if($options["TYPE"] == "HARMO") {
			$response["selectors"] = $selectors = array("H");
		}
		
		$response["options"] = $options;
		$response["data"] = $fileData["data"];
		$response["analisis"] = $fileData["analisis"];
		if(isset($fileData["reports"]))$response["reports"] = $fileData["reports"];
		return $response;
	}
	
	/*private function getConfigurations($type) {
		if($type == "I") {
			$analisis = array("main"=>array("chart"=>"multiDatasetStockChart", "events"=>array("max", "min"), "indicators"=>array("I1", "I2", "I3")),
								  "unbalance"=>array("chart"=>"multiDatasetStockChart", "events"=>array("max", "min"), "indicators"=>array("unbalance")));
		} else if($type == "V"){
			$analisis = array("main"=>array("chart"=>"multiDatasetStockChart", "events"=>array("max", "min"), "indicators"=>array("V12", "V23", "V31")),
								  "unbalance"=>array("chart"=>"multiDatasetStockChart", "events"=>array("max", "min"), "indicators"=>array("unbalance")));
		} else if($type == "P") {
			$analisis = array("main"=>array("chart"=>"multiDatasetStockChart", "events"=>array("max", "min"), "indicators"=>array("P1", "P2", "P3")),
								  "unbalance"=>array("chart"=>"multiDatasetStockChart", "events"=>array("max", "min"), "indicators"=>array("unbalance")));
		} else if($type == "S") {
			$analisis = array("main"=>array("chart"=>"multiDatasetStockChart", "events"=>array("max", "min"), "indicators"=>array("S1", "S2", "S3")));
		} else if($type == "PF") {
			$analisis = array("main"=>array("chart"=>"multiDatasetStockChart", "events"=>array("max", "min"), "indicators"=>array("PF_SYS", "PFH_SYS", "PF_SYS_NO_HARMO")));
		} else if($type == "W_SYS") {
			$analisis = array("main"=>array("chart"=>"multiDatasetStockChart", "events"=>array("max", "min"), "indicators"=>array("W_SYS")));
		} else if($type == "VA_SYS") {
			$analisis = array("main"=>array("chart"=>"multiDatasetStockChart", "events"=>array("max", "min"), "indicators"=>array("VA_SYS")));
		} else if($type == "WH_SYS") {
			$analisis = array("main"=>array("chart"=>"multiDatasetStockChart", "events"=>array(), "indicators"=>array("WH_SYS")));
		} else if($type == "H") {
			$analisis = array("main"=>array("chart"=>"multiGraphSerialChart", "events"=>array(), "indicators"=>array("1", "3", "5", "7", "9", "11", "13", "15")),
								  "THD"=>array("chart"=>"multiDatasetStockChart", "events"=>array("max", "min"), "indicators"=>array("THD-F")));
		}
		
		return array("analisis"=>$analisis,);
	}*/
}

?>
