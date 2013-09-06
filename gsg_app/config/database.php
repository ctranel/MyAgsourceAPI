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
$db['default']['hostname'] = 'testdare.verona.crinet\\' . $db_server;
$db['default']['username'] = 'myags_admin';//'webuser';
$db['default']['password'] = 'DHI4web*';//'m1$AgSourze';
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
    die( 'file: ' .__FILE__ . '--> Line: ' .__LINE__); */

$db['herd']['hostname'] = 'testdare.verona.crinet\\' . $db_server;
$db['herd']['username'] = 'myags_admin';//'webuser';
$db['herd']['password'] = 'DHI4web*';//'m1$AgSourze';
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

$db['reports']['hostname'] = 'testdare.verona.crinet\\' . $db_server;
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


$db['gsg']['hostname'] = 'testdare.verona.crinet\\' . $db_server;
$db['gsg']['username'] = 'webuser';//'myags_admin';
$db['gsg']['password'] = 'm1$AgSourze';//'DHI4web*';
$db['gsg']['database'] = "rpm";//"genetic_selection_guide";
$db['gsg']['dbdriver'] = "mssql";
$db['gsg']['dbprefix'] = "";
$db['gsg']['pconnect'] = FALSE;
$db['gsg']['db_debug'] = TRUE;
$db['gsg']['cache_on'] = TRUE;
$db['gsg']['cachedir'] = "";
$db['gsg']['char_set'] = "windows-1252";
$db['gsg']['dbcollat'] = "sql_latin1_general_cp1_ci_as";
$db['gsg']['swap_pre'] = '';
$db['gsg']['autoinit'] = TRUE;
$db['gsg']['stricton'] = FALSE;
/*
$db['bench']['hostname'] = "localhost";
$db['bench']['username'] = "ctranel"; // newdata
$db['bench']['password'] = "wahH0ahh"; // newdata
$db['bench']['database'] = "benchmarks";
$db['bench']['dbdriver'] = "mysqli";
$db['bench']['dbprefix'] = "";
$db['bench']['pconnect'] = FALSE;
$db['bench']['db_debug'] = TRUE;
$db['bench']['cache_on'] = TRUE;
$db['bench']['cachedir'] = "";
$db['bench']['char_set'] = "utf8";
$db['bench']['dbcollat'] = "utf8_general_ci";
$db['bench']['swap_pre'] = '';
$db['bench']['autoinit'] = TRUE;
$db['bench']['stricton'] = FALSE;
*/
$db['alert']['hostname'] = 'testdare.verona.crinet\\' . $db_server;
$db['alert']['username'] = 'myags_admin';//'webuser';
$db['alert']['password'] = 'DHI4web*';//'m1$AgSourze';
$db['alert']['database'] = "rpm";//"genetic_selection_guide";//dairymanager";
$db['alert']['dbdriver'] = "mssql";
$db['alert']['dbprefix'] = "";
$db['alert']['pconnect'] = FALSE;
$db['alert']['db_debug'] = TRUE;
$db['alert']['cache_on'] = TRUE;
$db['alert']['cachedir'] = "";
$db['alert']['char_set'] = "windows-1252";
$db['alert']['dbcollat'] = "sql_latin1_general_cp1_ci_as";
$db['alert']['swap_pre'] = '';
$db['alert']['autoinit'] = TRUE;
$db['alert']['stricton'] = FALSE;

$db['rep_card']['hostname'] = 'testdare.verona.crinet\\' . $db_server;
$db['rep_card']['username'] = 'myags_admin';//'webuser';
$db['rep_card']['password'] = 'DHI4web*';//'m1$AgSourze';
$db['rep_card']['database'] = "rpm";//"report_card";
$db['rep_card']['dbdriver'] = "mssql";
$db['rep_card']['dbprefix'] = "";
$db['rep_card']['pconnect'] = FALSE;
$db['rep_card']['db_debug'] = TRUE;
$db['rep_card']['cache_on'] = TRUE;
$db['rep_card']['cachedir'] = "";
$db['rep_card']['char_set'] = "windows-1252";
$db['rep_card']['dbcollat'] = "sql_latin1_general_cp1_ci_as";
$db['rep_card']['swap_pre'] = '';
$db['rep_card']['autoinit'] = TRUE;
$db['rep_card']['stricton'] = FALSE;

$db['uhm_summary']['hostname'] = 'testdare.verona.crinet\\' . $db_server;
$db['uhm_summary']['username'] = 'myags_admin';//'webuser';
$db['uhm_summary']['password'] = 'DHI4web*';//'m1$AgSourze';
$db['uhm_summary']['database'] = "rpm";//"uhm_summary";
$db['uhm_summary']['dbdriver'] = "mssql";
$db['uhm_summary']['dbprefix'] = "";
$db['uhm_summary']['pconnect'] = FALSE;
$db['uhm_summary']['db_debug'] = TRUE;
$db['uhm_summary']['cache_on'] = TRUE;
$db['uhm_summary']['cachedir'] = "";
$db['uhm_summary']['char_set'] = "windows-1252";
$db['uhm_summary']['dbcollat'] = "sql_latin1_general_cp1_ci_as";
$db['uhm_summary']['swap_pre'] = '';
$db['uhm_summary']['autoinit'] = TRUE;
$db['uhm_summary']['stricton'] = FALSE;

