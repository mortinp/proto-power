<?php
$file_id = $_POST["file_id"];

$m = new MongoClient();
$db = $m->pqm;
$grid = $db->getGridFS();

$file = $grid->findOne(array("_id"=>new MongoId($file_id)));

$lines = preg_split("/(\r\n|\n|\r)/", $file->getBytes());

foreach($lines as $l) {
	echo $l."<br>";
}
?>
