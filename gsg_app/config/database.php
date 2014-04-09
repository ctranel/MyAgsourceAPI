<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 /* -----------------------------------------------------------------
 *  UPDATE comment
 *  @author: carolmd
 *  @date: Nov 18, 2013
 *
 *  @description: Kept the 'default' database connection.
 *  Removed all the other database connections.
 *  Revisions in several other files was needed to complete this change.
 *
 *
 *  -----------------------------------------------------------------
 */
/* -----------------------------------------------------------------
 *  UPDATE comment
 *  @author: carolmd
 *  @date: Dec 17, 2013
 *
 *  @description: Changed default login from administrator to regular user id "webuser".
 *                This is for security purposes, so if any malicious SQL does get introduced through the web, it will
 *                not be able to do much damage to our data.
 *
 *  -----------------------------------------------------------------
 */
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the "Database Connection"
| page of the User Guide.
|
| -------------------------------------------------------------------
| DB SPECIFICATION TEMPLATE
| -------------------------------------------------------------------
|
| $db['default']['hostname'] = "localhost";
| $db['default']['username'] = "root";
| $db['default']['password'] = "";
| $db['default']['database'] = "database_name";
| $db['default']['dbdriver'] = "mysql";
| $db['default']['dbprefix'] = "";
| $db['default']['pconnect'] = TRUE;
| $db['default']['db_debug'] = FALSE;
| $db['default']['cache_on'] = FALSE;
| $db['default']['cachedir'] = "";
| $db['default']['char_set'] = "utf8";
| $db['default']['dbcollat'] = "utf8_general_ci";
| $db['default']['swap_pre'] = "";
| $db['default']['autoinit'] = TRUE;
| $db['default']['stricton'] = FALSE;
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the "default" group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class

*/

$active_group = "default";
$active_record = TRUE;
$db_server = 'myagsource';

switch (ENVIRONMENT)
{
	case 'development':
		$db['default']['hostname'] = 'testdare.verona.crinet\\' . $db_server;
		$db['default']['username'] = 'webuser';
		$db['default']['password'] = 'm1$AgSourze';
		$db['default']['database'] = "users";
		$db['default']['dbdriver'] = 'mssql';
		$db['default']['dbprefix'] = "";
		$db['default']['pconnect'] = FALSE;
		$db['default']['db_debug'] = TRUE;
		$db['default']['cache_on'] = TRUE;
		$db['default']['cachedir'] = "";
		$db['default']['char_set'] = 'windows-1252';
		$db['default']['dbcollat'] = 'sql_latin1_general_cp1_ci_as';
		$db['default']['swap_pre'] = '';
		$db['default']['autoinit'] = TRUE;
		$db['default']['stricton'] = FALSE;
		break;
	case 'testing':
		$db['default']['hostname'] = 'testdare.verona.crinet\\' . $db_server;
		$db['default']['username'] = 'webuser';
		$db['default']['password'] = 'm1$AgSourze';
		$db['default']['database'] = "users";
		$db['default']['dbdriver'] = 'mssql';
		$db['default']['dbprefix'] = "";
		$db['default']['pconnect'] = FALSE;
		$db['default']['db_debug'] = TRUE;
		$db['default']['cache_on'] = TRUE;
		$db['default']['cachedir'] = "";
		$db['default']['char_set'] = 'windows-1252';
		$db['default']['dbcollat'] = 'sql_latin1_general_cp1_ci_as';
		$db['default']['swap_pre'] = '';
		$db['default']['autoinit'] = TRUE;
		$db['default']['stricton'] = FALSE;
		break;
	case 'production':
		//default is the user database
		$db['default']['hostname'] = '173.229.1.155' ;
		$db['default']['username'] = 'webuser';
		$db['default']['password'] = 'm1$AgS_R0';
		$db['default']['database'] = "users";
		$db['default']['dbdriver'] = 'mssql';
		$db['default']['dbprefix'] = "";
		$db['default']['pconnect'] = FALSE;
		$db['default']['db_debug'] = TRUE;
		$db['default']['cache_on'] = TRUE;
		$db['default']['cachedir'] = "";
		$db['default']['char_set'] = 'windows-1252';
		$db['default']['dbcollat'] = 'sql_latin1_general_cp1_ci_as';
		$db['default']['swap_pre'] = '';
		$db['default']['autoinit'] = TRUE;
		$db['default']['stricton'] = FALSE;
		break;
	default:
		exit('The application environment is not set correctly - database.');
}







/* -----------------------------------------------------------------
 *  UPDATE comment
 *  @author: carolmd
 *  @date: Nov 18, 2013
 *
 *  @description: removed all the other database connections.
 *  Always  use default.
 *
 *
 *  -----------------------------------------------------------------
 */
/* End of file database.php */
/* Location: ./system/application/config/database.php */
