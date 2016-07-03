<?php
//error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
error_reporting(E_ALL & ~E_NOTICE);
//error_reporting(E_ALL);
ignore_user_abort(true);
//set_time_limit(0); // not allowed on zymic servers

include '../../config/header2.php';
require('makeSecure.php'); 
include '../admin/admin.php';
include '../data/mysqlconnector.php';
include '../data/CSVReader.php';
include '../mail/MailMessage.php';
	
function genRandomString() {
	$length = 5;
	$characters = "0123456789abcdefghijklmnopqrstuvwxyz";
	$string = "";    

	for ($p = 0; $p < $length; $p++) {
		$string .= $characters[mt_rand(0, strlen($characters))];
	}

	return $string;
}
	
class CSVpersistent {
	private $data = array();

	// constructor (not implemented)
	public function __construct() {
	}

	// factory method
	public static function factory() {
		try {
			session_start();
		} catch(Exception $ex) {
			// Do nothing
		}
		if(isset($_SESSION['CSVpersistent']) === TRUE)
		{
			return unserialize($_SESSION['CSVpersistent']);
		}
		return new CSVpersistent();
	}

	// set undeclared property
	public function __set($property, $value) {
		$this->data[$property] = array();
		array_push($this->data[$property], $value);
	}

	// get undeclared property
	public function __get($property)
	{
		if (isset($this->data[$property]) === TRUE)
		{
			return $this->data[$property];
		}
	}

	// save object to session variable
	public function __destruct() {
		$_SESSION['CSVpersistent'] = serialize($this);
	}
}

$cs = CSVpersistent::factory();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="../../css/main.css">
    <title>
		<?php echo $APPLICATION_NAME; ?> - Home
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="shortcut icon" href="../../images/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="../../css/installer.css">
    <script type="text/javascript" src="../../js/json.js"></script>
    <script type="text/javascript" src="../../js/ajax.js"></script>
    <script type="text/javascript" src="../../js/installer.js"></script>
    <script type="text/javascript" src="../../js/progbar.js"></script>
    <script type="text/javascript" src="../../js/func.js"></script>
    <script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
    <script type="text/javascript">
		function gotologout()
		{
			window.location = "logout.php";
		}
		
		var maxprogress = 100;		
		var actualprogress = 0;  // current value
		var itv = 0;  // id to setinterval
		var someid = 1;
		
		function prog()
		{
			if(actualprogress >= maxprogress) 
			{
				clearInterval(itv);   	
				return;
			}	
			
			/*var progressnum = document.getElementById("progressnum");
			var indicator = document.getElementById("indicator");
			actualprogress += 1;	
			indicator.style.width=actualprogress + "px";
			progressnum.innerHTML = actualprogress;*/
			if(actualprogress == maxprogress) clearInterval(itv);
		}
		
		function endsWith(str, suffix) {
		    return str.indexOf(suffix, str.length - suffix.length) !== -1;
		}
		
		$(document).ready(function() {
			$('#dispatch_interrupt').click(function () {
				return false;
			});
			
			$('#send').click(function () {		
				var protocol = $('select[name=protocol]');
				var subject  = $('input[name=subject]');
				var bodymsg  = $('textarea[name=body]');
				var data = 'protocol=' + protocol.val() + '&subject=' + subject.val() + '&bodymsg=' + bodymsg.val();
				var actualprogress = 0;
				
				document.getElementById("send").disabled = true;
				//document.getElementById("log").innerHTML += "<p>(INFO) Email dispatch has started.</p>";
				document.getElementById("log2").innerHTML += "(INFO) Email dispatch has started.\n";
				document.getElementById("dispatch_interrupt").disabled = false;
				
				someid = setInterval(function() {
					$.ajax({
						//this is the php file that processes the data and send mail
						url: "process.php",
						//GET method is used
						type: "GET",
						//pass the data
						data: data,
						//Do not cache the page
						cache: false,
						//success
						success: function (html) {
							var progressnum = document.getElementById("progressnum");
							var indicator = document.getElementById("indicator");
							
							if(endsWith(html,"\nEND\n")) {
								actualprogress = 100;	
								indicator.style.width=5*actualprogress + "px";
								progressnum.innerHTML = actualprogress;
								//document.getElementById("log").innerHTML += "<p>(INFO) Email dispatch completed!</p>";
								document.getElementById("log2").innerHTML += "\n(INFO) Email dispatch completed!\n";
								clearInterval(someid);
								document.getElementById("send").disabled = false;
								document.getElementById("dispatch_interrupt").disabled = true;
								return false;
								window.stop();
							} else {
								actualprogress = parseInt(html.substring(html.lastIndexOf("Prc % completed:")+16));	
								indicator.style.width=5*actualprogress + "px";
								progressnum.innerHTML = actualprogress;
								//document.getElementById("log").innerHTML += html;
								document.getElementById("log2").innerHTML += "\n" + html + "\n";
							}
						}
					})
				}, <?php echo $DISPDELAY; ?>);
				return false;
			});	
		});	
    </script>
