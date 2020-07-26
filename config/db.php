<?php
class db
{
    private static $db = null;

    public static function conn($connName=null)
    {
      if(is_null(self::$db))
      {
		  switch($connName)
		  {
			case "sb_osc":
				self::$db = new PDO("mysql:host=localhost;dbname=sb_osc", 'dev', 'dev1');
			break;
			
			default:
				self::$db = new PDO("mysql:host=localhost;dbname=test", 'dev', 'dev1');
			break;
		  }
      }
	  
	  self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	  
      return self::$db;
    }
}
?>
