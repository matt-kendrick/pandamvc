<?php

class dashboardController extends controller
{
	var $protectedFunctions = array("index"=>"xxx");
	var $layout = "dashboard";
	var $title = "Dashboard";
	
	function index()
	{
		$this->render("index",false);
	}
}

?>