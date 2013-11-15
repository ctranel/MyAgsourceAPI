<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

//default is the user database
$db['default']['hostname'] = '173.229.1.155' ;//testdare.verona.crinet\\' . $db_server;
$db['default']['username'] = 'myags_admin';//'webuser';//'sa';
$db['default']['password'] = 'DHI4web*';//'m1$AgSourze';//'ag2013!SQL';
/* agswww server
$db['default']['hostname'] = "localhost";
$db['default']['username'] = "ctranel";
$db['default']['password'] = "wahH0ahh"; */
/* local(ct) mysql
$db['default']['hostname'] = "localhost";
$db['default']['username'] = "dairymanager"; // 
$db['default']['password'] = "agsource"; // newdata */
$db['default']['database'] = "users";
$db['default']['dbdriver'] = 'mssql';//"mysqli";
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
 
/* echo '<pre>';
     print_r($db['default']);
  echo '</pre>';

  echo 'Trying to connect to database: ' .$db['default']['database'];
  $dbh=mssql_connect
  (
    $db['default']['hostname'],
    $db['default']['username'],
    $db['default']['password'])
    or die('Cannot connect to the database because: ' . mysql_error());
    mysql_select_db ($db['default']['database']);

    echo '<br />   Connected OK:'  ;
    print( 'file: ' .__FILE__ . '--> Line: ' .__LINE__); 
*/
$db['herd']['hostname'] = '173.229.1.155' ;//'testdare.verona.crinet\\' . $db_server;
$db['herd']['username'] = 'myags_admin';//'webuser';//
$db['herd']['password'] = 'DHI4web*';//'m1$AgSourze';//
$db['herd']['database'] = "herd";//"genetic_selection_guide";publication_name
$db['herd']['dbdriver'] = "mssql";
$db['herd']['dbprefix'] = "";
$db['herd']['pconnect'] = FALSE;
$db['herd']['db_debug'] = TRUE;
$db['herd']['cache_on'] = TRUE;
$db['herd']['cachedir'] = "";
$db['herd']['char_set'] = "windows-1252";
$db['herd']['dbcollat'] = "sql_latin1_general_cp1_ci_as";
$db['herd']['swap_pre'] = '';
$db['herd']['autoinit'] = TRUE;
$db['herd']['stricton'] = FALSE;
/* echo '<pre>';
     print_r($db['default']);
  echo '</pre>';

  echo 'Trying to connect to database: ' .$db['default']['database'];
  $dbh=mssql_connect
  (
    $db['default']['hostname'],
    $db['default']['username'],
    $db['default']['password'])
    or die('Cannot connect to the database because: ' . mysql_error());
    mysql_select_db ($db['default']['database']);

    echo '<br />   Connected OK:'  ;
    print( 'file: ' .__FILE__ . '--> Line: ' .__LINE__); 
*/

$db['reports']['hostname'] = '173.229.1.155' ;//'testdare.verona.crinet\\' . $db_server;
$db['reports']['username'] = 'myags_admin';//'webuser';
$db['reports']['password'] = 'DHI4web*';//'m1$AgSourze';
$db['reports']['database'] = "rpm";//"report_card";
$db['reports']['dbdriver'] = "mssql";
$db['reports']['dbprefix'] = "";
$db['reports']['pconnect'] = FALSE;
$db['reports']['db_debug'] = TRUE;
$db['reports']['cache_on'] = TRUE;
$db['reports']['cachedir'] = "";
$db['reports']['char_set'] = "windows-1252";
$db['reports']['dbcollat'] = "sql_latin1_general_cp1_ci_as";
$db['reports']['swap_pre'] = '';
$db['reports']['autoinit'] = TRUE;
$db['reports']['stricton'] = FALSE;
/* echo '<pre>';
     print_r($db['default']);
  echo '</pre>';

  echo 'Trying to connect to database: ' .$db['default']['database'];
  $dbh=mssql_connect
  (
    $db['default']['hostname'],
    $db['default']['username'],
    $db['default']['password'])
    or die('Cannot connect to the database because: ' . mysql_error());
    mysql_select_db ($db['default']['database']);

    echo '<br />   Connected OK:'  ;
    print( 'file: ' .__FILE__ . '--> Line: ' .__LINE__); 
*/


/* End of file database.php */
/* Location: ./system/application/config/database.php */