<?php 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *
 *    About the program
 *
 *    This is a program for mass dispatch of E-mail. 
 *
 *    This programm for Unix/Windows system and PHP4 (or higest).
 *
 *    (c) Papakirikou Evangelos , GPL,  vpapakir@yahoo.gr
 *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
include 'vars.inc.php';
include 'template.inc.php';
include 'phpmm.php';
include 'sock.php';
include 'smtp.php';
include 'sock2.php';

error_reporting(E_ALL);
// set_time_limit(0); // not allowed on zymic servers

if($auth == 1) {
	if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER']!==$name || $_SERVER['PHP_AUTH_PW']!==$pass)
	{
		header('WWW-Authenticate: Basic realm="PHP Mass Mailer"');
		header('HTTP/1.0 401 Unauthorized');
		exit("<b>Access Denied</b>");
	}
}

/*if(isset($_SESSION['username']))
{
	session_start();
} else {
    header( "Location: index.html" ); 
}*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	$id = $_POST['id'];
	
	if($id == 1) {
		
	    $send       = $_POST['send'];
		$sendmethod = $_POST['method'];
		
		$whom       = $_POST['Whom'];
		if(isset($whom)) {
			$datafile = fopen($whom,"r");
			if ($datafile) {
				while (!feof($datafile)) {
					$recvarray[] = fgets($datafile, 8192);
				}
				fclose($datafile);
			}
		}
		$whom = $recvarray[0];
		//echo $whom;
					
		$subj =  $_POST['Subj'];

		if( $sendmethod == "nosocksproxy" ) {		
			if($send == 'Send'){
				
				//variables
				$hostSmtp     = $_POST['SmtpHost'];
				$smtpServer   = $_POST['smtpServer'];
				$portSmtp     = $_POST['SmtpPort'];
				$userSmtp     = $_POST['UserSmtp'];
				$passSmtp     = $_POST['PassSmtp'];
				$authenticate = $_POST['Authenticate'];
				$smtp         = $_POST['Smtp'];
				$from         = $_POST['From'];
				$mess         = $_POST['Mess'];
				$list         = $_POST['List'];
				$kod          = $_POST['Kod'];
				$use          = $_POST['Use'];
								
				// connect to database and choose a sender
				$db = mysql_connect("$dbHost", "$dbUser", "$dbPass") or die ("Error connecting to database.");
				mysql_select_db("$dbDatabase", $db) or die ("Couldn't select the database.");
				$result=mysql_query("select * from mailsenders ORDER BY Rand() LIMIT 5", $db); // choose up to 5 senders
				$rowCheck = mysql_num_rows($result);
				$senderid = rand(0,($rowCheck - 1));
				while($row = mysql_fetch_array($result)){
					$__from[]       = $row['account'];
					$__portSmtp[]   = $row['port'];
					$__hostSmtp[]   = $row['host'];
					$__smtpServer[] = $row['host'];
					$__userSmtp[]   = $row['username'];
					$__passSmtp[]   = $row['password'];
				}

				$buffer = explode(';', $whom);
				for($counter = 0; $counter < count($buffer); $counter++) {
				
					if(isset($subj)) {
						$datafile = fopen($subj,"r");
						if ($datafile) {
							while (!feof($datafile)) {
								$subjectarray[] = fgets($datafile, 8192);
							}
							fclose($datafile);
						}
						$subj = $subjectarray[rand(1,(count($subjectarray)-1))];
					}
					
					$whom       = $buffer[$counter];
					//echo "<p>sender ".$whom."</p>";
					$from       = $__from[$counter+1];
					//echo $from;
					$portSmtp   = $__portSmtp[$counter+1];
					//echo $portSmtp;
					$hostSmtp   = $__hostSmtp[$counter+1];
					//echo $hostSmtp;
					$smtpServer = $__smtpServer[$counter+1];
					//echo $smtpServer;
					$userSmtp   = $__userSmtp[$counter+1];
					//echo $userSmtp;
					$passSmtp   = $__passSmtp[$counter+1];
					//echo $passSmtp;
					
					ini_set("SMTP",$smtpServer);
					ini_set("smtp_port", $portSmtp);
					ini_set("sendmail_from", $from);
					
					$empty = $post = array();
					foreach ($_POST as $varname => $varvalue){
							if (empty($varvalue)){
									$empty[$varname] = $varvalue;
							} else {
									$post[$varname] = $varvalue;
							}
					}
					
					$files = array();
					foreach ($post as $key => $value){
							if (preg_match("/file/", $key, $array)){
								array_push ($files, $value);
							}
					}

					$max =  new phpmm(isset($a_data));
					$time_start = $max->getmicrotime();

					if ($use == "whom" || $use == "maillist"){
							$max ->checkBeforeSending = $checkBeforeSending;
							$max ->whom         = $whom;
							$max ->from         = $from;
							$max ->mailer       = $mailer;
							$max ->use          = $use;
							$max ->files        = $files;
							$max ->list         = $list;
							$max ->kod          = $kod;
							$max ->send         = $send;
							$max ->subj         = $subj;
							$max ->charset      = $charset;
							$max ->mess         = $mess;
							$max ->smtp         = $smtp;
							$max ->smtpServer   = $smtpServer;
							$max ->hostSmtp     = $hostSmtp;
							$max ->portSmtp     = $portSmtp;
							$max ->userSmtp     = $userSmtp;
							$max ->passSmtp     = $passSmtp;
							$max ->authenticate = $authenticate;
							$max ->Send();
							$time_end = $max->getmicrotime();
							$time = $time_end - $time_start;
							if($max->smtpServer != "test"){
									$tmp2 = $max->number;
									echo(nl2br("<script>window.open(\"win1.php?max=$tmp2&time=$time\",\"Mail Sent\",\"menubar=no,width=256,height=128,toolbar=no\");</script>\n"));
							}

					} else {

							if(!isset($databases) || !is_array($databases)){
									Exit("Databases are not configured. \n");
							}

							foreach ($databases as $data){
									  $phpmm =  new phpmm($data);
									  $phpmm ->checkBeforeSending = $checkBeforeSending;
									  $phpmm ->whom         = $whom;
									  $phpmm ->from         = $from;
									  $phpmm ->mailer       = $mailer;
									  $phpmm ->use          = $use;
									  $phpmm ->files        = $files;
									  $phpmm ->list         = $list;
									  $phpmm ->kod          = $kod;
									  $phpmm ->send         = $send;
									  $phpmm ->subj         = $subj;
									  $phpmm ->charset      = $charset;
									  $phpmm ->mess         = $mess;
									  $phpmm ->smtp         = $smtp;
									  $phpmm ->smtpServer   = $smtpServer;
									  $phpmm ->hostSmtp     = $hostSmtp;
									  $phpmm ->portSmtp     = $portSmtp;
									  $phpmm ->userSmtp     = $userSmtp;
									  $phpmm ->passSmtp     = $passSmtp;
									  $phpmm ->authenticate = $authenticate;
									  $phpmm ->Send();
									  $phpmm ->disconnect();

							}

							$time_end = $max->getmicrotime();
							$time = $time_end - $time_start;
					
							if($phpmm ->smtpServer != "test"){
									$tmp = $phpmm->number;
									echo(nl2br("<script>window.open(\"win2.php?phpmm=$tmp&time=$time\",\"Mail Sent\",\"menubar=no,width=256,height=128,toolbar=no\");</script>\n"));
							}				

					}

				}
				
				


			} else {
					Exit();
			}
		}
		if( $sendmethod == "withsocksproxy" ) {
			
			// send mail via socks/proxy here
			if($send == 'Send'){
				
				// choose a random SOCKS IP
				$socksfile    = $_POST['sockset'];
				if(isset($socksfile)) {
					$datafile2 = fopen($socksfile,"r");
					if ($datafile2) {
						while (!feof($datafile2)) {
							$sockarray[] = fgets($datafile2, 8192);
						}
						fclose($datafile2);
					}
					$socksfile = $sockarray[rand(1,(count($sockarray)-1))];
				}
				
				$hostSmtp     = $_POST['SmtpHost'];
				$smtpServer   = $_POST['smtpServer'];
				$portSmtp     = $_POST['SmtpPort'];
				$userSmtp     = $_POST['UserSmtp'];
				$passSmtp     = $_POST['PassSmtp'];
				$authenticate = $_POST['Authenticate'];
				$smtp         = $_POST['Smtp'];
				$from         = $_POST['From'];
				$mess         = $_POST['Mess'];
				$list         = $_POST['List'];
				$kod          = $_POST['Kod'];
				$use          = $_POST['Use'];
				
				$db = mysql_connect("$dbHost", "$dbUser", "$dbPass") or die ("Error connecting to database.");
				mysql_select_db("$dbDatabase", $db) or die ("Couldn't select the database.");
				$result=mysql_query("select * from mailsenders ORDER BY Rand() LIMIT 1", $db);
				$rowCheck = mysql_num_rows($result);
				$senderid = rand(0,($rowCheck - 1));
				while($row = mysql_fetch_array($result)){
					$__from[]       = $row['account'];
					$__portSmtp[]   = $row['port'];
					$__hostSmtp[]   = $row['host'];
					$__smtpServer[] = $row['host'];
					$__userSmtp[]   = $row['username'];
					$__passSmtp[]   = $row['password'];
				}
				
				if(isset($subj)) {
						$datafile = fopen($subj,"r");
						if ($datafile) {
							while (!feof($datafile)) {
								$subjectarray[] = fgets($datafile, 8192);
							}
							fclose($datafile);
						}
						$subj = $subjectarray[rand(1,(count($subjectarray)-1))];
				}
				
				$buffer = explode(';', $whom);
				for($counter = 0; $counter < count($buffer); $counter++) {
					
					$whom       = $buffer[$counter];
					//echo $whom;
					$from       = $__from[$counter+1];
					//echo $from;
					$portSmtp   = $__portSmtp[$counter+1];
					//echo $portSmtp;
					$hostSmtp   = $__hostSmtp[$counter+1];
					//echo $hostSmtp;
					$smtpServer = $__smtpServer[$counter+1];
					//echo $smtpServer;
					$userSmtp   = $__userSmtp[$counter+1];
					//echo $userSmtp;
					$passSmtp   = $__passSmtp[$counter+1];
					//echo $passSmtp;
					
					ini_set("SMTP",$smtpServer);
					ini_set("smtp_port", $portSmtp);
					ini_set("sendmail_from", $from);
					
					$recip = $whom;
					$authdata = $authenticate;
					$msgdata = $mess;
					$headers = $subj; 
					try {
						$smtp = new smtp($socksfile,$portSmtp,$from,$recip,$headers,$authdata,$msgdata);
					} catch (Exception $e) {
						echo(nl2br("<script>window.open(\"info.php?phpmm=$e\",\"Mail Sent\",\"menubar=no,width=256,height=128,toolbar=no\");</script>\n"));
						//exit;
					}
					
				}

			} else {
					Exit();
			}
		}
	
	} elseif($id == 2) {

        $e_mail  =  $_POST['e_mail'];
        $dbfbasa =  $_POST['dbfbasa'];

        foreach ($databases as $data) {
                if($dbfbasa != 'all'){
                        if($dbfbasa == $data['dbasa']){
							$import = new import($data,$e_mail,$dbfbasa);
                            $import->disconnect();
                            break;
                        }
                } elseif($dbfbasa == 'all') {
                        $import = new import($data,$e_mail,$dbfbasa);
                        $import->disconnect();
                }
        }
	}
}

if(isset($_GET['img'])){
	echo base64_decode($image);
}

echo <<<phpMM

<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Frameset//EN'>
<html>
<head>
  <title>PHP Mass Mailer</title>
  <META HTTP-EQUIV='Content-Type: text/html; Charset=$charset_program http-equiv=Content-Type'>

<script type='text/javascript' language='JavaScript'>

<!--

function check(){

if(document.form1.From.value.length<1){
        alert('The receivers of the message are not specified.');
        return false;
}
if(document.form1.Use.value.length == 4){
        if(document.form1.Whom.value.length<6){
                alert('The e-mail address is not correct.');
                return false;
        }
        if(document.form1.Whom.value.length>100){
                alert('E-mail it is specified not correctly.');
                return false;
        }

}
if(document.form1.Use.value.length>106){
        if(document.form1.List.value.length<3){
                alert('maillist it is specified not correctly.');
                return false;
        }
}
if(document.form1.Mess.value.length>5000){
        alert('The message cannot be more than 5000 characters');
        return false;
}
if(document.form1.Smtp.value.length == 4){
        if(document.form1.SmtpHost.value.length<3){
                alert('Specify SMTP server.');
                return false;
        }
        if(document.form1.SmtpPort.value.length<1){
                alert('Smtp: The port specified, is not correctly!Please go back.');
                return false;
        }
        if(document.form1.Authenticate.value.length>1){
                if(document.form1.Authenticate.value.length>4){
                        if(document.form1.UserSmtp.value.length<1){
                                alert('You have not specified Smtp the user.');
                                return false;
                        }
                        if(document.form1.PassSmtp.value.length<1){
                                alert('You have not specified password Smtp.');
                                return false;
                        }
                }
        }
}else {
        return true;
}
}

function output(){
        var belem = document.getElementById('id1');
        belem.style.display = 'none';
        var iobj = document.getElementById('input');
        ielem = iobj.getElementsByTagName('div');
        iobj.style.display = '';
        var belem2 = document.getElementById('id2');
        belem2.style.display='';
}
function output2(){
        var belem2 = document.getElementById('id2');
        belem2.style.display='none';
        var iobj2 = document.getElementById('input2');
        ielem2 = iobj2.getElementsByTagName('div');
        iobj2.style.display='';
        var belem3 = document.getElementById('id3');
        belem3.style.display='';
}
function output3() {
        var belem3 = document.getElementById('id3');
        belem3.style.display='none';
        var iobj3 = document.getElementById('input3');
        ielem3 = iobj3.getElementsByTagName('div');
        iobj3.style.display='';
        var belem4 = document.getElementById('id4');
        belem4.style.display='';
}
function output4(){
        var iobj4 = document.getElementById('input4');
        ielem4 = iobj4.getElementsByTagName('div');
        iobj4.style.display="";
}

function output5() {
}

function hide(){
        var belem2 = document.getElementById('id2');
        belem2.style.display='none';
        var belem3 = document.getElementById('id3');
        belem3.style.display='none';
        var belem4 = document.getElementById('id4');
        belem4.style.display='none';
        var iobj = document.getElementById('input');
        ielem = iobj.getElementsByTagName('div');
        iobj.style.display='none';
        var iobj2 = document.getElementById('input2');
        ielem = iobj2.getElementsByTagName('div');
        iobj2.style.display='none';
        var iobj3 = document.getElementById('input3');
        ielem3 = iobj3.getElementsByTagName('div');
        iobj3.style.display='none';
        var iobj4 = document.getElementById('input4');
        ielem4 = iobj4.getElementsByTagName('div');
        iobj4.style.display='none';
}

function cancel(){
        hide();
        var belem = document.getElementById('id1');
        belem.style.display = '';
}

function test(method) {
	
	var loObject = document.getElementById('method');
	//test the current state of the object and toggle opposite state.
	
	if (method == 'WITH socks/proxy') {
		window.open("win1.html","Window1","menubar=no,width=430,height=360,toolbar=no");
	}
    if (method == 'withsocksproxy') 
    {
		window.open("win1.html","Window1","menubar=no,width=430,height=360,toolbar=no");
	}
	
}

// -->

</script>

<style type='text/css'>

        <!--

        INPUT, TEXTAREA, SELECT {
                font:10px Verdana, Times, Arial, serif, sans-serif;
                background-color: white;
                border:1px solid silver
        }

        TABLE, #x78y {
                font:12px Verdana, Times, Arial, serif, sans-serif;
        }

        BODY {
                background-color: white
        }

        #mybr {
                clear: left
        }

        #x34y  {
                float:left
        }

        #x35y {
                 border:solid silver 1.0pt;
        }

        #x77y {
                color:white;
                text-align: center
        }

        #x78y {
                color:white;
                text-align: right
        }

        #x79y {
                background-color: gray;
                border-style:none
        }

        A:link, A:visited,  A:active {
                font:10px Verdana, Times, Arial, serif, sans-serif;
                color: white;
                font-weight: bold
        }

        -->

</style>

</head>

<body>

<table id ='x79y' height=22 width=100%>
<tr>
    <td id = 'x79y'>
         <DIV id ='x77y'>
         <a href='$dir?id=1'>Home</a> |
         <a href='$dir?id=2'>Import Email Adresses</a> |
         <a href='$dir?id=3'>About PHP</a> |
         <a href='$dir?id=4'>About me</a> |
		 <a href="editsenders.html">Edit senders</a> |
		 <a href="editsocks.html">Edit SOCKS</a> |
         <a href='logout.php'onClick='javascript:window.close()'>Exit PHP Mailer</a>		 
         </DIV>
    </td>
</tr>
</table> <br />

phpMM;

$phpMM = <<<phpMM2

<center>

<table width=30%>

<form  name = 'form1' action='' onSubmit="return check()"  method='POST'>

<!-- 
<tr>
   <td>Send Method: <br /> &nbsp;</td>
   <td>
       <select size='1' name='method'>
           <option value='nosocksproxy'>NO socks/proxy</option>
           <option value='withsocksproxy'>WITH socks/proxy</option>
       </select> <br /> &nbsp; 
   </td>
</tr>
-->  

<tr>
<td>Send Method:</td>
<td>
<select size='1' name="method"
		onchange="if (this.selectedIndex == 1) {
			this.form.elements.sockset.disabled = false;
		}
		else {
			this.form.elements.sockset.disabled = true;
		}">
		<option value='nosocksproxy'> No SOCKS/proxy     </option>
		<option value='withsocksproxy' >With SOCKS/proxy </option> 
</select> <br /> &nbsp; &nbsp;
</td>

<tr>
<td> SOCKS IP list </td> 
<td> <input name='sockset' type='file' value=''> <br /> &nbsp; &nbsp; </td>
</tr>

<script type="text/javascript">
	document.forms.form1.elements.sockset.disabled = true;
</script>

</td>

</tr>

<tr>
   <td>Agent: <br /> &nbsp;</td>
   <td>
       <select size='1' name='Smtp'>
           <option value=''>     Direct      </option>
           <option value='smtp'> SMTP server </option>
       </select> <br /> &nbsp;
   </td>
</tr>
<tr>
   <td>Smtp Server: <br /> &nbsp;</td>
   <td>
       <select size='1' name='smtpServer'>
           <option value='esmtp'>esmtp</option>
           <option value='smtp'>smtp</option>
           <option value='test'>test</option>
       </select> <br /> &nbsp;
   </td>
</tr>

<tr>
   <td>Authenticate:<br /> &nbsp;</td>
   <td>
       <select size='1' name='Authenticate'>
          <option value=''>none</option>
          <option value='LOGIN'>login</option>
          <option value='PLAIN'>plain</option>
       </select>
       <br /> &nbsp;
   </td>
</tr>
<tr>
   <td>To: <br /> &nbsp;</td>
   <td><input name='Whom' type='file' value='receiver'><br /> &nbsp; </td>
</tr>
<tr>
   <td>Subject Set: <br /> &nbsp;</td>
   <td><input name='Subj' type='file' value='subject'><br />
   <td>
   </td> &nbsp; </td>
</tr>
<tr>
   <td>Message (Max 5000 characters): <br /> &nbsp;</td>
   <td><textarea name='Mess' rows=30 cols=60 wrap='off'>Text Here</textarea> <br /> &nbsp;</td>
</tr>
<tr>
   <td>The coding: <br /> &nbsp;</td>
   <td>
       <select size='1' name='Kod'>
          <option value=1>text</option>
          <option value=''>html</option>
       </select>
       <input name="id" type="hidden" value="1">
       <br /> &nbsp;
   </td>
</tr>

<!--
<tr>
   <td>Number: <br /> &nbsp; </td>
   <td>
       <input name='Number' type='text' value=''>
       <br /> &nbsp;
   </td>
</tr>
-->

<tr>
   <td>Using: <br /> &nbsp;</td>
   <td>
       <select size='1' name='Use'>
          <option value='whom'>To</option>
          <option value='maillist'>maillist</option>
          <option value='DB'>DataBase</option>
          <option value='all'>All</option>
       </select> <br /> &nbsp;
   </td>
</tr>
<tr>
   <td>Maillist:</td>
   <td><input name='List' type='file' value=''><br /><br /></td>
</tr>
<tr>
   <td>Attach:</td>
   <td>
       <input name='file1' type='file' value=''><br />
       <div id='input'>
       <input name='file2' type='file' value=''><br />
       <input name='file3' type='file' value=''><br />
       <input name='file4' type='file' value=''><br />
       <input name='file5' type='file' value=''>
       </div>
       <div id='input2'>
       <input name='file6' type='file' value=''><br />
       <input name='file7' type='file' value=''><br />
       <input name='file8' type='file' value=''><br />
       <input name='file9' type='file' value=''>
       </div>
       <div id='input3'>
       <input name='file10' type='file' value=''><br />
       <input name='file11' type='file' value=''><br />
       <input name='file12' type='file' value=''><br />
       <input name='file13' type='file' value=''>
       </div>
       <div id='input4'>
       <input name='file14' type='file' value=''><br />
       <input name='file15' type='file' value=''><br />
       <input name='file16' type='file' value=''><br />
       <input name='file17' type='file' value=''><br />
       <input name='file18' type='file' value=''><br />
       <input name='file19' type='file' value=''><br />
       <input name='file20' type='file' value=''><br />
       <input name='file21' type='file' value=''><br />
       <input name='file22' type='file' value=''><br />
       <input name='file23' type='file' value=''><br />
       <input name='file24' type='file' value=''><br />
       <input name='file25' type='file' value=''><br />
       <input name='file26' type='file' value=''><br />
       </div>
   </td>
</tr>
<tr>
   <td>
       <div id='id1'><input type='button' value = 'attach' onClick = 'output();'></div>
       <div id='id2'><input type='button' value = 'attach' onClick = 'output2();'></div>
       <div id='id3'><input type='button' value = 'attach' onClick = 'output3();'></div>
       <div id='id4'><input type='button' value = 'attach' onClick = 'output4();'></div>
       <input type='button' value = 'cancel' onClick = 'cancel();'>
   </td>
</tr>
<tr><td>&nbsp;</td></tr>
<tr>
   <td>
       <input type='submit' name = 'send' value='Send'>
       <!--
       <input type='submit' name = 'send' value='stop'>
       -->
       <input type = 'reset' name = 'Reset' value = 'Reset'>
   </td>
</tr>

<tr>
   <td type="hidden"><br /> &nbsp;</td>
   <td><input type="hidden" name='SmtpHost' type='text' value='$defdomain'><br /> &nbsp; </td>
</tr >
<tr>
   <td type="hidden"><br /> &nbsp;</td>
   <td><input type="hidden" name='SmtpPort' type='text' value='$defport'><br /> &nbsp; </td>
</tr>
<tr>
   <td type="hidden"></td>
   <td><input type="hidden" name='UserSmtp' type='text' value='$defuser'><br /> &nbsp;</td>
</tr>
<tr>
   <td><br /> &nbsp;</td>
   <td><input type="hidden" name='PassSmtp' type='password' value='$defpass' ><br /> &nbsp;</td>
</tr>
<tr>
   <td><br /> &nbsp;</td>
   <td><input type="hidden" name='From' type='text' value='$deffrom'><br /> &nbsp;</td>
</tr>

</form>

</table>

<script type="text/javascript"><!--
google_ad_client = "ca-pub-3344559188794146";
/* group3 */
google_ad_slot = "2642185801";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>

