<?php 

class phpmm {
             var $use;
             var $whom;
             var $list;
             var $subj;
             var $mess;
             var $from;
             var $mailer;
             var $files;
             var $kod;
             var $number;
             var $send;
             var $host;
             var $db;
             var $user;
             var $pass;
             var $query;
             var $query_result;
             var $mysql_link;
             var $link_pg;
             var $link_ib;
             var $msql_link;
             var $fbsql_link;
             var $sqli_link;
             var $oci_con;
             var $sybase_link;
             var $link_ingres;
             var $dbfbasa;
             var $id;
             var $to;
             var $hostSmtp;
             var $portSmtp;
             var $timeoutSmtp;
             var $smtpServer;
             var $userSmtp;
             var $passSmtp;
             var $smtp;
             var $authenticate;
             var $CRLF = "\r\n";
             var $DOUBLE_LF = "\n\n";
             var $back;

              function phpmm($data) {
                      $this->dbfbasa = $data['dbasa'];
                      $this->host    = $data['host'];
                      $this->db      = $data['db'];
                      $this->user    = $data['user'];
                      $this->pass    = $data['pass'];
                      $this->query   = $data['query'];
                      $this->selectDataBase($this->query, $this->dbfbasa);
              }

              function selectDataBase($query, $dbfbasa){

                      switch($dbfbasa){

                               case 'pgsql':
                                    if(extension_loaded('pgsql')){
                                            $this->connect_pg($this->host, $this->db, $this->user, $this->pass);
                                            $this->query_pg($query);
                                    } else {
                                            print(nl2br("Functions pgsql are absent. \n"));
                                    }
                               break;

                               case 'mysql':
                                    if(extension_loaded('mysql')){
                                            $this->connect_mysql($this->host, $this->user, $this->pass, $this->db);
                                            $this->query_mysql($query);
                                    } else {
                                            print(nl2br("Functions mysql are absent. \n"));
                                    }
                               break;

                               case 'ibase':
                                    if(extension_loaded('interbase')){
                                            $this->connect_ib($this->host, $this->user, $this->pass);
                                            $this->query_ib($query);
                                    } else {
                                            print(nl2br("Functions ibase are absent. \n"));
                                    }
                               break;

                               case 'msql':
                                    if(extension_loaded('msql')){
                                            $this->connect_msql($this->host, $this->user, $this->pass, $this->db);
                                            $this->query_msql($query);
                                    } else {
                                            print(nl2br("Functions msql are absent. \n"));
                                    }
                               break;

                               case 'fbsql':
                                    if(extension_loaded('fbsql')){
                                            $this->connect_fbql($this->host, $this->user, $this->pass, $this->db);
                                            $this->query_fbsql($this->query);
                                    } else {
                                            print(nl2br("Functions fbsql are absent. \n"));
                                    }
                               break;

                               case 'sqli':
                                    if(extension_loaded('sqli')){
                                            $this->connect_sqli($this->db);
                                            $this->query_sqli($query);
                                    } else {
                                            print(nl2br("Functions msql are absent. \n"));
                                    }
                               break;

                               case 'oci':
                                    if(extension_loaded('oracle')){
                                            $this->connect_oci($this->user, $this->pass, $this->db);
                                            $this->query_oci($query);
                                    } else {
                                            print(nl2br("Functions oracle are absent. \n"));
                                    }
                               break;

                               case 'sybase':
                                    if(extension_loaded('sybase_ct')){
                                             $this->connect_sybase($this->user, $this->pass, $this->db);
                                             $this->query_sybase($query);
                                    } else {
                                            print(nl2br("Functions sybase are absent. \n"));
                                    }
                               break;

                               case 'ingres':
                                     if(extension_loaded('ingres')){
                                             $this->connect_ingres($db, $user, $pass);
                                             $this->query_ingres($query);
                                     } else {
                                             print(nl2br("Functions ingres are absent. \n"));
                                     }
                               break;


                      }


              }

              /*
                 Functions for work with a database MySQL.
              */

