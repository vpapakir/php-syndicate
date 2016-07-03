<?php
header('Content-Type: application/json');
error_reporting(E_ALL & ~E_NOTICE);
ignore_user_abort(true);
ob_implicit_flush(1);
//set_time_limit(0); // Not allowed on zymic

include '../../config/header2.php';
require('makeSecure.php'); 
include '../admin/admin.php';
include '../data/mysqlconnector.php';
include '../data/CSVReader.php';
include '../mail/MailMessage.php';

if(!isset($_SESSION)) 
{
	session_start(); 
}

$protocol = ($_GET['protocol']) ? $_GET['protocol'] : $_POST['protocol'];
$bodymsg = ($_GET['bodymsg']) ? $_GET['bodymsg'] : $_POST['bodymsg'];
$subject = ($_GET['subject']) ? $_GET['subject'] : $_POST['subject'];
$csvobj = new CSVReader($_SESSION['uploadedfile'],',','email');
$csvobj->ReadCSV();

class userProfile {
	public $smtp_server = "";
	public $smtp_port   = "";
	public $smtp_user   = "";
	public $smtp_pass   = "";
	public $from        = ""; 
}

function selectRandomProfile($usname,$DBHOST,$DBNAME,$DBUSER,$DBPASS) {
	$profileobj = new userProfile();
	$dbcon = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
	$dbcon2 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
	if(!$dbcon) {
		$dbechovar = $dbcon2->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$usname);
		//if(isset($dbechovar)) { // There's is an interrupted session for this user
			// append data
			//$dbechovar = $dbcon2->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'","\r\n<p>(ERROR) No connection to DB!</p>\r\n",$usname);
		//} else { // There isn't
			// create a record
			//$dbechovar = $dbcon2->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`,`progress`,`event_id`) VALUES ('%d','%s','%s','%s','%d')",rand(),$usname,"\r\n<p>(ERROR) No connection to DB!</p>\r\n");
		//}
		$dbcon2->disconnect();
	} else {
  		$resss = $dbcon->getResult("SELECT * FROM `phpsyndicatesmtp_profile` WHERE `username`='%s' ORDER BY RAND() LIMIT 0,1",$usname);
		if(isset($resss)) {
			$profileobj->smtp_server = $resss[0]->smtp_server;
			$profileobj->smtp_port   = $resss[0]->smtp_port;
			$profileobj->smtp_user   = $resss[0]->smtp_user;
			$profileobj->smtp_pass   = $resss[0]->smtp_pass;
			$profileobj->from        = $resss[0]->email;			
		} else {
			// use default profile
		}
	}
	$dbcon->disconnect();
	return $profileobj;
}

$output = array();
$uname = $_SESSION['userName'];
$eventcounter = 0;
$progress = 0;

$myFile = $uname.".txt";
$fh = fopen($myFile, 'w');
// Everything for owner, read and execute for owner's group
// chmod($myFile, 0777);
fclose($fh);

