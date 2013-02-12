<?php
/*
IDEA:

I have to use the Aggregation Framework to create an output document that resembles the following:

{
	project: ... ,
	device: ... ,
	datablock: ...
	files: [...] //'files' should be grouped by 'type'???
}

Then I can perform the transformation into the response, which means to turn each file into a subanalisis,
and each group (by type) of files into an analisis entry.

QUERY JS:

db.projects.aggregate(
	{$match: {_id: new ObjectId("50d7448eb0d02aac0b00000b")}},
	{$unwind: "$devices"},
	{$match: {"devices._id": new ObjectId("50d74492b0d02aac0b00000c")}},
	{$unwind: "$devices.datablocks"},
	{$match: {"devices.datablocks._id": new ObjectId("50d744f1b0d02ad808000002")}},
	{$project: {project:{name:"$name", description:"$description"},
				device: {name:"$devices.name", kva: "$devices.kva", reactance:"$devices.reactance", voltage:"$devices.voltage", Isc:"$devices.Isc"},
				datablock: {name:"$devices.datablocks.name"},
				files: "$devices.datablocks.files"}}
)

QUERY PHP:

$m = new MongoClient();
$db = $m->pqm;
$collection = $db->projects;

$aggreg = $collection->aggregate(array(
	array('$match'=>array("_id"=>new MongoId($project_id))),
	array('$unwind'=> '$devices'),
	array('$match'=> array("devices._id"=> new MongoId($device_id))),
	array('$unwind'=> '$devices.datablocks'),
	array('$match'=> array("devices.datablocks._id"=> new MongoId($datablock_id))),
	array('$project'=> array(
				'project'=>array('_id'=>'$_id', 'name'=>'$name', 'description'=>'$description'),
				'device'=> array('_id'=>'$devices._id', 'name'=>'$devices.name', 'kva'=> '$devices.kva', 'reactance'=>'$devices.reactance', 'voltage'=>'$devices.voltage', 'Isc'=>'$devices.Isc'),
				'datablock'=> array('_id'=>'$devices.datablocks._id', 'name'=>'$devices.datablocks.name'),
				'files'=> '$devices.datablocks.files'))
));

*/

include_once("../../pqm-projects-management/server/pqm_projects_manager.php");

$project_id = $_GET["project_id"];
$device_id = $_GET["device_id"];
$datablock_id = $_GET["datablock_id"];

$proMgr = new PQMProjectsManager();
$aggreg = $proMgr->getAggregate($project_id, $device_id, $datablock_id);
$result = $aggreg["result"];

$analisis = array();
$type = "none";
$files = $result[0]["files"];
$is_default_set = false;
foreach($files as $f){
	if($type != $f["type"]) { // New analisis entry
		$type = $f["type"];
		if(!isset($analisis[$type])) {
			$analisis[$type] = array();
			$analisis[$type]["subanalisis"] = array();
		}
	}
	// Each file becomes a subanalisis
	$analisis[$type]["subanalisis"][] = 
		array("tag"=>$f["tag"],
			  "default_param"=>$f["default"],
			  "scope"=>$f["scope"]);
			  
    // Store first subanalisis to be the default
    if($is_default_set == false) {
        $default_subanalisis = array("main"=>$f["type"], "scope"=>$f["scope"], "default"=>$f["tag"]);
        $is_default_set = true;
    }
    
}

$response = array(
	"project"=>$result[0]["project"],
	"device"=>$result[0]["device"],
	"datablock"=>$result[0]["datablock"],
	"analisis"=>$analisis,
	"current_analisis"=>$default_subanalisis);

echo json_encode($response);
?>
