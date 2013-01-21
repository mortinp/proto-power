<?php
include_once("pqm_projects_manager.php");
include_once("pqm_projects_treeview_adapter.php");

$proMgr = new PQMProjectsManager();
$treeviewAdptr = new PQMProjectsTreeviewAdapter();

$project_id = $_POST["project_id"];
$device_id = $_POST["device_id"];
$name = $_POST["name"];

$datablock = array("_id"=>new MongoId(), "name"=>$name, "files"=>array());

$proMgr = new PQMProjectsManager();
$proMgr->insertDatablock($project_id, $device_id, $datablock);

$node["data"] = $name;
$node["attr"] = array("rel"=>"datablock");
$node["metadata"] = $datablock;

echo json_encode($treeviewAdptr->createNodeDatablock($datablock));
?>