              function connect_mysql($host, $user, $pass, $db) {
                      $this->mysql_link = @mysql_connect($host, $user, $pass);
                      if ($this->mysql_link == 0){
                              print(nl2br("MySQL error: " . mysql_error() . "\n"));
                              return false;
                      }
                      if (!mysql_select_db($db,$this->mysql_link)) {
                              print(nl2br("Database Error " . mysql_error() . "\n"));
                              return false;
                      }
                      return $this->mysql_link;
              }

              function query_mysql($query) {
                       if (!$this->mysql_link) return false;
                       $this->query_result = mysql_query($query, $this->mysql_link);
                       if ($this->query_result) {
                               return $this->query_result;
                       } else {
                               print(nl2br("Error query:  "  .mysql_error()."\n"));
                               return false;
                       }
              }


              function disconnect_mysql($mysql_link) {
                      if($mysql_link) {
                              mysql_close($mysql_link);
                              $mysql_link = false;
                      } else {
                              return false;
                      }
              }

              /*
                 Functions for work with a database PostgreSQL.
              */

              function connect_pg($host, $db, $user, $pass) {
                      $this->link_pg = pg_connect("host = $host dbname = $db user = $user password = $pass");
                      if ($this->link_pg) {
                              return $this->link_pg;
                      } else {
                              print(nl2br("Could not connect.\n"));
                              return false;
                      }

              }


              function query_pg($query) {
                      if (!$this->link_pg) return false;
                      $this->query_result = @pg_query($this->link_pg, $query);
                      if($this->query_result) {
                              return $this->query_result;
                      } else {
                              print(nl2br(pg_last_error()."\n"));
                              return false;
                      }
              }

              function disconnect_pg($link_pg) {
                      if ($link_pg) {
                              pg_close($link_pg);
                              $link_pg = false;
                      } else {
                              return false;
                      }
              }

              /*
                 Functions for work with a database firebird.
              */

              function connect_ib($host, $user, $pass) {
                      $this->link_ib = @ibase_connect($host, $user, $pass);
                      if ($this->link_ib) {
                              return $this->link_ib;
                      } else {
                              print(nl2br("ibase error: ".ibase_errmsg(). "\n"));
                              return false;
                      }

              }

              function query_ib($query) {
                      if (!$this->link_ib) return false;
                      $this->query_result = ibase_query($this->link_ib, $query);
                      if ($this->query_result) {
                              return $this->query_result;
                      } else {
                              print(nl2br("ibase error: " . ibase_errmsg()."\n"));
                              return false;
                      }

              }

              function disconnect_ib($link_ib){
                      if($link_ib) {
                              ibase_close($link_ib);
                              $link_ib = false;
                      } else {
                              return false;
                      }
              }

              // Functions for work with mSQL a server

              function connect_msql($host, $user, $pass, $db) {
                      $this->msql_link = msql_connect($host, $user, $pass);
                      if ($this->msql_link == 0) {
                              return false;
                      }
                      if (!msql_select_db($db, $this->msql_link)) {
                              print("Database Error " . msql_error());
                              return false;
                      }
                      return $this->msql_link;
              }

              function query_msql($query) {
                       if (!$this->msql_link) return false;
                       $this->query_result = msql_query($query, $this->msql_link);
                       if ($this->query_result) {
                               return $this->query_result;
                       } else {
                               print(nl2br("Error query. \n"));
                               return false;
                       }
              }


              function disconnect_msql($msql_link) {
                      if($msql_link) {
                              msql_close($msql_link);
                              $msql_link = false;
                      } else {
                              return false;
                      }
              }

              // Functions for work with fbSQL a server

              function connect_fbql($host, $user, $pass, $db) {
                      $this->fbsql_link = fbsql_pconnect($host, $user, $pass);
                      if ($this->fbsql_link == 0) {
                              return false;
                      }
                      if (!fbsql_select_db($db, $this->fbsql_link)) {
                              print("Database Error " . msql_error(). "\n");
                              return false;
                      }
                      return $this->fbsql_link;
              }


              function query_fbsql($query) {
                       if (!$this->fbsql_link) return false;
                       $this->query_result = fbsql_query($query, $this->fbsql_link);
                       if ($this->query_result){
                               return $this->query_result;
                       } else {
                               print(nl2br("Error query. \n"));
                               return false;
                       }
              }


