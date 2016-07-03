<?php

error_reporting(E_ALL & ~E_NOTICE);
ignore_user_abort(true);
//set_time_limit(0); // not allowed on zymic servers

/*include $_SERVER['DOCUMENT_ROOT'].'phpsyndicate/lib/mail/socks/sock2.php';
include $_SERVER['DOCUMENT_ROOT'].'phpsyndicate/lib/mail/smtp/smtp.php';
include $_SERVER['DOCUMENT_ROOT'].'phpsyndicate/config/header2.php';
require_once $_SERVER['DOCUMENT_ROOT'].'phpsyndicate/lib/mail/smtp/class_email.php';*/

include 'socks/sock2.php';
include 'smtp/smtp.php';
include '../../config/header2.php';
include 'smtp/class_email.php';

class MailMessage {
	
	var $from;
	var $to;
	var $subject;
	var $body;
	var $footer;
	var $protocol;
	var $delay;
	var $smtp_server;
	var $smtp_port;
	var $smtp_user;
	var $smtp_pass;
	var $smtpchat;
	var $error;
	
	function MailMessage($from,$to,$subject,$body,$footer,$protocol,$delay) {
		$this->from     = $from;
		$this->subject  = $subject;
		$this->to       = $to;
		$this->body     = $body;
		$this->footer   = $footer;
		$this->protocol = $protocol;
		$this->delay    = $delay;
   	}

	function MailMessage_smtp($from,$to,$subject,$body,$footer,$protocol,$delay,$smtp_server,$smtp_port,$smtp_user,$smtp_pass) {
		
		$this->from        = $from;
		$this->subject     = $subject;
		$this->to          = $to;
		$this->body        = $body;
		$this->footer      = $footer;
		$this->protocol    = $protocol;
		$this->delay       = $delay;
		$this->smtp_pass   = $smtp_pass;
		$this->smtp_user   = $smtp_user;
		$this->smtp_port   = $smtp_port;
		$this->smtp_server = $smtp_server;
		$this->error       = "NOERROR";
   	}

	function MailMessage_sock($from,$to,$subject,$body,$footer,$protocol,$delay) {
		$this->from     = $from;
		$this->subject  = $subject;
		$this->to       = $to;
		$this->body     = $body;
		$this->footer   = $footer;
		$this->protocol = $protocol;
		$this->delay    = $delay;
   	}

	function __destruct() {
	}
	
	function checkEmail($email) {
		if (preg_match('^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.[a-z]{2,5}$^', $email)) {
			return true;
        } else {
            return false;
        }
    }
	
	private function logError($args,$exception) {
  	$this->error = $exception->getMessage();

  	$filename = $_SERVER['DOCUMENT_ROOT'].'phpsyndicate/log/log.txt';

  	if (!$handle = fopen($filename, 'a+')) {
		$dispdate = date('l jS \of F Y');
		$extime   = date('h:i:s A');
		if(!connection_aborted()) {
			echo "\n[ ".$dispdate." | ".$extime."] - (INFO) Cannot open file ($filename)";
		} else {
			// TODO: output to DB
		}
		exit;
    }
    fwrite($handle,date("l dS of F Y h:i:s A"));
    fwrite($handle,"\n");
    if (is_array($args)) foreach ($args as $arguments) fwrite($handle,"argument: $arguments\n");
	fwrite($handle,"error: ".$exception->getMessage()."\n");
	fclose($handle);
  }
	
