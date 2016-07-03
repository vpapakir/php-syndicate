<?php
if (!defined('VCDDB_BASE')) {
	define('VCDDB_BASE', substr(dirname(__FILE__), 0, strrpos(dirname(__FILE__), DIRECTORY_SEPARATOR)));
}

/*require_once($_SERVER['DOCUMENT_ROOT'].'phpsyndicate/lib/data/adodb/adodb.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'phpsyndicate/lib/data/adodb/adodb-xmlschema03.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'phpsyndicate/lib/data/adodb/adodb-exceptions.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'phpsyndicate/lib/external/ajason/Ajax.php');*/

include '../lib/data/adodb/adodb.inc.php';
include '../lib/data/adodb/adodb-xmlschema03.inc.php';
include '../lib/data/adodb/adodb-exceptions.inc.php';
include '../lib/external/ajason/Ajax.php';

define("PHPSYNDICATE_VERSION","1.0");

class Installer {
	
	private static $XMLSchema = 'data/schema2.xml';
	private static $SchemaMSSQL = 'data/mssql.sql';
	private static $SchemaDB2 = 'data/db2.sql';
	private static $SchemaSQLite = 'data/sqlite.sql';
	private static $SchemaOracle = 'data/oracle.sql';
	private static $XMLData = 'data/data2.xml';
	
	private static $template = 'config.template';
	private static $totalRecordCount = 3345;
	
	/**
	 * Live database connection
	 *
	 * @var ADONewConnection
	 */
	private static $db = null;
	
