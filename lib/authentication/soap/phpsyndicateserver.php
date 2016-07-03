<?php
include '../../data/mysqlconnector.php';
include '../../../config/header2.php';

include '../../external/nusoap-0.9.5/lib/lib/nusoap.php';

function phpsyndicate_send_mail() {
	$result = "FOO!";
	for($i=0;$i<5000;$i++) {
		if(!connection_aborted()) {
			/*$dbcon2 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
			$dbechovar = $dbcon2->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`) VALUES ('%d','%s','%s')",rand(),$i,$result.$i);
			$dbcon2->disconnect();*/
			$result = $result."CONNECTED".$i;
		} else {
			/*$dbcon2 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
			$dbcon2 = new DBConnect($DBHOST,$DBNAME,$DBUSER,$DBPASS);
			$dbechovar = $dbcon2->getResult("INSERT INTO `phpsyndicateinterrupted_sessions` (`session_id`,`username`,`output`) VALUES ('%d','%s','%s')",rand(),$i,$result.$i);
			$dbcon2->disconnect();*/
			$result = $result."DISCONNECTED".$i;
		}
		usleep(8000);
	}
	return $result;
}

$phpsyndicateserver = new nusoap_server;

$phpsyndicateserver->register('phpsyndicate_send_mail');

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';

$phpsyndicateserver->service($HTTP_RAW_POST_DATA);
?>