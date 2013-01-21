<?php
include_once("pqm_projects_manager.php");

$project_id = $_POST["project_id"];

$proMgr = new PQMProjectsManager();
$proMgr->deleteProject($project_id);

echo json_encode(array("msg"=>"OK"));
?>