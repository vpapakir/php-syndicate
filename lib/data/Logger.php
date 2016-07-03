<?php

class Logger {
	
	var $msg;
	
	function logToFile($filename, $msg)
	{ 
		// open file
		$fd = fopen($filename, "a");
		// write string
		fwrite($fd, $msg . "\n");
		// close file
		fclose($fd);
	}
	
	function logToDB($msg) {
	}
}

?>