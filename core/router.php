<?php
require_once("request.php");

class router
{
	function __construct()
	{
		
	}
	
	function route($defaultController="home",$defaultAction="index",$debug=false)
	{
		if($debug) echo "~router";

		$request = new request($defaultController,$defaultAction);

		if(count($request->explode_url) > 0)
		{
			if($request->controller != null) //we have a controller
			{
				//alias for login/logout
				if($request->controller == "logout")
				{
					$request->controller = "login";
					$request->action = "logout";
				}
				
				$controller = $this->loadController($request->controller);

				if($controller != null AND $request->action != null && method_exists($controller,$request->action)) //we have a controller and a valid method
				{
					if(!$this->callController($controller,$request))
					{
						if( ob_get_level() > 0 ) ob_clean();
						//http_response_code(403);
						//echo "<strong>403 Forbidden.</strong>";
						
						$_SESSION["requestedPage"] = $_SERVER["REQUEST_URI"];
						
						header("Location: /login/forbidden");
					}
				}
				else //method not found
				{
					if( ob_get_level() > 0 ) ob_clean();
					http_response_code(404);
					echo "<strong>404 Not Found.</strong>";
				}
			}
			else //no controller
			{
					if( ob_get_level() > 0 ) ob_clean();
					http_response_code(404);
					echo "<strong>404 Not Found.</strong>";
			}
		}
		else //not sure what to do
		{
			if( ob_get_level() > 0 ) ob_clean();
			http_response_code(404);
			echo "<strong>404 Not Found.</strong>";
		}
	}

	public function checkControllerMethodPermission($controller,$request,$debug=false)
	{
		$hasPermission = true;
	
		//see if there is a required key
		$requiredPermissionKey = (isset($controller->protectedFunctions[$request->action]) ? $controller->protectedFunctions[$request->action] : null);
	
		if($debug)
		{
			echo "Action: $request->action ";
			echo "Need Key: $requiredPermissionKey";
		}
	
		//check to see if required_permission_key is not null (requires a permission)
		if($requiredPermissionKey != null)
		{
			$hasPermission = false;
			if(isset($_SESSION['permissionKeys']) AND in_array($requiredPermissionKey,$_SESSION["permissionKeys"]))$hasPermission = true;
		}

		return $hasPermission;
	}
	
	//will call the controller method if it has permission, otherwise returns false
	public function callController($controller,$request,$debug=false)
	{
		$hasPermission = $this->checkControllerMethodPermission($controller,$request,$debug);
		
		$success = false;
		
		if($hasPermission)
		{
			if($debug)
			{	
				echo "<br>";
				print_r($request->params);
				die();
			}

			call_user_func_array(array($controller, $request->action),$request->params);
			
			$success = true;
		}
	
		return $success;
	}

	//loads controller
	public function loadController($name)
	{
		$controller = null;

		$name .= "Controller";
		$file = "../controllers/$name.php";

		//check to see if file exists
		if(file_exists($file))
		{
			require_once($file);
			$controller = new $name();
		}

		return $controller;
	}
  
  	public function getControllerMethodContent($controllerName,$action,$params=array())
	{
		ob_start();
		
		$controller = $this->loadController($controllerName);
		
		$request = new request();
		$request->controller = $controllerName;
		$request->action = $action;
		$request->params = $params;
		
		$this->callController($controller,$request);
		
		$content = ob_get_clean();

		return $content;
	}
}
?>
