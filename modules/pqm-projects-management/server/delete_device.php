<?php
include_once("pqm_projects_manager.php");

$project_id = $_POST["project_id"];
$device_id = $_POST["device_id"];

$proMgr = new PQMProjectsManager();
$proMgr->deleteDevice($project_id, $device_id);
					
echo json_encode(array("msg"=>"OK"));
?>