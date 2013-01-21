<?php
include_once("pqm_projects_manager.php");
include_once("pqm_projects_treeview_adapter.php");

$proMgr = new PQMProjectsManager();
$treeviewAdptr = new PQMProjectsTreeviewAdapter();

//TODO: Find user in session variable
$user = "martin";

$root = array();
$root["data"] = "$user's projects";
$root["attr"] = array("rel"=>"root", "id"=>"root"); // MUST have id='root'
$root["metadata"] = array("name"=>$user);
$root["children"] = array();

$cursor = $proMgr->findProjectsByOwner($user);
foreach ($cursor as $pro) {
	$root["children"][] = $treeviewAdptr->createNodeProject($pro);
}

// Wrap data with tree structure
$jsonData = file_get_contents("data/tree.json");
$tree = json_decode($jsonData, true);
$tree['json_data'] = array("data"=>$root);

echo json_encode($tree);
?>
