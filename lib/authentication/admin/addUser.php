<?php 

require('../../config/header2.php');

if(isset($_POST['Submit']))
{

	if((!$_POST['Username']) || (!$_POST['Password']))
	{
		// display error message
		header('location: addUser.php?msg=1');
		exit;
	}
	
	$connection = mysql_connect($dbhost, $dbuser, $dbpassword) or die("Unable to connect to MySQL");
	mysql_select_db($dbname, $connection) or die("Unable to select DB!");
	
	$pw = md5(clean($_POST['Password']));
	$username = clean($_POST['Username']);
	
	$sql = "INSERT INTO user_tbl (UserName, Password) VALUES ('$username', '$pw')";
	
	mysql_query($sql) or die('Error Inserting Values');
	
	header('location: addUser.php?msg=2');
	exit();
	
}

/**
 *	Show error messages etc.
 */
function showMessage()
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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Add User</title>
	<link href="../css/styles.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>
<div id="wrapper">
	<div class="cent"><h1>Add User</h1></div>
	<div class="cent"><? showMessage();?></div>
	<form action="<? echo $_SERVER['PHP_SELF'];?>" method="post" name="addUserForm">
		<input name="Username" type="text" value="Username" size="30" maxlength="30" /><br />
		<input name="Password" type="text" value="Password" size="30" maxlength="30" /><br />
		<input name="Submit" type="submit" value="Add" />
	</form>
</div>
</body>
</html>
