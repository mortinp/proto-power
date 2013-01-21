<?php
include_once("pqm_projects_manager.php");
include_once("pqm_projects_treeview_adapter.php");

$proMgr = new PQMProjectsManager();
$treeviewAdptr = new PQMProjectsTreeviewAdapter();

$name = $_POST["name"];
$description = $_POST["description"];

//TODO: Find user in session variable
$user = "martin";

$project = array("name"=>$name, "description"=>$description, "owner"=>$user, "devices"=>array());

$proMgr->insertProject($project);

echo json_encode($treeviewAdptr->createNodeProject($project));
?>
