<? include("vars.inc.php") ?>
<? include("template.inc.php") ?>

<?php 

$con = mysql_connect($dbHost,$dbUser,$dbPass); //replace!
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

if (mysql_query("CREATE DATABASE phpmassmailer",$con))
  {
  echo "Database created.OK\n";
  }
else
  {
  echo "Error creating database: " . mysql_error();
  }

mysql_select_db("phpmassmailer", $con);

$queryy = "CREATE TABLE IF NOT EXISTS `mailsenders` (
  `port` int(20) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL,
  `account` varchar(20) NOT NULL,
  `host` varchar(20) NOT NULL,
  `id` int(20) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account` (`account`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='mail senders' AUTO_INCREMENT=5;";
mysql_query($queryy,$con);

$queryy = "INSERT INTO `mailsenders` (`port`, `username`, `password`, `account`, `host`, `id`) VALUES
(25, 'user1', 'user1pass', 'user1@mydomain1.com', 'mail.mydomain1.com', 1),
(25, 'user2', 'user2pass', 'user2@mydomain1.com', 'mail.mydomain1.com', 2),
(25, 'user3', 'user3pass', 'user3@mydomain1.com', 'mail.mydomain1.com', 3),
(25, 'user4', 'user4pass', 'user4@mydomain1.com', 'localhost', 4);";
mysql_query($queryy,$con);

$queryy = "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";";
mysql_query($queryy,$con);

$queryy = "CREATE TABLE IF NOT EXISTS `users` (
  `password` varchar(15) NOT NULL,
  `username` varchar(15) NOT NULL,
  `name` varchar(15) NOT NULL,
  `surname` varchar(15) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($queryy,$con);

$queryy = "CREATE TABLE IF NOT EXISTS `socks` (
  `sockip` varchar(20) NOT NULL,
  `id` int(20) NOT NULL,
  `proxy` varchar(20) NOT NULL,
  `port` int(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($queryy,$con);

$queryy = "INSERT INTO `mymail` (`id`, `mail`) VALUES
(1, 'user1@mydomain1.com');";
mysql_query($queryy,$con);

$queryy = "INSERT INTO `mymail` (`id`, `mail`) VALUES
(1, 'user1@mydomain1.com');";
mysql_query($queryy,$con);

$queryy = "CREATE TABLE IF NOT EXISTS `mymail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;";
mysql_query($queryy,$con);

$queryy = "CREATE TABLE IF NOT EXISTS `mailsubjects` (
  `subid` int(20) NOT NULL AUTO_INCREMENT,
  `subjecttext` varchar(20) NOT NULL,
  PRIMARY KEY (`subid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
mysql_query($queryy,$con);

$queryy = "INSERT INTO `users` (`password`, `username`, `name`, `surname`) VALUES
('admin', 'admin', 'admin', 'admin');";
mysql_query($queryy,$con);

mysql_close($con);

echo "Setup successful!Click <a href=\"index.html\">here</a> to enter PHP Mass Mailer.";

?>

<?php

function parse_mysql_dump($url) {
   
    $handle = @fopen($url, "r");
    $query = "";
    while(!feof($handle)) {
        $sql_line = fgets($handle);
        if (trim($sql_line) != "" && strpos($sql_line, "--") === false) {
            $query .= $sql_line;
			echo "<p>Query: $query</p>";
            if (preg_match("/;[\040]*\$/", $sql_line)) {
				
                $result = mysql_query($query) or die(mysql_error());
                $query = "";
            }
        }
    }
}

function mysql_import($filename) {
	global $wpdb;

	$return = false;
	$sql_start = array('INSERT', 'UPDATE', 'DELETE', 'DROP', 'GRANT', 'REVOKE', 'CREATE', 'ALTER');
	$sql_run_last = array('INSERT');

	if (file_exists($filename)) {
		$lines = file($filename);
		$queries = array();
		$query = '';

		if (is_array($lines)) {
			foreach ($lines as $line) {
				$line = trim($line);

				if(!preg_match("'^--'", $line)) {
					if (!trim($line)) {
						if ($query != '') {
							$first_word = trim(strtoupper(substr($query, 0, strpos($query, ' '))));
							if (in_array($first_word, $sql_start)) {
								$pos = strpos($query, '`')+1;
								$query = substr($query, 0, $pos) . $wpdb->prefix . substr($query, $pos);
							}

							$priority = 1;
							if (in_array($first_word, $sql_run_last)) {
								$priority = 10;
							} 

							$queries[$priority][] = $query;
							$query = '';
						}
					} else {
						$query .= $line;
					}
				}
			}

			ksort($queries);

			foreach ($queries as $priority=>$to_run) {
				foreach ($to_run as $i=>$sql) {
					$wpdb->query($sql);
				}
			}
		}
	}
}

