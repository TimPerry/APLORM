<?php

	// double check we are on ssl (using secret key, dont want someone to read it.
	if ($_SERVER['SERVER_PORT']!=443)
	{
		$url = "https://". $_SERVER['SERVER_NAME'] . ":443".$_SERVER['REQUEST_URI'];
		header("Location: $url");
	}
	
	// define secret key
	define("KEY", "<enter_key_here>");
		
	// check the key is set	
	if (!isset($_REQUEST['key']))	
		die ("Key not set");
		
	// check if the key is valid	
	if ($_REQUEST['key'] != KEY)
		die ("Invalid key");
		
	// setup datbase connection
	$username		= "";
	$password		= "";
	$hostaddress	= "";
	$database		= "";
	
	// check it we are just creating new classes or overriding the old ones
	
	$should_override = 0;
	
	if (isset($_GET['override']))
		$should_override = $_GET['override'];
	
	$con = mysql_connect($hostaddress, $username, $password) or die ("Error when trying to connect to database");
	mysql_select_db($database);
	
	// get all the tables
	$sql = "SHOW TABLES FROM ".$database;
	$result  = mysql_query($sql);
	
	// loop through each table
	while($row = mysql_fetch_array($result))
	{
		$tableName = $row[0];
	
		echo "<strong>Found new table, creating new class with name: ".$tableName."</br></strong>";
		
		// get the field names from the database
		$sql2 = 'SHOW COLUMNS FROM `'.$tableName.'`';
		$result2 = mysql_query($sql2);
		
		// start the file string
		$fileStr = "<?php \ninclude_once(\"dbobject.class.php\");\n\nclass ".$tableName." extends dbobject {\n";
			
		// whilst looping, add to str
		while($row2 = mysql_fetch_array($result2))
		{	
			$fieldName = $row2[0];
			if ($fieldName != "id")
			{
				$fileStr .= "\nvar $".$fieldName.";";
				echo "Found new field, adding new attribute <strong>".$fieldName."</strong> to class</br>"; 
			}
		}
		
		echo "<br/>";
		
		$fileStr .= "\n\n}\n?>";
			
		// save to classes folder
		$newClass = "classes/".$tableName.".class.php";
		if (file_exists($newClass))
		{
			if ($should_override != 0){
				echo "Overriding file: ".$newClass."</br>";
				$fh = fopen($newClass, 'w') or die("can't open file");
				fwrite($fh, $fileStr);
				fclose($fh);
			}
			else{
				echo "Not overriding file: ".$newClass."</br>";
			}
		}
		else
		{
			echo "Creating new file: ".$newClass."</br>";
			$fh = fopen($newClass, 'w') or die("can't open file");
			fwrite($fh, $fileStr);
			fclose($fh);
		}

	}
	
	echo "<strong>Finished.</strong>"; 
?>