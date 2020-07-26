<?php
class request
{
	function __construct($defaultController="home",$defaultAction="index")
	{
		$this->url = $_SERVER['REQUEST_URI'];

		$this->explode_url = explode('/', $this->url);

		$this->controller = ((isset($this->explode_url[1]) AND $this->explode_url[1] != null) ? $this->explode_url[1] : $defaultController); //default controller
		$this->action = ((isset($this->explode_url[2]) AND $this->explode_url[2] != null) ? $this->explode_url[2] : $defaultAction); //default action
		$this->params = (isset($this->explode_url[3]) ? array_slice($this->explode_url, 3) : array());
	}
}
?>
