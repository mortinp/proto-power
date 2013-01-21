<?php
include_once("pqm_projects_manager.php");

$project_id = $_POST["project_id"];
$device_id = $_POST["device_id"];
$datablock_id = $_POST["datablock_id"];

$proMgr = new PQMProjectsManager();
$proMgr->deleteDatablock($project_id, $device_id, $datablock_id);

echo json_encode(array("msg"=>"OK"));
?>