	/**
	 * The heart of the installer and the only public function, this function is the
	 * window into the class.  All Ajax calls are routed forward from this func.
	 *
	 * @param string $checkType | The name of the check to perform
	 * @param array $args | Optional argument array
	 * @param array $args2 | Optional argument array
	 * @return array
	 */
	public static function executeCheck($checkType, $args = null, $args2 = null) {
		try {
			
			$results = array('check' => $checkType, 'status' => 0, 'results' => '');
			
			switch ($checkType) {
				
				case 'recordcount':
					try {
						$count = self::getCurrentRecordCount($args);
						$results['status'] = 1;	
						$results['results'] = $count/self::$totalRecordCount;
					} catch (Exception $ex) {
						$results['status'] = 0;	
						$count = -1;
					}
					
					
					break;
				
				case 'createadmin':
						
					try {

						if (self::createAdmin($args, $args2)) {
							$results['status'] = 1;	
						}
											
					} catch (Exception $ex) {
						$results['results'] = $ex->getMessage();
					}
					
					
					break;
				
				
				case 'saveconfig':
					
					try {
						if (self::saveConfig($args, $args2)) {
							$results['status'] = 1;		
						}
					} catch (Exception $ex) {
						$results['results'] = $ex->getMessage();
					}
					
					
					
					
				
					break;
				
				case 'populatedata':
					try {
						
						if(self::populateData($args)) {
							$results['status'] = 1;
						}
						
					} catch (Exception $ex) {
						$results['results'] = $ex->getMessage();
					}
					
					
					
					
					break;
				
				case 'createtables':
					try {
						
						$schemaResults = self::createTables($args);
						
						switch ($schemaResults) {
							case 2:
								$results['status'] = 1;
								$results['results'] = "DB Schema successfully applied.";
								break;
								
							case 1:
								$results['status'] = 2;
								$results['results'] = "DB Schema created with errors, some tables may not have been created.";
								break;
						
							default:
								$results['status'] = 3;
								$results['results'] = "DB Schema Failed!";
								break;
						}

						
					} catch (Exception $ex) {
						$results['results'] = "Error: " . $ex->getMessage();
					}
					
					break;
				
				case 'testConnection':
					try {
						
						if (self::checkDatabaseConnecion($args)) {
							$results['status'] = 1;
						}
						
					} catch (Exception $adoex) {
						$results['results'] = $adoex->getMessage();
					}
					
					
					
					break;
				
				/* System check stuff .. */
				
				case 'phpversion':
					$results['results'] = "Installed v. " . PHP_VERSION;
					if (PHP_VERSION >= 5) {
						$results['status'] = 1;
					}
					break;
					
					
				case 'gd':
					$results['status'] = (int)function_exists('gd_info');
					if ($results['status'] == 1) {
						$gdinfo = gd_info();
						$gdversion = $gdinfo['GD Version'];
						$results['results'] = "GD Lib => {$gdversion} ";
					} else {
						$results['results'] = "No GD libraries installed!";
					}
					break;
					
				case 'simplexml':
					$results['status'] = (int)function_exists('simplexml_load_file');
					if (function_exists('simplexml_load_file')) {
						$results['results'] = "SimpleXML enabled";	
					} else {
						$results['results'] = "SimpleXML NOT enabled";
					}
					
					break;
					
					
				case 'mbstring':
					$results['status'] = (int)function_exists('mb_convert_encoding');
					if (function_exists('mb_convert_encoding')) {
						$results['results'] = "Multibyte String functions enabled";	
					} else {
						$results['results'] = "Multibyte String functions NOT enabled";
					}
					
					break;
					
					
				case 'session':
					$results['status'] = (int)function_exists('session_id');
					if (function_exists('session_id')) {
						$results['results'] = "Sessions enabled";	
					} else {
						$results['results'] = "Sessions are NOT enabled";
					}
					
					break;
					
				case 'shorttags':
					$results['status'] = (int)ini_get('short_open_tag');
					if (ini_get('short_open_tag')) {
						$results['results'] = "Short open tags enabled";	
					} else {
						$results['results'] = "Short open tags are NOT enabled";
					}
					
					break;
					
				case 'urlfopen':
					$results['status'] = (int)ini_get('allow_url_fopen');
					$results['results'] = "Allow_url_fopen is set in php.ini";
					
					// if url_fopen disabled ... check for CURL support
					if ((int)ini_get('allow_url_fopen') == 0 && function_exists('curl_init')) {
						$results['results'] = "Url_fopen disabled, but I can use CURL instead!";
						$results['status'] = 1;
					} else {
						$results['status'] = 2;	
					}
					
					if ( ($results['status'] == 0) || ($results['status'] == 2) ) {
						$results['results'] = "Allow_url_open and CURL disabled!";
					}
					
					
					break;
					
				case 'fileupload':
					$results['status'] = (int)ini_get('file_uploads');
					if (ini_get('file_uploads')) {
						$results['results'] = "File uploads enabled";	
					} else {
						$results['results'] = "File uploads are NOT enabled";
					}
					
					break;
					
				case 'permissions':
					$arrFolders = array('uploads/');
    				$arrBadFolders = array();
			    	$bUpload = true;
			    	foreach ($arrFolders as $folder) {
			    		$currStatus =  is_dir(self::getBaseDir().$folder) && is_writable(self::getBaseDir().$folder);
			    		$bUpload = $bUpload && $currStatus;
			    		if (!$currStatus) {
			    			array_push($arrBadFolders, $folder);
			    		}
			    	}
					
					$results['status'] = (int)$bUpload;
					if ($bUpload) {
						$strResults = "All upload folders OK.";
					} else {
						$j = 1;
						$strResults = "Wrong permissions on folders ..<br/>";
			    		$strResults .= "<ul>";
			    		foreach ($arrBadFolders as $folder) {
			    			$strResults .= "<li>". $j . " " . $folder . "</li>";
			    			$j++;	
			    		}
			    		$strResults .= "</ul>";
					}
					
					$results['results'] = $strResults;

					break;
					
				case 'config':
					clearstatcache();
					$config_file = self::getBaseDir().'/config/header.php';
					$config_exist = file_exists($config_file);
					$config_writable = is_writable($config_file);
					if (!$config_exist) {
						$strResults = "Config file does not exists:".$config_file;
					} else if ($config_exist && !$config_writable) {
						$strResults = "Config file is NOT writeable";
					} else {
						$strResults = "Config file is writeable";
						$results['status'] = 1;
					}
			
					
					$results['results'] = $strResults;
					break;
					
				case 'database':
					$arrConns = array('mysql_connect' => 'MySQL', 'pg_connect' => 'Postgres', 
						'mssql_connect' => 'Microsoft SQL', 'db2_connect' => 'IBM DB2', 'sqlite_open' => 'SQLite', 'oci_connect' => 'Oracle');
					$strResults = "<ul style='margin:0px;padding:0px'>";
					$bConnOk = false;
					
					$available = array();
					foreach ($arrConns as $func => $type) {
						if (function_exists($func)) {
							$bConnOk = true;
							array_push($available, 1);
							$strResults .= "<li style='color:green'>{$type} module loaded</li>";
						} else {
							array_push($available, 0);
							$strResults .= "<li style='color:red'>{$type} module NOT loaded</li>";
						}
					}
					
					$strResults .= "</ul>";
						
					$results['keys'] = $available;
					$results['status'] = (int)$bConnOk;
					$results['results'] = $strResults;
					break;
					
					
				case 'createRewriteFile':
					
					$status = false;
					$strResults = '';
					try {
						$status = self::createHTAccessFile();
						$strResults = '.htaccess successfully created.';
					} catch (Exception $ex) {
						$strResults = $ex->getMessage();
					}
					
					$results['status'] = $status;
					$results['results'] = $strResults;
					return $results;
					
					break;
						
			
				default:
					throw new Exception('Unknown check request: ' . $checkType);
			}
			
			return $results;
			
			
		} catch (Exception $ex) {
			throw new AjaxException($ex->getMessage(), $ex->getCode());			
		}
	}
	
	
	/**
	 * Get the number of records in database
	 *
	 * @param array $arrSettings | The array containing the connection settings
	 * @return int
	 */
	private static function getCurrentRecordCount($arrSettings) {
		try {
			
			if (!is_array($arrSettings)) {
				throw new Exception('Missing connection arguments.');
			}
									
			$db = self::getConnection($arrSettings);
						
			
			$tables = $db->MetaTables('TABLES');
			$count = 0;
			foreach ($tables as $num => $table) {
				$count += $db->GetOne("SELECT COUNT(*) FROM " . $table);
			}
	
			return $count;   		
			
			
		} catch (Exception $ex) {
			throw $ex;
		}
	}
	
	
	/**
	 * Check the database connection with the given connection parameters in the 
	 * $arrSettings array
	 *
	 * @param array $arrSettings
	 * @return bool
	 */
	private static function checkDatabaseConnecion($arrSettings) {
		try {
			
			
			if (!is_array($arrSettings)) {
				throw new Exception('Missing connection arguments.');
			}
					
			// Get the old error reporting level, and set current to E_ERROR
			$error_level = error_reporting(E_ERROR);
			
			$db = self::getConnection($arrSettings);
		
    	
    		// No error has been thrown .. return true
    		return true;	
	    			
			
		} catch (Exception $ex) {
			throw $ex;
		} 
	}
	