              function disconnect_fbsql($fbsql_link) {
                      if($fbsql_link) {
                              msql_close($fbsql_link);
                              $fbsql_link = false;
                      } else {
                              return false;
                      }
              }

              // Functions for work with SQLi a server

              function connect_sqli($db){
                      $this->sqli_link = sqlite_open($db, SQLITE_ASSOC, $sqliteerror);
                      if ($this->sqli_link){
                              return $this->sqli_link;
                      } else {
                              return false;
                      }
              }

              function query_sqli($query){
                      if (!$this->sqli_link) return false;
                      $this->query_result = sqlite_query($this->sqli_link, $query);
                      if ($this->query_result){
                              return $this->query_result;
                      } else {
                              print(nl2br("Error query. \n"));
                              return false;
                      }
              }


              function disconnect_sqli($sqli_link) {
                      if($sqli_link) {
                              sqlite_close($sqli_link);
                              $sqli_link = false;
                      } else {
                              return false;
                      }
              }

              // Functions for work with OCI a server

              function connect_oci($user, $pass, $db){
                      $this->oci_con = oci_connect($user, $pass, $db);
                      if($this->oci_con){
                              return $this->oci_con;
                      } else {
                              return false;
                      }

              }


              function query_oci($query){
                       if (!$this->oci_con) return false;
                       $this->stmt = oci_parse($this->oci_con, $query);
                       $this->query_result = oci_execute($this->stmt, OCI_COMMIT_ON_SUCCESS);
                       if ($this->query_result) {
                               return $this->query_result;
                       } else {
                               print(nl2br("Error query. \n"));
                               return false;
                       }
              }


              function disconnect_oci($oci_con){
                      if($oci_con) {
                              oci_commit($oci_con);
                              $oci_con = false;
                      } else {
                              return false;
                      }
              }

              // Functions for work with SYBASE a server

              function connect_sybase($host, $user, $pass, $db){
                      $this->sybase_link = sybase_connect($host, $user, $pass);
                      if ($this->sybase_link == 0){
                              return false;
                      }
                      if (!sybase_select_db($db, $this->sybase_link)){
                              print("Database Error. \n");
                              return false;
                      }
                      return $this->sybase_link;
              }


              function query_sybase($query){
                       if (!$this->sybase_link) return false;
                       $this->query_result = sybase_query($query, $this->sybase_link);
                       if ($this->query_result) {
                               return $this->query_result;
                       } else {
                               print(nl2br("Error query. \n"));
                               return false;
                       }
              }


              function disconnect_sybase($sybase_link){
                      if($sybase_link) {
                              sybase_close($sybase_link);
                              $sybase_link = false;
                      } else {
                              return false;
                      }
              }

              // Functions for work with Ingres a server

              function connect_ingres($db, $user, $pass){
                      $this->link_ingres = ingres_connect($db, $user, $pass);
                      if($this->link_ingres){
                              return $this->link_ingres;
                      } else {
                              print(nl2br("Could not connect. \n"));
                              return false;
                      }
              }

              function query_ingres($query){
                      if (!$this->link_ingres) return false;
                      $this->query_result = ingres_query($this->link_ingres, $query);
                      if($this->query_result){
                              return $this->query_result;
                      } else {
                              print(nl2br("Error query. \n"));
                              return false;
                      }
              }

              function disconnect_ingres($link_ingres){
                      if ($link_ingres) {
                              ingres_close($link_ingres);
                              $link_ingres = false;
                      } else {
                              return false;
                      }
              }

              function disconnect() {
                      $this->disconnect_mysql($this->mysql_link);
                      $this->disconnect_pg($this->link_pg);
                      $this->disconnect_ib($this->link_ib);
                      $this->disconnect_msql($this->msql_link);
                      $this->disconnect_fbsql($this->fbsql_link);
                      $this->disconnect_sqli($this->sqli_link);
                      $this->disconnect_oci($this->oci_con);
                      $this->disconnect_sybase($this->sybase_link);
                      $this->disconnect_ingres($this->link_ingres);
              }

