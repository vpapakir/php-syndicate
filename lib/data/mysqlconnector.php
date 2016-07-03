<?php

/*
The class encapsulates the connection to MySql
This class will talk to 1 database specified by the property dbName.

the use of exception is made (try/catch block)
*/

/*
class making the transaction with MySQL very handy.
The main public function are: getRecord, getResult and setQuery
- getResult submits a query to MySQL, the list of result is stored in an
array of stdClass objects.
- getRecord submits a query to MySQL, the result is stored in a stdClass object.
To use it, the query must fetch one line otherwise, no error will occur. However,
only the first record will be returned
- setQuery submits a query to MySQL, the query is update, insert or delete.
The result is therefore a code of success or error
*/
class DBConnect {
  private $dbName = '';
  private $host = '';
  private $user = '';
  private $password = '';
  public $database;
  private $error;

  // check if test is a database available in the server
  public function __construct($DBHOST,$DBNAME,$DBUSER,$DBPASS) {
	  
	  $this->host = $DBHOST;
	  $this->dbName = $DBNAME;
	  $this->user = $DBUSER;
	  $this->password = $DBPASS;
	  
    // connection to the mysql server
	//$this->database = mysql_pconnect($this->host,$this->user,$this->password); // not allowed on zymic servers
	$this->database = mysqli_connect($this->host,$this->user,$this->password,$this->dbName);
	if (!$this->database) {
	   die('Could not connect: ' . mysql_error());
	}

	$this->connectDatabase($this->dbName);
  }

  /*
  First checking on the database. If dbName is not an available database
  name, it exits and returns an error. This is supposed to be used during the setup
  not in the use of the product.
  */
  private function connectDatabase($dbName) {
	// check if test is a database available in the server
	if (!mysqli_select_db($this->database, $dbName)) {
	   echo 'Could not select database';
	   exit;
	}
  }

  /*
  Create a valid SQL sentence. If no argument is passed, the sentence is
  supposed to be valid on its own, otherwise, the parameters passed must match
  numargs at some points..
  Return a SQL sentence parsed using sprintf function using the argument as parameter.
  */
  private function parseArguments($args) {
  	$sql = $args[0];
  	$numargs = count($args);

  	if ($numargs > 1) {
		$listarg = array();
  		for ($i=1; $i<$numargs; $i++) {
  			$listarg[] = $args[$i];
  		}
		$sql = call_user_func_array('sprintf', array_merge((array)$sql, $listarg));
  	}
  	return $sql;
  }
  
  function disconnect()
  {
	  mysqli_close($this->database);
  }

  /*
  	using Exception handling, we are going to try to ask MySQL
  	something. In any case, the script won t die this function will
  	be called within a try/catch block
  */
  private function executeQuery($args) {
    $numargs = count($args);

  	if ($numargs == 0)
  		throw new Exception("bad use of the class");
  	else {
  		$sql = $this->parseArguments($args);
  	}
  	if ($sql!= "") {
  		//Execute the query
  		$result = mysqli_query($this->database,$sql);
  		if (!$result) throw new Exception(mysqli_error($this->database));
  	}

  	return $result;
  }


  /*
  This function evaluates the parameters on the fly.
  The first parameter is $sql: expect a valid sql sentence
  The second parameter is required if sql requires parameter
  eg: insert into table (field1,field2) values ('%s','%s') where id=%d
  requires 3 parameters.
	$this->getResult("insert into table (field1,field2) values ('%s','%s') where id=%d",$field1,$field2,$id);
	would work.
  Returns the list of records answering to the SQL sentence.
  */
  public function getResult() {
  	$args = func_get_args();
	// parsing the data retrieved by the query
	$data = array();
	try
	{
		$result = $this->executeQuery($args);
		
		if(is_bool($result)) {
			// Do nothing
		} else {
			while ($row = mysqli_fetch_object($result)) {
			   $data[]=$row;
			}
			// free the memory
			mysqli_free_result($result);
		}
	}
	catch (Exception $e)
	{
		$this->logError($args,$e);
	}

	/*if (!is_object($e)) {

		
	}*/

	return $data;
  }

  /*
  	Same function as getResult above but return one line
  */
  public function getRecord() {

  	$args = func_get_args();

	try
	{
		$result = $this->executeQuery($args);
	}
	catch (Exception $e)
	{
		$this->logError($args,$e);
	}

	if (!is_object($e)) {

		$row = mysqli_fetch_object($result);

		// free the memory
		mysqli_free_result($result);

		return $row;
	}
  }

  /*
  Submits a query to the database.
  The parameters are evaluated on the fly.
    The first parameter is $sql: expect a valid sql sentence
    The second parameter is required if sql requires parameter
    eg: insert into table (field1,field2) values ('%s','%s') where id=%d
    requires 3 parameters.
  	$this->getResult(insert into table (field1,field2) values ('%s','%s') where id=%d,$field1,$field2,$id);
	would work.
	for a insert returns the id of the record changed.
	The validation is not supposed to be done in this class, instead
  */
  public function setQuery() {
  	$args = func_get_args();

	try
	{
		$result = $this->executeQuery($args);
	}
	catch (Exception $e)
	{
		$this->logError($args,$e);
	}

	if (!is_object($e)) {

	  	$id = mysqli_insert_id();

	  	return $id;
	}
  }

  /*
	facility to keep the errors into a file.
	each time a query is submitted to MySQL, if the query is not successful,
	a message will be appended to the error.txt file
	no try/catch block is used here, it is part of the setup;
  */
  private function logError($args,$exception) {
  	$this->error = $exception->getMessage();

  	//$filename = $_SERVER['DOCUMENT_ROOT'].'phpsyndicate/log/log.txt'; // not allowed on zymic servers
	$filename = "../../log/log.txt";

  	if (!$handle = fopen($filename, 'a+')) {
		echo "Cannot open file ($filename)";
		exit;
    }
    fwrite($handle,date("l dS of F Y h:i:s A"));
    fwrite($handle,"\n");
    if (is_array($args)) foreach ($args as $arguments) fwrite($handle,"argument: $arguments\n");
	fwrite($handle,"error: ".$exception->getMessage()."\n");
	fclose($handle);
  }

  /*
  */
  public function hasError() {
    return $this->error != "";
  }

  /*
  this function allows to use many time the class without instantiating
  all the time the object. By calling this method, the error occuring
  during a call is emptied.
  */
  public function clearError() {
    $this->error = "";
  }

  public function getError() {
  	return $this->error;
  }

  function __destruct() {
	//mysql_close($this->database); not required PHP looks after this
  }
}