$db['uhm_cow']['hostname'] = 'testdare.verona.crinet\\' . $db_server;
$db['uhm_cow']['username'] = 'myags_admin';//'webuser';
$db['uhm_cow']['password'] = 'DHI4web*';//'m1$AgSourze';
$db['uhm_cow']['database'] = "rpm";//"uhm_cow";
$db['uhm_cow']['dbdriver'] = "mssql";
$db['uhm_cow']['dbprefix'] = "";
$db['uhm_cow']['pconnect'] = FALSE;
$db['uhm_cow']['db_debug'] = TRUE;
$db['uhm_cow']['cache_on'] = TRUE;
$db['uhm_cow']['cachedir'] = "";
$db['uhm_cow']['char_set'] = "windows-1252";
$db['uhm_cow']['dbcollat'] = "sql_latin1_general_cp1_ci_as";
$db['uhm_cow']['swap_pre'] = '';
$db['uhm_cow']['autoinit'] = TRUE;
$db['uhm_cow']['stricton'] = FALSE;

$db['herd_summary']['hostname'] = 'testdare.verona.crinet\\' . $db_server;
$db['herd_summary']['username'] = 'myags_admin';//'webuser';
$db['herd_summary']['password'] = 'DHI4web*';//'m1$AgSourze';
$db['herd_summary']['database'] = "rpm";//"herd_summary";
$db['herd_summary']['dbdriver'] = "mssql";
$db['herd_summary']['dbprefix'] = "";
$db['herd_summary']['pconnect'] = FALSE;
$db['herd_summary']['db_debug'] = TRUE;
$db['herd_summary']['cache_on'] = TRUE;
$db['herd_summary']['cachedir'] = "";
$db['herd_summary']['char_set'] = "windows-1252";
$db['herd_summary']['dbcollat'] = "sql_latin1_general_cp1_ci_as";
$db['herd_summary']['swap_pre'] = '';
$db['herd_summary']['autoinit'] = TRUE;
$db['herd_summary']['stricton'] = FALSE;
/*
$db['custom_report']['hostname'] = "localhost";
$db['custom_report']['username'] = "ctranel"; // newdata
$db['custom_report']['password'] = "wahH0ahh"; // newdata
$db['custom_report']['database'] = "custom_report";
$db['custom_report']['dbdriver'] = "mysqli";
$db['custom_report']['dbprefix'] = "";
$db['custom_report']['pconnect'] = FALSE;
$db['custom_report']['db_debug'] = TRUE;
$db['custom_report']['cache_on'] = TRUE;
$db['custom_report']['cachedir'] = "";
$db['custom_report']['char_set'] = "utf8";
$db['custom_report']['dbcollat'] = "utf8_general_ci";
$db['custom_report']['swap_pre'] = '';
$db['custom_report']['autoinit'] = TRUE;
$db['custom_report']['stricton'] = FALSE;
*/
$db['fresh_cow_summary']['hostname'] = 'testdare.verona.crinet\\' . $db_server;
$db['fresh_cow_summary']['username'] = 'myags_admin';//'webuser';
$db['fresh_cow_summary']['password'] = 'DHI4web*';//'m1$AgSourze';
$db['fresh_cow_summary']['database'] = "rpm";//"fresh_cow_summary";
$db['fresh_cow_summary']['dbdriver'] = "mssql";
$db['fresh_cow_summary']['dbprefix'] = "";
$db['fresh_cow_summary']['pconnect'] = FALSE;
$db['fresh_cow_summary']['db_debug'] = TRUE;
$db['fresh_cow_summary']['cache_on'] = TRUE;
$db['fresh_cow_summary']['cachedir'] = "";
$db['fresh_cow_summary']['char_set'] = "windows-1252";
$db['fresh_cow_summary']['dbcollat'] = "sql_latin1_general_cp1_ci_as";
$db['fresh_cow_summary']['swap_pre'] = '';
$db['fresh_cow_summary']['autoinit'] = TRUE;
$db['fresh_cow_summary']['stricton'] = FALSE;

/*
$db['display']['hostname'] = 'testdare.verona.crinet\\' . $db_server;
$db['display']['username'] = 'webuser';//'myags_admin';
$db['display']['password'] = 'm1$AgSourze';//'DHI4web*';
$db['display']['database'] = "users";//"genetic_selection_guide";publication_name
$db['display']['dbdriver'] = "mssql";
$db['display']['dbprefix'] = "";
$db['display']['pconnect'] = FALSE;
$db['display']['db_debug'] = TRUE;
$db['display']['cache_on'] = TRUE;
$db['display']['cachedir'] = "";
$db['display']['char_set'] = "windows-1252";
$db['display']['dbcollat'] = "sql_latin1_general_cp1_ci_as";
$db['display']['swap_pre'] = '';
$db['display']['autoinit'] = TRUE;
$db['display']['stricton'] = FALSE;
*/
/* End of file database.php */
/* Location: ./system/application/config/database.php */