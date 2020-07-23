<? include("vars.inc.php") ?>
<? include("template.inc.php") ?>
<? include("phpmm.php") ?>
 
<?php 

$mhost = $_POST['host'];
$musername = $_POST['username'];
$mpass = $_POST['password'];
$mport = $_POST['port'];
$maccount = $_POST['mail'];

$dbHost = "localhost";
$dbUser = "782354_dbuser";
$dbPass = "782354_";
$dbDatabase = "phpmassmailer_zxq_db";

//connet to the database
$db = mysql_connect("$dbHost", "$dbUser", "$dbPass") or die ("Error connecting to database.");
mysql_select_db("$dbDatabase", $db) or die ("Couldn't select the database.");

mysql_query("INSERT INTO mailsenders (host, port, username, password, account)
VALUES ('$mhost', '$port', '$musername', '$mpass', '$maccount')");

mysql_close($db);

echo "Host $host successfuly added!Return to <a href=index.php>home</a> or continue <a href=\"editsenders.html\">editing</a>.";

?>