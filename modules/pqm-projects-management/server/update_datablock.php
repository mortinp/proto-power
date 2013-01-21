<?php
include_once("pqm_projects_manager.php");
include_once("pqm_projects_treeview_adapter.php");

$proMgr = new PQMProjectsManager();
$treeviewAdptr = new PQMProjectsTreeviewAdapter();

$project_id = $_POST["project_id"];
$device_id = $_POST["device_id"];
$datablock_index = $_POST["datablock_index"];// TODO: fix with id (not index) when multiple positional operator is supported in MongoDB.
$name = $_POST["name"];

$newDatablock = array("name"=>$name);

$proMgr = new PQMProjectsManager();
$proMgr->updateDatablock($project_id, $device_id, $datablock_index, $newDatablock);

echo json_encode($treeviewAdptr->createNodeDatablock($newDatablock));
?>
