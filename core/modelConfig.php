<?php
class modelConfig
{
	var $connName; //dsn
	var $tableName;
	var $keyField; //id
	var $keyFieldType = "int"; //int, guid
	var $dbFieldType = array(); //array("id"=>char(36))

	public function __construct($connName,$tableName,$keyField,$keyFieldType="int",$dbFieldType=array())
	{
		$this->connName = $connName;
		$this->tableName = $tableName;
		$this->keyField = $keyField;
		$this->keyFieldType = $keyFieldType;
		$this->dbFieldType = $dbFieldType;
	}
}
?>