<?php

/*
smtp PHP Class
Copyright (C) 2010 Evangelos Papakirikou.
-----------------------------------------------------------------------------
Send a message via SMTP with optional LOGIN authorization. 
*/
error_reporting(E_ALL & ~E_NOTICE);
ignore_user_abort(true);
//set_time_limit(0); // not allowed on zymic servers

class smtp {
    var $errors;
    var $headers;
    var $data;
    var $host;
    var $state;
    var $recip;
    var $from;
    var $user;
	var $body;
    var $pass;
	var $SmtpServer;
	var $SmtpUser;
	
	function smtp ($SmtpServer, $SmtpPort, $SmtpUser, $SmtpPass, $from, $to, $subject, $body)
	{
		$this->SmtpServer = $SmtpServer;
		$this->SmtpUser = base64_encode ($SmtpUser);
		$this->pass = base64_encode ($SmtpPass);
		$this->from = $from;
		$this->recip = $to;
		$this->headers = $subject;
		$this->body = $body;
		
		if ($SmtpPort == "") 
		{
			$this->PortSMTP = 25;
		}else{
			$this->PortSMTP = $SmtpPort;
		}
	}
	
	function SendMail ()
	{
		if ($SMTPIN = fsockopen ($this->SmtpServer, $this->PortSMTP)) 
		{
           fputs ($SMTPIN, "EHLO ".$HTTP_HOST."\r\n");  
           $talk["hello"] = fgets ( $SMTPIN, 1024 ); 
		   fputs($SMTPIN, "auth login\r\n");
		   $talk["res"]=fgets($SMTPIN,1024);
		   fputs($SMTPIN, $this->SmtpUser."\r\n");
		   $talk["user"]=fgets($SMTPIN,1024);
		   fputs($SMTPIN, $this->SmtpPass."\r\n");
		   $talk["pass"]=fgets($SMTPIN,256);
		   fputs ($SMTPIN, "MAIL FROM: <".$this->from.">\r\n");  
           $talk["From"] = fgets ( $SMTPIN, 1024 );  
           fputs ($SMTPIN, "RCPT TO: <".$this->to.">\r\n");  
           $talk["To"] = fgets ($SMTPIN, 1024); 
           fputs($SMTPIN, "DATA\r\n");
		   $talk["data"]=fgets( $SMTPIN,1024 );
		   fputs($SMTPIN, "To: <".$this->to.">\r\nFrom: <".$this->from.">\r\nSubject:".$this->subject."\r\n\r\n\r\n".$this->body."\r\n.\r\n");
		   $talk["send"]=fgets($SMTPIN,256);
           fputs ($SMTPIN, "QUIT\r\n");  
           fclose($SMTPIN); 
		 } 
		 
		 echo $talk;
		 return $talk;
	}
}

class SMTPMAIL
	{
		var $host="";
		var $port=25;
		var $error;
		var $state;
		var $con=null;
		var $greets="";
		var $line = "";
		
		function SMTPMAIL($SmtpServer,$SmtpPort,$SmtpUser,$SmtpPass,$footer)
		{
			$this->host=$SmtpServer;
			$this->port=$SmtpPort;
			$this->state="DISCONNECTED";
		}
		function set_host($host)
		{
			$this->host=$host;
		}
		function set_port($port=25)
		{
			$this->port=$port;
		}
		function error()
		{
			return $this->error;
		}
		function connect($host="",$port=25)
		{
			if(!empty($host))
				$this->host($host);
			$this->port=$port;
			if($this->state!="DISCONNECTED")
			{
				$this->error="Error : connection already open.";
				return false;
			}
			
			$this->con=@fsockopen($this->host,$this->port,$errno,$errstr);
			if(!$this->con)
			{
				$this->error="Error($errno):$errstr";
				return false;
			}
			$this->state="CONNECTED";
			$this->greets=$this->get_line();
			return true;
		}
		function send_smtp_mail($to,$subject,$data,$cc="",$from='HARISH')
		{
			$ret=$this->connect();
			if(!$ret)
				return $ret;
			$this->put_line("MAIL FROM: $from");
			$response=$this->get_line();
			if(intval(strtok($response," "))!=250)
			{
				$this->error=strtok($response,"\r\n");
				return false;
			}
			$to_err=preg_split("/[,;]/",$to);
			foreach($to_err as $mailto)
			{
				$this->put_line("RCPT TO: $mailto");
				$response=$this->get_line();
				if(intval(strtok($response," "))!=250)
				{
					$this->error=strtok($response,"\r\n");
					return false;
				}
			}
			if(!empty($cc))
			{
				$to_err=preg_split("/[,;]/",$cc);
				foreach($to_err as $mailto)
				{
					$this->put_line("RCPT TO: $mailto");
					$response=$this->get_line();
					if(intval(strtok($response," "))!=250)
					{
						$this->error=strtok($response,"\r\n");
						return false;
					}
				}
			}
			$this->put_line("DATA");
			$response=$this->get_line();
			if(intval(strtok($response," "))!=354)
			{
				$this->error=strtok($response,"\r\n");
				return false;
			}
			$this->put_line("TO: $to");
			$this->put_line("SUBJECT: $subject");
			$this->put_line($data);
			$this->put_line(".");
			$response=$this->get_line();
			if(intval(strtok($response," "))!=250)
			{
				$this->error=strtok($response,"\r\n");
				return false;
			}
			$this->close();
			return true;
		}
		// This function is used to get response line from server
		function get_line()
		{
			while(!feof($this->con))
			{
				$this->line.=fgets($this->con);
				if(strlen($this->line)>=2 && substr($this->line,-2)=="\r\n")
					return(substr($this->line,0,-2));
			}
		}
		////This functiuon is to retrive the full response message from server

		////This functiuon is to send the command to server
		function put_line($msg="")
		{
			return @fputs($this->con,"$msg\r\n");
		}
		
		function close()		
		{
			@fclose($this->con);
			$this->con=null;
			$this->state="DISCONNECTED";
		}
	}

?>
