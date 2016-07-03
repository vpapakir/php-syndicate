<?php
error_reporting(E_ALL & ~E_NOTICE);
ignore_user_abort(true);

require('makeSecure.php');

include '../../config/header2.php';
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
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<link rel="stylesheet" type="text/css" href="../../css/main.css">
<link rel="stylesheet" type="text/css" href="../../css/installer.css">
<title><?php echo $APPLICATION_NAME; ?> - Home</title>
<link rel="shortcut icon" href="../../images/favicon.ico" type="image/x-icon" />
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
<script type="text/javascript" src="../../js/json.js"></script>
<script type="text/javascript" src="../../js/ajax.js"></script>
<script type="text/javascript" src="../../js/installer.js"></script>
<script type="text/javascript" src="../../js/progbar.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/prototype/1.7.1/prototype.js"></script>
<script type="text/javascript" src="../../js/func.js"></script>
<script type="text/javascript">
function endsWith(str, suffix) {
	return str.indexOf(suffix, str.length - suffix.length) !== -1;
}

function gotologout() {
	var confirmDisconnect = window.confirm("You are about to disconnect from your phpSyndicate session!\r\nAre you sure?");
	if(confirmDisconnect == true) {
		window.location = "logout.php";
	} else {
		// DO NOTHING
	}
}

function stopDispatch() {
	document.getElementById("log2").innerHTML += "\r\n(INFO) Email Dispatch interrupted by user.\r\n";
	document.getElementById("dispatch_interrupt").disabled = true;
	document.getElementById("send").disabled = false;
	window.stop();
}

function loadCSV() {
	document.getElementById("send").disabled = false; 
}

function enableCSVsubmit() {
	document.getElementById("submitcsvfile").disabled = false;
}

function dispatchEmails() {
	document.getElementById("dispatch_interrupt").disabled = false;
	document.getElementById("send").disabled = true;
}

function clearMailEditor() {
	document.getElementById("subject").value = "";
	document.getElementById("body").value = "";
}

function completeLog(pu) {
	var progressnum = document.getElementById("progressnum");
	var indicator = document.getElementById("indicator");
	var actualprogress = 100;	
	var somevar = "\r\n(INFO) Email dispatch completed.\r\n";
	
	indicator.style.width=5*actualprogress + "px";
	progressnum.innerHTML = actualprogress;
	document.getElementById("log2").innerHTML += somevar;
	document.getElementById("send").disabled = false;
	document.getElementById("dispatch_interrupt").disabled = true;
	pu.stop();
	window.stop();
}

function updateLog(log4obj,pu) {
	var progressnum = document.getElementById("progressnum");
	var indicator = document.getElementById("indicator");
	document.getElementById("log3").innerHTML += "\r\n" + log4obj + "\r\n";
	
	if(log4obj.lastIndexOf("100") != -1) {
		completeLog(pu);
	} else if(log4obj.lastIndexOf("null") != -1) {
		// Do nothing
	} else {
		actualprogress = parseFloat(log4obj.substring( log4obj.lastIndexOf("progress")+11, log4obj.lastIndexOf("progress")+16));	
		indicator.style.width=5*actualprogress + "px";
		progressnum.innerHTML = actualprogress;
	}
}

jQuery(document).ready(function() {

	jQuery('#submitcsvfile').click(function() {
		loadCSV();    
	});

	jQuery('#clear').click(function() {
		clearMailEditor();    
	});

	jQuery('#dispatch_interrupt').click(function () {
		stopDispatch();
	});

	jQuery('#send').click(function () {
		var progressnum = document.getElementById("progressnum");
		var indicator = document.getElementById("indicator");
		
		actualprogress = 0;	
		indicator.style.width=5*actualprogress + "px";
		progressnum.innerHTML = actualprogress;
		
		document.getElementById("dispatch_interrupt").disabled = false;
		document.getElementById("send").disabled = true;
		var protocol = jQuery('select[name=protocol]');
		var subject  = jQuery('input[name=subject]');
		var bodymsg  = jQuery('textarea[name=body]');
		var data2pass = 'protocol=' + protocol.val() + '&subject=' + subject.val() + '&bodymsg=' + bodymsg.val();
		var actualprogress = 0;
		
		document.getElementById("send").disabled = true;
		document.getElementById("log2").innerHTML += "\r\n(INFO) Email dispatch has started.\r\n";
		document.getElementById("dispatch_interrupt").disabled = false;

		<?php echo 'var textVar = "'.$_SESSION["userName"].'";'; ?>
		var eventcounterVar = 0;
		
		var pu = new Ajax.PeriodicalUpdater('log4','updater.php', {
			method: "get",
			frequency: 4, 
			dataType: "json",
			parameters: {uname: textVar, eventcounter: eventcounterVar},
			evalJSON : 'force',
			beforeSend: function(x) {
				if(x && x.overrideMimeType) {
					x.overrideMimeType("application/j-son;charset=UTF-8");
				}
			},
			decay: 1,
			onSuccess: function (oJson) {
				updateLog(document.getElementById("log4").value,pu);
			},
			onFailure: function (oXHR, oJson) {
				eventcounterVar++;
				alert("ERROR!");
			}
		});
		
		jQuery.ajax({
			type: "GET",
			url: "process.php",
			async: true,
			beforeSend: function(x) {
				if(x && x.overrideMimeType) {
					x.overrideMimeType("application/j-son;charset=UTF-8");
				}
			},
			dataType: "json",
			//pass the data
			data: data2pass,
			//Do not cache the page
			cache: false,
			success: function(data){
				completeLog(pu);
			},
			error: function(data) {
			}
		});
	});
});

