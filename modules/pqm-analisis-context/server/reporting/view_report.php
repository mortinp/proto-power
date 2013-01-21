<?php
require("report_creator.php");

$project_id = $_GET["project_id"];
$device_id = $_GET["device_id"];
$datablock_id = $_GET["datablock_id"];

$rCreator = new ReportCreator();
echo json_encode($rCreator->loadReport($project_id, $device_id, $datablock_id));
?>
