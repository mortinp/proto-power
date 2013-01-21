<?php
class PQMProjectsTreeviewAdapter {

	public function createNodeProject($project) {
		$pro = array();
		$pro["data"] = $project["name"];
		$pro["attr"] = array("rel"=>"project");
		$pro["metadata"] = $project;
		$pro["children"] = array();
		
		if(isset($project["devices"])) {
			$devices = $project["devices"];
			foreach ($devices as $dev) {
				$pro["children"][] = $this->createNodeDevice($dev);
			}
		}
		
		return $pro;
	}

	public function createNodeDevice($device) {
		$dev = array();
		$dev["data"] = $device["name"];
		$dev["attr"] = array("rel"=>"device");
		$dev["metadata"] = $device;
		$dev["children"] = array();
		
		if(isset($device["datablocks"])) {
			$datablocks = $device["datablocks"];
			foreach ($datablocks as $db) {
				$dev["children"][] = $this->createNodeDataBlock($db);
			}
		}
		
		return $dev;
	}

	public function createNodeDatablock($datablock) {
		$db = array();
		$db["data"] = $datablock["name"];
		$db["attr"] = array("rel"=>"datablock");
		$db["metadata"] = $datablock;
		$db["children"] = array();
		
		if(isset($datablock["files"])) {
			$files = $datablock["files"];
			foreach ($files as $f) {
				$db["children"][] = $this->createNodeFile($f);
			}
		}
		
		return $db;
	}

	public function createNodeFile($file) {
		$f = array();
		$f["data"] = $file["name"];
		$f["attr"] = array("rel"=>"file");
		$f["metadata"] = $file;
		
		return $f;
	}
}
?>
