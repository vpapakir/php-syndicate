<?php
error_reporting(E_ALL & ~E_NOTICE);
ignore_user_abort(true);
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

function selectRandomProfile($uname,$DBHOST,$DBNAME,$DBUSER,$DBPASS) {
	$profileobj = new userProfile();
	$dbcon = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
	$dbcon2 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
	if(!$dbcon) {
		if(!connection_aborted()) {
			echo "\r\n<p>(ERROR) No connection to DB!</p>\r\n";
		} else {
			$dbechovar = $dbcon2->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$uname);
			if(isset($dbechovar)) { // There's is an interrupted session for this user
				// append data
				$dbechovar = $dbcon2->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'","\r\n<p>(ERROR) No connection to DB!</p>\r\n",$uname);
			} else { // There isn't
				// create a record
				$dbechovar = $dbcon2->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`) VALUES ('%d','%s','%s')",rand(),$uname,"\r\n<p>(ERROR) No connection to DB!</p>\r\n");
			}
		}
		$dbcon2->disconnect();
	} else {
  		$resss = $dbcon->getResult("SELECT * FROM `phpsyndicatesmtp_profile` WHERE `username`='%s' ORDER BY RAND() LIMIT 0,1",$uname);
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

$dbcon = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
if(!$dbcon) {
	echo "\r\n<p>(ERROR) No connection to DB!</p>\r\n";
	exit();
} else {
	$from = "";     // retrieve it from user info
	$to = "";
	$subject = $subject;
	$smtp_server = "";
	$smtp_port   = "";
	$smtp_user   = "";
	$smtp_pass   = "";
	
	$res = $dbcon->getResult("SELECT `email` FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
	$from = $res[0]->email;
			
	$res = $dbcon->getResult("SELECT `smtp_server` FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
	$smtp_server = $res[0]->smtp_server;
				
	$res = $dbcon->getResult("SELECT `smtp_port` FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
	$smtp_port = $res[0]->smtp_port;
				
	$res = $dbcon->getResult("SELECT `smtp_user` FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
	$smtp_user = $res[0]->smtp_user;
				
	$res = $dbcon->getResult("SELECT `smtp_pass` FROM `phpsyndicateuser` WHERE `username`='%s'",$_SESSION['userName']);
	$smtp_pass = $res[0]->smtp_pass;
		
	$body2send = $bodymsg;
	if( !mysqli_ping($dbcon->database) ) {
		$dbcon->disconnect();
		$dbcon = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
	}
	$to = $csvobj->ItemsList[0]['Email'];
	
	$resu = $dbcon->getResult("SELECT * FROM `phpsyndicatedo_not_send_list` WHERE `email`='%s'",$to);
	if(!$resu) {
		$body2send = str_ireplace("^HOST^",$csvobj->ItemsList[0]['Host'],$bodymsg);
		//$body2send = str_ireplace("^Host^",$csvobj->ItemsList[0]['Host'],$bodymsg);
		if(isset($csvobj->ItemsList[0]['Name'])) {
			$body2send = str_ireplace("^NAME^",$csvobj->ItemsList[0]['Name'],$body2send);
		} else {
			$body2send = str_ireplace("^NAME^",$csvobj->ItemsList[0]['Email'],$body2send);
		}
		$body2send = str_ireplace("^EMAIL^",$csvobj->ItemsList[0]['Email'],$body2send);
		$subject   = str_ireplace("^EMAIL^",$csvobj->ItemsList[0]['Email'],$subject);
		$subject   = str_ireplace("^HOST^",$csvobj->ItemsList[0]['Host'],$subject);

		if(isset($csvobj->ItemsList[0]['Name'])) {
			$subject = str_ireplace("^NAME^",$csvobj->ItemsList[0]['Name'],$subject);
		} else {
			$subject = str_ireplace("^NAME^",$csvobj->ItemsList[0]['Email'],$subject);
		}
		
		$dispdate    = date('l jS \of F Y');
		$extime      = date('h:i:s A');
		$profileobj  = selectRandomProfile($_SESSION['userName'],$DBHOST,$DBNAME,$DBUSER,$DBPASS);
		$from        = $profileobj->from;
		$smtp_server = $profileobj->smtp_server;
		$smtp_port   = $profileobj->smtp_port;
		$smtp_user   = $profileobj->smtp_user;
		$smtp_pass   = $profileobj->smtp_pass;
		$msgobj      = new MailMessage($from,$to,$subject,$body2send,"",$protocol,$DISPDELAY);

		/*$fh = fopen("../../log/bluh.txt", 'w');
		fwrite($fh,$profileobj->smtp_server."\n".$profileobj->smtp_port."\n".$profileobj->smtp_user."\n".$profileobj->smtp_pass."\n".$profileobj->from."\n");
		fclose($fh);*/

		if($protocol == 1) {
			$dispdate  = date('l jS \of F Y');
			$extime    = date('h:i:s A');
			$msg2db = "\n[ ".$dispdate." | ".$extime."] - (INFO) Sending from ".$msgobj->from." to ".$to.".\n";
			if(!connection_aborted()) {
				echo $msg2db;
			} else {
				$dbcon2 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
				$dbechovar = $dbcon2->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$uname);
				if(isset($dbechovar)) { // There's is an interrupted session for this user
					// append data
					$dbechovar = $dbcon2->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db,$_SESSION['userName']);
				} else { // There isn't
					// create a record
					$dbechovar = $dbcon2->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`) VALUES ('%d','%s','%s')",rand(),$_SESSION['userName'],$msg2db);
				}
				$dbcon2->disconnect();
			}
			if( ($msgobj->checkEmail($msgobj->from)) && ($msgobj->checkEmail($to)) ) {
				$msgobj->MailMessage_smtp($msgobj->from,$to,$subject,$body2send,"",$protocol,$DISPDELAY,$smtp_server,$smtp_port,$smtp_user,$smtp_pass);
			} else if ( !($msgobj->checkEmail($msgobj->from)) ) {
				$msg2db3 = "\n(ERROR) Sender address (".$msgobj->from.") is not valid!\n";
				$msg2db4 = "\n(ERROR) Receiver address (".$to.") is not valid!\n";
				$msg2db5 = "\n(ERROR) Both sender and receiver address are not valid!\n";
				if(!connection_aborted()) {
					echo $msg2db3;
				} else {
					$dbcon2 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
					$dbechovar = $dbcon2->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$_SESSION['userName']);
					if(isset($dbechovar)) { // There's is an interrupted session for this user
						// append data
						$dbechovar = $dbcon2->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db3,$_SESSION['userName']);
					} else { // There isn't
						// create a record
						$dbechovar = $dbcon2->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`) VALUES ('%d','%s','%s')",rand(),$_SESSION['userName'],$msg2db3);
					}
					$dbcon2->disconnect();
				}
				//continue;
			} else if ( !($msgobj->checkEmail($to)) ) {
				if(!connection_aborted()) {
					echo $msg2db4; 
				} else {
					$dbcon2 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
					$dbechovar = $dbcon2->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$uname);
					if(isset($dbechovar)) { // There's is an interrupted session for this user
						// append data
						$dbechovar = $dbcon2->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db4,$_SESSION['userName']);
					} else { // There isn't
						// create a record
						$dbechovar = $dbcon2->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`) VALUES ('%d','%s','%s')",rand(),$_SESSION['userName'],$msg2db4);
					}
					$dbcon2->disconnect();
				}
				//continue;
			} else {
				if(!connection_aborted()) {
					echo $msg2db5; 
				} else {
					$dbcon2 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
					$dbechovar = $dbcon2->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$_SESSION['userName']);
					if(isset($dbechovar)) { // There's is an interrupted session for this user
						// append data
						$dbechovar = $dbcon2->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db5,$_SESSION['userName']);
					} else { // There isn't
						// create a record
						$dbechovar = $dbcon2->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`) VALUES ('%d','%s','%s')",rand(),$_SESSION['userName'],$msg2db5);
					}
					$dbcon2->disconnect();
				}
				//continue;
			}
		} else {
			$msgobj->MailMessage_sock($from,$to,$subject,$body2send,"",$protocol,$DISPDELAY);
		}
		if($msgobj->sendMail()) {
			$dispdate  = date('l jS \of F Y');
			$extime    = date('h:i:s A');
			$msg2db6 = "\n[ ".$dispdate." | ".$extime."] - (INFO) Email Sent Successfully to [".$to."]\n";
			if(!connection_aborted()) {
				echo $msg2db6;
			} else {
				$dbcon2 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
				$dbechovar = $dbcon2->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$_SESSION['userName']);
				if(isset($dbechovar)) { // There's is an interrupted session for this user
					// append data
					$dbechovar = $dbcon2->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db6,$_SESSION['userName']);
				} else { // There isn't
					// create a record
					$dbechovar = $dbcon2->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`) VALUES ('%d','%s','%s')",rand(),$_SESSION['userName'],$msg2db6);
				}
				$dbcon2->disconnect();
			}
		} else {
			$dispdate3 = date('l jS \of F Y');
			$extime3   = date('h:i:s A');
			$msg2db7 = "\n[ ".$dispdate3." | ".$extime3."] - (ERROR) Dispatch failed.\n";
			if(!connection_aborted()) {
				echo $msg2db7;
			} else {
				$dbcon2 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
				$dbechovar = $dbcon2->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$_SESSION['userName']);
				if(isset($dbechovar)) { // There's is an interrupted session for this user
					// append data
					$dbechovar = $dbcon2->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db7,$_SESSION['userName']);
				} else { // There isn't
					// create a record
					$dbechovar = $dbcon2->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`) VALUES ('%d','%s','%s')",rand(),$_SESSION['userName'],$msg2db7);
				}
				$dbcon2->disconnect();
			}
		}
	} else {
		$dispdate2 = date('l jS \of F Y');
		$extime2 = date('h:i:s A');
		$msg2db8 = "\n[ ".$dispdate2." | ".$extime2."] - (INFO) Email to ".$to." will not be sent!\n";
		if(!connection_aborted()) {
			echo $msg2db8;
		} else {
			$dbcon2 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
			$dbechovar = $dbcon2->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$_SESSION['userName']);
			if(isset($dbechovar)) { // There's is an interrupted session for this user
				// append data
				$dbechovar = $dbcon2->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db8,$_SESSION['userName']);
			} else { // There isn't
				// create a record
				$dbechovar = $dbcon2->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`) VALUES ('%d','%s','%s')",rand(),$_SESSION['userName'],$msg2db8);
			}
			$dbcon2->disconnect();
		}
	}	
	$dbcon->disconnect();
}

