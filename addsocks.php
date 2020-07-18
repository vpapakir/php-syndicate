<? include("vars.inc.php") ?>
<? include("template.inc.php") ?>
<? include("phpmm.php") ?>
 
<?php 

$mhost = $_POST['host'];
$mport = $_POST['port'];

$dbHost = "localhost";
$dbUser = "782354_dbuser";
$dbPass = "782354_";
$dbDatabase = "phpmassmailer_zxq_db";

//connet to the database
$db = mysql_connect("$dbHost", "$dbUser", "$dbPass") or die ("Error connecting to database.");
mysql_select_db("$dbDatabase", $db) or die ("Couldn't select the database.");

mysql_query("INSERT INTO socks (host, port)
VALUES ('$mhost', '$port')");

mysql_close($db);

echo "SOCKS successfuly added!Return to <a href=index.php>home</a> or continue <a href=\"editsocks.html\">editing</a>.";

?>
