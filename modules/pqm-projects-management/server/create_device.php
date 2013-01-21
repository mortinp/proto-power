<?php
include_once("pqm_projects_manager.php");
include_once("pqm_projects_treeview_adapter.php");

$proMgr = new PQMProjectsManager();
$treeviewAdptr = new PQMProjectsTreeviewAdapter();

$project_id = $_POST["project_id"];
$name = $_POST["name"];
$kva = $_POST["kva"];
$reactance = $_POST["reactance"];
$voltage = $_POST["voltage"];

$device = array("_id"=>new MongoId(),"name"=>$name , "kva"=>$kva, "reactance"=>$reactance, "voltage"=>$voltage, "datablocks"=>array());
$Isc = (1000*$device["kva"])/($device["reactance"]/100 * 1.73*$device["voltage"]);
$device["Isc"] = round($Isc, 2);

$proMgr->insertDevice($project_id, $device);

echo json_encode($treeviewAdptr->createNodeDevice($device));
?>