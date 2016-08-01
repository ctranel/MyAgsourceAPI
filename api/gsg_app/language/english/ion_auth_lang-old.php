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
$lang['account_creation_unsuccessful'] 	 	 = 'Unable to Create Account.  Please try again or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';
$lang['account_creation_duplicate_email'] 	 = 'Email Already Used or Invalid.  Please try again or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';
$lang['account_creation_duplicate_username'] 	 = 'Username Already Used or Invalid.  Please try again or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';
$lang['account_creation_invalid_herd_release_code'] 	 = 'The herd release code entered is not valid for the specified herd code.  Please try again or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';
$lang['account_creation_invalid_herd_code'] 	 = 'The herd code entered was not found.  Please try again or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';


// Password
$lang['password_change_successful'] 	 	 = 'Password Successfully Changed.  Please check your e-mail to retrieve your new password.';
$lang['password_change_unsuccessful'] 	  	 = 'Unable to Change Password.  Please try again or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';
$lang['forgot_password_successful'] 	 	 = 'Password Reset Email Sent';
$lang['forgot_password_unsuccessful'] 	 	 = 'Unable to Reset Password.  Please try again or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';

// Activation
$lang['activate_successful'] 		  	 = 'Account Activated';
$lang['activate_unsuccessful'] 		 	 = 'Unable to Activate Account.  Please try again or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';
$lang['deactivate_successful'] 		  	 = 'Account De-Activated';
$lang['deactivate_unsuccessful'] 	  	 = 'Unable to De-Activate Account.  Please try again or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';
$lang['activation_email_successful'] 	  	 = 'Activation Email Sent';
$lang['activation_email_unsuccessful']   	 = 'Unable to Send Activation Email.  Please try again or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';

// Login / Logout
$lang['login_successful'] 		  	 = 'Logged In Successfully';
$lang['login_unsuccessful'] 		  	 = 'In-Correct Login';
$lang['logout_successful'] 		 	 = 'Logged Out Successfully';

// Account Changes
$lang['update_successful'] 		 	 = 'Account Information Successfully Updated';
$lang['update_unsuccessful'] 		 	 = 'Unable to Update Account Information.  Please try again or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';
$lang['delete_successful'] 		 	 = 'User Deleted';
$lang['delete_unsuccessful'] 		 	 = 'Unable to Delete User.  Please try again or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';

// Consultant Access
$lang['consultant_status_update_successful'] 	  	 = 'Consultant Access Information Updated';
$lang['consultant_status_update_unsuccessful']   	 = 'Unable to Update Consultant Access Information.  Please try again or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';
$lang['consultant_status_email_successful'] 	  	 = 'Consultant Access Email Sent';
$lang['consultant_status_email_unsuccessful']   	 = 'Unable to Send Consultant Access Email.  Please try again or contact ' . $this->config->item('cust_serv_company') . ' at ' . $this->config->item('cust_serv_email') . ' or ' . $this->config->item('cust_serv_phone') . '.';
$lang['consultant_request_recorded']   	 = 'Your access request has been recorded.';