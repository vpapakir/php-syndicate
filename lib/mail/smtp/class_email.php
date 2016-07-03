<?php

error_reporting(E_ALL & ~E_NOTICE);
ignore_user_abort(true);
//set_time_limit(0); // not allowed on zymic servers

class email{
  public  $srv_ret;
  public  $type;
  private $sender_email;
  private $sender_name;
  private $server;
  private $user;
  private $pw;
  private $hostname;
  private $content_type;
  private $headers;
  private $crypto;
  private $smtp_try;
  private $server_ehlo;
  const SLEEP_SOCKET = 2000;  //socket delay in microseconds. default 2000
                              //Your CPU usage may spike if this value is too low!
                              //Increase this for slow connections or decrease to ~100 for *low* latency connections.

  function __construct()
  {
    //Please don't change the values here. Use the appropriate set_* function
    //from within your code so you can upgrade to new releases of this class
    // 
    //
    @set_time_limit(600); //5 minutes should be plenty ... famous last words ;)
    $this->server = null;
    $this->port = null;
    $this->user = null;
    $this->pw = null;
    $this->server_ehlo = array();
    $this->type = 0; // 1= custom smtp() class THIS CLASS  0= PHP mail()
    $this->hostname = php_uname('n'); //best guess, some server may require a valid rdns name
    $this->content_type = 'text/plain'; //html e-mails are evil!
    $this->crypto = 'starttls'; //this should be fine for most servers
    $this->smtp_try = true; //if true try starttls, tls:// or ssl:// before the connection fails
    $this->set_sender('class_email.php', 'test@localhost.local');
    $this->set_headers(); //create the default headers
  }

  /**
   * overwrite the default behavior of attempting to connect with a different
   * method if the first fails. the script may try starttls, tls:// or ssl://
   * before giving up unless you pass false
   * @param bool $switch true/false false=overwrite the default behavior
   */
  function set_smtp_try($switch=true)
  {
    $this->smtp_try = ( $switch === false ) ? false : true;
  }
  
  /**
   * function to set a default header, set your own or append your text
   * *warning* php_mail() will add From: and Reply-To: header entries above the header set here.
   * @param string $value=null new smtp header as string or null to load the defulat
   * @param bool $append=false $value will be appened to current header if this is true or used to overwrite the existing value
   */
  function set_headers( $value=null, $append=false )
  {
    if( $value === null ) {
      //load default headers
      $this->headers = "MIME-Version: 1.0\r\n"
                  ."Content-type: $this->content_type; charset=UTF-8; format=flowed\r\n"
                  ."Content-Transfer-Encoding: base64\r\n"
                  ."X-Mailer: class_email.php";
    }else{
      if( $append === false )
        $this->headers = $value;
      else
        $this->headers .= $value;
    }
  }
  
  /**
   * set your server URL and port
   * @param string $server name or IP of the e-mail server
   * @param int $port server port
   */
  function set_server( $server, $port )
  {
    $this->server = $server;
    $this->port = $port;
  }
  
  /**
   * specify the crypto type for the connection. defaults to STARTTLS
   * @param string $type=starttls can be [none],[tls],[starttls],[ssl]
   */
  function set_crypto( $type='starttls' )
  {
    $type = strtolower($type);
    switch ($type)
    {
      case 'none':
        $this->crypto = '';
        break;
      case 'tls':
        $this->crypto = 'tls';
        break;
      case 'ssl':
        $this->crypto = 'ssl';
        break;        
      default: //hopefully a good idea to default to starttls as default
        $this->crypto = 'starttls';
        break;
    }
  }
  
  /**
   * set the username and password for the smtp account
   * @param string $username username to authenticate with
   * @param string $password the password for the user in plain text
   */
  function set_auth( $username, $password )
  {
    $this->user = $username;
    $this->pw = $password;
  }
  
  /**
   * specify sender information
   * @param string $name Name of the person sending the e-mail
   * @param string $email='user@localhost.local' e-mail address where the message originates.
   */
  function set_sender( $name, $email )
  {
    if( $email == '' ) $email = 'user@localhost.local';
    if( $name == '' ) $name = $email;
    $this->sender_email = $email;
    $this->sender_name = '=?UTF-8?B?'.base64_encode($name).'?=';
  }
  
