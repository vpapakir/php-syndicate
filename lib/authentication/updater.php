<?php
//header('Content-Type: application/json');
header("Content-type: text/javascript");
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

//$uname = ($_GET['uname']) ? $_GET['uname'] : $_POST['uname'];

if(isset($_GET['uname'])) {
	$uname = $_GET['uname'];
	$eventcounter = $_GET['eventcounter'];
}

if(isset($_POST['uname'])) {
	$uname = $_POST['uname'];
	$eventcounter = $_POST['eventcounter'];
}

if(isset($_SESSION['userName'])) {
	$uname = $_SESSION['userName'];
}

session_write_close();

$myFile = $uname.".txt";
$fh = fopen($myFile, 'r');
$eventcounter = fread($fh, 5);
fclose($fh);

$output = array(
'status' => "NODATA",
'progress' => '0'
);
//$uname = $_SESSION['userName'];

$dbcon = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
if(!$dbcon) {
	$output["status"] = "\r\n<p>(ERROR) No connection to DB!</p>\r\n";
} else {
	$dbechovar = $dbcon->getResult("SELECT * FROM `phpsyndicateinterrupted_sessions` WHERE `username`='%s' AND `event_id`='%d'",$uname,$eventcounter);
	$output["status"] = $dbechovar[0]->output;
	$output["progress"] = $dbechovar[0]->progress;
	$dbcon->disconnect();
}
$eventcounter++;
$myFile = $uname.".txt";
$fh = fopen($myFile, 'w');
//chmod($myFile, 0777);
fwrite($fh, $eventcounter);
fclose($fh);
//
echo json_encode($output);
?>
