<?php

class ParametersUtils {
	public static function addUnbalanceEntry(&$targetDataProvider, $sourceDataProvider, $indicators) {
		$indicatorsCount = count($indicators);
		$firstIndicator = $indicators[0]; 
		foreach($targetDataProvider as $i=>&$obj) {
			$average = 0;
			$currentMin = floatval($sourceDataProvider[$i][$firstIndicator]);
			foreach($indicators as $n=>$ind) {
				$average += floatval($sourceDataProvider[$i][$ind]);
				if($currentMin > floatval($sourceDataProvider[$i][$ind])) $currentMin = floatval($sourceDataProvider[$i][$ind]);
			}
			$average /= $indicatorsCount;
			
			// Keep devision by cero out!!!
			($average == 0)? $unbalance = 0 : $unbalance = round((($average - $currentMin)/$average)*100, 2);
			$obj["unbalance"] = $unbalance;
		}
	}
	
	public static function addPowerFactorNoHarmoEntry(&$targetDataProvider, $sourceDataProvider, $indicators_PF, $indicators_THD) {
		$pfNoHarmo = array();
		foreach($targetDataProvider as $i=>&$obj) {
			// TODO: Make sure that the target and source have the same number of entries (bad things can happen if they don't!!!)
		
			$pfNoHarmo = 0;
			foreach($indicators_PF as $j=>$ind) {
				$pf = $sourceDataProvider[$i][$indicators_PF[$j]];
				$thd = $sourceDataProvider[$i][$indicators_THD[$j]];
				$pfNoHarmo += $pf * sqrt(1 + pow($thd, 2));
			}
			$pfNoHarmo /= count($indicators_PF);
			
			$obj["PF_SYS_NO_HARMO"] = round($pfNoHarmo,2);
		}
	}
}

?>