$dbcon = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
if(!$dbcon) {
	$output["status"] = "\r\n<p>(ERROR) No connection to DB!</p>\r\n";
	echo json_encode($output);
	exit();
} else {
	$from = "";     // retrieve it from user info
	$to = "";
	$smtp_server = "";
	$smtp_port   = "";
	$smtp_user   = "";
	$smtp_pass   = "";
	
	$res = $dbcon->getResult("SELECT `email` FROM `phpsyndicateuser` WHERE `username`='%s'",$uname);
	$from = $res[0]->email;
			
	$res = $dbcon->getResult("SELECT `smtp_server` FROM `phpsyndicateuser` WHERE `username`='%s'",$uname);
	$smtp_server = $res[0]->smtp_server;
				
	$res = $dbcon->getResult("SELECT `smtp_port` FROM `phpsyndicateuser` WHERE `username`='%s'",$uname);
	$smtp_port = $res[0]->smtp_port;
				
	$res = $dbcon->getResult("SELECT `smtp_user` FROM `phpsyndicateuser` WHERE `username`='%s'",$uname);
	$smtp_user = $res[0]->smtp_user;
				
	$res = $dbcon->getResult("SELECT `smtp_pass` FROM `phpsyndicateuser` WHERE `username`='%s'",$uname);
	$smtp_pass = $res[0]->smtp_pass;
		
	$body2send = $bodymsg;
	if( !mysqli_ping($dbcon->database) ) {
		$dbcon->disconnect();
		$dbcon = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
	}

	for($dispatchcounter=0;$dispatchcounter<($csvobj->Items_Count);$dispatchcounter++) {
		usleep($DISPDELAY);
		for($dummycounter=0;$dummycounter<($DISPDELAY);$dummycounter++) {
		}
		
		$to = $csvobj->ItemsList[$dispatchcounter]['Email'];
		$resu = $dbcon->getResult("SELECT * FROM `phpsyndicatedo_not_send_list` WHERE `email`='%s'",$to);
		if(!$resu) {
			$body2send = str_ireplace("^HOST^",$csvobj->ItemsList[$dispatchcounter]['Host'],$bodymsg);
			if(isset($csvobj->ItemsList[$dispatchcounter]['Name'])) {
				$body2send = str_ireplace("^NAME^",$csvobj->ItemsList[$dispatchcounter]['Name'],$body2send);
			} else {
				$body2send = str_ireplace("^NAME^",$csvobj->ItemsList[$dispatchcounter]['Email'],$body2send);
			}
			$body2send = str_ireplace("^EMAIL^",$csvobj->ItemsList[$dispatchcounter]['Email'],$body2send);
			$subject   = str_ireplace("^EMAIL^",$csvobj->ItemsList[$dispatchcounter]['Email'],$subject);
			$subject   = str_ireplace("^HOST^",$csvobj->ItemsList[$dispatchcounter]['Host'],$subject);
			if(isset($csvobj->ItemsList[$dispatchcounter]['Name'])) {
				$subject = str_ireplace("^NAME^",$csvobj->ItemsList[$dispatchcounter]['Name'],$subject);
			} else {
				$subject = str_ireplace("^NAME^",$csvobj->ItemsList[$dispatchcounter]['Email'],$subject);
			}
			$dispdate    = date('l jS \of F Y');
			$extime      = date('h:i:s A');
			$profileobj  = selectRandomProfile($uname,$DBHOST,$DBNAME,$DBUSER,$DBPASS);
			$from        = $profileobj->from;
			$smtp_server = $profileobj->smtp_server;
			$smtp_port   = $profileobj->smtp_port;
			$smtp_user   = $profileobj->smtp_user;
			$smtp_pass   = $profileobj->smtp_pass;
			$msgobj      = new MailMessage($from,$to,$subject,$body2send,"",$protocol,$DISPDELAY);
			if($protocol == 1) {
				$dispdate  = date('l jS \of F Y');
				$extime    = date('h:i:s A');
				$msg2db = "\n[ ".$dispdate." | ".$extime."] - (INFO) Sending from ".$msgobj->from." to ".$to.".";
				$dbcon10 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
				//$dbechovar = $dbcon10->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$uname);
				//if(isset($dbechovar)) { // There's is an interrupted session for this user
					// append data
					//$dbechovar = $dbcon10->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db,$uname);
				//} else { // There isn't
					// create a record
					$progress = (100*($dispatchcounter+1))/($csvobj->Items_Count);
					$dbechovar = $dbcon10->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`,`progress`,`event_id`) VALUES ('%d','%s','%s','%s','%d')",rand(),$uname,$msg2db,$progress,$eventcounter);
					$eventcounter++;
				//}
				$dbcon10->disconnect();
				if( ($msgobj->checkEmail($msgobj->from)) && ($msgobj->checkEmail($to)) ) {
					$msgobj->MailMessage_smtp($msgobj->from,$to,$subject,$body2send,"",$protocol,$DISPDELAY,$smtp_server,$smtp_port,$smtp_user,$smtp_pass);
				} else if ( !($msgobj->checkEmail($msgobj->from)) ) {
					$msg2db3 = "\n(ERROR) Sender address (".$msgobj->from.") is not valid!\n";
					$msg2db4 = "\n(ERROR) Receiver address (".$to.") is not valid!\n";
					$msg2db5 = "\n(ERROR) Both sender and receiver address are not valid!\n";
					$dbcon42 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
					$dbechovar = $dbcon42->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$uname);
					//if(isset($dbechovar)) { // There's is an interrupted session for this user
						// append data
						//$dbechovar = $dbcon42->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db3,$uname);
					//} else { // There isn't
						// create a record
						$progress = (100*($dispatchcounter+1))/($csvobj->Items_Count);
						$dbechovar = $dbcon42->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`,`progress`,`event_id`) VALUES ('%d','%s','%s','%s','%d')",rand(),$uname,$msg2db3,$progress,$eventcounter);
						$eventcounter++;
					//}
					$dbcon42->disconnect();
				} else if ( !($msgobj->checkEmail($to)) ) {
					$dbcon32 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
					$dbechovar = $dbcon32->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$uname);
					//if(isset($dbechovar)) { // There's is an interrupted session for this user
						// append data
						//$dbechovar = $dbcon32->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db4,$uname);
					//} else { // There isn't
						// create a record
						$progress = (100*($dispatchcounter+1))/($csvobj->Items_Count);
						$dbechovar = $dbcon32->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`,`progress`,`event_id`) VALUES ('%d','%s','%s','%s','%d')",rand(),$uname,$msg2db4,$progress,$eventcounter);
						$eventcounter++;
					//}
					$dbcon32->disconnect();
				} else {
					$dbcon25 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
					$dbechovar = $dbcon25->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$uname);
					//if(isset($dbechovar)) { // There's is an interrupted session for this user
						// append data
						//$dbechovar = $dbcon25->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db5,$uname);
					//} else { // There isn't
						// create a record
						$progress = (100*($dispatchcounter+1))/($csvobj->Items_Count);
						$dbechovar = $dbcon25->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`,`progress`,`event_id`) VALUES ('%d','%s','%s','%s','%d')",rand(),$uname,$msg2db5,$progress,$eventcounter);
						$eventcounter++;
					//}
					$dbcon25->disconnect();
				}
			} else {
				$msgobj->MailMessage_sock($from,$to,$subject,$body2send,"",$protocol,$DISPDELAY);
			}
			if($msgobj->sendMail()) {
				$dispdate  = date('l jS \of F Y');
				$extime    = date('h:i:s A');
				$msg2db6 = "[ ".$dispdate." | ".$extime."] - (INFO) Email Sent Successfully to [".$to."]";
				$dbcon29 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
				$dbechovar = $dbcon29->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$uname);
				//if(isset($dbechovar)) { // There's is an interrupted session for this user
					// append data
					//$dbechovar = $dbcon29->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db6,$uname);
				//} else { // There isn't
					// create a record
					$progress = (100*($dispatchcounter+1))/($csvobj->Items_Count);
					$dbechovar = $dbcon29->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`,`progress`,`event_id`) VALUES ('%d','%s','%s','%s','%d')",rand(),$uname,$msg2db6,$progress,$eventcounter);
					$eventcounter++;
				//}
				$dbcon29->disconnect();
			} else {
				$dispdate3 = date('l jS \of F Y');
				$extime3   = date('h:i:s A');
				$msg2db7 = "\n[ ".$dispdate3." | ".$extime3."] - (ERROR) Dispatch failed.\n";
				$dbcon23 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
				$dbechovar = $dbcon23->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$uname);
				//if(isset($dbechovar)) { // There's is an interrupted session for this user
					// append data
					//$dbechovar = $dbcon23->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db7,$uname);
				//} else { // There isn't
					// create a record
					$progress = (100*($dispatchcounter+1))/($csvobj->Items_Count);
					$dbechovar = $dbcon23->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`,`progress`,`event_id`) VALUES ('%d','%s','%s','%s','%d')",rand(),$uname,$msg2db7,$progress,$eventcounter);
					$eventcounter++;
				//}
				$dbcon23->disconnect();
			}
		} else {
			$dispdate2 = date('l jS \of F Y');
			$extime2 = date('h:i:s A');
			$msg2db8 = "[ ".$dispdate2." | ".$extime2."] - (INFO) Email to ".$to." will not be sent!";
			$dbcon2 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
			$dbechovar = $dbcon2->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$uname);
			//if(isset($dbechovar)) { // There's is an interrupted session for this user
				// append data
				//$dbechovar = $dbcon2->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db8,$uname);
			//} else { // There isn't
				// create a record
				$progress = (100*($dispatchcounter+1))/($csvobj->Items_Count);
				$dbechovar = $dbcon2->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`,`progress`,`event_id`) VALUES ('%d','%s','%s','%s','%d')",rand(),$uname,$msg2db8,$progress,$eventcounter);
				$eventcounter++;
			//}
			$dbcon2->disconnect();		
		}

		$output["progress"] = (100*($dispatchcounter+1))/($csvobj->Items_Count);
		
		/*$dbconprogress = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
		$dbechovarprogress = $dbconprogress->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `progress`='%s' WHERE `username`='%s'",$output["progress"],$uname);
		$dbconprogress->disconnect();*/
	}
	$dbcon->disconnect();
}
//$output["progress"] = 100;
echo json_encode($output);
?>
