<?php
require_once("../core/modelConfig.php");

class model
{
	public function __construct()
	{
	}
	
	public function getModelConfig()
	{
		$modelConfig = new modelConfig($connName=null,$tableName=get_class($this),$keyField="id",$dbFieldType=array());
		return $modelConfig;
	}	
	
	public function getAllRecords()
	{
		$modelConfig = $this->getModelConfig();
		
		$sql = "CALL getAll$modelConfig->tableName";
		$stmt = db::conn($modelConfig->connName)->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function fill($record=array())
	{
		$vars = array_keys(get_object_vars($this));
		foreach($vars AS $var) if(isset($record[$var])) $this->{$var} = $record[$var];
	}
	
	public function getById($id)
	{
		$record = $this->getRecordById($id);
		$this->fill($record);
	}
  
	public function getRecordById($id)
	{
		$modelConfig = $this->getModelConfig();
		$sql = "CALL get$modelConfig->tableName(:id)";
		$stmt = db::conn($modelConfig->connName)->prepare($sql);
		$stmt->bindParam(':id',$id);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	public function getRecordsFromProcedure($procedureName,$parameters)
	{
		$modelConfig = $this->getModelConfig();
		
		//array to return
		$records = array();
		
		//build query
		$sql = "CALL $procedureName(";
		foreach($parameters as $parameter => $parameter_value) $sql .= ":$parameter,";
		$sql = rtrim($sql,",") . ")";
		
		try
		{
			$stmt = db::conn($modelConfig->connName)->prepare($sql);
			$stmt->execute($parameters);
			
			//read records into array
			while($record = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$records[] = $record;
			}
		}
		catch(Exception $e)
		{
			echo "Error: $e";
		}
		
		return $records;
	}
	
	public function callProcedure($procedureName,$parameters)
	{
		$modelConfig = $this->getModelConfig();
		
		//build query
		$sql = "CALL $procedureName(";
		foreach($parameters as $parameter => $parameter_value) $sql .= ":$parameter,";
		$sql = rtrim($sql,",") . ")";
		
		try
		{
			$stmt = db::conn($modelConfig->connName)->prepare($sql);
			$stmt->execute($parameters);
		}
		catch(Exception $e)
		{
			echo "Error: $e";
		}
	}
	
	public function save()
	{
		$modelConfig = $this->getModelConfig();
		$tableName = $modelConfig->tableName;
		
		//uppercase the first char of the table name for procedure name only (read ablity)
		$tableNameForProcedureName = ucfirst($modelConfig->tableName);
		
		$isNewRecord = ($this->{$modelConfig->keyField} == '0' ? true:false); //guids were not passing the zero test, made sure the id was a string for compare - 2/7/2020
		
		$vars = array_keys(get_object_vars($this));
		
		//ouput example (:pid,:pdescription) - maps array and implodes into string
		$parameterString = implode(",",array_map(function($x){return ":$x";},$vars));
		
		if($isNewRecord)
		{
		  $sql = "CALL insert$tableNameForProcedureName($parameterString)";
		}
		else
		{
		  $sql = "CALL update$tableNameForProcedureName($parameterString)";
		}

		$conn = db::conn($modelConfig->connName);

		$stmt = $conn->prepare($sql);

		//if zero assign a guid
		if($this->{$modelConfig->keyField} == '0' AND $modelConfig->keyFieldType == 'guid') $this->{$modelConfig->keyField} = $this->getGUID(); //guids were not passing the zero test, made sure the id was a string for compare - 2/7/2020

		//output example array(":id"=>1,":description"=>"test")
		$values = array();
		foreach($vars AS $var) $values[":$var"] = $this->{$var};
		
		$stmt->execute($values);
		
		//grab last id if zero and not a guid
		if($isNewRecord AND $modelConfig->keyFieldType != 'guid')
		{
			$stmt = $conn->query("SELECT LAST_INSERT_ID()");
			$this->{$modelConfig->keyField} = $stmt->fetchColumn();
		}
	}

	public function deleteById($id)
	{
		$modelConfig = $this->getModelConfig();
		
		$sql = "CALL delete$modelConfig->tableName(:id)";
		$stmt = db::conn($modelConfig->connName)->prepare($sql);
		$stmt->bindParam(':id',$id);
		$stmt->execute();
	}
	
	public function delete()
	{
		$modelConfig = $this->getModelConfig();
		if($this->{$modelConfig->keyField} != 0) $this->deleteById($this->{$modelConfig->keyField});
	}
	
	public function getGUID()
	{
		if (function_exists('com_create_guid'))
		{
			return com_create_guid();
		}
		else 
		{
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			$uuid = "" //chr(123)// "{"
				.substr($charid, 0, 8).$hyphen
				.substr($charid, 8, 4).$hyphen
				.substr($charid,12, 4).$hyphen
				.substr($charid,16, 4).$hyphen
				.substr($charid,20,12);
				//.chr(125);// "}"
			return $uuid;
		}
	}	
	
	public function setupCRUD($ignoreVars=array())
	{
		$modelConfig = $this->getModelConfig();
		
		$tableName = $modelConfig->tableName;
		
		//uppercase the first char of the table name for procedure name only (read ablity)
		$tableNameForProcedureName = ucfirst($modelConfig->tableName);
		
		$vars = array_keys(get_object_vars($this));
		
		//remove ignored vars
		$vars = array_diff($vars,$ignoreVars);
		
		//field string
		$fieldString = implode(",",$vars);
		
		//parameter string ex(pid int(6),pdescription varchar(50)) 
		$parameterString = "";
		
		//sets the parameter type through mapping (ex. "id" = "pid int", "firstName" = "pfirstName varchar(65535)")
		foreach($vars AS $var) $parameterString .= "p$var " . ((!empty($modelConfig->dbFieldType) AND in_array($var,array_keys($modelConfig->dbFieldType))) ? $modelConfig->dbFieldType[$var] : "varchar(65535)") . ",";

		$parameterString = rtrim($parameterString,",");
		
		//parameter value string ex(pid,pdescription)
		$parameterValueString = "p".implode(",p",$vars);
		
		$updateString = "";
		foreach($vars AS $var) $updateString .= "$var=p$var,";
		$updateString = rtrim($updateString,",");
		
		$procedures = array();
		
		//get
		$procedures["get$tableNameForProcedureName"] = "CREATE PROCEDURE get$tableNameForProcedureName(IN p$modelConfig->keyField ".(in_array($var,array_keys($modelConfig->dbFieldType)) ? $modelConfig->dbFieldType[$var] : "int").") SELECT $fieldString FROM $tableName WHERE $modelConfig->keyField=p$modelConfig->keyField";

		//getAll
		$procedures["getAll$tableNameForProcedureName"] = "CREATE PROCEDURE getAll$tableNameForProcedureName() SELECT $fieldString FROM $tableName";
		
		//insert
		$procedures["insert$tableNameForProcedureName"] = "CREATE PROCEDURE insert$tableNameForProcedureName(IN $parameterString) INSERT INTO $tableName($fieldString) VALUES($parameterValueString)";
		
		//update
		$procedures["update$tableNameForProcedureName"] = "CREATE PROCEDURE update$tableNameForProcedureName(IN $parameterString) UPDATE $tableName SET $updateString WHERE $modelConfig->keyField=p$modelConfig->keyField";
		
		//delete
		$procedures["delete$tableNameForProcedureName"] = "CREATE PROCEDURE delete$tableNameForProcedureName(IN p$modelConfig->keyField ".(in_array($var,array_keys($modelConfig->dbFieldType)) ? $modelConfig->dbFieldType[$var] : "int").") DELETE FROM $tableName WHERE $modelConfig->keyField=p$modelConfig->keyField";
		
		foreach($procedures AS $procedureName => $procedure)
		{
			echo "DROP PROCEDURE IF EXISTS $procedureName;\r\n";
			echo $procedure.";\r\n";
		}
	}
}
?>