</script>

</head>

<body>

<table cellspacing="3" style="border-collapse:collapse;border-spacing:0;" cellpadding="3" border="0" width="100%" height="100%" id="maintbl">

<tr>
<!-- Top bar -->
<td colspan="2" id="topcell" valign="middle">
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

<div id="csvloader">
	<fieldset>
		<legend>Load data file...</legend>
		<form id="form1" name="form1" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']."?username=".$_SESSION['userName']; ?>">
			<p>
				<label>Load CSV File:
					<input name="csvfile" type="file" required="required" onchange="enableCSVsubmit()" id="csvfile" accesskey="c" tabindex="1" size="40" maxlength="200" />
				</label>
			</p>
			<p>
				<input type="submit" name="submitcsvfile" disabled="disabled" id="submitcsvfile" value="Load CSV Data" accesskey="s" tabindex="3" onclick="loadCSV()"/>
			</p>
		</form>
	</fieldset>
</div>

<div id="recipients">
	<fieldset>
		<legend>Recipients</legend>
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
			$cs->__set("host",$hostarray);
			$cs->__set("name",$namearray);
			$cs->__set("email",$emailarray);
			$cs->__set("Items_Count",$csvobj->Items_Count);		
			$_SESSION['csv'] = $target_path;
		}
	}
?>
	</fieldset>
</div>

<div id="maileditor">
<fieldset>
<legend>Mail Editing</legend>
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
      <input name="subject" type="text" id="subject" size="67" maxlength="100" value="<?php
	  	echo "Important email Message for ^EMAIL^.";
      ?>" />
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
      <th height="37" scope="row"><input type="submit" <?php if(isset($_SESSION['csv'])) {/*do nothing*/}else{echo 'disabled=\"disabled\"';}?> name="send" id="send" value="Start Sending Message to Batch" accesskey="s" tabindex="9" /></th>
      <td><input type="reset" name="clear" id="clear" value="Clear" /></td>
    </tr>
</table>
</fieldset>
</div>

<fieldset>
<legend>Batch Progress Monitor</legend>
<div id="pwidget">  
	<div id="progressbar">
		<div id="indicator"></div>
	</div>
	<div id="progressnum">0</div>
</div>
<p>
<input name="dispatch_interrupt" id="dispatch_interrupt" disabled="disabled" type="button" value="Stop" />
</p>
<div id="log">
  <label>
	<textarea name="log2" id="log2" readonly="readonly" cols="130" rows="7">
	</textarea>
  </label>
</div>

<textarea name="log4" id="log4" readonly="readonly" style="display:none;" cols="130" rows="15"></textarea>
<textarea name="log3" id="log3" readonly="readonly" cols="130" rows="17">
<?php /* Look for interrupted sessions */ 
$intsesdbobj = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
$intsessresult = $intsesdbobj->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$_SESSION['userName']); 
if(isset($intsessresult)) {
	if(count($intsessresult) > 0) {
		for($i=0;$i<count($intsessresult);$i++) { 
			echo $intsessresult[$i]->output; 
			echo $intsessresult[$i]->progress;
		}
		$intsesdbobj->getResult("DELETE FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$_SESSION['userName']);
	} else {
		echo "(INFO) No past activity logged for ".$_SESSION['userName']."\r\n";
	}
} else {
	echo "(INFO) No past activity logged for ".$_SESSION['userName']."\r\n";
} 
$intsesdbobj->disconnect(); 
?>
</textarea>

</fieldset>
</td>
<td valign="top" id="menucell" width="17%">
<!-- Menu -->
<br>
<div style="margin-left:50px">
<span id="process"><img src="../../images/processing.gif" border="0" align="absmiddle" hspace="5" alt="Processing, be patient!" title="Processing, be patient!">
</span>
<input type="button" id="BtnContinue" value="Logout" onClick="gotologout()">
</div>
<!-- / Menu -->
</td>
</tr>
</table>
</div>
</body>
</html>
