<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Ion Auth Config
*
* Author: Ben Edmunds
* 		  ben.edmunds@gmail.com
*         @benedmunds
*
* Added Awesomeness: Phil Sturgeon
*
* Location: http://github.com/benedmunds/ion_auth/
*
* Created:  10.01.2009
*
* Description:  Modified auth system based on redux_auth with extensive customization.  This is basically what Redux Auth 2 should be.
* Original Author name has been kept but that does not mean that the method has not been modified.
*
*/
	$config['use_mongodb'] = FALSE;
	$config['collections']['users']          	= 'users';
	$config['collections']['groups']         	= 'groups';
	$config['collections']['login_attempts'] 	= 'login_attempts';
	/**
	 * Tables.
	 **/
	//USERS DB tables
	$config['tables']['lookup_display_types']	= 'users.dbo.lookup_display_types';
	$config['tables']['lookup_chart_types'] 	= 'users.dbo.lookup_chart_types';
	$config['tables']['lookup_scopes'] 			= 'users.dbo.lookup_scopes';
	$config['tables']['consultants_herds']    	= 'users.dbo.service_groups_herds';
	$config['tables']['users_service_groups']   = 'users.dbo.users_service_groups';
	$config['tables']['service_groups']    		= 'address.dbo.service_group';
	$config['tables']['super_sections']    		= 'users.dbo.super_sections';
	$config['tables']['sections']    			= 'users.dbo.sections';
	$config['tables']['pages']  				= 'users.dbo.pages';
	$config['tables']['pages_blocks']  			= 'users.dbo.pages_blocks';
	$config['tables']['blocks']  				= 'users.dbo.blocks';
	$config['tables']['access_log']  			= 'users.dbo.access_log';
	$config['tables']['users_sections']    		= 'users.dbo.users_sections';
	$config['tables']['groups']  				= 'users.dbo.groups';
	$config['tables']['users_dhi_supervisors']  = 'address.dbo.dhi_supervisor';
	$config['tables']['regions']  				= 'address.dbo.association';//users.dbo.regions';
	$config['tables']['users']   				= 'users.dbo.users';
	$config['tables']['users_groups']    		= 'users.dbo.users_groups';
	$config['tables']['meta']    				= 'users_meta'; 
	$config['tables']['users_herds']    		= 'users.dbo.users_herds';
	$config['tables']['login_attempts']  		= 'users.dbo.login_attempts';
	$config['tables']['tasks']  				= 'users.dbo.tasks';
	$config['tables']['groups_tasks']  			= 'users.dbo.groups_tasks';
	$config['tables']['herds_sections']    		= 'users.dbo.herds_sections';
	$config['tables']['herds_regions']    		= 'users.dbo.herds_regions';
	
	//HERD DB tables
	$config['tables']['herds']    				= 'herd.dbo.herd_id';

	//vma DB views
	$config['tables']['vma_Dates_Last_7_Tests']			= 'vma.dbo.vma_Dates_Last_7_Tests'; // Kevin - for dynamic test_date table headers
		
	/**
	 * Meta sections to be included with profile.
	 * Each should have a config setting for 'towrite' 'tables', 'join' and 'columns'
	 **/
	$config['meta_sections']         = array('users_herds', 'users_sections');//'meta', 
	$config['herd_meta_sections']         = array('herds_sections');

	/**
	 * Fields from meta table that must be present in order to write to the meta table
	 * Note that these fields are not required on the user record
	 **/
	$config['towrite'] = array(
		'users_herds'		=> array('herd_code'),
		//'dhi_supervisor'=> array('supervisor_num','region_id'),
		//'meta'		=> array(),
	 	'users_sections'=> array('section_id', ''),
		'herds_sections'=> array('section_id')
	);

/*
 | table column you want to join WITH.
 |
 | "meta sections" need to be user_id, "herd_meta_sections" need to be herd_code ("groups" are neither)
 */
	$config['join'] = array(
		'groups'	=> 'group_id',//from ion_auth
		'users'		=> 'user_id', //from ion_auth
		'users_herds'		=> 'user_id',
//		'users_dhi_supervisors'=> 'user_id',
		//'meta'		=> 'user_id',
		'users_sections'=> 'user_id',
		'herds_sections'=> 'herd_code'
	);
	/**
	 * Columns in your meta table,
	 * id not required.
	 **/
	$config['columns'] = array(
		'users_herds'		=> array('herd_code'),
//		'dhi_supervisor'=> array('supervisor_num', 'region_id'),
		//'meta'		=> array('first_name', 'last_name', 'company', 'phone'),
		'users_sections'=> array('section_id', 'access_level'),
		'herds_sections'=> array('section_id', 'access_level')
	);
	
/*
 | -------------------------------------------------------------------------
 | Hash Method (sha1 or bcrypt)
 | -------------------------------------------------------------------------
 | Bcrypt is available in PHP 5.3+
 |
 | IMPORTANT: Based on the recommendation by many professionals, it is highly recommended to use
 | bcrypt instead of sha1.
 |
 | NOTE: If you use bcrypt you will need to increase your password column character limit to (80)
 |
 | Below there is "default_rounds" setting.  This defines how strong the encryption will be,
 | but remember the more rounds you set the longer it will take to hash (CPU usage) So adjust
 | this based on your server hardware.
 |
 | If you are using Bcrypt the Admin password field also needs to be changed in order login as admin:
 | $2a$07$SeBknntpZror9uyftVopmu61qg0ms8Qv1yV6FG.kQOSM.9QhmTo36
 |
 | Becareful how high you set max_rounds, I would do your own testing on how long it takes
 | to encrypt with x rounds.
 */