</head>

	<body>
<table cellspacing="3" style="border-collapse:collapse;border-spacing:0;" cellpadding="3" border="0" width="100%" height="100%" id="maintbl">

<tr>
	<td colspan="2" id="topcell" valign="middle">
	<!-- Top bar -->
	<table cellspacing="0" cellpadding="0" width="100%" border="0">
		<tr>
			<td id="message" nowrap="nowrap">
            <?php
				$username = $_SESSION['userName'];
				echo "Welcome <b>".$username."</b>";
			?>
            </td>
			<td width="100" align="right">
            </td>
		</tr>
        <tr>
        <td>
        </td>
        <td>
        <a href="panel.php?username=<?php echo $_SESSION['userName']; ?>" title="Home">Home</a> | <a href="../user/settings.php" title="Settings" target="_blank">Settings</a>
        </td>
        </tr>
	</table>
	<!-- / Top bar -->
</tr>

<tr>

<td width="83%" valign="top" id="maincell">
	<!-- Main window -->

<p>
	<?php
		// 
    ?>
</p>

<fieldset>
<legend>Load data file...</legend>
<form id="form1" name="form1" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']."?username=".$_SESSION['userName']; ?>">
  <p>
    <label>Load CSV File:
      <input name="csvfile" type="file" id="csvfile" accesskey="c" tabindex="1" size="40" maxlength="100" />
    </label>
  </p>
  <p>
    <input type="submit" name="submitcsvfile" id="submitcsvfile" value="Load" accesskey="s" tabindex="3" />
  </p>
</form>
<?php
$target_path = "../../uploads/";
$csvobj = new CSVReader($target_path,',','email');

if(isset($_FILES['csvfile'])) {

	$hostarray = array();
	$namearray = array();
	$emailarray = array();
	$target_path = $target_path . basename( $_FILES['csvfile']['name']).genRandomString();
	
	$_SESSION['uploadedfile'] = $target_path;
	
	if(!move_uploaded_file($_FILES['csvfile']['tmp_name'], $target_path)) {
		$file_handle = fopen($_FILES['csvfile']['tmp_name'], "r");
		while (!feof($file_handle)) {
			$line = fgets($file_handle);
		}
		$csvobj = new CSVReader($_FILES['csvfile']['tmp_name'],',','email');
		$csvobj->ReadCSV();
		$csvobj->ListAll();
		
		for($i=0;$i<($csvobj->Items_Count);$i++) { // For each email address found in CSV file
			array_push($hostarray,$csvobj->ItemsList[$i]['Host']);
			if(isset($csvobj->ItemsList[$i]['Name'])) {
				array_push($namearray,$csvobj->ItemsList[$i]['Name']);
			} else {
				array_push($namearray,$csvobj->ItemsList[$i]['Email']);
			}
			array_push($emailarray,$csvobj->ItemsList[$i]['Email']);
		}
		
		$cs->__set("host",$hostarray);
		$cs->__set("name",$namearray);
		$cs->__set("email",$emailarray);
		$cs->__set("Items_Count",$csvobj->Items_Count);

		$_SESSION['csv'] = $_FILES['csvfile']['tmp_name'];
		$_SESSION['count'] = $csvobj->Items_Count;
		//fclose($file_handle);
	} else {
		echo "<p>The file ".  basename( $_FILES['csvfile']['name'])." has been uploaded</p>";	
		$csvobj = new CSVReader($target_path,',','email');
		$csvobj->ReadCSV();
		$csvobj->ListAll();
		$_SESSION['csvobj'] = $csvobj;
		$_SESSION['count'] = $csvobj->Items_Count;
		if($csvobj->Items_Count >=0) { // PAGING - TO FIX!!! 
			print "<a href='panel.php?start=0'><font face='Verdana' size='2'>PREV</font></a>"; 
		} 

		for($i=0;$i<($csvobj->Items_Count);$i++) { // For each email address found in CSV file
			array_push($hostarray,$csvobj->ItemsList[$i]['Host']);
			if(isset($csvobj->ItemsList[$i]['Name'])) {
				array_push($namearray,$csvobj->ItemsList[$i]['Name']);
			} else {
				array_push($namearray,$csvobj->ItemsList[$i]['Email']);
			}
			array_push($emailarray,$csvobj->ItemsList[$i]['Email']);
		}
		
		/*print_r($hostarray);
		print_r($emailarray);
		print_r($namearray);*/
		
		$cs->__set("host",$hostarray);
		$cs->__set("name",$namearray);
		$cs->__set("email",$emailarray);
		$cs->__set("Items_Count",$csvobj->Items_Count);
		
		$_SESSION['csv'] = $target_path;
	}
}

