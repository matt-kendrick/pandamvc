<?php
class usersController extends controller
{
	//functions that requires permission keys
	var $protectedFunctions = array("index"=>"xxx","delete"=>"xxx","process"=>"xxx","getUserJson"=>"xxx","getUsersJson"=>"xxx","setupSP"=>"xxx","setVars"=>"xxx","render"=>"xxx","listFunctionPermissions"=>"xxx");
	
	function index()
	{
		$this->title="Users";
		$this->layout = "dashboard";
		$this->render("index",false);
	}

	function delete()
	{
		require("../models/user.php");
		
		$user = new user();
		$user->getById($_POST['id']);
		
		$user->deleteById($_POST['id']);
		
		echo json_encode(array("message"=>"Deleted: $user->name"));
	}
  
	function process()
	{
		require("../models/user.php");

		$user = new user();  
		
		$user->id = $_POST['id'];
		
		//load user to keep what we haven't changed
		if($user->id != 0) $user->getById($user->id);

		$user->name = $_POST['name'];
		$user->email = $_POST['email'];
		
		//if user id = 0 then set to default password
		
		if($user->id == 0) 
		{
			$user->password = MD5('changeme');
		}
		elseif(isset($_POST['password']) AND $user->password != null)
		{
			$user->password = MD5($_POST['password']);
		}
		
		$user->save();
		
		//return empty password
		$user->password = null;

		echo json_encode($user);
	}
  
	function getUserJson($id)
	{
		require("../models/user.php");

		$user = new user();
		$user->getById($id);
		$user->password = ""; //blank the password we do not want to pass it 

		echo json_encode($user);
	}

	function getUsersJson()
	{
		require("../models/user.php");
		$user = new user(); 
		$users = $user->getAll();
		echo json_encode($users);
	}
	
	function setupSP()
	{
		require("../models/user.php");
		$user = new user();
		$user->setupCRUD();
		$user->setupSP();
	}
}
?>
