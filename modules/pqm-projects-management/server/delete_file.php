<?php
$project_id = $_POST["project_id"];
$device_id = $_POST["device_id"];
$datablock_index = $_POST["datablock_index"];
$file_id = $_POST["file_id"];

// Update in BD
$m = new MongoClient();
$db = $m->pqm;
$collection = $db->projects;
$grid = $db->getGridFS();

$collection->update(array("_id"=>new MongoId($project_id), "devices._id"=>new MongoId($device_id)), 
					array('$pull'=>array("devices.$.datablocks.$datablock_index.files"=>array("_id"=>new MongoId($file_id)))));
					
$grid->remove(array("_id" => new MongoId($file_id)));

echo json_encode(array("msg"=>"OK"));
?>