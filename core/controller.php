<?php
class controller
{
	var $vars = array();
	var $layout = "default";
	var $title = "PandaMVC";
	
	//functions that requires permission keys
	var $protectedFunctions = array("setVars"=>"neverever","render"=>"neverever","listFunctionPermissions"=>"xxx"); //ex. array("function_name"=>"permission key")

	function setVars($data)
	{
		$this->vars = array_merge($this->vars, $data);
	}

	function render($filename=null,$debug=false)
	{
		if(!$debug) ob_flush();

		ob_start();

		extract($this->vars);

		//if the filename is null then render with the default/index.php content, otherwise pull the view
		if($filename == null)
		{
			require('../views/default/index.php');
		}
		else
		{
			//default view location
			$file = "../views/" . (str_replace('Controller', '', get_class($this))) . '/' . $filename . '.php';
			
			//if the filename contains a directory then treat as a path, instead of defaulting to the ../views/controller/filename
			if(count(explode("/",$filename)) > 1) $file = "../views/$filename.php";

			if($debug) echo "<p>Loading View: $file</p>";
			
			if(file_exists($file))	require($file);
		}

		$content = ob_get_clean();

		if ($this->layout == false)
		{
			echo $content;
		}
		else
		{
			require("../views/layouts/" . $this->layout . '.php');
		}
	}

	//default index
	function index()
	{
		$this->render(null,false);
	}
	
	//lists permissions required for each controller function
	function listFunctionPermissions($outputPermissionArray=false,$defaultPermission="")
	{
		$arrayString = 'var $protectedFunctions = array(';
		
		foreach(get_class_methods($this) AS $key)
		{
			if(!in_array($key,array(""))) //"setVars","render","listFunctionPermissions"
			{
				echo "$key -> " . (in_array($key,array_keys($this->protectedFunctions)) ? $this->protectedFunctions[$key] : "none") . "<br>";
				$arrayString .= "\"$key\"=>\"" . (in_array($key,array_keys($this->protectedFunctions)) ? $this->protectedFunctions[$key] : $defaultPermission) . "\",";
			}
		}
		
		$arrayString = rtrim($arrayString,",") . ");";
		
		if($outputPermissionArray) echo $arrayString;
	}
}
?>
