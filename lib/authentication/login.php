<?php

if(!isset($_SESSION)) 
{ 
session_start(); 
}  

class Login {

	var	$db_host,
		$db_name,
		$db_user,
		$db_password,
		$connection,
		$username,
		$password;

	function __construct() {
		
		$this->dusername = "";
		$this->dpassword = "";

   	}

	function __destruct() {
	}

	/**
	 * Check if the user is logged in
	 * 
	 * @return true or false
	 */
	function isLoggedIn()
	{
		if($_SESSION['LoggedIn'])
		{
			return true;
		}
		else return false;
	}
	
	function doLogin($username,$password,$DBHOST,$DBNAME,$DBUSER,$DBPASS) {
		$this->connect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
		
		$this->username = $username;
		$this->password = $password;
		
		// check db for user and pass here.
		$sql = sprintf("SELECT * FROM phpsyndicateuser WHERE username = '%s' and Password = '%s'", 
											$this->clean($this->username), md5($this->clean($this->password)));
						
		$result = mysql_query($sql, $this->connection);
		
		// If no user/password combo exists return false
		if(mysql_affected_rows($this->connection) != 1)
		{
			$this->disconnect();
			return false;
		}
		else // matching login ok
		{
			$row = mysql_fetch_assoc($result);
			
			// more secure to regenerate a new id.
			//session_regenerate_id(); // not allowed on zymic servers
			
			//set session vars up
			$_SESSION['LoggedIn'] = true;
			$_SESSION['userName'] = $this->username;
			
		}
		
		$this->disconnect();
		return true;
	}
	
		/**
	 * Destroy session data/Logout.
	 */
	function logout()
	{
		unset($_SESSION['LoggedIn']);
		unset($_SESSION['userName']);
		session_destroy();
	}
	
	/**
	 * Connect to the Database
	 * 
	 * @return true/false
	 */
	function connect($DBHOST,$DBNAME,$DBUSER,$DBPASS)
	{
		$this->connection = mysql_connect($DBHOST, $DBUSER, $DBPASS) 
														or die("Unable to connect to MySQL");
		
		mysql_select_db($DBNAME, $this->connection) or die("Unable to select DB!");
		
		// Valid connection object? everything ok?
		if($this->connection)
		{
			return true;
		}
		else return false;
	}
	
	/**
	 * Disconnect from the db
	 */
	function disconnect()
	{
		mysql_close($this->connection);
	}
	
	/**
	 * Cleans a string for input into a MySQL Database.
	 * Gets rid of unwanted characters/SQL injection etc.
	 * 
	 * @return string
	 */
	function clean($str)
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
	
	/**
	 * create a random password
	 * 
	 * @param	int $length - length of the returned password
	 * @return	string - password
	 *
	 */
	function randomPassword($length = 8)
	{
		$pass = "";
		
		// possible password chars.
		$chars = array("a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J",
			   "k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T",
			   "u","U","v","V","w","W","x","X","y","Y","z","Z","1","2","3","4","5","6","7","8","9");
			   
		for($i=0 ; $i < $length ; $i++)
		{
			$pass .= $chars[mt_rand(0, count($chars) -1)];
		}
		
		return $pass;
	}


}

?>