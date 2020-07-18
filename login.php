<?php
include 'vars.inc.php';
include 'template.inc.php';
include 'phpmm.php';

if( isset($_POST['username']) && isset($_POST['password']) ) {
	$user = $_POST['username'];
	$pass = $_POST['password'];
	//echo $user.$pass;
	//convert the field values to simple variables
	//add slashes to the username and md5() the password
	//$user = addslashes($_POST['username']);
	//$pass = md5($_POST['password']);	
	//connet to the database
	$db = mysql_connect("$dbHost", "$dbUser", "$dbPass") or die ("Error connecting to database.");
	mysql_select_db("$dbDatabase", $db) or die ("Couldn't select the database.");
	$result=mysql_query("select * from users where username='$user' AND password='$pass'", $db);
	//check that at least one row was returned
	$rowCheck = mysql_num_rows($result);
	if($rowCheck > 0) {
		while($row = mysql_fetch_array($result)) {
			//start the session and register a variable
			session_start();
			session_register('username');
			
			//successful login code will go here...
			echo 'Success!Click <a href="index.php">here</a> to enter the user panel.';
		}
	} else {
		//if nothing is returned by the query, unsuccessful login code goes here...
		echo "Incorrect login name or password. Please try <a href=\"index.html\">again.</a>";
	}
} else {
	echo '<script type="text/javascript">alert("Username and Password cannot be blanks!");</script>';
}

?>