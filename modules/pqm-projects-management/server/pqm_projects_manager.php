<?php
class PQMProjectsManager {
	private $projects_collection;
	private $gridfs;
	
	function __construct() { 
		$m = new MongoClient();
		$db = $m->pqm;
		
		$this->projects_collection = $db->projects;
		$this->gridfs = $db->getGridFS();
    }
    
    public function getAggregate($project_id, $device_id=-1, $datablock_id=-1, $scope=-1) {
    	$aggregFilters = array();
    	$resultDef = array();
    	
    	// Add project aggregation
    	$aggregFilters[] = array('$match'=>array("_id"=>new MongoId($project_id)));
    	$resultDef["project"] = array('_id'=>'$_id', 'name'=>'$name', 'description'=>'$description');
    	
    	// Add the rest of the aggregates only if requested
    	if($device_id != -1) {
    		// Add device aggregation
    		$aggregFilters[] = array('$unwind'=> '$devices');
    		$aggregFilters[] = array('$match'=> array("devices._id"=> new MongoId($device_id)));
    		$resultDef["device"] = array('_id'=>'$devices._id', 'name'=>'$devices.name', 'kva'=> '$devices.kva', 'reactance'=>'$devices.reactance', 'voltage'=>'$devices.voltage', 'Isc'=>'$devices.Isc');
    		
    		if($datablock_id != -1) {
				// Add datablock aggregation
				$aggregFilters[] = array('$unwind'=> '$devices.datablocks');
				$aggregFilters[] = array('$match'=> array("devices.datablocks._id"=> new MongoId($datablock_id)));
				$resultDef["datablock"] = array('_id'=>'$devices.datablocks._id', 'name'=>'$devices.datablocks.name');
				
				// TODO: check for scope and aggregate corresponding files
				$resultDef["files"] = '$devices.datablocks.files';
			}
    	}
    	
    	$aggregFilters[] = array('$project'=>$resultDef);
    	
    	$result = $this->projects_collection->aggregate($aggregFilters);
    	
    	return $result;
    }
	
	public function insertProject($project) {
		$this->projects_collection->insert($project);
	}
	
	public function updateProject($id, $newProject) {
		$this->projects_collection->update(array("_id"=>new MongoId($id)), 
										   array('$set'=>$newProject));
	}
	
	public function deleteProject($id) {
		$this->projects_collection->remove(array("_id"=>new MongoId($id)));
	}
	
	public function findProjectsByOwner($owner) {
		return $this->projects_collection->find(array("owner"=>$owner));
	}
	
	public function insertDevice($project_id, $device) {
		$this->projects_collection->update(array("_id"=>new MongoId($project_id)), 
										   array('$push'=>array("devices"=>$device)));
	}
	
	public function updateDevice($project_id, $device_id, $newDevice) {
		$this->projects_collection->update(array("_id"=>new MongoId($project_id), "devices._id"=>new MongoId($device_id)), 
										   array('$set'=>$this->buildMergeStatement($newDevice, "devices.$"))); // Merge, not full replace
	}
	
	public function deleteDevice($project_id, $device_id) {
		$this->projects_collection->update(array("_id"=>new MongoId($project_id)), 
										   array('$pull'=>array("devices"=>array("_id"=>new MongoId($device_id)))));
	}
	
	public function insertDatablock($project_id, $device_id, $datablock) {
		$this->projects_collection->update(array("_id"=>new MongoId($project_id), "devices._id"=>new MongoId($device_id)),
										   array('$push'=>array("devices.$.datablocks"=>$datablock)));
	}
	
	public function updateDatablock($project_id, $device_id, $datablock_index, $newDatablock) {
		$this->projects_collection->update(array("_id"=>new MongoId($project_id), "devices._id"=>new MongoId($device_id)), 
										   array('$set'=>$this->buildMergeStatement($newDatablock, "devices.$.datablocks.$datablock_index"))); // Merge, not full replace
	}
	
	public function deleteDatablock($project_id, $device_id, $datablock_id) {
		$this->projects_collection->update(array("_id"=>new MongoId($project_id), "devices._id"=>new MongoId($device_id)), 
										   array('$pull'=>array("devices.$.datablocks"=>array("_id"=>new MongoId($datablock_id)))));
	}
	
	public function addFile($project_id, $device_id, $datablock_index, $fileContent, &$file) {
		$id = $this->gridfs->storeBytes($fileContent, 
										array("metadata" => $file,
										"filename" =>$file["name"]));
										
		$file["_id"] = new MongoId($id);
		$this->projects_collection->update(array("_id"=>new MongoId($project_id), "devices._id"=>new MongoId($device_id)), 
										   array('$push'=>array("devices.$.datablocks.$datablock_index.files"=>$file)));
	}
	
	public function getFiles($criteria) {
		// Match with metadata
		$matchExpression = array();
		foreach($criteria as $key=>$value) {
			$matchExpression["metadata.$key"] = $value;
		}		
		return $this->gridfs->find($matchExpression);
	}
	
	/*  Auxiliary Functions  */
	
	private function buildMergeStatement($obj, $target = "") {
		$statement = array();
		foreach($obj as $key=>$value) {
			$statement["$target.$key"] = $value;
		}
		
		return $statement;
	}
}
?>