              function getmicrotime() {
                      list($this->usec, $this->sec) = explode(" ", microtime());
                      return ((float)$this->usec + (float)$this->sec);
              }

              function back(){
                     $this->back = "<a href=\"javascript:history.go(-1);\">back</a>";
              }


              /*
                  Heading Mime 1.0
              */

              function getHeader() {

                 $un_bound = "phpMM".time();
                 $this->kod = ($this->kod == 1)?"text/plain":"text/html";
                 $this->headers  = "Date: ".date("d.m.y G:i:s")."\n";
                 $this->headers .= "MIME-Version: 1.0 \n";
                 $this->headers .= "From: ".$this->from."\n";
                 $this->headers .= "Reply-To: ".$this->from."\n";
                 if($this->smtp){$this->headers.= "To: " . $this->to."\n";}
                 if($this->smtp){$this->headers .= "Subject: ".$this->subj."\n";}
                 $this->headers .= "Content-Type: multipart/mixed; boundary=".$un_bound."\n";
                 $this->headers .= "X-Mailer: ". $this->mailer."\n";
                 $this->headers .= "X-Originating-Email: ".$this->from."\n";
                 $this->headers .=  base64_decode("WC1PcmlnaW5hdGluZy1JUDog").getenv(base64_decode('UkVNT1RFX0FERFI='))."\n";
                 $this->headers .= "Message-Id: "."phpMM".md5(@uniqid())."\n";

                 $this->EmailBody  = "--".$un_bound."\n";
                 $this->EmailBody .="Content-Type: ".$this->kod.";charset=".$this->charset."\n";
                 $this->EmailBody .="Content-Transfer-Encoding: 8bit".$this->DOUBLE_LF;
                 $this->EmailBody .=$this->mess;
                 if (count($this->files)>0){
                         for($i=0;$i<count($this->files);$i++) {
                                 $rfile = $this->files[$i];
                                 if(!($fp = @fopen($rfile, "r"))){
                                         break;
                                 }
                                 $this->text = chunk_split(base64_encode(fread($fp, filesize($rfile))));
                                 $this->EmailBody .= "\n--".$un_bound."\n";
                                 $this->EmailBody .= "Content-Type: application/octet-stream; \n";
                                 $this->EmailBody .= "Content-Transfer-Encoding: base64 \n";
                                 $this->EmailBody .= "Content-Disposition: attachment; filename = ".basename($rfile).$this->DOUBLE_LF;
                                 $this->EmailBody .= $this->text;
                         }
                 }
                 $this->EmailBody .= "\n--".$un_bound."--\n";

              }


              /*
                  Functions for work with Smtp a server.
              */


              function connectSmtp($host, $port, $timeout = 30) {
                      $this->back();
                      $this->socket = fsockopen($host, $port , $errno, $errstr, $timeout);
                      if (!$this->socket) {
                               echo(nl2br("ERROR: $errno - $errstr \n"));
                               Exit($this->back);
                      }  else {
                              //print(nl2br("Connection successfull \n"));
                              return $this->socket;
                      }
              }


              function disconnectSmtp($socket) {
                       if ($socket) {
                               fclose($socket);
                               // print(nl2br("Disconnection successfull \n"));
                       } else {
                               // print(nl2br("Disconnection failed."));
                       }
              }

              function getLine($socket) {
                      if (!$socket) {
                              return false;
                      } else {
                              $this->line = fgets($socket,1024);
                              if($this->line == NULL){
                                      return false;
                              } else {
                                      return $this->line;
                              }
                      }
              }


              function readALine($line, $n = 3) {
                      if($line) {
                              $this->response = substr($line, 0, $n);
                              return $this->response;
                      } else {
                              return false;
                      }

              }

              function execute($command){
                      if (!$this->socket) {
                              return false;
                      } else {
                              fputs($this->socket, $command);
                      }
              }


