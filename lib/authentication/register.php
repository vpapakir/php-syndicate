<?php

class Register {
	
	var $dusername;
	var $dpassword;
	var $dname;
	var $dsurname;
	var $demail;
	
	/**
	 * Cleans a string for input into a MySQL Database.
	 * Gets rid of unwanted characters/SQL injection etc.
	 * 
	 * @return string
	 */
	public function clean($str)
	{
		// Only remove slashes if it's already been slashed by PHP
		if(get_magic_quotes_gpc())
		{
			$str = stripslashes($str);
		}
		// Let MySQL remove nasty characters.
		$str = mysql_real_escape_string($str);
			
		return $str;
	}
	
	function __construct() {
		$this->dusername = "";
		$this->dpassword = "";
		$this->dname     = "";
		$this->dsurname  = "";
		$this->demail    = "";
   	}
	
	function sendConfirmationMail() {
	}
	
	function doRegistration($dusername,$dpassword,$dname,$dsurname,$demail,$DBHOST,$DBNAME,$DBUSER,$DBPASS) {
		$this->dusername = $dusername;
		$this->dpassword = $dpassword;
		$this->dname = $dname;
		$this->dsurname = $dsurname;
		$this->demail = $demail;

		$connection = mysql_connect($DBHOST, $DBUSER, $DBPASS) or die("Unable to connect to MySQL");
		mysql_select_db($DBNAME, $connection) or die("Unable to select DB!");
		
		$reg = new Register();
		
		$pw = md5($reg->clean($dpassword));
		$username = $reg->clean($dusername);
		$email = $this->demail;
		$surname = $this->dsurname;
		$name = $this->dname;
		
		$sql = "INSERT INTO phpsyndicateuser (username, password, email, usertype, name, surname) VALUES ('$username', '$pw', '$email','user','$name','$surname')";
		
		$result = mysql_query($sql);
		if (!$result) {
			die('Error while inserting values: ' . mysql_error());
		}
		
		exit();

	}
	
	function __destruct() {
	}
		
	/**
      *	Show error messages etc.
      */
	public function showMessage()
	{
		if(is_numeric($_GET['msg']))
			{
				switch($_GET['msg'])
				{
					case 1: echo "Please fill both fields.";
					break;
					
					case 2: echo "User Added!";
					break;
					
				}
			}
	}

}

?>