<?php
include '../../data/mysqlconnector.php';
include '../../../config/header2.php';

ignore_user_abort(true);
//set_time_limit(0); // Not allowed on zymic

//require_once('../lib/nusoap.php');
include '../../external/nusoap-0.9.5/lib/nusoap.php';

$phpsyndicateclient = new nusoap_client('http://phpsyndicate.zxq.net/lib/authentication/soap/phpsyndicateserver.php',false);

$result = $phpsyndicateclient->call('phpsyndicate_send_mail',
array('msg' => 'FOO 2!')
);

//if(!connection_aborted()) {
//	print_r($result);
//} else {
	$dbcon3 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
	$dbechovar2 = $dbcon3->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`) VALUES ('%d','%s','%s')",rand(),rand(),$result);
	$dbcon3->disconnect();
//}
?>