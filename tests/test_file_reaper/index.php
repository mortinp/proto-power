<?php
include_once("files_reaper.php");

$definitions = array(array("filePath"=>"proto-power/test/test_file_reaper/data/power.txt", 
						   "fromIndicators"=>array("PF1", "PF2", "PF3"), 
						   "magnitudes"=>array("", "", "")),
					 array("filePath"=>"proto-power/test/test_file_reaper/data/harmoi1.txt", 
						   "fromIndicators"=>array("THD-F"), 
						   "toIndicators"=>array("THD-F1"), 
						   "magnitudes"=>array("%")),
					 array("filePath"=>"proto-power/test/test_file_reaper/data/harmoi2.txt", 
						   "fromIndicators"=>array("THD-F"), 
						   "toIndicators"=>array("THD-F2"), 
						   "magnitudes"=>array("%")),
					 array("filePath"=>"proto-power/test/test_file_reaper/data/harmoi3.txt", 
						   "fromIndicators"=>array("THD-F"), 
						   "toIndicators"=>array("THD-F3"), 
						   "magnitudes"=>array("%")));
						   
$result = PQMFilesReaper::reap($definitions);

print_r($result);
echo "<br><br><br>";					 
print_r(getPowerFactorNoHarmo($result["data"]));


function getPowerFactorNoHarmo($data) {
	$pfNoHarmo = array();
	foreach($data as $i=>$obj) {
		$pf1 = $obj["PF1"];
		$pf2 = $obj["PF2"];
		$pf3 = $obj["PF3"];
		
		$thd1 = $obj["THD-F1"];
		$thd2 = $obj["THD-F2"];
		$thd3 = $obj["THD-F3"];
		
		$pfNoHarmo1 = $pf1 * sqrt(1 + pow($thd1, 2));
		$pfNoHarmo2 = $pf2 * sqrt(1 + pow($thd2, 2));
		$pfNoHarmo3 = $pf3 * sqrt(1 + pow($thd3, 2));
		
		$pfNoHarmo[] = round(($pfNoHarmo1 + $pfNoHarmo2 + $pfNoHarmo3)/3, 2);
	}
	
	return $pfNoHarmo;
}

?>