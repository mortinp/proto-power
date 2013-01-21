<?php
require("parameters_reader.php");

$project_id = $_GET["project_id"];
$device_id = $_GET["device_id"];
$datablock_id = $_GET["datablock_id"];
$type = $_GET["type"];
$scope = $_GET["scope"];

$reader = new ParameterReader();
echo json_encode($reader->getResponseForParameter($project_id, $device_id, $datablock_id, $type, $scope));
?>