  /**
   * specify which e-mail function to use
   * @param int $type 0 = PHP mail() function, 1 = SMTP function of this class
   */
  function set_type( $type=1 )
  {
    $this->type = ($type === 0) ? 0 : 1;
  }
  
  /**
   * html or plain text message for the default header?
   * *notice* run set_headers(null) after setting this
   * @param string $type='text/plain' text/html or text/plain
  */
  function set_content_type( $type='text/plain' )
  {
    if( $type === 'text/html' )
      $this->content_type = 'text/html';
    else
      $this->content_type = 'text/plain';
  }
  
  /**
   * specify the rdns hostname of the computer running this script
   * this may be required by some smtp servers.
   * @param string $hn rnds hostname as string
   */
  function set_hostname( $hn )
  {
    $this->hostname = $hn;
  }

  /**
   * this function prepares the subject and passes the e-mail
   * to the correct mail function.
   * @param string $to send to e-mail address in form user@domain.com
   * @param string $subject subject as one line of text (no line breaks). will be base64 encoded
   * @param string $body the body of your e-mail. will be base64 encoded
   * @param string $headers=null any custom headers or null to use the default
   * @return bool true/false true on success and false on failure
   */ 
  function mail($to=null, $subject=null, $body=null, $headers=null)
  {
    if( $to == null || $subject == null || $body == null ) return false;	//Do not send empty mails
    $this->srv_ret = array( 'last' => '', 'all' => "\n", 'full' => "\n"); //Always start with a fresh return array
    
    //base64 encode the subject and sender
    $subject = '=?UTF-8?B?'.base64_encode($subject).'?=';
    $body = base64_encode($body); //no need for =?UTF... here

    
    //set header if it is null
    if( $headers === null )
      $headers =& $this->headers;
    
    if($this->type === 0 ) 
      return $this->php_mail($to, $subject, $body, $headers);
    else
      return $this->smtp_mail($to, $subject, $body, $headers);
  }

  /**
   * this one takes care of sending the message through the internal PHP mail()
   * function.
   * @param string &$to To: e-mail address
   * @param string &$subject the subject of the e-mail
   * @param string &$body the main text of the message
   * @param string &$headers=null the e-mail headers
   * @return bool true/false check get_server_returns() on error
   */
  private function php_mail(&$to, &$subject, &$body, &$headers)
  {
    $tmpheader = 'From:'.$this->sender_email."\r\n"
                .'Reply-To:'.$this->sender_email. "\r\n"
                .$headers;

    if( is_array($to) !== true ){
      //get next recipient from the array and store in send_to
      $send_to = array($to); //turn $to into an array for foreach
    }else $send_to =& $to;

    foreach( $send_to as $sto )
    {
      if( @mail($sto, $subject, $body, $tmpheader) == True ){
        $this->srv_ret['last'] = "notice: e-mail for $sto has been delivered using the PHP mail() function.\r\n";
        $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
        $this->srv_ret['full'] .= $this->srv_ret['last']."\r\n";
        return true;
      }else {
        $err = error_get_last();
        $this->srv_ret['last'] = "ERROR: The E-mail was not sent using the PHP mail() function, please check your configuration\r\n".$err['message'];
        $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
        $this->srv_ret['full'] .= $this->srv_ret['last']."\r\n";
        return false;
      }
    }
  }
  
  /**
   * will reorder an array by placing $first_string as key [0] then the rest but 
   * skipping any $value == $first_string
   * @param array &$source_array array to work with
   * @param string $first_string string to be placed at position zero
   */
  private function reorder_array( &$source_array, $first_string )
  {
    $ret = array($first_string);
    
    foreach( $source_array as $val )
    {
      if( $val != $first_string )
        $ret[] = $val;
    }
    $source_array = $ret;
  }
  