FUNCTION run_query_batch($handle, $filename=""){

	$stmt = "";
 
  // Open SQL file.
  IF (! ($fd = FOPEN($filename, "r")) ) {
    DIE("Failed to open $filename: " . MYSQL_ERROR() . "<br>");
  }
 
  // Iterate through each line in the file.
  WHILE (!FEOF($fd)) {
 
    // Read next line from file.
    $line = FGETS($fd, 32768);
    $stmt = "$stmt$line";
 
    // Semicolon indicates end of statement, keep adding to the statement.
    // until one is reached.
    IF (!PREG_MATCH("/;/", $stmt)) {
      CONTINUE;
    }
 
    // Remove semicolon and execute entire statement.
    $stmt = PREG_REPLACE("/;/", "", $stmt);
 
    // Execute the statement.
    MYSQL_QUERY($stmt, $handle) ||
      DIE("Query failed: " . MYSQL_ERROR() . "<br>");
 
    $stmt = "";
  }
 
  // Close SQL file.
  FCLOSE($fd);
}

 /******************************************************************************************
 * Possibility to select dbase when creating an object instance:
 * -------------------------------------------------------------
 * $db = new sqlImport('dump.sql', false, 'localhost', 'testuser', 'testpass', 'testdbase');
 * $db->import();
 * if ($db->error) exit($db->error);
 * else echo "<b>Data written successfully</b>";
 * -------------------------------------------------------------
 * Now working with both /r/n resp. /n line endings (to make it work with /r see php.net)
 * Now working when using ; inside SQL statements
 * Check parameter added to output what would be written into dbase.
 * If host isn't set the active connection will be used (if any) as always.
 /******************************************************************************************/

 class sqlImport {

     // param $check bool: echo the sql statements instead of writing them into dbase

     // Constructor
     function sqlImport($SqlArchive, $check = false, $host = false, $user = false, $pass = false,
         $database = false) {
         $this->host = $host;
         $this->database = $database;
         $this->user = $user;
         $this->pass = $pass;
         $this->SqlArchive = $SqlArchive;
         $this->check = $check;
     }

     // Connnect
     function dbConnect() {
         $this->con = @mysql_connect($this->host, $this->user, $this->pass);
         if (!$this->con) {
             $this->error = "<b>Error (dbConnect): " . mysql_error() . "</b>";
         }
     }

     // Select dbase
     function select_db() {
         $result = @mysql_select_db($this->database);
         if (!$result) {
             $this->error = "<b>Error (select_db): " . mysql_error() . "</b>";
         }
     }

     // Import Data
     function import() {

         // Connect if $host is set, else we're using the active connection (if any) !
         if ($this->host) {
             $this->dbConnect();
             if ($this->error)
                 return;
         }

         // Select dbase if $database is set, can be set via sql as well
         if ($this->database) {
             $this->select_db();
             if ($this->error)
                 return;
         }

         // For existing connections $this->con is false...
         if ($this->con !== false || $this->check) {

             // To avoid problems we're reading line by line ...
             $lines = file($this->SqlArchive, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
             $buffer = '';
             foreach ($lines as $line) {
                 // Skip lines containing EOL only
                 if (($line = trim($line)) == '')
                     continue;

                 // skipping SQL comments
                 if (substr(ltrim($line), 0, 2) == '--')
                     continue;

                 // An SQL statement could span over multiple lines ...
                 if (substr($line, -1) != ';') {
                     // Add to buffer
                     $buffer .= $line;
                     // Next line
                     continue;
                 } else
                     if ($buffer) {
                         $line = $buffer . $line;
                         // Ok, reset the buffer
                         $buffer = '';
                     }

                 // strip the trailing ;
                 $line = substr($line, 0, -1);

                 // Write the data
                 if (!$this->check)
                     $result = mysql_query($line);
                 // or print it out
                 else {
                     echo substr($line, 0, 180) . ((strlen($line) > 180) ? "...<br>" : "<br>");
                     $this->error = "<b>No data has been written (check = true)</b>";
                 }

                 if (!$result and !$this->check) {
                     $this->error = "<b>Error (mysql_query): " . mysql_error() . "</b>";
                     return;
                 }
             }
         }
     }
 }

?>