<?php
/**
 * makeSecure.php
 * 
 * This file is included at the top of any page you wish to make secure with a login.
 *
 * Access will be granted only if they are logged in, else returned to login page.
 * 
 * Usage:	require('makeSecure.php');
 * 
 */

if(!isset($_SESSION)) 
{ 
	session_start(); 
}  
	
require('login.php');

$loginSys = new Login();

/**
 * if not logged in goto login form, otherwise we can view our page
 */
if(!$loginSys->isLoggedIn())
{
	header("Location: ../../index.php");
	exit;
}



?>