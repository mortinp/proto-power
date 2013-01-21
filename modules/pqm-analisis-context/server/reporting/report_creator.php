<?php

include_once("../parameters_controllers/controllers/utils/files_reaper.php");
include_once("../../../pqm-projects-management/server/pqm_projects_manager.php");

class ReportCreator {

	public function loadReport($project_id, $device_id, $datablock_id) {
		$data = array();
		$hasFiles = false;
		
		// Extract power content
		$powerContents = $this->getFilesContentByType($datablock_id, "3P4W");
		if(isset($powerContents["ALL"])) {
			$definitions = array();
			$definitions[] = array("fileContent"=>$powerContents["ALL"], 
								   "fromIndicators"=>array("I1", "I2", "I3", "V1", "V2", "V3"), 
								   "magnitudes"=>array("A", "A", "A", "V", "V", "V"));
		   $hasFiles = true;
		}
		
		/*// Extract harmo content
		$harmoContents = $this->getFilesContentByType($datablock_id, "HARMO");
		if(isset($harmoContents["I1"])) {
			// Add an entry to find THD-F1 in harmo file
			$definitions[] = array("fileContent"=>$harmoContents["I1"], 
								   "fromIndicators"=>array("1", "3", "5", "7", "9", "11", "13", "15"), 
								   "toIndicators"=>array("1-I1", "3-I1", "5-I1", "7-I1", "9-I1", "11-I1", "13-I1", "15-I1"), 
								   "magnitudes"=>array("", "", "", "", "", "", "", ""));
			
			$hasFiles = true;
		} 
		if(isset($harmoContents["I2"])) {
			// Add an entry to find THD-F2 in harmo file
			$indicators_THD[] = "THD-F2";
			$definitions[] = array("fileContent"=>$harmoContents["I2"], 
								   "fromIndicators"=>array("THD-F"), 
								   "toIndicators"=>array("THD-F2"), 
								   "magnitudes"=>array("%"));
			
			$hasFiles = true;
		} 
		if(isset($harmoContents["I3"])) {
			// Add an entry to find THD-F3 in harmo file
			$indicators_THD[] = "THD-F3";
			$definitions[] = array("fileContent"=>$harmoContents["I3"], 
								   "fromIndicators"=>array("THD-F"), 
								   "toIndicators"=>array("THD-F3"), 
								   "magnitudes"=>array("%"));
			
			$hasFiles = true;
		}*/

		// Reap files
		if($hasFiles) {
			$data = PQMFilesReaper::reapAs($definitions);
		}
		
		$values = $this->calculateValues($data["data"], array("I1", "I2", "I3", "V1", "V2", "V3"));
		
		$response = array(
			"Power Summary"=>array(
				array("type"=>"table-2D-phases", 
					  "phases"=>array("1", "2", "3"),
					  "calculus"=>array("Min", "Max", "Avg"),
					  "rows"=>array("I", "V"),
					  "values"=>$values
				)
			),
		);
		
		return $response;
	}
	
	// TODO: I think I should move this function to PQMProjectsManager
	private function getFilesContentByType($datablock_id, $type) {
		$contents = array();
		
		$proMgr = new PQMProjectsManager();
		$cursor = $proMgr->getFiles(array("type"=>$type, "datablock_id"=>$datablock_id));
		foreach ($cursor as $file) {
			$scope = $file->file["metadata"]["scope"];
			$contents[$scope] = preg_split("/(\r\n|\n|\r)/", $file->getBytes());
		}

		return $contents;
	}
	
	private function calculateValues($dataProvider, $indicators) {
		$values = array();
		foreach($indicators as $i=>$ind) {
			$values[] = $this->findValues($dataProvider, $ind);
		} 
		
		return $values;
	}
	
	private function findValues($dataProvider, $indicator) {
		$min = 1000;
		$max = -1000;
		$avg = 0;
		foreach($dataProvider as $i=>$obj) {
			$avg += floatval($obj[$indicator]);
			if(floatval($obj[$indicator]) < $min) {
				$min = floatval($obj[$indicator]);
			} else if(floatval($obj[$indicator]) > $max) {
				$max = floatval($obj[$indicator]);
			}
		}
		$avg /= count($dataProvider);
		
		return array("indicator"=>$indicator, "Min"=>$min, "Max"=>$max, "Avg"=>$avg);
	}
}

?>