$dbcon3 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
$msg2db10 = "\nEND\n";
if($csvobj->Items_Count>0) {
	$status = $csvobj->csv_delete_rows($_SESSION['uploadedfile'], 2, 2);
	$csvobj->ReadCSV();
	$tmp = (100*(($_SESSION['count'])-($csvobj->Items_Count))/($_SESSION['count']));
	$dispdate  = date('l jS \of F Y');
	$extime    = date('h:i:s A');
	$msg2db9  = "\n[ ".$dispdate." | ".$extime."] - (INFO) Prc % completed:".$tmp."\n";
	if(!connection_aborted()) {
		echo $msg2db9;
	} else {
		$dbechovar = $dbcon3->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$_SESSION['userName']);
		if(isset($dbechovar)) { // There's is an interrupted session for this user
			// append data
			$dbechovar = $dbcon3->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db9,$_SESSION['userName']);
		} else { // There isn't
			// create a record
			$dbechovar = $dbcon3->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`) VALUES ('%d','%s','%s')",rand(),$_SESSION['userName'],$msg2db9);
		}
	}
	exit();
} else {
	if(!connection_aborted()) {
		echo $msg2db10;
	} else {
		$dbechovar = $dbcon3->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s'",$_SESSION['userName']);
		if(isset($dbechovar)) { // There's is an interrupted session for this user
			// append data
			$dbechovar = $dbcon3->getResult("UPDATE `phpsyndicateinterrupted_sessions` SET `output`=CONCAT(`output`,'%s') WHERE `username`='%s'",$msg2db10,$_SESSION['userName']);
		} else { // There isn't
			// create a record
			$dbechovar = $dbcon3->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`) VALUES ('%d','%s','%s')",rand(),$_SESSION['userName'],$msg2db10);
		}
	}
	exit();
}
$dbcon3->disconnect();
?>
