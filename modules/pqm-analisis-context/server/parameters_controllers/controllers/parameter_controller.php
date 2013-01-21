<?php

include_once("../../pqm-projects-management/server/pqm_projects_manager.php");

abstract class ParameterController {
	
	abstract public function loadData($project_id, $device_id, $datablock_id, $scope);

	protected function getFilesContentByType($datablock_id, $type) {
		$contents = array();
		
		$proMgr = new PQMProjectsManager();
		$cursor = $proMgr->getFiles(array("type"=>$type, "datablock_id"=>$datablock_id));
		foreach ($cursor as $file) {
			$scope = $file->file["metadata"]["scope"];
			$contents[$scope] = preg_split("/(\r\n|\n|\r)/", $file->getBytes());
		}

		return $contents;
	}
}

?>