	private static function createTables($arrSettings) {
		try {
			
			if (!is_array($arrSettings)) {
				throw new Exception('Missing connection arguments.');
			}
			
			//Start by creating a normal ADODB connection.
			$db = self::getConnection($arrSettings);
			
			
			// Use the database connection to create a new adoSchema object.
			$schema = new adoSchema( $db );
			
			// Parse the Schema - and supress errors to override the index not found errors
			@$schema->ParseSchema(dirname(__FILE__) . '/' . self::$XMLSchema );
			
			// Get the current database type
			$type = $arrSettings[4];
			
			// Execute based on the database type
			switch ($type) {
				case 'mssql':
					$mssqlFile = dirname(__FILE__) . '/' . self::$SchemaMSSQL;
					if (!file_exists($mssqlFile)) {
						throw new Exception('MSSQL sql script missing!');
					}
					
					$fd = fopen($mssqlFile ,'rb');
					if (!$fd) {
						throw new Exception('Cannot open MSSQL script: ' . self::$SchemaMSSQL);
					}
					
					// Read the file 
					$sql = fread($fd, filesize($mssqlFile));
					fclose($fd);
					if(!$db->Execute($sql)) {
						throw new Exception("Error creating tables with SQL file.");
					} else {
						$result = 2;
					}
					
					break;
					
				case 'db2':
					$db2File = dirname(__FILE__) . '/' . self::$SchemaDB2;
					if (!file_exists($db2File)) {
						throw new Exception('IBM Db2 sql script missing!');
					}
					
					$fd = fopen($db2File,'rb');
					if (!$fd) {
						throw new Exception('Cannot open DB2 script: ' . self::$SchemaDB2);
					}
					
					// Read the file 
					$sql = fread($fd, filesize($db2File));
					fclose($fd);
					// We have to split each CREATE TABLE STATEMENT to single statements
					// Because the ODBC driver can't handle more than one Create Table at a time
					$arrTables = split("GO",$sql);
					foreach ($arrTables as $table) {
						$db->Execute(trim($table));
					}
					
					$result = 2;
					break;
					
				 case 'oci8':
					$oracleFile = dirname(__FILE__) . '/' . self::$SchemaOracle;
					if (!file_exists($oracleFile)) {
						throw new Exception('Oracle sql script missing!');
					}
					
					$fd = fopen($oracleFile,'rb');
					if (!$fd) {
						throw new Exception('Cannot open Oracle script: ' . self::$SchemaOracle);
					}
					
					// Read the file
					$sql = fread($fd, filesize($oracleFile));
					fclose($fd);
					// We have to split each CREATE TABLE STATEMENT to single statements
					// Because the ODBC driver can't handle more than one Create Table at a time
					$arrTables = split("/",$sql);
					foreach ($arrTables as $table) {
						$db->Execute(trim($table));
					}
					
					$result = 2;
					break; 
					
					
				case 'sqlite':
					$sqliteFile = dirname(__FILE__) . '/' . self::$SchemaSQLite;
					if (!file_exists($sqliteFile)) {
						throw new Exception('SQLite sql script missing!');
					}
					
					$tables = file($sqliteFile);
					if (!is_array($tables)) {
						throw new Exception('Cannot open SQLite script: ' . self::$SchemaSQLite);
					}
					
					foreach ($tables as $tablenum => $table) {
			  			$db->Execute($table);
					}
    						
					$result = 2;
					
					break;
					
				default:	// mysql and postgres
					$result = $schema->ExecuteSchema();
					break;
			}
			
			
			
			
			return $result;
			
			
		} catch (Exception $ex) {
			throw $ex;
		}
	}
	
	
	/**
	 * Populate user submitted settings, both in database and in the config file.
	 *
	 * @param array $arrSettings | Assoc array containing the settings
	 * @return bool
	 */
	private static function populateData($arrSettings) {
		try {
			
			if (!is_array($arrSettings)) {
				throw new Exception('Missing connection arguments.');
			}
			
			
			// Give it some time ... 10 minutes for really bad hardware and/or slow connections
			@set_time_limit(60*10);
			
			// Get database connection
			$db = self::getConnection($arrSettings);
			
			// Get the current database type
			$type = $arrSettings[4];
						
			$schema = new adoSchema( $db );
			
			if ($type == 'db2') {
				$schema->ParseSchemaString(self::transformSchema(dirname(__FILE__) . '/' . self::$XMLData));
			} else {
				$schema->ParseSchema(dirname(__FILE__) . '/' . self::$XMLData );
			}
			
			
			$result = $schema->ExecuteSchema();
			
			return true;
			
			
			
		} catch (Exception $ex) {
			throw $ex;
		}
	}
	
