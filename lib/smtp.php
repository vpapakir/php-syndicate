<?
/*
smtp PHP Class
Copyright (C) 2010 Evangelos Papakirikou.
-----------------------------------------------------------------------------
Send a message via SMTP with optional LOGIN authorization. 

Constructor:
   new smtp(<hostname>,	// string: hostname of mail server
            <port>,	// integer: port of smtp server on host, usually 25
	    <from>,	// address message is from
	    <recip>,	// string or array: list of recipients
	    <headers>,	// headers to be sent with message. (HINT = put
	    		// subject here)
	   <auth>,	// hash of auth data ('user' => user, 
	   		// 'pass' = password)
	   <message data>// text of message to be sent.
	   
     Throws Exception on error.
*/     
define('au_st_auth',0);
define('au_st_user',1);
define('au_st_pass',2);
define('au_st_done',3);

define('sm_st_helo',0);
define('sm_st_auth',1);
define('sm_st_mfrom',2);
define('sm_st_rcpto',3);
define('sm_st_data',4);
define('sm_st_cleanup',5);

require_once('sock.php');

class smtp extends sock { 
    var $errors;
    var $headers;
    var $data;
    var $host;
    var $state;
    var $recip;
    var $from;
    var $user;
    var $pass;
    
    function __construct($server, $port = -1, $from = "", $recip = "", $headers = "", $authdata = "", $data = "") {
	
	$this->errors = array();
	if($port == -1)
	  $port = 25;
	$cdata = array('address' => $server,
		       'port' => $port);
	try { 
	    parent::__construct($cdata);
	} catch(Exception $e) {
	    throw($e);
	}
	$this->get_data($junkdata);
	$this->from = $from;
	$this->recip = $recip;
	$this->headers = $headers;
	$this->authdata = $authdata;
	$this->data = $data;
	
	if($this->from == "" || $this->recip == "" || $this->data == "") { 
	    ob_start();
	    print_r($this);
	    $xxx = ob_get_contents();
	    ob_end_clean();
	    throw new Exception("! NOT ENOUGH INFO TO BUILD MESSAGE: OBJ Data: $xxx\n");
	}
	$state = sm_st_helo;
	while($state <= sm_st_cleanup) {
	    $prevstate = $state;
	    $state = $this->sendMessage($state);
	    if($state === false) {
		$x = $this->errors[count($this->errors)-1];
		throw new exception($x['text'],$x['code']);
	    }
	}
    }
    function sendMessage($state) {
	switch($state) {
	 case sm_st_helo:
	    if($this->helo())
	      break;
	    return false;
	 case sm_st_auth:
	    if(is_array($this->authdata)) {
		$this->user = $this->authdata['user'];
		$this->pass = $this->authdata['pass'];
		if($this->auth())
		  break;
		return false;
	    }
	    break;
	 case sm_st_mfrom:
	    if($this->mailfrom()) 
	      break;
	    return false;
	 case sm_st_rcpto:
	    if($this->rcpto())
	      break;
	    return false;
	 case sm_st_data:
	    if($this->send_msgdata())
	      break;
	    return false;
	 case sm_st_cleanup:
	    $this->cleanup();
	    break;
	 default: 
	    $msg="fsm: Unknown state $state";
	    $this->errors[] = array('code' => -1, 'text' => $msg);
	    return false;
	}
	$state ++ ;
	return $state;
    }
    function helo() {
	if(!isset($host) || $host == "") {
	    $host = exec('hostname');
	}
	$this->send_data("HELO $host\n");
	if($this->get_data($data)) {
	    list($code,$text) = explode(' ',trim($data),2);
	    if($code > 250) {
		$this->errors[] = array('code' => $code, 'text' => $text);
		return false;
	    }
	} else {
	    $this->errors[] = array('code' => -1, 'text' => 'cannot send command');
	    return false;
	}
	return true;
    }
    function rcpto() {
	if(!is_array($this->recip)) {
	    $recip = array();
	    foreach(explode(',',$this->recip) as $r)
	      $recip[] = trim($r);
	    $this->recip = $recip;
	}
	foreach($this->recip as $r) {
	    if($this->send_data("RCPT TO: $r\n")) {
		if($this->get_data($data)) {
		    list($code,$text) = explode(' ',trim($data),2);
		    if($code != 250) {
			$this->errors[] = array('code' => $code, 'text' => $text);
			return false;
		    }
		} else {
		    $this->errors[] = array('code' => -1, 'text' => 'cannot send command');
		    return false;
		}
	    }
	}
	return true;
    }
    function mailfrom() {
	if($this->send_data("MAIL FROM: $this->from\n")) {
	    if(!$this->get_data($data)) {
		$this->errors[] = array('code' => -1, 'text' => 'cannot read command response');
		return false;
	    }
	    list($code,$text) = explode(' ',trim($data),2);
	    if($code > 250) {
		$this->errors[] = array('code' => $code, 'text' => $text);
		return false;
	    }
	} else {
	    $this->errors[] = array('code' => -1, 'text' => 'cannot send command');
	    return false;
	}
	return true;
    }
    /*
     * auth is a mini-finite-state-machine which sends the AUTH LOGIN commands
     * in succession until the appropriate state (au_st_done) is reached.
     */
    function auth() {
	$codes = array(334,334,235,0);
	$state = au_st_auth;
	while($state != au_st_done) {
	    switch($state) {
	     case au_st_auth:
		$r = $this->send_data("AUTH LOGIN\n");
		break;
	     case au_st_user:
		$r = $this->send_data(base64_encode($this->user) . "\n");
		break;
	     case au_st_pass:
		$r = $this->send_data(base64_encode($this->pass) . "\n");
		break;
	     case au_st_done:
		break;
	    }
	    if(!$r) {
		$this->errors[] = array('code' => -1, 'text' => 'cannot send command');
		return false;
	    }
	    if(!$this->get_data($data)) {
		$this->errors[] = array('code' => -1, 'text' => 'cannot read command response');
		return false;
	    }
	    list($code,$text) = explode(' ',trim($data),2);
	    if($code != $codes[$state]) {
		$this->errors[] = array('code' => $code, 'text' => $text . "(expected code: " . $codes[$state] . ")");
		return false;
	    }
	    $state ++;
	}
	return true;
    }
    function send_msgdata() {
	if(is_array($this->headers)) {
	    $headers = "";
	    foreach($this->headers as $h) 
	      $headers .= str_replace("\n.\n","\n..\n",trim($h)) . "\n"; // Transparency
	} else 
	  $headers = str_replace("\n.\n","\n..\n",trim($this->headers));

	$data = str_replace("\n.\n","\n..\n",$this->data); // Transparency
	if(!$this->send_data("DATA\n")) {
	    $this->errors[] = array('code' => -1,'text' => 'Cannot send command');
	    return false;
	}
	if(!$this->get_data($data)) {
	    $this->errors[] = array('code' => -1,'text' => 'Cannot get command response');
	    return false;
	}
	list($code,$text) = explode(' ',trim($data),2);
	if($code != 354) {
	    $this->errors[] = array('code' => $code, 'text' => $text);
	    return false;
	}
	if($this->send_data($headers . "\n\n" . $this->data . "\n.\n")) {
	    if(!$this->get_data($data)) {
		$this->errors[] = array('code' => -1,'text' => 'Cannot get command response');
		return false;
	    }
	    list($code,$text) = explode(' ',trim($data),2);
	    if($code != 250) {
		$this->errors[] = array('code' => $code, 'text' => $text);
		return false;
	    }
	    return true;
	}
	$this->errors[] = array('code' => -1,'text' => 'Cannot get command response');
	return false;
    }
    function cleanup() {
	$this->send_data("QUIT\n");
	$this->disconnect();
    }
}
?>