  /**
   * this connection functions can try different connection methods before failing
   * @return type 
   */
  private function smtp_connect()
  {
    if( $this->smtp_try === true )
    {
      $order = array( 'starttls', 'tls', 'ssl' ); //default order, DO NOT DEFAULT TO NO ENCRYPTION! (none)
      //determine the configured connection order and store it in $order as an array
      switch($this->crypto){
        case 'starttls':
          $this->reorder_array( $order, 'starttls');
          break;
        case 'tls':
          $this->reorder_array( $order, 'tls');
          break;
        case 'ssl':
          $this->reorder_array( $order, 'ssl');
          break;
        case 'none': //only allow none if it was configured!
          $this->reorder_array( $order, 'none');
          break;
      }
    }else{
      $order = array($this->crypto); //only one connection attempt
    }

    //attempt to establish a connection
    foreach( $order as $crypto_type )
    {
      switch($crypto_type){
        case 'starttls':
          $server_type = '';
          $this->set_crypto('starttls'); //this is crucial or STARTTLS will not be sent on a second attempt
          $this->srv_ret['full'] .= 'notice: will attempt to switch to a tls secured connection after EHLO';
          break;
        case 'tls':
          $server_type = 'tls://';
          $this->set_crypto('tls');
          $this->srv_ret['full'] .= 'notice: will create a tls secured connection to your server\r\n';
          break;
        case 'ssl':
          $server_type = 'ssl://';
          $this->set_crypto('ssl');
          $this->srv_ret['full'] .= 'notice: will create a SSL secured connection to your server\r\n';
          break;
        case 'none':
          $server_type = '';
          $this->set_crypto('none');
          $this->srv_ret['full'] .= 'WARNING: your connection will NOT be encrypted! Please consider a different configuration!\r\n';
          break;
        default:
          $this->srv_ret['full'] .= 'ERROR: Invalid crypto type: '.$crypto_type.'\r\n';
          return false;
      }

      if( !($socket = @fsockopen($server_type.$this->server, $this->port , $errno, $errstr, 5)) )
      {
        if( $errno == 10060 ) {
          $this->srv_ret['last'] = 'ERROR: Unable to connect to SMTP server '.$server_type.$this->server.'. Please check the URL and the port setting. ('.$errstr.')';
          $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
          $this->srv_ret['full'] .= $this->srv_ret['last']."\r\n";
        }else{
          $this->srv_ret['last'] = 'ERROR: Unable to connect to SMTP server '.$server_type.$this->server.' ('.$errstr.')';
          $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
          $this->srv_ret['full'] .= $this->srv_ret['last']."\r\n";
        }
        if( $this->smtp_try === false ) return false;
      }else{    
        $ret = $this->server_parse($socket);//, '220');
        $this->srv_ret['last'] = trim($ret);
        $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
        $this->srv_ret['full'] .= "notice: connected to server\r\nrecieved: ".$this->srv_ret['last']."\r\n";
        if( $this->expected_return($ret, '220') !== true ) {return false;} //Stop the operation if the server does not respond as expected        
        return $socket;
      }
      $server_type = '';
      unset($socket);

    }//foreach( $order as $crypto_type )
  $this->srv_ret['last'] = 'ERROR: All connection attempts failed! Please check your configuration. Aborting\r\n';
  $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
  $this->srv_ret['full'] .= $this->srv_ret['last']."\r\n";
  return false;
  }
  
