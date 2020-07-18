<? include("vars.inc.php") ?>
<? include("template.inc.php") ?>
<? include("phpmm.php") ?>
 
<?php 

$host2remove = $_POST['host2remove'];

$db = mysql_connect("$dbHost", "$dbUser", "$dbPass") or die ("Error connecting to database.");

mysql_select_db("$dbDatabase", $db) or die ("Couldn't select the database.");

$result=mysql_query("delete * from senders where account='$host2remove'", $db);

echo "Host $host2remove successfuly removed!Return to <a href=index.php>home</a> or continue <a href=\"editsenders.html\">editing</a>.";

?>