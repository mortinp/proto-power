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

QUERY:

db.projects.aggregate(
	{$match: {_id: new ObjectId("50d7448eb0d02aac0b00000b")}},
	{$unwind: "$devices"},
	{$match: {"devices._id": new ObjectId("50d74492b0d02aac0b00000c")}},
	{$unwind: "$devices.datablocks"},
	{$match: {"devices.datablocks._id": new ObjectId("50d744f1b0d02ad808000002")}},
	{$project: {project:{name:"$name", description:"$description"},
				device: {name:"$devices.name", kva: "$devices.kva", reactance:"$devices.reactance", voltage:"$devices.voltage"},
				datablock: {name:"$devices.datablocks.name"},
				files: "$devices.datablocks.files",
				_id: 0}}
)
*/

$project_id = "50d79286b0d02a6017000000";
$device_id = "50d79290b0d02a6017000001";
$datablock_id = "50d79294b0d02a6017000002";

$m = new MongoClient();
$db = $m->pqm;
$collection = $db->projects;

/*$aggreg = $collection->aggregate(array(
	array('$match'=>array("_id"=>new MongoId($project_id))),
	array('$unwind'=> '$devices'),
	array('$match'=> array("devices._id"=> new MongoId($device_id))),
	array('$unwind'=> '$devices.datablocks'),
	array('$match'=> array("devices.datablocks._id"=> new MongoId($datablock_id))),
	array('$project'=> array(
				'project'=>array('_id'=>'$_id', 'name'=>'$name', 'description'=>'$description'),
				'device'=> array('_id'=>'$devices._id', 'name'=>'$devices.name', 'kva'=> '$devices.kva', 'reactance'=>'$devices.reactance', 'voltage'=>'$devices.voltage'),
				'datablock'=> array('_id'=>'$devices.datablocks._id', 'name'=>'$devices.datablocks.name'),
				'files'=> '$devices.datablocks.files'))
));
$result = $aggreg["result"];

$analisis = array();
$type = "none";
$files = $result[0]["files"];
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
		array("name"=>$f["name"], 
			  "ref_parameter"=>$f["default"],
			  "ref_subparameter"=>$f["scope"]);
}

$response = array(
	"project"=>$result[0]["project"],
	"device"=>$result[0]["device"],
	"datablock"=>$result[0]["datablock"],
	"analisis"=>$analisis,
	"current_analisis"=>array("main"=>"3P4W", "scope"=>"ALL"));

echo json_encode($response);*/

$contents = array();
$grid = $db->getGridFS();
$cursor = $grid->find(array("metadata.type"=>"HARMO"));
foreach ($cursor as $file) {
	$scope = $file->file["metadata"]["scope"];
	$contents[$scope] = preg_split("/(\r\n|\n|\r)/", $file->getBytes());
}
echo json_encode($contents);

/*$grid = $db->getGridFS();
$cursor = $grid->find(array("metadata.type"=>"HARMO"));
foreach ($cursor as $file) {
	echo json_encode($file).'<br/>';
}*/

/*$cursor = $collection->find();
foreach($cursor as $pro) {
	echo json_encode($pro);
}*/
?>