</center>

<script type='text/javascript' language='JavaScript'>hide();</script>

<br />

phpMM2;

$about = <<<phpMM3
<center>
<table id = 'x35y' width = 28% height = 55%>
<tr>
   <td valign = 'top'> <br />
        <center>PHP Mass Mailer</center>
        <br /> <br />
        &nbsp;Copyright (&copy;) Papakirikou Evangelos, GPL  <br /> <br />
        &nbsp;http://koitamamaexoblog.blogspot.com <br /> <br />
        &nbsp;vpapakir@yahoo.gr
        <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br />
        <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br />
        <br /> <br />
   </td>
</tr>
</table> <br /> <br /> <br /> <br /> <br />
</center>

phpMM3;

$import = <<<phpMM4
<center>
<table>
<tr><td>
<form action='' method = 'post'>
<select size='1' name='dbfbasa'>
  <option value='mysql'>mysql</option>
  <option value='pgsql'>pgsql</option>
  <option value='ibase'>ibase</option>
  <option value='all'>all</option>
</select> <br />
<TEXTAREA NAME='e_mail' ROWS=30 COLS=40 WRAP='VIRTUAL'></TEXTAREA> <br />
<input name="id" type="hidden" value="2">
<input type='submit' value='Send'>
</form>
<tr><td>
</table>
</center>

phpMM4;

$id = (isset($_GET['id']))?$_GET['id']:'';

switch($id){
        case '1':
        echo $phpMM;
        break;
        case '2':
        echo $import;
        break;
        case '3':
        phpinfo();
        break;
        case '4':
        echo $about;
        break;
        default:
        echo $phpMM;
}

echo <<<phpMM4

<table id ='x79y' width=100%>
<tr>
   <td id = 'x79y'>
   </td>
</tr>
<tr>
   <td id = 'x79y'>
	      <DIV id ='x78y'>PHP Mass Mailer &copy; Papakirikou Evangelos (version $version) </DIV>
   </td>
</tr>
</table>

</center>

</body>

</html>

phpMM4;

?>