$config['hash_method']    = 'sha1';	// IMPORTANT: Make sure this is set to either sha1 or bcrypt
$config['default_rounds'] = 8;		// This does not apply if random_rounds is set to true
$config['random_rounds']  = FALSE;
$config['min_rounds']     = 5;
$config['max_rounds']     = 9;

/*
 | -------------------------------------------------------------------------
 | Authentication options.
 | -------------------------------------------------------------------------
 | maximum_login_attempts: This maximum is not enforced by the library, but is
 | used by $this->ion_auth->is_max_login_attempts_exceeded().
 | The controller should check this function and act
 | appropriately. If this variable set to 0, there is no maximum.
 */
$config['default_herd']			="35999923";
$config['site_title']           = "MyAgSource"; 		// Site Title, example.com
$config['cust_serv_company']	= "AgSource Cooperative Services"; //custom CDT

/* 
 * email and phone numbers vary by environment.
 */

switch (ENVIRONMENT)
{
	case 'development':
		$config['admin_email']          = "ghartmann@agsource.com"; 	// Admin Email, admin@example.com
		$config['cust_serv_email']		= "ghartmann@agsource.com"; //custom CDT
		$config['cust_serv_phone']		= "1-800-236-0097"; //custom CDT
		break;
	case 'testing':
		$config['admin_email']          = "ghartmann@agsource.com"; 	// Admin Email, admin@example.com
		$config['cust_serv_email']		= "ghartmann@agsource.com"; //custom CDT
		$config['cust_serv_phone']		= "1-800-236-0097"; //custom CDT
		break;
	case 'production':
		$config['admin_email']          = "support@myagsource.com"; 	// Admin Email, admin@example.com
		$config['cust_serv_email']		= "cust_service@myagsource.com"; //custom CDT
		$config['cust_serv_phone']		= "1-800-236-0097"; //custom CDT		
		break;
	default:
		exit('The application environment is not set correctly - t_base_url.');
}

$config['default_group']        = 'Producer'; 			// Default group, use name
$config['default_group_id']     = 2;
$config['admin_group']          = 1; 				// Default administrators group, use name
$config['manager_group']        = 3; //custom CDT

$config['identity']             = 'email'; 				// A database column which is used to login with
$config['min_password_length']  = 8; 					// Minimum Required Length of Password
$config['max_password_length']  = 20; 					// Maximum Allowed Length of Password
$config['email_activation']     = TRUE; 				// Email Activation for registration
$config['manual_activation']    = FALSE; 				// Manual Activation for registration
$config['remember_users']       = TRUE; 				// Allow users to be remembered and enable auto-login
$config['user_expire']          = (60 * 60 * 24 * 90); 	//90 Days				// How long to remember the user (seconds)
$config['user_extend_on_login'] = TRUE; 				// Extend the users cookies everytime they auto-login
$config['track_login_attempts'] = FALSE;				// Track the number of failed login attempts for each user or ip.
$config['maximum_login_attempts']     = 3; 				// The maximum number of failed login attempts.
$config['forgot_password_expiration'] = 0; 				// The number of seconds after which a forgot password request will expire. If set to 0, forgot password requests will not expire.

/*
 | -------------------------------------------------------------------------
 | Email options.
 | -------------------------------------------------------------------------
 | email_config:
 | 	  'file' = Use the default CI config or use from a config file
 | 	  array  = Manually set your email config settings
 */
$config['use_ci_email'] = TRUE; // Send Email using the builtin CI email class, if false it will return the code and the identity
$config['email_config'] = array(
	'mailtype' => 'html',
);

/*
 | -------------------------------------------------------------------------
 | Email templates.
 | -------------------------------------------------------------------------
 */
$config['email_templates'] = 'auth/email/';		//Folder where email templates are stored.
$config['email_activate'] = 'activate.tpl.php';	//Activate Account Email Template
$config['email_forgot_password'] = 'forgot_password.tpl.php'; //Forgot Password Email Template
$config['email_forgot_password_complete'] = 'new_password.tpl.php'; //Forgot Password Complete Email Template
$config['consult_granted']   = 'consult_granted.tpl.php';	//grant consultant access to herd
$config['consult_denied']   = 'consult_denied.tpl.php';		//deny consultant access to herd
$config['consult_request']   = 'consult_request.tpl.php';	//consultants' request to access herd

/*
 | -------------------------------------------------------------------------
 | Salt options
 | -------------------------------------------------------------------------
 | salt_length Default: 10
 |
 | store_salt: Should the salt be stored in the database?
 | This will change your password encryption algorithm,
 | default password, 'password', changes to
 | fbaa5e216d163a02ae630ab1a43372635dd374c0 with default salt.
 */
$config['salt_length'] = 10;
$config['store_salt']  = FALSE;

/*
 | -------------------------------------------------------------------------
 | Message Delimiters.
 | -------------------------------------------------------------------------
 */
$config['message_start_delimiter'] = '<p>'; 	// Message start delimiter
$config['message_end_delimiter']   = '</p>'; 	// Message end delimiter
$config['error_start_delimiter']   = '<p>';		// Error mesage start delimiter
$config['error_end_delimiter']     = '</p>';	// Error mesage end delimiter
	 
/* End of file ion_auth.php */
/* Location: ./application/config/ion_auth.php */
