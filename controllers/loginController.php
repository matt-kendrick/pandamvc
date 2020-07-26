<?php

class loginController extends controller
{
	var $layout = "noheader";
	var $title = "Login";

	function index()
	{
		$this->title = "Login";
		$this->render("index",false);
	}
	
	function forbidden()
	{
		$requestedPage = (isset($_SESSION["requestedPage"]) ? $_SESSION["requestedPage"] : "/home");
			
		$this->title = "Forbidden Login";
		$this->setVars(array("errorCode"=>403,"requestedPage"=>$requestedPage));
		$this->render("index",false);
	}
	
	function logout()
	{
		session_destroy();
		header("Location: /login/");
	}
	
	function process()
	{
		//check for post
		if(isset($_POST['email']) AND $_POST["email"] != "" AND isset($_POST['password']) AND $_POST["password"] != "")
		{
			require("../models/user.php");

			$user = new user();

			$user->getByEmailPassword($_POST['email'],$_POST["password"]);
			
			//make a session
			if($user->id != 0) 
			{
				$_SESSION["userID"] = $user->id;
				
				$_SESSION["permissionKeys"] = array("xxx"); //change later
				
				if(isset($_POST["requestedPage"]) AND $_POST["requestedPage"] != null) header("Location: " . $_POST["requestedPage"]);
				else header("Location: /home/");
			}
			else
			{
				$this->setVars(array("email"=>$_POST["email"],"error_message"=>"Login failed."));
				$this->render("index",false);
			}
		}
		else
		{
			header("Location: /login/");
		}
	}
}

?>