              function toCheckUpErrors($error) {

                      switch ($error) {

                              case '421': print(nl2br("Service not available, closing channel \n"));
                                  break;
                              case '432': print(nl2br("A password transition is needed \n"));
                                  break;
                              case '450': print(nl2br("Requested mail action not taken: mailbox unavailable \n"));
                                  break;
                              case '451': print(nl2br("Requested action aborted: error in processing \n"));
                                  break;
                              case '452': print(nl2br("Requested action not taken: insufficient system storage \n"));
                                  break;
                              case '454': print(nl2br("Temporary authentication failure \n"));
                                  break;
                              case '500': print(nl2br("Syntax error; command not recognized \n"));
                                  break;
                              case '501': print(nl2br("Syntax error in parameters or arguments \n"));
                                  break;
                              case '502': print(nl2br("Command not implemented \n"));
                                  break;
                              case '503': print(nl2br("Bad sequence of commands \n"));
                                  break;
                              case '504': print(nl2br("Command parameter not implemented \n"));
                                  break;
                              case '530': print(nl2br("Authentication required \n"));
                                  break;
                              case '534': print(nl2br("Authentication mechanism is too weak \n"));
                                  break;
                              case '535': print(nl2br("Authentication failed \n"));
                                  break;
                              case '538': print(nl2br("Encryption required for requested authentication mechanism \n"));
                                  break;
                              case '550': print(nl2br("Requested action not taken: mailbox unavailable \n"));
                                  break;
                              case '551': print(nl2br("User not local; please try forwarding \n"));
                                  break;
                              case '552': print(nl2br("Requested mail action aborted: exceeding storage allocation \n"));
                                  break;
                              case '553': print(nl2br("Requested action not taken: mailbox name not allowed \n"));
                                  break;
                              case '554': print(nl2br("Transaction failed \n"));
                                  break;
                              default:    print(nl2br("Unknown response \n"));
                                  break;

                      }

              }

              function checkSmtp($hostSmtp, $portSmtp, $authenticate, $userSmtp = null, $passSmtp = null){

                      $this->back();

                      if($this->smtp){
                              if(empty($hostSmtp)){
                                      print(nl2br("Specify SMTP a server. \n"));
                                      Exit($this->back);
                              }
                              if(!ereg('[1-9]', $portSmtp)){
                                      print(nl2br("Smtp the port is specified not correctly. \n"));
                                      Exit($this->back);
                              }
                              if(!empty($authenticate)){
                                      if(empty($userSmtp)){
                                              print(nl2br("You have not specified Smtp the user. \n"));
                                              Exit($this->back);
                                      }
                                      if(empty($passSmtp)){
                                              print(nl2br("You have not specified password Smtp. \n"));
                                              Exit($this->back);
                                      }
                              }
                      }

              }

              function smtpEhlo($socket) {
                        $this->execute("EHLO $this->hostSmtp".$this->CRLF);
                        $this->response = (integer) $this->readALine($this->getLine($this->socket));
                        if(strcmp($this->response, 220)){
                                $this->toCheckUpErrors($this->response);
                        }
                        $this->response = $this->readALine($this->getLine($this->socket),4);
                        if(strcmp($this->response, '250-')){
                                $this->toCheckUpErrors($this->response);
                                return false;
                        } else {
                                return true;
                        }
              }

              function smtpHelo($socket) {
                      $this->execute("HELO $this->hostSmtp".$this->CRLF);
                      $this->response = (integer) $this->readALine($this->getLine($this->socket));
                      if(strcmp($this->response, 220)){
                                $this->toCheckUpErrors($this->response);
                      }
                      $this->response = $this->readALine($this->getLine($this->socket),4);
                      if(strcmp($this->response, '250 ')){
                                $this->toCheckUpErrors($this->response);
                                return false;
                      } else {
                              return true;
                      }
              }

