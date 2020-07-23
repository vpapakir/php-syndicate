<?php

$databases = array(
                   array("dbasa"     => "mysql",         //The identifier of a database mysql
                         "host"      => "localhost",     // MySQL host - TO EDIT!
                         "db"        => "phpmassmailer_zxq_db", // MySQL DataBase
                         "user"      => "782354_dbuser",          // MySQL user - TO EDIT!
                         "pass"      => "782354_",              // MySQL Password - TO EDIT!
                         "query"     => "SELECT mail FROM mymail",// MySQL query
                         "imp_query" => "INSERT INTO mymail(mail)",
                         ),
                   array("dbasa"     => "pgsql",     // The identifier of a database pgsql
                         "host"      => "localhost",
                         "db"        => "phpmassmailer",
                         "user"      => "phpmm",
                         "pass"      => "123",
                         "query"     => "SELECT mail FROM mymail",
                         "imp_query" => "INSERT INTO mymail(mail)", // Other code in a class "import ".
                         ),
                   array("dbasa"     => "ibase", // The identifier of a database Firebird
                         "host"      => "D:\Prog\WebServers\usr\local\firebird\PHPMM",
                         "db"        => "",
                         "user"      => "SYSDBA",
                         "pass"      => "masterkey",
                         "query"     => "SELECT mail FROM mymail",
                         "imp_query" => "INSERT INTO mymail(mail)",
                         ),
                   array("dbasa" => "msql",
                         "host"  => "localhost",
                         "db"    => "phpmm",
                         "user"  => "phpmm",
                         "pass"  => "123",
                         "query" => "SELECT mail FROM mymail"
                         ),
                   array("dbasa" => "fbsql",
                         "host"  => "localhost",
                         "db"    => "phpmm",
                         "user"  => "phpmm",
                         "pass"  => "123",
                         "query" => "SELECT mail FROM mymail"
                         ),
                   array("dbasa" => "sqli",
                         "host"  => "localhost",
                         "db"    => "phpmm",
                         "user"  => "phpmm",    //Please leave not the necessary parameters.
                         "pass"  => "123",
                         "query" => "SELECT mail FROM mymail"
                         ),
                   array("dbasa" => "oci",
                         "host"  => "localhost", //Please leave not the necessary parameters.
                         "db"    => "phpmm",
                         "user"  => "phpmm",
                         "pass"  => "123",
                         "query" => "SELECT mail FROM mymail"
                         ),
                   array("dbasa" => "sybase",
                         "host"  => "localhost", //Please leave not the necessary parameters.
                         "db"    => "phpmm",
                         "user"  => "phpmm",
                         "pass"  => "123",
                         "query" => "SELECT mail FROM mymail"
                         ),
                   array("dbasa" => "ingres",
                         "host"  => "localhost", //Please leave not the necessary parameters.
                         "db"    => "phpmm",
                         "user"  => "phpmm",
                         "pass"  => "123",
                         "query" => "SELECT mail FROM mymail"
                         ),
                   array("dbasa"     => "phpmm", 
                         "host"      => "phpmm",
                         "db"        => "phpmm",
                         "user"      => "phpmm",
                         "pass"      => "phpmm",
                         "query"     => "phpmm",
                         "imp_query" => "phpmm",
                         )
                 );

// Login and the password of access to the program:
$name               = "admin"; // Login
$pass               = "admin"; // Password

// $auth = 1;   Definition of authenticity is included
// $auth = 0;   Definition of authenticity is switched off

$auth               = 0;

// name of mail programm

$mailer   =     "PHP Mass Mailer";

// Strict check before sending

$checkBeforeSending = TRUE;

$charset_program    = "windows-1251";

// The coding of the message

$charset            = "windows-1251";

$version            = "12-11-2010";

$dbHost = "localhost"; // DB hosto - TO EDIT!
$dbUser = "782354_dbuser";      // DB username - TO EDIT!
$dbPass = "782354_";          // DB password - TO EDIT!
$dbDatabase = "phpmassmailer_zxq_db";

$dir = $_SERVER['PHP_SELF'];

$databasefilename = "phpmassmailer.txt"; // The file containing datatbase schema - edit it if needed

$subjectarray = array();
$subjectarray[] = "dummy"; //dummy data

$recvarray = array();
$subjectarray[] = "user1@mydomain.com"; // dummy data

// dummy data
$defdomain = "mydomain1.com";
$defport = 25;
$defuser = "user1";
$defpass = "user1pass";
$deffrom = "user1@mydomain1.com";

$__from       = array();
$__portSmtp   = array();
$__hostSmtp   = array();
$__smtpServer = array();
$__userSmtp   = array();
$__passSmtp   = array();

//dummy data
$__from[]       = "dummy";
$__portSmtp[]   = "dummy";
$__hostSmtp[]   = "dummy";
$__smtpServer[] = "dummy";
$__userSmtp[]   = "dummy";
$__passSmtp[]   = "dummy";

$image = "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
hEq4hEYYAgA7";

?>