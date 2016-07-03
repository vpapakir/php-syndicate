<?php
/**
 * 	logout.php
 * 
 * 	signs the user out, destroying session data etc.
 * 
 */

if(!isset($_SESSION)) 
{ 
session_start(); 
}  

require('login.php');

$loginSys = new Login();

$loginSys->logout();

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Thank you!</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<link rel="shortcut icon" href="../../images/favicon.ico" type="image/x-icon" />
	<script type="text/javascript" src="../../js/json.js"></script> 
	<script type="text/javascript" src="../../js/ajax.js"></script> 
	<script type="text/javascript" src="../../js/installer.js"></script>
	<script type="text/javascript" src="../../js/progbar.js"></script> 
    <link rel="stylesheet" type="text/css" href="../../css/installer.css">
	<script type="text/javascript" src="../../js/func.js">
    </script>
</head>

<body>

<table cellspacing="3" style="border-collapse:collapse;border-spacing:0;" cellpadding="3" border="0" width="100%" height="100%" id="maintbl">

<tr>
	<td colspan="2" id="topcell" valign="middle">
	<!-- Top bar -->
	<table cellspacing="0" cellpadding="0" width="100%" border="0">
		<tr>
			<td id="message" nowrap="nowrap">Thank you!</td>
			<td width="100" align="right"></td>
		</tr>
	</table>
	<!-- / Top bar -->
	
</tr>

<tr>
	<td valign="top" id="maincell" width="75%">
	<!-- Main window -->
Return to <a href="../../index.php" tabindex="1" title="Login Page" accesskey="o" target="_self">Login Page</a>.
</td>
</tr>

</table>

</body>

</html>