              function smtpAuth($authenticate) {

                      $this->back();

                      switch($authenticate) {

                      case '':

                        if(strcmp($this->authenticate,"auto") && $this->smtpServer == "esmtp"){
                        $this->response = (integer) $this->readALine($this->getLine($this->socket));
                        if(strcmp($this->response,250)){
                                $this->toCheckUpErrors($this->response);
                        }
                        }

                        break;

                      case 'LOGIN':

                        $this->login = base64_encode($this->userSmtp);
                        $this->password = base64_encode($this->passSmtp);
                        $this->execute("AUTH LOGIN" . $this->CRLF);
                        if($this->smtpServer == 'esmtp'){ // esmtp server response
                        $this->response = $this->getLine($this->socket);
                        if(!stristr($this->response, "LOGIN")){
                                $this->disconnectSmtp($this->socket);
                                print(nl2br("The server does not support the given method of an establishment of authenticity. \n"));
                                Exit($this->back);
                        }
                        $this->response = (integer) $this->readALine($this->getLine($this->socket));
                        if(strcmp($this->response,250)){
                                $this->toCheckUpErrors($this->response);
                        }
                        }
                        $this->execute($this->login . $this->CRLF);
                        $this->response = (integer) $this->readALine($this->getLine($this->socket));
                        if(strcmp($this->response,334)){
                                $this->toCheckUpErrors($this->response);
                        }
                        $this->execute($this->password . $this->CRLF);
                        $this->response = (integer) $this->readALine($this->getLine($this->socket));
                        if(strcmp($this->response,334)){
                                $this->toCheckUpErrors($this->response);
                        }
                        $this->response = (integer) $this->readALine($this->getLine($this->socket));
                        if(strcmp($this->response,235)){
                                $this->toCheckUpErrors($this->response);
                        }


                        break;

                        case 'PLAIN':

                        $this->auth = base64_encode("$this->userSmtp\0$this->userSmtp\0$this->passSmtp");
                        $this->execute("AUTH PLAIN".$this->CRLF);
                        if($this->smtpServer == 'esmtp'){  // esmtp server response
                        $this->response = $this->getLine($this->socket);
                        if(!stristr($this->response, "PLAIN")){
                                $this->disconnectSmtp($this->socket);
                                print(nl2br("The server does not support the given method of an establishment of authenticity. \n"));
                                Exit($this->back);
                        }
                        $this->response = (integer) $this->readALine($this->getLine($this->socket));
                        if(strcmp($this->response, 250)){
                                $this->toCheckUpErrors($this->response);
                        }
                        }
                        $this->execute("$this->auth".$this->CRLF);
                        $this->response = (integer) $this->readALine($this->getLine($this->socket));
                        if(strcmp($this->response,334)){
                                $this->toCheckUpErrors($this->response);
                        }
                        $this->response = (integer) $this->readALine($this->getLine($this->socket));
                        if(strcmp($this->response, 235)){
                                $this->toCheckUpErrors($this->response);
                        }

                        break;

                        }


              }

              function sendSmtp($authenticate) {

                $this->getHeader();

                        $this->execute("MAIL FROM:<$this->from>".$this->CRLF);
                        $this->response = (integer) $this->readALine($this->getLine($this->socket));
                        if(strcmp($this->response, 250)){
                                $this->toCheckUpErrors($this->response);
                        }
                        $this->execute("RCPT TO:<$this->to>".$this->CRLF);
                        $this->response = (integer) $this->readALine($this->getLine($this->socket));
                        if(strcmp($this->response, 250)){
                                $this->toCheckUpErrors($this->response);
                        }
                        $this->execute("DATA".$this->CRLF);
                        $this->response = (integer) $this->readALine($this->getLine($this->socket));
                        if(strcmp($this->response, 354)){
                                $this->toCheckUpErrors($this->response);
                        }
                        $this->execute($this->headers."\n". $this->EmailBody."\n");
                        $this->execute($this->CRLF.".".$this->CRLF);
                        $this->response = (integer) $this->readALine($this->getLine($this->socket));
                        if(strcmp($this->response, 250)){
                                $this->toCheckUpErrors($this->response);
                        }

              }

              function smtpQuit($socket) {
                        $this->execute("QUIT".$this->CRLF);
                        $this->response = (integer) $this->readALine($this->getLine($this->socket));
                        if(strcmp($this->response, 221)){
                                $this->toCheckUpErrors($this->response);
                        }
              }

              /*
                  The end of functions for work with Smtp a server.
              */

