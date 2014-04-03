<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Ion Auth Lang - English
*
* Author: Ben Edmunds
* 		  ben.edmunds@gmail.com
*         @benedmunds
*
* Location: http://github.com/benedmunds/ion_auth/
*
* Created:  03.14.2010
*
* Description:  English language file for Ion Auth messages and errors
*
*/

// Account Creation
$lang['account_creation_successful'] 	  	 = 'Account Successfully Created';
$lang['account_creation_unsuccessful'] 	 	 = 'Unable to Create Account';
$lang['account_creation_duplicate_email'] 	 = 'Email Already Used or Invalid';
$lang['account_creation_duplicate_username'] = 'Username Already Used or Invalid';

// Password
$lang['password_change_successful'] 	 	 = 'Password Successfully Changed';
$lang['password_change_unsuccessful'] 	  	 = 'Unable to Change Password';
$lang['forgot_password_successful'] 	 	 = 'Password Reset Email Sent';
$lang['forgot_password_unsuccessful'] 	 	 = 'Unable to Reset Password';

// Activation
$lang['activate_successful'] 		  	     = 'Account Activated';
$lang['activate_unsuccessful'] 		 	     = 'Unable to Activate Account';
$lang['deactivate_successful'] 		  	     = 'Account De-Activated';
$lang['deactivate_unsuccessful'] 	  	     = 'Unable to De-Activate Account';
$lang['activation_email_successful'] 	  	 = 'Activation Email Sent';
$lang['activation_email_unsuccessful']   	 = 'Unable to Send Activation Email';

// Login / Logout
$lang['login_successful'] 		  	         = 'Logged In Successfully';
$lang['login_unsuccessful'] 		  	     = 'Incorrect Login';
$lang['login_unsuccessful_not_active'] 		 = 'Account is inactive';
$lang['logout_successful'] 		 	         = 'Logged Out Successfully';

// Register
$lang['register_successful'] 		  	     = 'Registered Successfully';
$lang['register_unsuccessful'] 		  	     = 'Registration Failed';

// Account Changes
$lang['update_successful'] 		 	         = 'Account Information Successfully Updated';
$lang['update_unsuccessful'] 		 	     = 'Unable to Update Account Information';
$lang['delete_successful'] 		 	         = 'User Deleted';
$lang['delete_unsuccessful'] 		 	     = 'Unable to Delete User';

//herd code
$lang['account_creation_invalid_herd_release_code']	= 'Invalid Herd Release Code';
$lang['account_creation_invalid_herd_code'] 		= 'Invalid Herd Code';

//consultant
$lang['consultant_status_email_successful']			= 'Request sent successfully';
$lang['consultant_status_email_unsuccessful'] 		= 'Unable to send request';
$lang['consultant_status_update_successful'] 		= 'Consultant record updated';
$lang['consultant_status_update_unsuccessful'] 		= 'Consultant record update failed';
$lang['consultant_request_recorded']				= 'Your herd access request was recorded';
$lang['service_group_required'] 					= 'Service group account is required';
$lang['service_group_not_found'] 					= 'The service group account entered was not found';
$lang['relationship_exists'] 						= 'You already have a relationship with this herd.  Please enter another herd or go to the &quot;Manage Herd Access&quot; page to modify your current access to this herd.';
