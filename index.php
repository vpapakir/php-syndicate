<?php

include 'config/header2.php';
include 'lib/authentication/login.php';
include 'lib/authentication/register.php';

error_reporting(E_ALL);
// set_time_limit(0); // not allowed on zymic servers

//start the session if it's not started already
if(!isset($_SESSION)) 
{ 
session_start(); 
}

if(isset($_SESSION['userName'])) { // check if user is already logged in
	if($_SESSION['userName'] != "") { // if yes, then redirect user to panel
		header('Location: lib/authentication/panel.php?username='.$_SESSION['userName']);
	}
}

if( isset( $_POST['username'] ) && isset( $_POST['password'] ) ) {
	
	$username = $_POST['username'];
	$password = $_POST['password'];
	$loginobj = new Login();
	
	$loginsucc = $loginobj->doLogin($username,$password,$DBHOST,$DBNAME,$DBUSER,$DBPASS);
	
	if($loginsucc) {
		header('Location: lib/authentication/panel.php?username='.$username) ;
	} else {
		echo "<p>Login Failed</p>";
	}
	
} else {
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Welcome to <?php echo $APPLICATION_NAME; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon" />
<script type="text/javascript" src="js/json.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/installer.js"></script>
<script type="text/javascript" src="js/progbar.js"></script>
<link rel="stylesheet" type="text/css" href="css/installer.css">
<script type="text/javascript" src="js/func.js">
    </script>
</head>
<body>
<table cellspacing="3" style="border-collapse:collapse;border-spacing:0;" cellpadding="3" border="0" width="100%" height="100%" id="maintbl">
  <tr>
    <td colspan="2" id="topcell" valign="middle"><!-- Top bar -->
      <table cellspacing="0" cellpadding="0" width="100%" border="0">
        <tr>
          <td id="message" nowrap="nowrap">Welcome to <?php echo $APPLICATION_NAME; ?></td>
          <td width="100" align="right"></td>
        </tr>
      </table>
      <!-- / Top bar -->
  </tr>
  <tr>
    <td valign="top" id="maincell" width="75%"><!-- Main window -->
      <fieldset>
        <legend>Sign In</legend>
        <form id="form1" name="form1" method="post" action="index.php">
          <table width="39%" height="137" border="0">
            <tr>
              <th width="31%" nowrap scope="row">User Name:</th>
              <td width="69%" nowrap><input name="username" type="text" id="username" size="25" maxlength="25" required="required" /></td>
            </tr>
            <tr>
              <th nowrap scope="row">Password:</th>
              <td nowrap><input name="password" type="password" id="password" size="25" maxlength="25" required="required" /></td>
            </tr>
            <tr>
              <th nowrap scope="row"><input type="submit" name="login" id="login" onClick="notEmpty()" value="Log-in" /></th>
              <td nowrap>Need help? Click <a href="help/help.htm">here</a>!</td>
            </tr>
          </table>
        </form>
      </fieldset>
      <p>
      </p>
      <!-- <fieldset>
<legend>Sign Up</legend>
<form id="form2" name="form2" method="post" style="border:thin;" enctype="multipart/form-data" action="index.php">
  <p>
    <label>Username
      <input name="dusername" type="text" id="dusername" size="20" maxlength="20" />
    </label>
  </p>
  <p>
    <label>Password
      <input name="dpassword" type="password" id="dpassword" size="20" maxlength="20" />
    </label>
  </p>
  <p>Re-type Password 
    <input name="dpassword2" type="password" id="dpassword2" size="20" maxlength="20" />
  </p>
  <p>
    <label>Name
      <input name="dname" type="text" id="dname" size="25" maxlength="25" />
    </label>
  </p>
  <p>
    <label>Surname
      <input name="dsurname" type="text" id="dsurname" size="25" maxlength="25" />
    </label>
  </p>
  <p>
    <label>Email
        <input type="text" name="demail" id="demail" />
    </label>
  </p>
  <p>
    <img name="captcha" src="./lib/authentication/captcha/captcha.php?.png" width="187" height="55" alt="Write down the depicted text" />
    <input type="submit" name="refresh" id="refresh" value="Reload" accesskey="r" tabindex="5" onClick="window.location.reload()"/>
  </p>
  
  <p>
    <label>Type the captcha:
      <input type="text" name="dcaptcha" id="dcaptcha" />
    </label>
  </p>  
  
  <p>
    <label>
      <input type="submit" name="register_button" id="register_button" onClick="notEmpty2()" value="Register Now!" />
    </label>
    <label>
      <input type="reset" name="reset_reg_form" id="reset_reg_form" value="Reset" />
    </label>
  </p>
</form>
</fieldset> -->
      <?php
	/*if( isset( $_POST['dusername'] ) && isset( $_POST['dpassword'] ) && isset( $_POST['dpassword2'] ) && isset( $_POST['dname'] ) && isset( $_POST['dsurname'] ) && isset( $_POST['demail'] ) && isset( $_POST['dcaptcha'] ) ) {
				
		if ( ($_SESSION['CAPTCHAString'] == $_POST['dcaptcha']) && ($_POST['dpassword'] == $_POST['dpassword2']) )
        {
			$dusername = $_POST['dusername'];
			$dpassword = $_POST['dpassword'];
			$dname     = $_POST['dname'];
			$dsurname  = $_POST['dsurname'];
			$demail    = $_POST['demail'];
			$regobj = new Register();
			
			$regres = $regobj->doRegistration($dusername,$dpassword,$dname,$dsurname,$demail,$DBHOST,$DBNAME,$DBUSER,$DBPASS);
			
			if($regres) {
				$regobj->sendConfirmationMail();
			} else {
				echo "<p>Registration Failed</p>";
			}
		}
        else
        {
          echo 'Strings are not equal.';
        }
		
	} else {
		//
	}*/
?>
      <p>&nbsp; </p></td>
  </tr>
</table>

<script type="text/javascript"><!--
google_ad_client = "ca-pub-3344559188794146";
/* group4 */
google_ad_slot = "3543075165";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>

</body>
</html>
