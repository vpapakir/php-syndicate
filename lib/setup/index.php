<?php
	error_reporting(E_ALL);
	include 'Installer.php';
	include '../config/header2.php';
	// Create Ajax class instance
	$ajax = new Ajax(); 
	$ajax->registerMethod( 'Installer', 'executeCheck' );
	$ajaxServer = $ajax->getServer(); 
	// Check for Ajax Request and handle it.
	if ( $ajaxServer->isRequest() ) 
	{ 
	  echo $ajaxServer->handleRequest(); 
	  exit(); 
	} 
	$ajaxClient = $ajax->getClient(); 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title><?php echo $APPLICATION_NAME; ?> Installer</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<link rel="shortcut icon" href="../images/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="../css/installer.css">
	<script type="text/javascript" src="../js/json.js"></script> 
	<script type="text/javascript" src="../js/ajax.js"></script> 
	<script type="text/javascript" src="../js/installer.js"></script>
	<script type="text/javascript" src="../js/progbar.js"></script> 
	
	<script>
	var Installer = new Installer();
	<?php echo $ajaxClient->getJavaScript(); ?> 
	</script>
</head>

<body>
<table cellspacing="3" cellpadding="3" border="0" width="100%" height="100%" id="maintbl">
<tr>
	<td colspan="2" id="topcell" valign="middle">
	<!-- Top bar -->
	<table cellspacing="0" cellpadding="0" width="100%" border="0">
		<tr>
			<td id="message" nowrap="nowrap">Welcome to <?php echo $APPLICATION_NAME; ?> installation.</td>
			<td width="100" align="right"></td>
		</tr>
	</table>
	<!-- / Top bar -->
	