  /**
   * handles user authentication
   * @param resource &$socket socket for server connection
   * @return bool true/false false if there was an error authenticating 
   */
  private function smtp_auth(&$socket)
  {
    if ($this->user != '' && $this->pw != '' )
    {
      if( strstr($this->server_ehlo['auth'], 'PLAIN') ) { //PLAIN
        fwrite($socket, 'AUTH PLAIN '.base64_encode("\0".$this->user."\0".$this->pw)."\r\n");
        $ret = $this->server_parse($socket);
        $this->srv_ret['last'] = trim($ret);
        $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
        $this->srv_ret['full'] .= "sent: AUTH PLAIN ".base64_encode("\0".$this->user."\0".$this->pw)."\r\nreceived: ".$this->srv_ret['last']."\r\n";
        if( $this->expected_return($ret, '334') !== true && $this->expected_return($ret, '235') !== true ) {return false;} //Stop the operation if the server does not respond as expected
      }elseif( strstr($this->server_ehlo['auth'], 'CRAM-MD5') ) { //CRAM-MD5
        fwrite($socket, 'AUTH CRAM-MD5'."\r\n");
        $ret = $this->server_parse($socket);
        $this->srv_ret['last'] = trim($ret);
        $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
        $this->srv_ret['full'] .= "sent: AUTH CRAM-MD5\nreceived: ".$this->srv_ret['last']."\r\n";
        if( $this->expected_return($ret, '334') !== true && $this->expected_return($ret, '235') !== true ) {return false;} //Stop the operation if the server does not respond as expected
        $ret = explode(' ', $ret);
        $key = base64_decode($ret[1]);

        $send = $this->user." ".$this->_hmacMd5($this->pw, $key);
        fwrite($socket, base64_encode($send)."\r\n");
        $ret = $this->server_parse($socket);
        $this->srv_ret['last'] = trim($ret);
        $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
        $this->srv_ret['full'] .= "sent base64 string:".base64_encode($send)."\nreceived: ".$this->srv_ret['last']."\r\n";
        if( $this->expected_return($ret, '235') !== true ) {return false;} //Stop the operation if the server does not respond as expected
        unset($send);
     }elseif( strstr($this->server_ehlo['auth'], 'LOGIN') ){ //LOGIN
        fwrite($socket, 'AUTH LOGIN'."\r\n");
        $ret = $this->server_parse($socket);
        $this->srv_ret['last'] = trim($ret);
        $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
        $this->srv_ret['full'] .= "sent: AUTH LOGIN\nreceived: ".$this->srv_ret['last']."\r\n";
        if( $this->expected_return($ret, '334') !== true ) {return false;} //Stop the operation if the server does not respond as expected
        fwrite($socket, base64_encode($this->user)."\r\n");
        $ret = $this->server_parse($socket);
        $this->srv_ret['last'] = trim($ret);
        $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
        $this->srv_ret['full'] .= "sent base64 username:".base64_encode($this->user)."\nreceived: ".$this->srv_ret['last']."\r\n";
        if( $this->expected_return($ret, '334') !== true ) {return false;} //Stop the operation if the server does not respond as expected

        fwrite($socket, base64_encode($this->pw)."\r\n");
        $ret = $this->server_parse($socket);
        $this->srv_ret['last'] = trim($ret);
        $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
        $this->srv_ret['full'] .= "sent base64 password:".base64_encode($this->pw)."\nreceived: ".$this->srv_ret['last']."\r\n";
        if( $this->expected_return($ret, '334') !== true && $this->expected_return($ret, '235') !== true ) {return false;} //Stop the operation if the server does not respond as expected
     }else{
       $this->srv_ret['full'] .= "ERROR: class_email.php is unable to find a supported authentication method. Please contact support!\r\nreceived: ".$this->srv_ret['last']."\r\n";
       return false;
     } 
    }else{
      $this->srv_ret['last'] = "WARNING: You did not specify a username or password. This notice is here to remind you that this is most likely as mistake and may cause errors when sending your message.\r\n";
      $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
      $this->srv_ret['full'] .= $this->srv_ret['last']."\r\n";
    }
    return true;
  }
  
