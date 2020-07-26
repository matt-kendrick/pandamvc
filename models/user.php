<?php
class user extends model
{
	/*
	CREATE TABLE user (id char(36) UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(50) NOT NULL,email VARCHAR(50) NOT NULL,password VARCHAR(50) NOT NULL,created DATETIME,last_updated DATETIME,created_by INT(6),last_updated_by INT(6));
	*/
	
	var $id = 0;
	var $name = "";
	var $email = "";
	var $password = "";
	var $created = "";
	var $last_updated = "";
	var $created_by = "";
	var $last_updated_by = "";
	
	public function __construct()
	{

	}
	
	public function getModelConfig()
	{
		$modelConfig = new modelConfig($connName=null,$tableName=get_class($this),$keyField="id",$keyFieldType="guid",$dbFieldType=array("id"=>"char(36)","created"=>"datetime","last_updated"=>"datetime","created_by"=>"char(36)","last_updated_by"=>"char(36)"));
		return $modelConfig;
	}
  
	public function getAll()
	{
		$records = $this->getAllRecords();

		$arr = array();

		foreach($records AS $record)
		{
			$className = get_class();
			$o = new $className();
			$o->fill($record);
			$arr[] = $o;
		}

		return $arr;
	}
  
	public function getByEmailPassword($email,$password)
	{
		$password = MD5($password);
		$records = parent::getRecordsFromProcedure("getUserByEmailPassword",array("email"=>$email,"password"=>$password));
		
		//got record?
		$record = (isset($records[0]) ? $records[0] : array());
		
		//don't fill if it's empty
		if(!empty($record)) $this->fill($record);
	}
	
	public function setupSP()
	{
		$procedures = array();
		
		//getUserByEmailPassword
		$procedures["getUserByEmailPassword"] = "CREATE PROCEDURE getUserByEmailPassword(pemail varchar(50),ppassword varchar(50)) SELECT * FROM user WHERE email=pemail AND password=ppassword";
		
		foreach($procedures AS $procedureName => $procedure)
		{
			echo "DROP PROCEDURE IF EXISTS $procedureName;\r\n";
			echo $procedure."\r\n";
		}
	}
}
?>