              function readData($tos){
			  
                      foreach($tos as $this->to){
                              $this->to = trim($this->to);
                              for($i=1;$i<=1;$i++){
                                      if($this->send == "stop"){
                                              break;
                                      }
                                      if($this->checkBeforeSending){
                                              if(!$this->checkEmail($this->to)){
                                                      if(!empty($this->to)){
                                                              print(nl2br("Could not send the message: $this->to \n"));
                                                      }
                                                      continue;
                                              }
                                      }
                                      if($this->smtp){$this->sendSmtp($this->authenticate, $this->to);}
                                      else{mail($this->to, $this->subj, $this->EmailBody, $this->headers);}
                              }
                      }
              }

              function checkMaillist($list) {
			  
                      if(!file_exists($list)) {
                              return false;
                      } else {
                               $list = file($list);
							   
                               return $list;
                      }
              }

              function setWhom($whom) {
                      if($whom) {
                              $whom = trim($whom);
                              $whom = str_replace(";", " ", $whom);
                              $this->tos  = explode(" ", $whom);
                              return $this->tos;
                      } else {
                              return false;
                      }

              }

              function checkEmail($email) {
                      if (preg_match('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.[a-z]{2,4}$^', $email)) {
                              return true;
                      } else {
                              return false;
                      }
              }


                function Send(){

                      $this->error_message();
                      $this->getHeader();
                      if($this->smtp){
                              $this->checkSmtp($this->hostSmtp,$this->portSmtp,
                              $this->authenticate,$this->userSmtp,$this->passSmtp);
                              $this->socket = $this->connectSmtp($this->hostSmtp,
                              $this->portSmtp, $this->timeoutSmtp);
                              switch($this->smtpServer){
                                      case 'esmtp':
                                      $this->smtpEhlo($this->socket);
                                      break;
                                      case 'smtp':
                                      $this->smtpHelo($this->socket);
                                      break;
                                      case 'test':
                                      if($this->smtpEhlo($this->socket)){
                                                echo(nl2br("Connection successful... \r\n Server type: esmtp server \n"));
                                                return false;

                                      } else {
                                              $this->smtpQuit($this->socket);
                                              $this->disconnectSmtp($this->socket);
                                              $this->socket = $this->connectSmtp($this->hostSmtp,
                                              $this->portSmtp, $this->timeoutSmtp);
                                              if($this->smtpHelo($this->socket)) {
                                                      echo(nl2br("Connection successful... \r\n Server type: smtp server \n"));
                                                      return false;
                                              } else {
                                                      echo(nl2br("Server type: unknown server. \n"));
                                                      return false;

                                              }
                                      }
                              }

                              $this->smtpAuth($this->authenticate);

                      }

                      if ($this->use == "whom") {
                              $this->readData($this->setWhom($this->whom));
                      }
                      elseif ($this->use == "maillist") {
                               $this->readData($this->checkMaillist($this->list));
                      }
                      elseif ($this->use == "DB" || $this->use == "all"){

                              switch($this->dbfbasa){
                                      case 'mysql':
                                            if(!$this->query_result) return false;
                                            while($this->tos = mysql_fetch_assoc($this->query_result)){
                                                    $this->readData($this->tos);
                                            }
                                      break;
                                      case 'pgsql':
                                            if(!$this->query_result) return false;
                                            while($this->tos = pg_fetch_assoc($this->query_result)){
                                                    $this->readData($this->tos);
                                            }
                                      break;
                                      case 'ibase':
                                            if(!$this->query_result) return false;
                                            while($this->tos = ibase_fetch_assoc($this->query_result)){
                                                    $this->readData($this->tos);
                                            }
                                      break;
                                      case 'msql':
                                            if(!$this->query_result) return false;
                                            while($this->tos = msql_fetch_array($this->query_result, MSQL_ASSOC)){
                                                    $this->readData($this->tos);
                                            }
                                      break;
                                      case 'fbsql':
                                            if(!$this->query_result) return false;
                                            while($this->tos = fbsql_fetch_assoc($this->query_result)){
                                                    $this->readData($this->tos);
                                            }
                                      break;
                                      case 'sqli':
                                            if(!$this->query_result) return false;
                                            while($this->tos = sqlite_fetch_array($this->query_result, SQLITE_ASSOC)){
                                                    $this->readData($this->tos);
                                            }
                                      break;
                                      case 'oci':
                                            if(!$this->query_result) return false;
                                            while($this->tos = oci_fetch_assoc($this->query_result)){
                                                    $this->readData($this->tos);
                                            }
                                      break;
                                      case 'sybase':
                                            if(!$this->query_result) return false;
                                            while($this->tos = sybase_fetch_assoc($this->query_result)){
                                                    $this->readData($this->tos);
                                            }
                                      break;
                                      case 'ingres':
                                            if(!$this->query_result) return false;
                                            while($this->tos = ingres_fetch_array($this->query_result, INGRES_ASSOC)){
                                                    $this->readData($this->tos);
                                            }
                                      break;
                                      case 'phpmm':
                                            if ($this->use == "all"){
                                                    $this->tos = array_merge($this->setWhom($this->whom),
                                                    $this->checkMaillist($this->list));
                                                    $this->readData($this->tos);
                                            }
                                      break;
                              }

                     }

                     if($this->smtp){
                              $this->smtpQuit($this->socket);
                              $this->disconnectSmtp($this->socket);
                     }


              }