  /**
   * connects, talks to the smtp server and sends the e-mail
   * @param string &$to To: e-mail address
   * @param string &$subject the subject of the e-mail
   * @param string &$body the main text of the message
   * @param string &$headers the e-mail headers
   * @return bool true/false check get_server_returns() on error
   */
  private function smtp_mail(&$to, &$subject, &$body, &$headers)
  {
    $smtp_ret='';
    $server_type = '';

    //create a connection to the smtp server
    $socket = $this->smtp_connect();
    if( $socket === false ) return false;
    
    $hn = ( strlen($this->hostname) > 0 ) ? $this->hostname : 'localhost';
    fwrite($socket, 'EHLO '.$hn."\r\n"); $ret = '';
    $ret = $this->server_parse($socket);
    $this->srv_ret['last'] = trim($ret);
    $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
    $this->srv_ret['full'] .= "sent: EHLO $hn\r\nreceived: ".$this->srv_ret['last']."\r\n";
    if( $this->expected_return($ret, '250') !== true ) {return false;} //Stop the operation if the server does not respond as expected
    $this->parse_ehlo($ret);

    //send starttls if the server supports it and if it has not been disabled
    if( $this->crypto === 'starttls' )
    {
      if( $this->server_ehlo['starttls'] === true )
      {
        fwrite($socket, "STARTTLS\r\n");    $ret = '';
        $ret = $this->server_parse($socket);
        $this->srv_ret['last'] = trim($ret);
        $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
        $this->srv_ret['full'] .= "sent: STARTTLS\nreceived: ".$this->srv_ret['last']."\r\n";
        if( $this->expected_return($ret, '220') !== true ) {return false;} //Stop the operation if the server does not respond as expected
        stream_set_blocking ($socket, true);
        if( !@stream_socket_enable_crypto ($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) ) return false;
        stream_set_blocking ($socket, false);
        $this->srv_ret['full'] .= "notice: tls is now enabled\r\n";
        
        //send EHLO again since some servers will not send all info in plain text (gmail.com)
        fwrite($socket, 'EHLO '.$hn."\r\n"); $ret = '';
        $ret = $this->server_parse($socket);
        $this->srv_ret['last'] = trim($ret);
        $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
        $this->srv_ret['full'] .= "notice: sending EHLO again but this time encrypted\r\n";
        $this->srv_ret['full'] .= "sent: EHLO $hn\r\nreceived: ".$this->srv_ret['last']."\r\n";
        if( $this->expected_return($ret, '250') !== true ) {return false;} //Stop the operation if the server does not respond as expected
        $this->parse_ehlo($ret);
      }else
        $this->srv_ret['full'] .= "WARNING: server does not appear to support STARTTLS\r\n";
    }
    
    //authenticate with the server
    if( $this->smtp_auth($socket) === false ) return false;

	//now send the e-mail, process multiple recipients
	while(true)
	{
    if( is_array($to) === true ){
      //get next recipient from the array and store in send_to
      $send_to = array_shift($to);
    }else $send_to =& $to;

    fwrite($socket, 'MAIL FROM:<'.$this->sender_email.'>'."\r\n");
    $ret = $this->server_parse($socket);
    $this->srv_ret['last'] = trim($ret);
    $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
    $this->srv_ret['full'] .= "sent: MAIL FROM:<$this->sender_email>\r\nreceived: ".$this->srv_ret['last']."\r\n";
    if( $this->expected_return($ret, '250') !== true ) {return false;} //Stop the operation if the server does not respond as expected

    fwrite($socket, 'RCPT TO:<'.$send_to.'>'."\r\n");
    $ret = $this->server_parse($socket);
    $this->srv_ret['last'] = trim($ret);
    $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
    $this->srv_ret['full'] .= "sent: RCPT TO:<$send_to>\r\nreceived: ".$this->srv_ret['last']."\r\n";
    if( $this->expected_return($ret, '250') !== true ) {return false;} //Stop the operation if the server does not respond as expected

    fwrite($socket, 'DATA'."\r\n");
    $ret = $this->server_parse($socket);
    $this->srv_ret['last'] = trim($ret);
    $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
    $this->srv_ret['full'] .= "sent: DATA\r\nreceived: ".$this->srv_ret['last']."\r\n";
    if( $this->expected_return($ret, '354') !== true ) {return false;} //Stop the operation if the server does not respond as expected

    fwrite($socket, "FROM:$this->sender_name <$this->sender_email>\r\nSubject: $subject\r\nTO: $send_to\r\n$headers\r\n\r\n$body\r\n");
    fwrite($socket, ".\r\n"); //end the transmission of the email
    $ret = $this->server_parse($socket);
    $this->srv_ret['last'] = trim($ret);
    $this->srv_ret['all'] .= $this->srv_ret['last']."\r\n";
    $this->srv_ret['full'] .= "sent: FROM:$this->sender_name <$this->sender_email>\r\nSubject: $subject\r\nTO: $send_to\r\n$headers\r\n\r\n$body\r\n\nreceived: ".$this->srv_ret['last']."\r\n";
    if( $this->expected_return($ret, '250') !== true ) {return false;} //Stop the operation if the server does not respond as expected

    if( is_array($to) !== true || count($to) === 0 ) break; //not an array or all done. who cares, just get out!
	}
	
    //should improve the speed sending the buggers
    fwrite($socket, 'QUIT'."\r\n");
    $this->srv_ret['full'] .= "sent: QUIT\r\n";
    fclose($socket);

    $this->srv_ret['full'] .= "notice: connection closed, return true\r\n";
    return true;
  }//function smtp_mail($to, $subject, $body, $headers)

