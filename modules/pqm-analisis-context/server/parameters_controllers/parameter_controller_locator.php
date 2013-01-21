<?php

include_once("controllers/i.php");
include_once("controllers/v.php");
include_once("controllers/p.php");
include_once("controllers/s.php");
include_once("controllers/pf.php");
include_once("controllers/wsys.php");
include_once("controllers/vasys.php");
include_once("controllers/whsys.php");
include_once("controllers/harmo.php");

class ParameterControllerLocator {

	public static function getParameterController($paramType) {
		if($paramType == "I") return new ParameterController_I();
		else if($paramType == "V") return new ParameterController_V();
		else if($paramType == "P") return new ParameterController_P();
		else if($paramType == "S") return new ParameterController_S();
		else if($paramType == "PF") return new ParameterController_PF();
		else if($paramType == "W_SYS") return new ParameterController_WSYS();
		else if($paramType == "VA_SYS") return new ParameterController_VASYS();
		else if($paramType == "WH_SYS") return new ParameterController_WHSYS();
		else if($paramType == "H") return new ParameterController_HARMO();
		
		// throw new Exception("No controller found for parameter $paramType");
	}
}

?>