$dbobj = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
$result = $dbobj->getResult("SELECT `usertype` FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);

if($result[0]->usertype == "admin") {
   $adminobj = new Admin();
   $adminobj->generateAddUser();
   echo "<p></p>";
   $adminobj->generateRemoveUser();
   echo "<p></p>";
   $adminobj->generateEditUser();
   echo "<p></p>";
} else {
}
?>
</fieldset>

<p></p>

<fieldset>
<legend>Mail Editing</legend>
<form id="form2" name="form2" method="post" action="<?php echo $_SERVER['PHP_SELF']."?username=".$_SESSION['userName']; ?>">
  <table width="69%" height="361" border="0">
    <tr>
      <th width="36%" scope="row">
      	<label>Select Send Method:</label>
      </th>
      <td width="64%">
      	<select name="protocol" id="protocol">
        	<option value="1">SMTP</option>
            <!-- <option value="2">SOCKS</option> -->
      	</select>
      </td>
    </tr>
    <tr>
      <th scope="row"><label>Mail Subject:</label></th>
      <td>
      <input name="subject" type="text" id="subject" size="80" maxlength="100" value="<?php echo \"Important email Message for ^EMAIL^.";?>" />
      </td>
    </tr>
    <tr>
      <th scope="row"><label>Mail body:</label></th>
      <td>
      <textarea name="body" id="body" cols="67" rows="23"><?php
	  	$EMAIL = "";
		$DOMAIN = "";
		$NAME = "";
	  	echo 'Hello ^NAME^!'."\n".'This is an email message for your ^HOST^ account!';
	  ?></textarea>
      </td>
    </tr>
    <tr>
      <th height="37" scope="row"><input type="submit" <?php if(isset($_SESSION['csv'])) {/*do nothing*/}else{echo 'disabled=\"disabled\"';}?> name="send" id="send" value="Start Sending Message to Batch" onclick="setInterval(prog,1000)" accesskey="s" tabindex="9" /></th>
      <td><input type="reset" name="clear" id="clear" value="Clear" /></td>
    </tr>
  </table>
</form>
</fieldset>

<p></p>

<fieldset>
<legend>Batch Progress Monitor</legend>
<div id="pwidget">  
	<div id="progressbar">
		<div id="indicator"></div>
	</div>
	<div id="progressnum">0</div>
</div>
<p>
<input name="dispatch_interrupt" id="dispatch_interrupt" type="button" value="Stop" onclick="clearInterval(itv);clearInterval(someid);window.stop()"/>
</p>
<div id="log">
  <label>
	<textarea name="log2" id="log2" readonly="readonly" cols="130" rows="20"><?php /* Look for interrupted sessions */ $intsesdbobj = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);$intsessresult = $intsesdbobj->getResult("SELECT `output` FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$_SESSION['userName']); if(isset($intsessresult)) { echo $intsessresult[0]->output; $intsesdbobj->getResult("DELETE FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s' LIMIT 1",$_SESSION['userName']);} else {echo "shit!";} $intsesdbobj->disconnect(); ?> </textarea>
  </label>
</div>
</fieldset>
</td>
<td valign="top" id="menucell" width="17%">
	<!-- Menu -->
	<br>
	<div style="margin-left:50px">
	<span id="process"><img src="../../images/processing.gif" border="0" align="absmiddle" hspace="5" alt="Processing, be patient!" title="Processing, be patient!"></span>
	<input type="button" id="BtnContinue" value="Logout" onClick="gotologout()">
	</div>
	<!-- / Menu -->
	</td>

</tr>
</table>
	</body>
</html>
