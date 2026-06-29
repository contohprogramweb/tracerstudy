<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to connect to your database.
|
| For complete instructions please consult the 'Database Connection' page
| of the User Guide:
|
|	https://codeigniter.com/userguide3/database/
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['dsn']      The full DSN string describe a connection to the database.
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database driver. e.g.: mysqli.
|			Currently supported:
|				 cubrid, ibase, mssql, mysql, mysqli, oci8,
|				 odbc, pdo, postgre, sqlite, sqlite3, sqlsrv
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Query Builder class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['encrypt']  Whether or not to use an encrypted connection.
|
|			'mysql' (deprecated), 'sqlsrv' and 'pdo/sqlsrv' drivers accept TRUE/FALSE
|			'mysqli' and 'pdo/mysql' drivers accept an array with the following options:
|
|				'ssl_key'    - Path to the private key file
|				'ssl_cert'   - Path to the public certificate file
|				'ssl_ca'     - Path to the certificate authority file
|				'ssl_capath' - Path to a directory containing trusted CA certificates in PEM format
|				'ssl_cipher' - List of *allowed* ciphers to be used for the encryption, separated by colons (':')
| 				'ssl_verify' - TRUE/FALSE; Whether verify the server certificate or not
|
|	['compress'] Whether or not to send client compression (MySQL only)
|	['stricton'] TRUE/FALSE - Forces 'STRICT_MODE' mode in MySQL databases
|								strict mode disables strict SQL mode, which accepts more invalid data
|	['failover'] array - A array with 0 or more data for connections if the main should fail.
|	['save_queries'] TRUE/FALSE - Whether to "save" all executed queries.
| 				NOTE: Disabling this will also effectively disable both
| 				$this->db->last_query() and profiling of DB queries.
| 				When you run a query, with this setting set to FALSE (default),
| 				CodeIgniter will store the error messages associated with the query.
| 				If you enable this setting then the errors will be stored in the
| 				property '$this->db->error_info'.
|	['port']     The port number to use when connecting.
|
| To test the database connection, you can use the following command from the terminal:
|
| php index.php cli/db_test
|
*/

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
	'dsn'		=> '',
	'hostname'	=> 'localhost',
	'username'	=> 'root',
	'password'	=> '',
	'database'	=> 'tracerstudy',
	'dbdriver'	=> 'mysqli',
	'dbprefix'	=> '',
	'pconnect'	=> FALSE,
	'db_debug'	=> (ENVIRONMENT !== 'production'),
	'cache_on'	=> FALSE,
	'cachedir'	=> '',
	'char_set'	=> 'utf8mb4',
	'dbcollat'	=> 'utf8mb4_unicode_ci',
	'swap_pre'	=> '',
	'encrypt'	=> FALSE,
	'compress'	=> FALSE,
	'stricton'	=> FALSE,
	'failover'	=> array(),
	'save_queries' => TRUE,
	'port'		=> 3306
);

// Database untuk testing (opsional)
$db['testing'] = array(
	'dsn'		=> '',
	'hostname'	=> 'localhost',
	'username'	=> 'root',
	'password'	=> '',
	'database'	=> 'tracer_study_v31_testing',
	'dbdriver'	=> 'mysqli',
	'dbprefix'	=> '',
	'pconnect'	=> FALSE,
	'db_debug'	=> TRUE,
	'cache_on'	=> FALSE,
	'cachedir'	=> '',
	'char_set'	=> 'utf8mb4',
	'dbcollat'	=> 'utf8mb4_unicode_ci',
	'swap_pre'	=> '',
	'encrypt'	=> FALSE,
	'compress'	=> FALSE,
	'stricton'	=> FALSE,
	'failover'	=> array(),
	'save_queries' => TRUE,
	'port'		=> 3306
);