	/**
	 * Get a config value from the stdClass that was typecasted from the javascript Object
	 *
	 * @param stdClass $objConfig | The stdClass containing the data
	 * @param string $varname | The variable name within the class
	 * @param string $default | The value to return if the $varname was not set
	 * @return string
	 */
	private static function getConfigValue($objConfig, $varname, $default = "") {
		if (isset($objConfig) && is_object($objConfig)) {
			if (isset($objConfig->$varname) && !empty($objConfig->$varname)) {
				return $objConfig->$varname;
			}
		}
		
		return $default;
		
	}
	
	/**
	 * Save the config file, with the given values.  Uses config.template as a template.
	 *
	 * @param object $arrSettings | The StdClass containing the database connection settings
	 * @param object $objConfig | The StdClass containing the config values
	 * @return bool
	 */
	private static function saveConfig($arrSettings, $objConfig) {
		try {
		
			if (!is_array($arrSettings)) {
				throw new Exception('Missing connection arguments.');
			}
			
			
			// Give it some time ... 2 minutes for really bad hardware and/or slow connections
			@set_time_limit(60*2);
			
			$db = self::getConnection($arrSettings);
			
			if (!is_object($objConfig)) {
				throw new Exception('Config params missing!');
			} 
			
			$host = $arrSettings[0];
			$user = $arrSettings[1];
			$pass = $arrSettings[2];
			$name = $arrSettings[3];
			$type = $arrSettings[4];
			
			// Create the mappings to update the config.php file
			$cmap = array(
				'db.type'			=> $type,
				'db.user'			=> $user,
				'db.pass'			=> $pass,
				'db.host'			=> $host,
				'db.catalog'		=> $name,
				'ldap.auth'			=> self::getConfigValue($objConfig, 'useldap', 0),
				'ldap.host'			=> self::getConfigValue($objConfig, 's_ldaphost'),
				'ldap.base'			=> self::getConfigValue($objConfig, 's_ldapdn'),
				'ldap.isad'			=> self::getConfigValue($objConfig, 'ldapad', 0),
				'ldap.domain'		=> self::getConfigValue($objConfig, 's_ldapad'),
				'proxy.enable'		=> self::getConfigValue($objConfig, 'useproxy', 0),
				'proxy.hostname'	=> self::getConfigValue($objConfig, 's_proxyhost', 0),
				'proxy.port'		=> self::getConfigValue($objConfig, 's_proxyport', "8080"),
				'soap.secret'		=> substr(md5(uniqid(mt_rand(),true)),0, 6)
			);
			
			
			// Create the db mappings to update ..
			$map = array(
				's_register' 		=> 'ALLOW_REGISTRATION', 
				's_covers'			=> 'DB_COVERS',
				's_category'		=> 'PAGE_COUNT',
				's_session'			=> 'SESSION_LIFETIME',
				's_title'			=> 'SITE_NAME',
				's_email'			=> 'SMTP_FROM',
				's_smtphost'		=> 'SMTP_SERVER',
				's_smtpusername'	=> 'SMTP_USER',
				's_smtppassword'	=> 'SMTP_PASS',
				's_smtprealm'		=> 'SMTP_REALM',
				'userewrite'		=> 'MOD_REWRITE'
			);
			
						
			// Update the settings in database and map the user submitted entries to the config file
			$class_vars = get_object_vars($objConfig);
			foreach ($class_vars as $key => $value) {
				if (strcmp($value, '' != 0) && key_exists($key, $map)) {
					$query = "UPDATE phpsyndicate_settings SET settings_value = {$db->Quote($value)} WHERE settings_key = {$db->Quote($map[$key])}";
					$db->Execute($query);
				}
				
				if (strcmp($value, '' != 0) && key_exists($key, $cmap)) {
					$cmap[$key] = $value;
				}
				
			}
			
			// Add the SITE_ROOT and SITE_HOME values ..
			$query = "UPDATE phpsyndicate_settings SET settings_value = {$db->Quote(self::getUrl())} WHERE settings_key = 'SITE_HOME'";
			$db->Execute($query);
			$query = "UPDATE phpsyndicate_settings SET settings_value = {$db->Quote(self::getRelativeUrl())} WHERE settings_key = 'SITE_ROOT'";
			$db->Execute($query);
			$query = "INSERT INTO phpsyndicate_metadata (record_id,mediatype_id, user_id, type_id, metadata_value) VALUES (0,0,0,7,{$db->Quote(0)})";
			$db->Execute($query);
			
			// Then read the config file template and write with the used based values.
			$configtemplate = file_get_contents(self::$template);
			$configfile = str_replace(array_keys($cmap), array_values($cmap), $configtemplate);
			
			if (!self::write(self::getBaseDir().'config/header.php', $configfile)) {
				throw new Exception('Could not write to config file!'.self::getBaseDir().'config\header.php');
			}
		
			
			
			
			return true;
			
			
		} catch (Exception $ex) {
			throw $ex;
		}
	}
	
	
	private static function getUrl() {
		$prefix = (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off" ? 'http' : 'https') . "://";
		$fullurl = $prefix.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		return substr($fullurl, 0, strpos($fullurl, "setup")); 
	}

	/**
	 * Get the relative url within the domain name
	 *
	 * @return string
	 */
	private static function getRelativeUrl() {
		$path = $_SERVER['PHP_SELF'];
		$pos = strpos($path, "setup");
		return substr($path, 0, $pos); 
	}
	
	/**
	 * Create the admin account in database.
	 *
	 * @param object $arrSettings | The StdClass containing the database connection settings
	 * @param object $objConfig | The StdClass containing the config values
	 * @return bool
	 */
	private static function createAdmin($arrSettings, $objConfig) {
		try {
		
			
			if (!is_array($arrSettings)) {
				throw new Exception('Missing connection arguments.');
			}
			
			// Give it some time ... 2 minutes for really bad hardware and/or slow connections
			@set_time_limit(60*2);
			
			// Get the current database type
			$type = $arrSettings[4];
			
			// Get database connection
			$db = self::getConnection($arrSettings);
			
			if (!is_object($objConfig)) {
				throw new Exception('Config params missing!');
			} 
			
			if ($type == 'db2') {
				$query = "INSERT INTO phpsyndicateuser (username, password, name, 
				email, usertype, isActivated, surname) VALUES (
				{$db->qstr(self::getConfigValue($objConfig, 'vcd_username'))},
				{$db->qstr(md5(self::getConfigValue($objConfig, 'vcd_password')))},
				{$db->qstr(utf8_encode(self::getConfigValue($objConfig, 'vcd_fullname')))},
				{$db->qstr(self::getConfigValue($objConfig, 'vcd_email'))},
			    1, 0, {$db->DBDate(time())})";
			} else {
				$query = "INSERT INTO phpsyndicateuser (username, password, name, 
				email, usertype, isActivated, surname) VALUES (
				{$db->qstr(self::getConfigValue($objConfig, 'vcd_username'))},
				{$db->qstr(md5(self::getConfigValue($objConfig, 'vcd_password')))},
				{$db->qstr(utf8_encode(self::getConfigValue($objConfig, 'vcd_fullname')))},
				{$db->qstr(self::getConfigValue($objConfig, 'vcd_email'))},
			    1, '0', {$db->DBDate(time())})";
			}
			
			
			$db->Execute($query);
			
			return true;
			
		} catch (Exception $ex) {
			throw $ex;
		}
	}
	
	
	/**
	 * Get a live database connection
	 *
	 * @param array $arrSettings | The array containing the connection params
	 * @return ADONewConnection
	 */
	private static function getConnection($arrSettings) {
		try {
			
			if (!is_array($arrSettings)) {
				throw new Exception('Missing connection arguments.');
			}
			
			
			if (!is_null(self::$db) && self::$db instanceof ADONewConnection && self::$db->IsConnected()) {
				
				return self::$db;
				
			} else {
				
				$host = $arrSettings[0];
				$user = $arrSettings[1];
				$pass = $arrSettings[2];
				$name = $arrSettings[3];
				$type = $arrSettings[4];
			
				
				self::$db = ADONewConnection( $type );
				switch ($type) {
					case 'sqlite':
						self::$db->Connect(self::getBaseDir().'upload/cache/vcddb.db');
						break;
				
					default:
						self::$db->Connect($host, $user, $pass, $name);
						break;
				}
				
				return self::$db;
				
			}
			
		} catch (Exception $ex) {
			throw $ex;
		}
	}
	
	
	
	/**
	 * Transform the XML schema when needed for special cases, like DB2
	 *
	 * @param string $schemaFile | The Xml Schema file to transform
	 * @return string | The transformed XML schema as string
	 */
	private static function transformSchema($schemaFile) {
		try {
			
			
			
			$xmlObject = simplexml_load_file($schemaFile);
			$queries = $xmlObject->sql->query;
			
			$i = 0;
			foreach ($queries as $query) {
				if (isset($query['transform'])) {
			
					$newQuery = $query;
					
					switch ($query['transform']) {
						
						// Settings
						case 1:
							$items = explode(',', $query);
							$items[7] = str_replace("'", "", $items[7]);
							$newQuery = implode(',', $items);
							break;
							
						// Source sites
						case 2:
							$items = explode(',', $query);
							if (sizeof($items) > 10) {
								$items[10] = str_replace("'", "", $items[10]);
							} else {
								$items[8] = str_replace("'", "", $items[8]);
							}
							$newQuery = implode(',', $items);
							break;
							
						// Rss Feeds
						case 3:
							$items = explode(',', $query);
							$items[7] = str_replace("'", "", $items[7]);
							$items[8] = str_replace("'", "", $items[8]);
							$newQuery = htmlspecialchars(implode(',', $items), ENT_QUOTES);
							break;
						
					}
					$xmlObject->sql->query[$i] = $newQuery;
				}
				$i++;
			}
			
			return $xmlObject->asXML();					
						
		} catch (Exception $ex) {
			throw $ex;
		}
	}
	
	
	/**
	 * Try to create the .htaccess file for mod_rewrite
	 *
	 * @return bool
	 */
	private static function createHTAccessFile() {
		$templateFile = VCDDB_BASE.DIRECTORY_SEPARATOR.'includes/schema/htaccess.txt';
		if (!file_exists($templateFile)) {
			throw new Exception('Could not load .htaccess template: ' . $templateFile);
		}
		
		// Check if we are running apache
		if (function_exists('apache_get_version') && apache_get_version() !== false) {
			// Check if mod_rewrite is loaded ..
			if (!in_array('mod_rewrite', apache_get_modules())) {
				throw new Exception('The "mod_rewrite" module is not loaded. Cannot continue.');
			}
		} else {
			throw new Exception('This action is only available when using Apache webserver.');
		}
				
		$fileContents = file_get_contents($templateFile);
		$base = self::getWebBaseDir();
		// Precaution if this is being executed from the admin panel or the setup process
		if (strpos($base,'admin')>0) {
			$base = substr($base,0,strpos($base,'admin'));
		} elseif (strpos($base,'setup')>0) {
			$base = substr($base,0,strpos($base,'setup'));
		}
		
		$htaccess = sprintf($fileContents, $base);
		
		// Check for the .htaccess in the document root
		$htaccessFile = VCDDB_BASE.DIRECTORY_SEPARATOR.'.htaccess';
		if (!file_exists($htaccessFile)) {
			throw new Exception('The file .htaccess does not exist in the document root.  Please create it.');
		}
		
		if (is_readable($htaccessFile) && is_writable($htaccessFile)) {
			$byteCount = file_put_contents($htaccessFile,$htaccess);
			if (is_numeric($byteCount) && $byteCount > 0) {
				return true;
			} else {
				throw new Exception('Could not write to file, please check permissions.');
			}
		} else {
			throw new Exception('The file .htaccess not writeable, please fix the file permissions.');
		}
	}
	
	private static function getWebBaseDir() {
		$base = dirname($_SERVER['PHP_SELF']);
		if (self::endsWith('/',$base)) {
			return $base;
		} else {
			return $base.'/';
		}
	}
	
	/**
	 * Check if specified string ends with certain character
	 *
	 * @param string $str | The needle
	 * @param string $sub | The haystack
	 * @return bool
	 */
	private static function endsWith($str, $sub) {
   		return (substr($str, strlen($str) - strlen($sub)) === $sub );
	}
	
	private static function getBaseDir() {
		return substr(dirname(__FILE__), 0, strrpos(dirname(__FILE__), DIRECTORY_SEPARATOR)).DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Write file to HD.
	 *
	 * @param string $filename | The filename to use
	 * @param string $content | The file content
	 * @return bool
	 */
	private static function write($filename, $content){
		if(!empty($filename) && !empty($content)){
			$fp = fopen($filename,"w");
			$b = fwrite($fp,$content);
			fclose($fp);
			if($b != -1){
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
	
	
	/**
	 * Shorten text, used by Oracle installations
	 *
	 * @param string $text | The text to shorten
	 * @param string $length | The supposed text length
	 * @return string | The shortened string
	 */
	private static function shortenText($text, $length) {
		if (strlen($text) > $length) {
				$text_spl = explode(' ', $text);
				$i = 1;
				$text = $text_spl[0];
				while(strlen($text.$text_spl[$i]) < $length) {
					$text .= " ".$text_spl[$i++];
				}
				$text = $text."...";
			}
		return $text;
	}


}

?>
