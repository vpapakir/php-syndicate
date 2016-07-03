<?php 
define('DB_TYPE',   	'mysql');
define('DB_USER',   	'root');
define('DB_PASS',   	'root');
define('DB_HOST',   	'localhost');
define('DB_CATALOG', 	'phpsyndicate');


/**
	Support for LDAP based authentication.  To enable set LDAP_AUTH to 1.
	The LDAP authentication has been tested with OpenLDAP, Microsoft Active Directory
	and Lotus Domino directory servers.
	The LDAP Directory constants consist of the following keys ...
	-----------------------------------------------------------
	LDAP_AUTH		Use LDAP authentication or not?
	LDAP_HOST		The LDAP host name (and port name if not using default port)
	LDAP_BASEDN		The Base DN to Bind and Search with on the directory server
	LDAP_AD			Is the target directory server a Microsoft Active Directory server ?
	AD_DOMAIN		If server is an Active Directory server, Windows Domain name must be specified.
**/
define('LDAP_AUTH',		'0');
define('LDAP_HOST', 	'');
define('LDAP_BASEDN', 	'');
define('LDAP_AD', 		'0');
define('AD_DOMAIN', 	'');


/**
 	If your server machine is stuck behind a proxy, you can specify proxy server to handle your
 	web-request.  The fetch classes will then use the proxy server to fetch data instead of 
 	assuming direct connection to the internet.
	The Proxy constants consist of the following keys ...
	-----------------------------------------------------------
	USE_PROXY		The directive to use a proxy server
	PROXY_URL		The IP or hostname of the proxy server
	PROXY_PORT		The port number to use on your proxy server, usually 8080
**/
define('USE_PROXY',  	'0');
define('PROXY_URL',  	'0');
define('PROXY_PORT', 	'8080');


/**
	These constants, define the folder location of the HD storage DVDRental uses.
	These values should never need modifying.
	The directory constants consist of the following keys ...
	------------------------------------------------------------
	NFO_PATH		The directory containing uploaded .nfo files
	COVER_PATH		The directory containing uploaded image covers
	TEMP_FOLDER		The working directory for DVDRental
	CACHE_FOLDER		The cache working folder for DVDRental, including storage for SQLite when used
	THUMBNAIL_PATH		The directory containing thumbnails/poster images
**/
define('TEMP_FOLDER',		'upload/');
define('NFO_PATH',			'upload/nfo/');
define('CACHE_FOLDER',		'upload/cache/');
define('THUMBNAIL_PATH',	'upload/thumbnails/');
define('COVER_PATH',		'upload/covers/');

/**
	These constants are used to keep some restrictions on the size of the uploaded files
	DVDRental accepts.  It's default values are pretty tolerant so changing these settings
	should not really be necessary, but feel free to poke around.
	Remember these settings can NEVER override the size restrictions from php.ini, please refer
	to keys 'upload_max_filesize' and 'post_max_size' in your php.ini file.
	All the decimal values defined below are interpreted as MB.
	
	The Uploaded file size constants consist of the following keys ...
	-------------------------------------------------------------
	VSIZE_THUMBS		Max Thumbnail filesize
	VSIZE_COVERS		Max CD-Cover filesize
	VSIZE_XML		Max Imported XML filesize
	VSIZE_XMLTHUMBS		Max imported XML Thumbnail filesize
**/
define('VSIZE_THUMBS', 		0.2);
define('VSIZE_COVERS', 		5);
define('VSIZE_XML', 		20);
define('VSIZE_XMLTHUMBS', 	30);


/**
	The following constants are mixture of different constants used around DVDRental
	and needed to be kept somewhere :)  The value for these constants can be modified
	as needed.
	These constants consist of the following keys ...
	-------------------------------------------------------------
	STYLE			The default style to use, availble styles are folder names in '/includes/templates/'
	RSS_CACHE_TIME		The time RSS feeds are cached upon refetch.
	DATE_FORMAT			How dates are formatted.
	TIME_FORMAT			How timestamps are formatted.
	
**/
define('STYLE',			'includes/templates/default/');
define('RSS_CACHE_TIME',7200);
define('DATE_FORMAT', 	'%d/%m/%Y');
define('TIME_FORMAT', 	'%H:%M:%S');
define('VCDDB_USEPROXY', '0');
define('VCDDB_SOAPPROXY', 'http://');
define('VCDDB_SOAPSECRET', '00583e');
define('CACHE_MANAGER', '');

?>
