<?php
include_once("pqm_projects_manager.php");
include_once("pqm_projects_treeview_adapter.php");

$proMgr = new PQMProjectsManager();
$treeviewAdptr = new PQMProjectsTreeviewAdapter();

$project_id = $_POST["project_id"];
$name = $_POST["name"];
$description = $_POST["description"];

$newProject = array("name"=>$name, "description"=>$description);

$proMgr->updateProject($project_id, $newProject);

echo json_encode($treeviewAdptr->createNodeProject($newProject));
?>