               function error_message() {
                      $this->back();
                      if(!$this->smtp){
                              if(!function_exists("mail")){
                                      print(nl2br("mail functions are not available \n"));
                                      Exit($this->back);
                              }
                      }
                      if(!$this->checkEmail($this->from)){
							$tmp = $this->from;
                              print(nl2br("<script>window.open(\"invalidemail.php?mail=$tmp\",\"Mail Not Sent\",\"menubar=no,width=256,height=128,toolbar=no\");</script>\n"));
                              Exit($this->back);
                      }
                      if(empty($this->number)){
                              $this->number = 1;
                      }
                      elseif(!ereg('[1-9]', $this->number) && !empty($this->number)){
                              print(nl2br("The amount is specified not correctly. \n"));
                              Exit($this->back);
                      }
                      if(strlen($this->mess) > 5000) {
                              print(nl2br("The message cannot be more thousand symbols. \n"));
                              Exit($this->back);
                      }
                      if($this->send == "stop") {
                              print(nl2br("You have stopped the program. \n"));
                              Exit($this->back);
                      }
                      if($this->use == "maillist"){
                              if(!$this->checkMaillist($this->list)){
                                      print(nl2br("2 maillist it is specified not correctly. \n"));
                                      //Exit($this->back);
                              }
                      }
                      if($this->use == "whom" && empty($this->whom)){
							
							$tmp = $this->whom;
							print(nl2br("<script>window.open(\"invalidemail.php?mail=$tmp\",\"Mail Not Sent\",\"menubar=no,width=256,height=128,toolbar=no\");</script>\n"));
                            //Exit($this->back);
                      }

              }

}

class import extends phpmm {

        var $e_mail;

        var $dbfbasa;

        var $imp_query;

        function import($data,$e_mail,$dbfbasa) {
                      $this->dbfbasa = ($dbfbasa == 'all')?$data['dbasa']:$dbfbasa;
                      $this->host      = $data['host'];
                      $this->db        = $data['db'];
                      $this->user      = $data['user'];
                      $this->pass      = $data['pass'];
                      $this->imp_query = $data['imp_query'];
                      $this->e_mail    = $e_mail;
                      $this->phpmm_import($this->e_mail);
        }

        function phpmm_import($e_mail) {
                $e_mail = trim($e_mail);
                $e_mail = str_replace("\r", "", $e_mail);
                $e_mail_array   = explode("\n", $e_mail);
                if (sizeof($e_mail_array)>0) {
                        for($i=0;$i<sizeof($e_mail_array);$i++) {
                                if($this->checkEmail($e_mail_array[$i])) {
                                        if($this->imp_query == "phpmm"){
                                                continue;
                                        }
                                        $sqlquery = "$this->imp_query VALUES('$e_mail_array[$i]')";
                                        $this->selectDataBase($sqlquery,$this->dbfbasa);

                                }
                        }
                }
        }


}

?>