</tr>
<tr>
	<td valign="top" id="maincell" width="75%">
	<!-- Main window -->
	
	<!-- Page 0 -->
	<div id="page0">
	
	<blockquote>
	
	Congratulations with your copy of <?php echo $APPLICATION_NAME; ?><br/>
			Before we start there are few things that need to be done.
			<br/><br/>

			<ul>
				<li>1) Create an empty database.</li>
				<blockquote style="margin-top:0px;">At the moment the following databases are supported 
				<br/> a) MySQL (3.x, 4.x and up)
				</blockquote></li>
			</ul>

	
		</blockquote>
	</div>
	<!-- Page 1 -->
	<div id="page1" class="hidden">
	<blockquote>
	
	<div class="info">
		Before the installation continues, <?php echo $APPLICATION_NAME; ?> needs to verify the host environment.<br/>
		Each procedure must return positive results for the installer to continue.
	</div>
	
	<table cellspacing="1" cellpadding="1" border="0" width="100%" class="list">
		<tr>
			<td class="header">Procedure</td>
			<td class="header">Status</td>
			<td class="header">Results</td>
		</tr>
		<tr>
			<td width="250">Verify PHP version</td>
			<td width="100" id="t0">[pending]</td>
			<td id="r0"></td>
		</tr>
		<tr>
			<td>Verify GD libraries</td>
			<td id="t1">[pending]</td>
			<td id="r1"></td>
		</tr>
		<tr>
			<td>Verify SimpleXML module</td>
			<td id="t2">[pending]</td>
			<td id="r2"></td>
		</tr>
		<tr>
			<td>Verify MBstring module</td>
			<td id="t3">[pending]</td>
			<td id="r3"></td>
		</tr>
		<tr>
			<td>Verify Session support</td>
			<td id="t4">[pending]</td>
			<td id="r4"></td>
		</tr>
		<tr>
			<td>Verify Remote fetch support</td>
			<td id="t5">[pending]</td>
			<td id="r5"></td>
		</tr>
		<tr>
			<td>Verify File upload support</td>
			<td id="t6">[pending]</td>
			<td id="r6"></td>
		</tr>
		<tr>
			<td>Verify Upload folders</td>
			<td id="t7">[pending]</td>
			<td id="r7"></td>
		</tr>
		<tr>
			<td>Verify Config file</td>
			<td id="t8">[pending]</td>
			<td id="r8"></td>
		</tr>
		<tr>
			<td>Verify Database support</td>
			<td id="t9">[pending]</td>
			<td id="r9"></td>
		</tr>
	</table>
	<br/>
		
		<div id="checksuccess" class="goodinfo">
		Your web-server meets all the requirements to run phpSyndicate.<br/>
		Your can now continue to the database settings part of the installer.
		</div>
		<div id="checkerrors" class="badinfo"></div>
	
	</blockquote>
	</div>
	
	
	<!-- Page 2 -->
	<div id="page2" class="hidden">
		<blockquote>
		
			<div class="info">
			Type in the credentials for your database connection, and make sure you select the correct database type you want to use with phpSyndicate.<br>
			The database type selection has been set to match those supported by your server.
			</div>
		
			
			<fieldset>
			<legend><?php echo $APPLICATION_NAME; ?> database settings</legend>
			<table cellspacing="1" cellpadding="1" border="0" width="550" class="list">
			<tr>
				<td width="220">Database host server:</td>
				<td><input type="text" name="dbhost" id="dbhost" size="25"></td>
			</tr>
			<tr>
				<td>Database username:</td>
				<td><input type="text" name="dbuser" id="dbusername" size="25"></td>
			</tr>
			<tr>
				<td>Database password:</td>
				<td><input type="password" name="dbpassword" id="dbpassword" size="25"></td>
			</tr>
			<tr>
				<td>Database name:</td>
				<td><input type="text" name="dbname" id="dbname" size="25"></td>
			</tr>
			<tr>
				<td>Database type:</td>
				<td><select id="dbtype" name="dbtype" onChange="Installer.dbChange(this)"></select></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><br><input type="button" value="Verify settings" id="verify" onClick="Installer.verifyConnection(this)"></td>
			</tr>
			</table>
			</fieldset>
			
			<br/>
			<div id="dbsuccess" class="goodinfo">
			Database access successfully tested and verified..<br>
			You can now continue to next step to create the database tables.
			</div>
			<div id="dberror" class="badinfo"></div>
		
		</blockquote>
	</div>
	
	
	
	<!-- Page 3 -->
	<div id="page3" class="hidden">
		<blockquote>
			The installer is now creating all the tables needed by phpSyndicate in your database.<br/>
			Most of the time, this process takes only several seconds ..
			
			
			<br/><br/>
			<div id="tablesuccess" class="goodinfo">
			All tables were successfully created.<br/>
			You can now press continue to populate the database with data needed by phpSyndicate.
			</div>
			
			<div id="tableerror1" class="badinfo">
				The installer could not create all the tables needed.<br/>
				Aborting installation.	
			</div>
			
			<div id="tableerror2" class="badinfo">
				The installer could not create necessary tables needed by phpSyndicate.<br/>
				Make sure that user [user] has permission to create tables on database [database].<br/>
				After you have verified database permissions, try again.
			</div>
			
			<br/><br/>
			<div id="tableservererror" class="badinfo"></div>
			
			
			
		</blockquote>
	</div>
	
	
	<!-- Page 4 -->
	<div id="page4" class="hidden">
		<blockquote>
			The installer is now populating your database with data used by phpSyndicate.<br/>
			The data includes system settings, movie categories, user roles and rss feeds to name a few.<br/>
			This process can take from several seconds up to minutes, depending on server hardware and/or connection speed 
			between the webserver and the database server.
		
			<br/><br/>
			<script type="text/javascript" language="javascript1.2">
				barObj = new progressBar(1,'#000000','#ffffff','#043db2',550,20,1);
				Installer.setProgressBar(barObj);
			</script>
			
			<br/><br/>
			<div id="populatesuccess" class="goodinfo">
			All necessary data has been populated in the database used by phpSyndicate.<br/>
			You can now continue to configure the default settings phpSyndicate uses.
			</div>
			
			<div id="populateError" class="badinfo">
			Could not populate the database with the necessary data.<br/>
			Please verify that no files are missing from your installation package.
			</div>
			
			<br/><br/>
			<div id="populateservererror" class="badinfo"></div>
			
		</blockquote>
	
	</div>
	
	<!-- Page 5 -->
	<div id="page5" class="hidden">
	
		<blockquote>
		<fieldset>
			<legend><?php echo $APPLICATION_NAME; ?> basic settings</legend>
			<table cellspacing="1" cellpadding="1" border="0" width="600" class="list">

			<tr>
				<td width="400">Allow new users to register?</td>
				<td><select name="s_register" id="s_register"><option value="1">Yes</option><option value="0">No</option></select></td>
			</tr>

			<tr>
				<td>Session lifetime in hours:</td>
				<td><select name="s_session" id="s_session">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option value="6" selected="selected">6</option>
					<option value="12">12</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>The title of your phpSyndicate website:</td>
				<td><input type="text" name="s_title" id="s_title" value="phpSyndicate" readonly="readonly" size="25"></td>
			</tr>
			</table>
		</fieldset>	
		
		<br/>
		<fieldset>
			<legend>Use friendly urls (mod_rewrite)</legend>
			<table cellspacing="1" cellpadding="1" border="0" width="600" class="list">
			<tr>
				<td width="400">Use friendly urls with apache mod_rewrite:</td>
				<td>Yes<input type="radio" id="userewrite" name="userewrite" value="1" disabled="disabled">No<input type="radio" name="userewrite" value="0" checked="checked"></td>
			</tr>
			<tr>
				<td colspan="2">For mod_rewrite to work, the mod_rewrite apache module needs to enabled.
				First add the following entry to your apache configuration file for this host.
				When you have done that, press the "Create .htaccess" button below.
				This procedure has to be done before you move on to the next stage in the installer.
				If the .htaccess is successfully created, the option for friendly urls will be automatically set to "yes"
<pre>
&lt;Directory "<?php echo str_replace('setup/index.php','',$_SERVER['SCRIPT_FILENAME'])?>"&gt;
        AllowOverride Options FileInfo AuthConfig
