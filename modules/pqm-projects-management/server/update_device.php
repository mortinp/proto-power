<?php
include_once("pqm_projects_manager.php");
include_once("pqm_projects_treeview_adapter.php");

$proMgr = new PQMProjectsManager();
$treeviewAdptr = new PQMProjectsTreeviewAdapter();

$project_id = $_POST["project_id"];
$device_id = $_POST["device_id"];
$name = $_POST["name"];
$kva = $_POST["kva"];
$reactance = $_POST["reactance"];
$voltage = $_POST["voltage"];

$newDevice = array("name"=>$name , "kva"=>$kva, "reactance"=>$reactance, "voltage"=>$voltage);
$Isc = (1000*$newDevice["kva"])/($newDevice["reactance"]/100 * 1.73*$newDevice["voltage"]);
$newDevice["Isc"] = round($Isc, 2);

$proMgr->updateDevice($project_id, $device_id, $newDevice);

echo json_encode($treeviewAdptr->createNodeDevice($newDevice));
?>
