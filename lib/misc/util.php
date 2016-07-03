<?php

function genRandomString() {
	$length = 5;
	$characters = "0123456789abcdefghijklmnopqrstuvwxyz";
	$string = "a";    

	for ($p = 0; $p < $length; $p++) {
		$string .= $characters[mt_rand(0, strlen($characters))];
	}

	return $string;
}

?>