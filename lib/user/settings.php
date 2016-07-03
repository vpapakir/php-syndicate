<?php 
	require('../authentication/makeSecure.php'); 
	/*include $_SERVER['DOCUMENT_ROOT'].'phpsyndicate/config/header2.php';
	include $_SERVER['DOCUMENT_ROOT'].'phpsyndicate/lib/admin/admin.php';
	include $_SERVER['DOCUMENT_ROOT'].'phpsyndicate/lib/data/mysqlconnector.php';
	include $_SERVER['DOCUMENT_ROOT'].'phpsyndicate/lib/data/CSVReader.php';
	include $_SERVER['DOCUMENT_ROOT'].'phpsyndicate/lib/mail/MailMessage.php';
	include $_SERVER['DOCUMENT_ROOT'].'phpsyndicate/setup/Installer.php';*/
	include '../../config/header2.php';
	include '../admin/admin.php';
	include '../data/mysqlconnector.php';
	include '../data/CSVReader.php';
	include '../mail/MailMessage.php';
	include '../misc/util.php';

	//include 'phpsyndicate/setup/Installer.php';
	if(!isset($_SESSION)) 
	{ 
	session_start(); 
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
<link rel="stylesheet" type="text/css" href="../../css/main.css">
<title><?php echo $APPLICATION_NAME; ?> - Settings</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<link rel="shortcut icon" href="../../images/favicon.ico" type="image/x-icon" />
<script type="text/javascript" src="../../js/json.js"></script>
<script type="text/javascript" src="../../js/ajax.js"></script>
<script type="text/javascript" src="../../js/installer.js"></script>
<script type="text/javascript" src="../../js/progbar.js"></script>
<link rel="stylesheet" type="text/css" href="../../css/installer.css">
<script type="text/javascript" src="../../js/func.js">
    </script>
<script type="text/javascript">
	function gotologout()
	{
		var confirmDisconnect = window.confirm("You are about to disconnect from your phpSyndicate session!\r\nAre you sure?");
		if(confirmDisconnect == true) {
			window.location = "../authentication/logout.php";
		} else {
			// DO NOTHING
		}
	}
		
	var clear = true;
    
	function clear(obj)
    {
        if(clear)
        {
            obj.value = '';
            clear = false;
        }
    }

    </script>
</head>
<body>
<table cellspacing="3" style="border-collapse:collapse;border-spacing:0;" cellpadding="3" border="0" width="100%" id="maintbl">
  <tr>
    <td colspan="2" id="topcell" valign="middle"><!-- Top bar -->
      <table cellspacing="0" cellpadding="0" width="100%" border="0">
        <tr>
          <td id="message" nowrap="nowrap"><?php
				$username = $_SESSION['userName'];
				echo "Welcome <b>".$username."</b>";
			?></td>
          <td width="100" align="right"></td>
        </tr>
        <tr>
          <td></td>
          <td><a href="../authentication/panel.php" title="Home">Home</a> | <a href="settings.php" title="Settings" target="_blank">Settings</a></td>
        </tr>
      </table>
      <!-- / Top bar -->
  </tr>
  <tr>
    <td width="83%" valign="top" id="maincell"><!-- Main window -->
      <fieldset>
        <legend>Settings</legend>

		<form action=<?php echo $_SERVER['PHP_SELF']."?username=".$_SESSION['userName']; ?> method="post" enctype="multipart/form-data" name="profilesform" target="_self">
        	<table width="70%" border="1">
	        <tr>
              <th scope="row">Load SMTP Profiles:</th>
              <td>
              <input name="smtp_prof_upl" id="smtp_prof_upl" type="file" size="29" />
              </td>
            </tr>
	        <tr>
              <th scope="row">&nbsp;</th>
              <td>
              <input name="load_profiles_btn" type="submit" id="submitprofiles" value="Load Profiles"/>
              </td>
            </tr>
            </table>
        </form>
        <?php
        	$target_path = "../../uploads/";
			$csvobj = new CSVReader($target_path,',','PROFILENAME');
			
			if(isset($_FILES['smtp_prof_upl'])) {
				$target_path = $target_path . basename( $_FILES['smtp_prof_upl']['name']).genRandomString();
				$_SESSION['uploadedprofile'] = $target_path;
				if(!move_uploaded_file($_FILES['smtp_prof_upl']['tmp_name'], $target_path)) {
					$file_handle = fopen($_FILES['smtp_prof_upl']['tmp_name'], "r");
					while (!feof($file_handle)) {
						$line = fgets($file_handle);
					}
					$csvobj = new CSVReader($_FILES['smtp_prof_upl']['tmp_name'],',','PROFILENAME');
					$csvobj->ReadCSV();
					$csvobj->ListAll();
				} else {
					echo "<p>The file ".  basename( $_FILES['smtp_prof_upl']['name'])." has been uploaded</p>";	
					$csvobj = new CSVReader($target_path,',','PROFILENAME');
					$csvobj->ReadCSV();
					$csvobj->ListAll();
				}
				// TODO: STORE PROFILES IN DB
				for($i=0;$i<($csvobj->Items_Count);$i++) {
					$dbconobjprof = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
					$tmp_res = $dbconobjprof->getResult("INSERT INTO `phpsyndicatesmtp_profile` (`profileid`, `profilenickname`, `username`, `smtp_server`, `smtp_port`, `smtp_user`, `smtp_pass`, `email`) VALUES ('%d','%s','%s','%s','%s','%s','%s','%s')",rand(),$csvobj->ItemsList[$i]['PROFILENAME'],$_SESSION['userName'],$csvobj->ItemsList[$i]['SMTP_HOST'],$csvobj->ItemsList[$i]['SMTP_PORT'],$csvobj->ItemsList[$i]['SMTP_USER'],$csvobj->ItemsList[$i]['SMTP_PASS'],$csvobj->ItemsList[$i]['FROMEMAIL']);
					$dbconobjprof->disconnect();
				}
			} else {
				// no uploaded profiles file
			}
        ?>
<hr />
        <form action=<?php echo $_SERVER['PHP_SELF']."?username=".$_SESSION['userName']; ?> method="post" enctype="multipart/form-data" name="settingsform" target="_self">
          <table width="70%" border="1">
            <tr>
              <th scope="row">Username:</th>
              <td><input name="username" type="text" id="username" size="40" onclick='this.value=""' maxlength="40" 
              value=
              <?php
			  $dbconobj = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
			  $res = $dbconobj->getResult("SELECT `username` FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
			  if($res) {
					echo '"'.$res[0]->username.'"';
                } else {
                    // NO BANNED ADDRESSES TO DISPLAY
                    echo '"Please choose a username..."';
                }
			  $dbconobj->disconnect();
			  ?> /></td>
            </tr>
            <tr>
              <th scope="row">Password:</th>
              <td>
              <input name="password" type="text" id="password" size="40" onclick='this.value=""' maxlength="40"/>
              </td>
            </tr>
            <tr>
              <th scope="row">Retype Password:</th>
              <td>
              <input name="password2" type="text" id="password2" size="40" onclick='this.value=""' maxlength="40"/>
              </td>
            </tr>
            <tr>
              <th scope="row">SMTP  Profiles:</th>
              <td>
              	<select name="smtpprofiles">
                <?php
					$num_of_profiles = 5;
					$dbconobjfrom = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
			  		$resss = $dbconobjfrom->getResult("SELECT * FROM `phpsyndicatesmtp_profile` WHERE `username`='%s'",$_SESSION['userName']);
					if($resss) {
						$num_of_profiles = count($resss);
					} else {
						$num_of_profiles=0;
					}
					if($num_of_profiles>0) {
						for($i=0;$i<$num_of_profiles;$i++) {
							echo '<option value="'.$i.'">'.$resss[$i]->profilenickname.'</option>';
						}
					} else {
						echo '<option value="0">No profiles loaded</option>';
					}
					$dbconobjfrom->disconnect();
				?>
                </select>
              </td>
            </tr>
            <tr>
              <th scope="row">"FROM" Email (Default Profile):</th>
              <td>
              <input name="fromemail" type="text" id="password2" size="40" onclick='this.value=""' maxlength="40" 
              value=
			  <?php 
				  $dbconobjfrom = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
				  $res = $dbconobjfrom->getResult("SELECT `email` FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
				  if($res) {
						echo '"'.$res[0]->email.'"';
				  } else {
					   // NO BANNED ADDRESSES TO DISPLAY
					   echo '"Please add an email address..."';
				  }
				  $dbconobjfrom->disconnect();
			  ?>
              />
              </td>
            </tr>
            <tr>
              <th scope="row">SMTP Server (Default Profile):</th>
              <td><input name="smtpserver" type="text" id="smtpserver" size="40" onclick='this.value=""' maxlength="40" 
              value=
              <?php
			  $dbconobj = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
			  $res = $dbconobj->getResult("SELECT `smtp_server` FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
			  if($res) {
					echo '"'.$res[0]->smtp_server.'"';
                } else {
                    // NO BANNED ADDRESSES TO DISPLAY
                    echo '"Please choose an SMTP server..."';
                }
			  $dbconobj->disconnect();
			  ?> /></td>
            </tr>
            <tr>
            <tr>
              <th scope="row">SMTP Port (Default Profile):</th>
              <td><input name="smtpport" type="text" id="smtpport" size="40" onclick='this.value=""' maxlength="40" 
              value=
              <?php
			  $dbconobj = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
			  $res = $dbconobj->getResult("SELECT `smtp_port` FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
			  if($res) {
					echo '"'.$res[0]->smtp_port.'"';
                } else {
                    // NO BANNED ADDRESSES TO DISPLAY
                    echo '"Please choose an SMTP port..."';
                }
			  $dbconobj->disconnect();
			  ?> /></td>
            <tr>
            <tr>
              <th scope="row">SMTP Username (Default Profile):</th>
              <td><input name="smtpusername" type="text" id="smtpusername" size="40" onclick='this.value=""' maxlength="40" 
              value=
              <?php
			  $dbconobj = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
			  $res = $dbconobj->getResult("SELECT `smtp_user` FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
			  if($res) {
					echo '"'.$res[0]->smtp_user.'"';
                } else {
                    // NO BANNED ADDRESSES TO DISPLAY
                    echo '"Please choose an SMTP username..."';
                }
			  $dbconobj->disconnect();
			  ?> /></td>
            <tr>
            <tr>
              <th scope="row">SMTP Password (Default Profile):</th>
              <td><input name="smtppassword" type="text" id="smtppassword" size="40" onclick='this.value=""' maxlength="40" 
              value=
              <?php
			  $dbconobj = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
			  $res = $dbconobj->getResult("SELECT `smtp_pass` FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
			  if($res) {
					echo '"'.$res[0]->smtp_pass.'"';
                } else {
                    // NO BANNED ADDRESSES TO DISPLAY
                    echo '"Please choose an SMTP password..."';
                }
			  $dbconobj->disconnect();
			  ?> /></td>
            <tr>
              <th width="32%" height="88" scope="row"><label for="banned_domains_list">Do NOT Send To Email List:</label></th>
              <td width="68%">
              <p>
                <select name="add_replace" id="add_replace">
                  <option value="1">Add</option>
                  <option value="2">Replace</option>                
                </select>
              </p>
              <?php
			  	echo '<textarea name="banned_domains_list" wrap="physical" id="banned_domains_list" cols="60" rows="20">';
				$dbcon = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
				$res = $dbcon->getResult("SELECT `email`,`domain` FROM `phpsyndicatedo_not_send_list`");
				if($res) {
					for($i=0;$i<(count($res));$i++) {
						echo $res[$i]->email."@".$res[$i]->domain."\r\n";
					}
				} else {
					// NO BANNED ADDRESSES TO DISPLAY
					echo 'NO BLACKLISTED ADDRESSESS';
				}
				echo "</textarea>";
				$dbcon->disconnect();
			  ?>
              </td>
            </tr>
            <tr>
              <th scope="row">Time Delay Between Emails (sec):</th>
              <td><?php 
	  	if(isset($_SESSION['delay'])) {
			echo "<input name=\"delay\" type=\"text\" id=\"delay\" value=\"".($_SESSION['delay']/1000000)."\" size=\"40\" maxlength=\"40\" />";
		} else {
	  		echo "<input name=\"delay\" type=\"text\" id=\"delay\" value=\"".($DISPDELAY/1000000)."\" size=\"40\" maxlength=\"40\" />";
		}
	  ?></td>
            </tr>
            <tr>
              <th scope="row">Serial Key:</th>
              <td><?php
      	if(isset($_SESSION['serial'])) {
			echo "<input name=\"serial\" type=\"text\" id=\"delay\" value=\"".$_SESSION['serial']."\" size=\"40\" maxlength=\"40\" />";
		} else {
      		echo "<input name=\"serial\" type=\"text\" id=\"serial\" value=\"".$SERIAL."\" size=\"40\" maxlength=\"40\" />";
		}
	  ?></td>
            </tr>
          </table>
          
          <div align="center">
            <p>&nbsp;            </p>
            <p>
              <input type="submit" name="config_button" id="config_button" value="Save Settings" />
            </p>
          </div>
        
        </form>
        <?php
			if( isset($_POST['delay']) && isset($_POST['serial']) && isset($_POST['banned_domains_list']) && isset($_POST['username']) ) {
				
				if( isset($_POST['password']) && isset($_POST['password2']) ) {
					if( ($_POST['password'] == $_POST['password2']) ) {
						if((strcmp($_POST['password2'],"") != 0) && (strcmp($_POST['password'],"") != 0)) {
							$dbobj_ban = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
							$result_name = $dbobj_ban->getResult("SELECT * FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
							$newpassword = md5(clean($_POST['password']));
							if( (count($result_name) > 0) && (strcmp($result_name[0]->password,$newpassword) != 0) ) {
								$result_name = $dbobj_ban->getResult("UPDATE `phpsyndicateuser` SET `password`='%s' WHERE `phpsyndicateuser`.`username` = '%s' LIMIT 1;",$newpassword,$_SESSION['userName']);
							}
							$dbobj_ban->disconnect();
						}
					} else {
						exit("Passwords do not match!");
					}
				}

				$dbobj_ban3 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
				$result_username = $dbobj_ban3->getResult("SELECT * FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
				if(count($result_username) > 0) {
					$result_username   = $dbobj_ban3->getResult("UPDATE `phpsyndicateuser` SET `username` = '%s' WHERE `phpsyndicateuser`.`username` = '%s' LIMIT 1;",$_POST['username'],$_SESSION['userName']);
					$_SESSION['userName'] = $_POST['username'];
				}
				$dbobj_ban3->disconnect();

				$dbobj_ban4 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
				$result_smtp_server   = $dbobj_ban4->getResult("SELECT * FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
				if(count($result_smtp_server) > 0) {
					$result_smtp_server   = $dbobj_ban4->getResult("UPDATE `phpsyndicateuser` SET `smtp_server` = '%s' WHERE `phpsyndicateuser`.`username` = '%s' LIMIT 1;",$_POST['smtpserver'],$_SESSION['userName']);
				}
				$dbobj_ban4->disconnect();

				$dbobj_ban5 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
				$result_smtp_port   = $dbobj_ban5->getResult("SELECT * FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
				if(count($result_smtp_port) > 0) {
					$result_smtp_port = $dbobj_ban5->getResult("UPDATE `phpsyndicateuser` SET `smtp_port` = '%s' WHERE `phpsyndicateuser`.`username` = '%s' LIMIT 1;",$_POST['smtpport'],$_SESSION['userName']);
				}
				$dbobj_ban5->disconnect();

				$dbobj_ban6 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
				$result_smtp_user   = $dbobj_ban6->getResult("SELECT * FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
				if(count($result_smtp_user) > 0) {
					$result_smtp_user = $dbobj_ban6->getResult("UPDATE `phpsyndicateuser` SET `smtp_user` = '%s' WHERE `phpsyndicateuser`.`username` = '%s' LIMIT 1;",$_POST['smtpusername'],$_SESSION['userName']);
				}

				$dbobj_fromemail = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
				$result_fromemail   = $dbobj_fromemail->getResult("SELECT * FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
				if(count($result_fromemail) > 0) {
					$result_fromemail = $dbobj_fromemail->getResult("UPDATE `phpsyndicateuser` SET `email` = '%s' WHERE `phpsyndicateuser`.`username` = '%s' LIMIT 1;",$_POST['fromemail'],$_SESSION['userName']);
				}
				$dbobj_ban6->disconnect();

				$dbobj_ban7 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
				$result_smtp_pass = $dbobj_ban7->getResult("SELECT * FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
				if(count($result_smtp_pass) > 0) {
					$result_smtp_pass = $dbobj_ban7->getResult("UPDATE `phpsyndicateuser` SET `smtp_pass` = '%s' WHERE `phpsyndicateuser`.`username` = '%s' LIMIT 1;",$_POST['smtppassword'],$_SESSION['userName']);
				}
				$dbobj_ban7->disconnect();

					$hasUpdated = false;
					$bannedemailist = $_POST['banned_domains_list'];
					$tok = strtok($bannedemailist, ",\n\t ");
					
					if ($_POST['add_replace'] == 1) {
						// Do nothing
					} else {
						$dbobj_replace = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
						$result_replace = @$dbobj_replace->getResult("DELETE FROM `phpsyndicatedo_not_send_list`");
						$dbobj_replace->disconnect();
					}
					
					while ( ($tok !== false) ) {
						$dbobj_ban_2 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
						$tmp       = substr($tok,0,strpos($tok,"@"));
						$tmp2      = substr($tok,strpos($tok,"@")+1);
						$result1   = $dbobj_ban_2->getResult("SELECT * FROM `phpsyndicatedo_not_send_list` WHERE `email`='%s'",$tmp);
						if(count($result1) > 0) {
							// Do nothing
						} else {
							if ($_POST['add_replace'] == 1) {
								$result_ban = @$dbobj_ban_2->getResult("INSERT INTO `phpsyndicatedo_not_send_list` (`id`, `email`, `domain`, `name`, `comments`) VALUES ('%s','%s','%s','%s','%s')",NULL,$tmp,$tmp2,"Name","Comments");
							} else {
								$result_ban = @$dbobj_ban_2->getResult("INSERT INTO `phpsyndicatedo_not_send_list` (`id`, `email`, `domain`, `name`, `comments`) VALUES ('%s','%s','%s','%s','%s')",NULL,$tmp,$tmp2,"Name","Comments");
							}
						}	
						$dbobj_ban_2->disconnect();
						$tok = strtok(" ,\n\t");
					}
					$hasUpdated = true;
					$_SESSION['delay'] = $_POST['delay'];
					$_SESSION['serial'] = $_POST['serial'];
				if($hasUpdated) {
					usleep(1000);
					//header('Location: '.curPageURL());
					echo "<META HTTP-EQUIV=\"Refresh\" Content=\"0; URL=settings.php?username=$username\">";
					echo "Settings have been updated successfully!";
				} else {
					echo "Settings update failed!";
				}
			} else {
				// TODO: leave configuration values	untouched
			}
		?>
		  </fieldset>
		  <p>
		  <?php
		  	function curPageURL() {
			 $pageURL = 'http';
			 if ($_SERVER["HTTPS"] == "on") {
				 $pageURL .= "s";
			 }
			 $pageURL .= "://";
			 if ($_SERVER["SERVER_PORT"] != "80") {
			 	$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			 } else {
			 	$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			 }
			 return $pageURL;
			}			
          ?>
      </p></td>
    <td valign="top" id="menucell" width="17%"><!-- Menu -->
      <br>
      
      <div style="margin-left:50px"> 
      <span id="process">
      <img src="../../images/processing.gif" border="0" align="absmiddle" hspace="5" alt="Processing, be patient!" title="Processing, be patient!">
      </span>
      <input type="button" id="BtnContinue" value="Logout" onClick="gotologout()">
      </div>
      <!-- / Menu -->
      </td>
  </tr>
</table>
</body>
</html>