&lt;/Directory&gt;
</pre>
				</td>
			</tr>
			<tr>
				<td id="htCreate">Create the .htaccess file:</td>
				<td><input type="button" name="createFile" id="createFile" value="Create .htaccess" size="25" onClick="Installer.createHTAccess(this)"></td>
			</tr>
			</table>
		</fieldset>
		
		<br/>
		<fieldset>
			<legend>Mail server settings</legend>
			<table cellspacing="1" cellpadding="1" border="0" width="600" class="list">
			<tr>
				<td width="400">Email address to use with mail sent from phpSyndicate:</td>
				<td><input type="text" name="s_email" id="s_email" size="25"></td>
			</tr>
			<tr>
				<td>SMTP server to use:</td>
				<td><input type="text" name="s_smtphost" id="s_smtphost" size="25"></td>
			</tr>
			<tr>
				<td>SMTP username:</td>
				<td><input type="text" name="s_smtpusername" id="s_smtpusername" size="25"></td>
			</tr>
			<tr>
				<td>SMTP password:</td>
				<td><input type="password" name="s_smtppassword" id="s_smtppassword" size="25"></td>
			</tr>
			<tr>
				<td>SMTP realm:</td>
				<td><input type="text" name="s_smtprealm" id="s_smtprealm" size="25"></td>
			</tr>
			</table>
		</fieldset>
		
		<br/>
		<fieldset title="The proxy server settings are optional!">
			<legend>Proxy settings</legend>
			<table cellspacing="1" cellpadding="1" border="0" width="600" class="list">
			<tr>
				<td width="400">Use proxy server to fetch data:</td>
				<td>Yes<input type="radio" name="useproxy" value="1">No<input type="radio" name="useproxy" value="0" checked="checked"></td>
			</tr>
			<tr>
				<td>Proxy server address:</td>
				<td><input type="text" name="s_proxyhost" id="s_proxyhost" size="25"></td>
			</tr>
			<tr>
				<td>Proxy server port:</td>
				<td><input type="text" name="s_proxyport" id="s_proxyport" value="8080" size="4"></td>
			</tr>
			</table>
		</fieldset>
		
		<br/>
			
		<br/><br/>
		<div id="configservererror" class="badinfo"></div>
		
		</blockquote>
	</div>
	
	
	<!-- Page 6 -->
	<div id="page6" class="hidden">
		<blockquote>
		
			<br/>
			<div id="configsavesuccess" class="goodinfo">
				The configuration file has been successfully saved.<br/>
				You can now continue to create the phpSyndicate admin account.
			</div>
			
			<div id="configsaveerror" class="badinfo"></div>
		
		</blockquote>
		
	</div>
	
	
	<!-- Page 7 -->
	<div id="page7" class="hidden">
		<blockquote>
		
		<fieldset>
			<legend>Create phpSyndicate admin account</legend>
			<table cellspacing="1" cellpadding="1" border="0" width="600" class="list">
			<tr>
				<td width="200">Fullname:</td>
				<td><input type="text" name="vcd_fullname" id="vcd_fullname" size="40"></td>
			</tr>
			<tr>
				<td>Username:</td>
				<td><input type="text" name="vcd_username" id="vcd_username" size="40"></td>
			</tr>
			<tr>
				<td>Password:</td>
				<td><input type="password" name="vcd_password" id="vcd_password" size="40"></td>
			</tr>
			<tr>
				<td>Password again:</td>
				<td><input type="password" name="vcd_password2" id="vcd_password2" size="40"></td>
			</tr>
			<tr>
				<td>Email:</td>
				<td><input type="text" name="vcd_email" id="vcd_email" size="40"></td>
			</tr>
			</table>
		</fieldset>
		</blockquote>
	
	</div>
	
	
	<!-- Page 8 -->
	<div id="page8" class="hidden">
	
		<blockquote>
	
		<div class="info">
			<b>Congratulations!</b><br/>
			You have successfully installed <?php echo $APPLICATION_NAME; ?>.<br/><br/>
			If you are running on a Unix based system, you should chmod the config.php file you created earlier.
			That is to ensure nobody else will change your config file.<br/>
			You can type in "<i>chmod 644 config.php</i>" within your command line to secure the config file.			
		</div>
		
		<br/><br/>
		
			
		</blockquote>
	
	</div>
	
	<!-- Main window -->
	</td>
	<td valign="top" id="menucell" width="25%">
	<!-- Menu -->
	<ol id="menu">
		<li class="active">Introduction</li>
		<li class="pending">System check</li>
		<li class="pending">Set database connection</li>
		<li class="pending">Create database tables</li>
		<li class="pending">Populate data</li>
		<li class="pending">Configure settings</li>
		<li class="pending">Write to config file</li>
		<li class="pending">Create admin account</li>
		<li class="pending">Clean up and finish</li>
	</ol>
	
	
	<br>
	<div style="margin-left:50px">
	<span id="process"><img src="../images/processing.gif" border="0" align="absmiddle" hspace="5" alt="Processing, be patient!" title="Processing, be patient!"></span>
	<input type="button" id="BtnContinue" value="Continue &gt;&gt;" onClick="Installer.Continue()">
	</div>
	<!-- / Menu -->
	</td>
</tr>
</table>




</body>
</html>