  /**
   * parse server responds and store in $this->server_ehlo
   * @param string &$string return string to process
   */
  private function parse_ehlo( &$string )
  {
    //set defaults
    $this->server_ehlo['hostname'] = '';
    $this->server_ehlo['starttls'] = false;
    $this->server_ehlo['auth'] = '';
    $this->server_ehlo['size'] = 0;
    //set defaults
    
    $ar = $this->eol($string);
    $ar = explode("\r\n", $ar);
    
    //first name should contain the server name between - and the first space
    $tmp = explode(' ', $ar[0]);
    $this->server_ehlo['hostname'] = trim(substr($tmp[0], 4));
    
    //check the rest for keywords
    foreach( $ar as $val )
    {
      if(strstr($val, '250-STARTTLS') !== false)
        $this->server_ehlo['starttls'] = true;
      
      if(strstr($val, '250-AUTH') !== false)
        $this->server_ehlo['auth'] = trim(substr($val, 8));
      
      if(strstr($val, '250-SIZE') !== false)
        $this->server_ehlo['size'] = trim(substr($val, 8));
    }
  }
  
  /**
   * fix line endings, make all \n
   * @param string $string With mixed line endings
   * @return string with all line endings replaced to \n
   */
  private function eol($string) {
    $string = str_replace("\r\n", "\r", $string);
    $string = str_replace("\r", "\n", $string);
    return $string;
  }
  
  /**
   * checks the first three characters fo the passed string for the expected result
   * @param string $string return message from SMTP server
   * @param str/int $val expected value of the return code
   * @return bool true/false true if the value is the return code, false if not 
   */
  private function expected_return($string, $val)
  {
    $this->srv_ret['full'] .= "notice: checking expected:$val against response:".substr($string, 0, 3);
    if( substr($string, 0, 3) == $val ){
	  $this->srv_ret['full'] .= " -- PASSED!\r\n";
      return true;
    }else{
	  $this->srv_ret['full'] .= " -- FAILED!\r\n";
      return false;
	}
  }

  /**
  * Prepare CRAM-MD5 response to server's ticket
  * Copyright (c) 2005-2010, Zend Technologies USA, Inc.
  * source: Zend framework crammd5.php
  *
  * @param  string $key   Challenge key (usually password)
  * @param  string $data  Challenge data
  * @param  string $block Length of blocks
  * @return string
  */
  private function _hmacMd5($key, $data, $block = 64)
  {
    if (strlen($key) > 64) {
      $key = pack('H32', md5($key));
    } elseif (strlen($key) < 64) {
      $key = str_pad($key, $block, chr(0));
    }

    $k_ipad = substr($key, 0, 64) ^ str_repeat(chr(0x36), 64);
    $k_opad = substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64);

    $inner = pack('H32', md5($k_ipad . $data));
    $digest = md5($k_opad . $inner);

    return $digest;
  }
  
  /**
  * this is here to parse the data the server sends us
  * I am running a dalayed loop to get all the lines returned
  * without maxing the CPU. 
  * You can set SLEEP_SOCKET below 100 if the communication is really fast 
  * @param  socket &$socket the socket of the connection
  * @return string/bool returns the information sent by the server or boolean false if the endless loop protection kicked in
  */
  private function server_parse(&$socket)
  {
    $server_response = '';
    $ret = '';
    $x=0;
    while( substr($server_response, 3, 1) != ' ' )
    {
      try {
		  if( ($server_response = fgets($socket, 4096)) != false) {
      	  	$ret .= $server_response;
		  } else {
			$server_response = '';
			$ret = '';
		  }
	  } catch (Exception $ex) {
		  $ret .= $ex;
	  }
      
      //usleep is here to reduce CPU load while the script is waiting for a response.
      usleep(self::SLEEP_SOCKET); //slow down or it will max the CPU
      ++$x;
      if( $x === 10000 ) {
        $this->srv_ret['full'] .= "ERROR: The script timed out while waiting for the smtp server to respond. One of the servers may be having connection difficulties or is overloaded ... try again later.\r\n";
        return false;
      }
    }
    return $ret;
  }
}
?>
