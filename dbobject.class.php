<?php

abstract class dbobject
{	
	public $id;

	function __construct()
    {
    	// setup datbase connection
		$username		= "";
		$password		= "";
		$hostaddress	= "";
		$database		= "";

		$con = mysql_connect($hostaddress, $username, $password) or die ('Error when trying to connect to database');
    	mysql_select_db($database);
    }
    
    function dbobject() 
    {
        return $this->__construct();  
    }

	function initFromArray($array)
	{
		// for each attribute in the given array, set the class to its value
		if (is_array($array))
		{
			foreach($array as $attr => $value)
			{
				if (!is_array($value) && property_exists(get_class($this), $attr) && $value != "null" && $value != "(null)")
				{
					$this->$attr = $value;
				}	
			}	
		}
	}
	
	function initFromDbId($id)
	{
		// load the item from the database
		$sql = "SELECT * FROM `".get_class($this)."` WHERE id = ".mysql_escape_string($id);
		$result = mysql_query($sql);
		
		// reuse the initFromArray method
		$this->initFromArray(mysql_fetch_assoc($result));
	}
	
	function insertNew()
	{
	
		$sql = "INSERT INTO `".get_class($this)."` ";
		$sqlAttrs = "(";
		$sqlValues = " VALUES (";			
		
		// loop through the attrs and values, set them to the two strings
		foreach($this as $attr => $value)
		{
			$sqlAttrs .= "`".$attr."`, ";
			if ($attr == 'id')
				$sqlValues .= "NULL, ";
			else
				$sqlValues .= "'".mysql_real_escape_string($value)."', ";
		}		
					
		// remove the extra comma			
		$sqlAttrs = substr($sqlAttrs,0,-2);
		$sqlValues = substr($sqlValues,0,-2);

		$sqlAttrs .= ")";	
		$sqlValues .= ")";	
		
		$sql .= $sqlAttrs.$sqlValues;
		$result = mysql_query($sql);
					
		$this->id = mysql_insert_id();
	
	}
	
	function updateObj()
	{
	
		$sql = "UPDATE `".get_class($this)."` SET ";
			
		foreach($this as $attr => $value)
		{
				$sql .= " `".mysql_real_escape_string($attr)."` = '".mysql_real_escape_string($value)."' , ";
		}
		
		$sql = substr($sql,0,-2);
		$sql .= " WHERE id = ".mysql_escape_string($this->id);
		
		mysql_query($sql);
	
	}
	
	function persist()
	{
		// check if we have a id, if we don't we need to insert rather than update
		if ($this->id > 0)
		{
			// we have a id, but is it valid? lets check if it exists in the db
			$sql = "SELECT * FROM `".get_class($this)."` WHERE `id` = ".mysql_escape_string($this->id);
			$result = mysql_query($sql);
			
			if (mysql_num_rows($result)){
				// valid id that exists in the db, so update it
				$this->updateObj();
			}
			else{
				// has an id, but it is not valid, so reinsert
				$this->insertNew();
			}			
		}
		else{
			// no id, insert into db
			$this->insertNew();
		}
	}
}

?>