	function sendMail() {
		$res = true;
		
		if($this->protocol == 1) {
			
			$SmtpServer=$this->smtp_server;
			$SmtpPort=$this->smtp_port;
			$SmtpUser=$this->smtp_user;
			$SmtpPass=$this->smtp_pass;
			$from=$this->from;
			$to=$this->to;
			$subject=$this->subject;
			$body=$this->body;
			
			$e = new email();

			$e->SLEEP_SOCKET = $this->delay;
			//First value is the URL of your server, the second the port number
			$e->set_server( $SmtpServer, $SmtpPort);
			//First value is your username, then your password
			$e->set_auth($SmtpUser, $SmtpPass);
			//Set the "From" setting for your e-mail. The Name will be base64 encoded
			$e->set_sender( $from, $from );

			//for one recipient
			$send_to = $to;
			//you may also specify multiple recipients by creating an array like this:
			//$send_to = array('vpapakir@yahoo.gr', 'papakiru@cti.gr');

			if( $e->mail($send_to, $subject, $body) == true )
			{
				$res = true;
				//message was received by the smtp server
				//['last'] tends to contain the queue id so I like to save that string in the database
				$dispdate = date('l jS \of F Y');
				$extime   = date('h:i:s A');
				if(!connection_aborted()) {
					echo "\n[ ".$dispdate." | ".$extime."] - (INFO) ".htmlspecialchars($e->srv_ret['last']).'';
				} else {
					// TODO: output to DB
				}
				$res = true;
			}else{
				if($e->type == 0) {
					$e->type = 1;
					if( $e->mail($send_to, $subject, $body) == true )
					{
						$res = true;
						//message was received by the smtp server
						//['last'] tends to contain the queue id so I like to save that string in the database
						$dispdate = date('l jS \of F Y');
						$extime   = date('h:i:s A');
						if(!connection_aborted()) {
							echo "\n[ ".$dispdate." | ".$extime."] - (INFO) ".htmlspecialchars($e->srv_ret['last']).'';
						} else {
							// TODO: output to DB
						}
						$res = true;
					} else {
					  //something went wrong
						if(!connection_aborted()) {
					  		echo '(ERROR) '.htmlspecialchars($e->srv_ret['all']).'';
					  		echo '(ERROR) '.htmlspecialchars($e->srv_ret['full']).'';
						} else {
							// TODO: output to DB
						}
					}
					if(!connection_aborted()) {
						echo $e->type;
					} else {
						// TODO: output to DB
					}
				} else {
					$e->type = 0;
					if( $e->mail($send_to, $subject, $body) == true )
					{
						$res = true;
						//message was received by the smtp server
						//['last'] tends to contain the queue id so I like to save that string in the database
						if(!connection_aborted()) {
							echo 'last: '.htmlspecialchars($e->srv_ret['last']).'';
						} else {
							// TODO: output to DB
						}
						$res = true;
					}else{
					  //something went wrong
						if(!connection_aborted()) {
							echo 'all: '.htmlspecialchars($e->srv_ret['all']).'';
							echo 'full:'.htmlspecialchars($e->srv_ret['full']).'';
						} else {
							// TODO: output to DB
						}
					}
					if(!connection_aborted()) {
						echo $e->type;
					} else {
						// TODO: output to DB
					}
				}
			  /*if($e->type == 0) {
				$e->type = 1;
			  	if( $e->mail($send_to, $subject, $body) == true )
				{
					$res = true;
					//message was received by the smtp server
					//['last'] tends to contain the queue id so I like to save that string in the database
					echo 'last: '.htmlspecialchars($e->srv_ret['last']).'';
					$res = true;
				}else{
				  //something went wrong
				  echo 'all: '.htmlspecialchars($e->srv_ret['all']).'';
				  echo 'full:'.htmlspecialchars($e->srv_ret['full']).'';
				}
			  } else {
				$e->type = 0;
			  	if( $e->mail($send_to, $subject, $body) == true )
				{
					$res = true;
					//message was received by the smtp server
					//['last'] tends to contain the queue id so I like to save that string in the database
					echo 'last: '.htmlspecialchars($e->srv_ret['last']).'';
					$res = true;
				}else{
				  //something went wrong
				  echo 'all: '.htmlspecialchars($e->srv_ret['all']).'';
				  echo 'full:'.htmlspecialchars($e->srv_ret['full']).'';
				}
			  }*/
			  //echo 'all: '.htmlspecialchars($e->srv_ret['all']).'';
			  //echo 'full:'.htmlspecialchars($e->srv_ret['full']).'';
			}
			
			/*$SMTPMail = new smtp($SmtpServer, $SmtpPort, $SmtpUser, $SmtpPass, $from, $to, $subject, $body);
			
			$SMTPChat = $SMTPMail->SendMail();
			
			if($ENABLE_LOGGING==1) {
				echo "<h1>Talking with the SMTP Server</h1>";
				echo "<p>The server response:</p>";
				echo $SMTPChat["hello"]."<br />";
				echo $SMTPChat["res"]."<br />";
				echo $SMTPChat["user"]."<br />";
				echo $SMTPChat["pass"]."<br />";
				echo $SMTPChat["From"]."<br />";
				echo $SMTPChat["To"]."<br />";
				echo $SMTPChat["data"]."<br />";
				echo $SMTPChat["send"]."<br />";
			}
			
			$smtp = new SMTPMAIL($SmtpServer,$SmtpPort,$SmtpUser,$SmtpPass,$this->footer);
			
			$cc="";
			
			if(!$smtp->send_smtp_mail($to,$subject,$body,$cc,$from)) {
				echo "Error in sending mail!<BR>Error: ".$smtp->error;
				$res = false;
			}
			else {
				echo "Mail sent succesfully!";
				
			}*/
		}
		
		if($this->protocol == 0) {
			// TODO: Send mail through SOKCS
		}
		return $res;
	}
	
}

?>
