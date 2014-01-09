<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Name:  AS Ion Auth
* Author: Chris Tranel
* Description:  Extends Ben Edmunds Ion Auth library.
* Requirements: PHP5.3 or above
*/
/* -----------------------------------------------------------------
 *  UPDATE comment
 *  @author: carolmd
 *  @date: Dec 9, 2013
 *
 *  @description: removed strval function from all the is_<group name> functions. Group_id is int.
 *  
 *  @date: Jan 07, 2014
 *  @description: changed the is_xxx (is_admin, is_producer etc) to use == instead of === due to differences in datatypes.
 *  
 *  -----------------------------------------------------------------
 */

require_once APPPATH . 'libraries/Ion_auth.php';
class As_ion_auth extends Ion_auth {

	/**
	 * is_manager
	 *
	 * @var boolean
	 **/
	public $is_manager;

	/**
	 * is_admin
	 *
	 * @var boolean
	 **/
	public $is_admin;

	/**
	 * referrer
	 *
	 * @var string
	 **/
	public $referrer;

	/**
	 * super_section_id
	 *
	 * @var string
	 **/
	public $super_section_id;

	/**
	 * arr_groups
	 *
	 * @var array id=>name
	 **/
//	public $arr_groups;

	/**
	 * arr_task_permissions
	 *
	 * @var array
	 **/
	public $arr_task_permissions;

	/**
	 * arr_user_super_sections
	 *
	 * @var array
	 **/
	public $arr_user_super_sections;

	/**
	 * arr_user_sections
	 *
	 * @var array
	 **/
	public $arr_user_sections;

	/**
	 * gsg_access
	 *
	 * @var string
	 **/
	public $gsg_access;
	
	public function __construct(){
		//if (!$this->logged_in() && get_cookie('identity') && get_cookie('remember_code')){
			//$this->arr_task_permissions = $this->ion_auth_model->get_task_permissions('3');
		//}
		parent::__construct();
		$this->load->library('herd_manage');
		$this->load->model('access_log_model');
		$this->load->model('region_model');
		$this->load->model('herd_model');
		$this->load->helper('url');

		//set supersection if there is one
		$section_path = $this->router->fetch_class(); //this should match the name of this file (minus ".php".  Also used as base for css and js file names and model directory name
		if($this->uri->segment(1) != $section_path){
			$this->super_section_id = $this->ion_auth_model->get_super_section_id_by_path($this->uri->segment(1));
		} 
		//reliably set the referrer
		$this->referrer = $this->session->userdata('referrer');
		$tmp_uri= $this->uri->uri_string();
		if($this->session->userdata('referrer') != $tmp_uri && strpos($tmp_uri, 'ajax') === FALSE) $this->session->set_userdata('referrer', $tmp_uri);
		
		$this->is_admin = $this->is_admin();
		$this->is_manager = $this->is_manager();
		if($this->logged_in()){
			$this->arr_task_permissions = $this->ion_auth_model->get_task_permissions();
			$tmp = $this->session->userdata('herd_code');
			$arr_scope = array('subscription','public','unmanaged');
			if($this->is_admin) $arr_scope[] = 'admin';
			/* -----------------------------------------------------------------
			 *  UPDATE comment
			 *  @author: carolmd
			 *  @date: Nov 19, 2013
			 *
			 *  @description: changed so it gets all super-sections for all users.
			 *  This change made the topmost menu show only one set of super-sections. 
			 *  Before, it was showing 2 or 3 sets.
			 *  
			 *  -----------------------------------------------------------------
			 */
			//$this->arr_user_super_sections = $this->get_super_sections_array($this->session->userdata('active_group_id'), $this->session->userdata('user_id'), $this->session->userdata('herd_code'), $arr_scope);
			$this->arr_user_super_sections = $this->get_super_sections();
			$this->arr_user_sections = $this->get_sections_array($this->session->userdata('active_group_id'), $this->session->userdata('user_id'), $this->session->userdata('herd_code'), $this->super_section_id, $arr_scope);
		}
		if(!isset($tmp) || empty($tmp) !== FALSE) {
			$this->session->set_userdata('herd_code', $this->config->item('default_herd', 'ion_auth'));
			$this->session->set_userdata('arr_pstring', $this->herd_model->get_pstring_array($this->config->item('default_herd', 'ion_auth'), FALSE));
		}
	}
	
	//accessor
	public function get_super_section_id(){
		return $this->super_section_id;
	}

	//overridden functions below
	/**
	 * @method logout
	 *
	 * @return boolean/void
	 * @author Chris Tranel
	 **/
	public function logout()
	{
		$this->session->unset_userdata('arr_groups');
		$this->session->unset_userdata('active_group_id');
		$this->session->unset_userdata('herd_code');
		$this->session->unset_userdata('arr_pstring');
		$this->session->unset_userdata('first_name');
		$this->session->unset_userdata('last_name');
//		$this->session->unset_userdata('company');
		$this->session->unset_userdata('phone');
		$this->session->unset_userdata('section_id');
		$this->session->unset_userdata('access_level');
		$this->session->unset_userdata('pstring');
		$this->session->unset_userdata('herd_size_code');
		$this->session->unset_userdata('all_breeds_code');

		return parent::logout();
	}

	/**
	 * @method is_admin
	 *
	 * @return bool
	 * @author Chris Tranel
	 **/
	public function is_admin() {
		/* -----------------------------------------------------------------
		 *  UPDATE comment
		 *  @author: carolmd
		 *  @date: Nov 22, 2013
		 *
		 *  @description: fixed this to return true or false.
		 *  
		 *  -----------------------------------------------------------------
		 */
		$active_group = $this->session->userdata('active_group_id');
		if ($active_group === $this->config->item('admin_group', 'ion_auth')) {
		return TRUE; 
		}
		else { return FALSE;
		}
	}
	/**
	 * @method is_producer
	 *
	 * @return bool
	 * @author Carol McCullough-Dieter
	 * @comment: Compares user's active_group_id to the config value for the producer group.
	 **/
	public function is_producer() {
		$active_group = $this->session->userdata('active_group_id');
		if ($active_group == $this->config->item('producer_group', 'ion_auth')) {
			return TRUE;
		}
		else { return FALSE;
		}
	}
	/**
	 * @method is_emrss
	 *
	 * @return bool
	 * @author Carol McCullough-Dieter
	 * @comment: Compares user's active_group_id to the config value for the emrss group.
	 **/
	public function is_emrss() {
		$active_group = $this->session->userdata('active_group_id');
		if ($active_group == $this->config->item('rss_group', 'ion_auth')) {
			return TRUE;
		}
		else { return FALSE;
		}
	}
	/**
	 * @method is_rss
	 *
	 * @return bool
	 * @author Carol McCullough-Dieter
	 * @comment: Compares user's active_group_id to the config value for the rss group.
	 **/
	public function is_rss() {
		$active_group = $this->session->userdata('active_group_id');
		if ($active_group == $this->config->item('rss_group', 'ion_auth')) {
			return TRUE;
		}
		else { return FALSE;
		}
	}
	/**
	 * @method is_consultant
	 *
	 * @return bool
	 * @author Carol McCullough-Dieter
	 * @comment: Compares user's active_group_id to the config value for the consultant group.
	 **/
	public function is_consultant() {
		$active_group = $this->session->userdata('active_group_id');
		if ($active_group == $this->config->item('consultant_group', 'ion_auth')) {
			return TRUE;
		}
		else { return FALSE;
		}
	}
	/**
	 * @method is_association
	 *
	 * @return bool
	 * @author Carol McCullough-Dieter
	 * @comment: Compares user's active_group_id to the config value for the association group.
	 **/
	public function is_association() {
		$active_group = $this->session->userdata('active_group_id');
		if ($active_group == $this->config->item('association_group', 'ion_auth')) {
			return TRUE;
		}
		else { return FALSE;
		}
	}
	/**
	 * @method is_field_tech
	 *
	 * @return bool
	 * @author Carol McCullough-Dieter
	 * @comment: Compares user's active_group_id to the config value for the field tech group.
	 **/
	public function is_field_tech() {
		$active_group = $this->session->userdata('active_group_id');
		if ($active_group == $this->config->item('field_tech_group', 'ion_auth')) {
			return TRUE;
		}
		else { return FALSE;
		}
	}
	/**
	 * @method is_manager
	 *
	 * @return bool
	 * @author Chris Tranel
	 **/
	/* -----------------------------------------------------------------
	 *  UPDATE comment
	 *  @author: carolmd
	 *  @date: Dec 6, 2013
	 *
	 *  @description: Revised function to not use an array, since active_group_id is always one value.
	 *  
	 *  -----------------------------------------------------------------
	 */
	public function is_manager() {
		$manager_group = $this->config->item('manager_group', 'ion_auth');
		return ($this->session->userdata('active_group_id') == $manager_group);
		//return $this->in_group($manager_group);
	}

	/**
	 * @method is_child_user()
	 * @param int child id
	 * @return boolean
	 * @access public
	 *
	 **/
	/* -----------------------------------------------------------------
	 *  UPDATE comment
	 *  @author: carolmd
	 *  @date: Dec 9, 2013
	 *
	 *  @description: revised so that only admin users are allowed to edit users.
	 *  
	 *  -----------------------------------------------------------------
	 */
	public function is_child_user($child_id){ //, $parent_id = FALSE
/*		if (!$parent_id){
			$parent_id = $this->session->userdata('user_id');
		} */
//		$obj_user = $this->ion_auth_model->user($this->session->userdata('user_id'))->row();
//		$obj_user->arr_groups = $this->ion_auth_model->get_users_group($obj_user->id);
		if($this->is_admin){
			return TRUE;
		}
		else {
			return FALSE;
		}
/* removed all this other logic. Only admin can edit users.
		if($this->is_manager){
			return ($this->ion_auth_model->is_child_producer_user_by_region($obj_user->region_id, $child_id) || $this->ion_auth_model->is_child_field_user_by_region($obj_user->region_id, $child_id));
		}
		if(in_array('5', $obj_user->arr_groups) || in_array('8', $obj_user->arr_groups)){
			return $this->ion_auth_model->is_child_producer_user_by_tech($obj_user->supervisor_num, $child_id);
		}
		else {
			return false;
		}
*/
	}

	/**
	 * @method get_child_users()
	 * @return array of child user objects
	 * @access public
	 *
	 **/
	/* -----------------------------------------------------------------
	 *  UPDATE comment
	 *  @author: carolmd
	 *  @date: Dec 9, 2013
	 *
	 *  @description: Revised so that only admin can edit users.
	 *  
	 *  -----------------------------------------------------------------
	 */
	public function get_child_users(){ //$user_id = FALSE
/*		if (!$user_id){
			$user_id = $this->session->userdata('user_id');
		} */
		
		//$obj_user = $this->ion_auth_model->user($this->session->userdata('user_id'))->row();
		//$obj_user->arr_groups = $this->ion_auth_model->get_users_group($obj_user->id);
		
		if($this->is_admin){
			return $this->ion_auth_model->users()->result_array();
		}
		else {
			return FALSE;
		}
/* Commented out all remaining code. No others can edit users.
 * 
		if($this->is_manager){
			$arr_tmp1 = $this->ion_auth_model->get_producer_users_by_region($obj_user->region_id)->result_array();
			$arr_tmp2 = $this->ion_auth_model->get_field_users_by_region($obj_user->region_id)->result_array();
			//call function from multid_array_helper.php
			return array_merge_distinct($arr_tmp1, $arr_tmp2);
		}
		if(in_array('5', $obj_user->arr_groups) || in_array('8', $obj_user->arr_groups)){
			return $this->ion_auth_model->get_producer_users_by_tech($obj_user->supervisor_num)->result_array();
		}
		else { // Genex groups or producers
			return array();
		}
*/
	}

	/**
	 * @method get_users_herds()
	 * @return mixed array of herds or boolean
	 * @access public
	 *
	 **/
	public function get_users_herds(){
		if($this->as_ion_auth->is_admin){
			return $this->herd_model->get_herds(null, null)->result_array();
		}
		else{
			$arr_herds = array();
			if($this->as_ion_auth->has_permission("View Region Herds")){
				$arr_regions = $this->ion_auth_model->get_child_regions_array();
				$arr_herds['region'] = $this->herd_model->get_herds_by_region($arr_regions)->result_array();
			}
			if($this->as_ion_auth->has_permission("View Non-owned Herds")){
				$arr_herds['user'] = $this->herd_model->get_herds_by_rep($this->session->userdata('user_id'))->result_array();
			}
			else {
				return $this->session->userdata('herd_code');
			}
			if(!empty($arr_herds)){
				$arr_combined = array();
				foreach($arr_herds as $v => $h){
					$arr_combined = array_merge_distinct($arr_combined, $h, 'farm_name');
				}
				return $arr_combined;
			}
		}
		return FALSE;
	}

	/**
	 * @method get_herds_by_group()
	 * @param string group name, defaults to logged in user's group
	 * @param array of regions, defaults to logged in user's region array
	 * @return mixed array of herds or boolean
	 * @access public
	 *
	 **/
	/* -----------------------------------------------------------------
	 *  UPDATE comment
	 *  @author: carolmd
	 *  @date: Dec 6, 2013
	 *
	 *  @description: Revised function to get herds by user (looking at users_herds table), 
	 *  				By region (looking at the users table and the herd.dbo.herd_id table.)
	 *                  Or all herds. 
	 *  
	 *                Modified case stmt to use values in CONFIG instead of hard coded values for group_id.
	 *                Added LIMIT value into input parameters.
	 *  -----------------------------------------------------------------
	 */
	public function get_herds_by_group($group_in = false, $region_arr_in = false, $limit_in = NULL){
		log_message('debug', 'DEBUG.......................libraries/as_ion_auth/get_herds_by_group('.$group_in.',  '.$limit_in.') ');
		If (!isset($group_in) or empty($group_in)) {
			// no group id -- fail this function.
			return FALSE;
		}
		switch($group_in){
			case $this->config->item('admin_group', 'ion_auth'):
				return $this->herd_model->get_herds($limit_in);
				break;
			case $this->config->item('producer_group', 'ion_auth'):
				return $this->herd_model->get_herds_by_user($this->session->userdata('user_id'), $limit_in);
				break;
			case $this->config->item('manager_group', 'ion_auth'):
				return $this->herd_model->get_herds_by_region($region_arr_in, $limit_in);
				break;
			case $this->config->item('field_tech_group', 'ion_auth'):
				return $this->herd_model->get_herds_by_user($this->session->userdata('user_id'), $limit_in);
				break;
			case $this->config->item('rss_group', 'ion_auth'):
				return $this->herd_model->get_herds_by_region($region_arr_in,$limit_in);
				break;
			case $this->config->item('consultant_group', 'ion_auth'):
				return $this->herd_model->get_herds_by_user($this->session->userdata('user_id'), $limit_in);
				break;
			case $this->config->item('association_group', 'ion_auth'):
				return $this->herd_model->get_herds_by_region($region_arr_in, $limit_in);
				break;
			case $this->config->item('emrss_group', 'ion_auth'):
				return $this->herd_model->get_herds($limit_in);
				break;
				default:
				return false;
		}
	}

	/**
	 * @method get_group_dropdown_data()
	 * @return array (key=>value) array of groups for populating options lists
	 * @access public
	 *
	 **/
	public function get_group_dropdown_data(){
		$ret_array = array();
		if($this->is_admin){
			$arr_group_obj = $this->ion_auth_model->groups()->result();
		}
		elseif($this->is_manager){
			$arr_group_obj = $this->ion_auth_model->get_child_groups($this->session->userdata('active_group_id'));
		}
		else{
			$arr_group_obj = (object) $this->session->userdata('arr_groups');
		}
		if(is_array($arr_group_obj)) {
			$ret_array[''] = "Select one";
			foreach($arr_group_obj as $g){
				$ret_array[$g->id] = $g->name;
			}
			return $ret_array;
		}
		elseif(is_object($arr_group_obj)) {
			return $arr_group_obj;
		}
		else {
			return false;
		}
	}

	/**
	 * @method get_region_dropdown_data()
	 * @return array 1d (key=>value) array of herds for populating options lists
	 * @access public
	 *
	 **/
	public function get_region_dropdown_data(){
		$ret_array = array();
		if($this->is_admin){
			$arr_group_obj = $this->region_model->get_regions();
		}
		else{
			/* ----  BEGIN debugging code - for testing only --------DEBUG_SEARCH_TAG
			 *  Remove before deploying
			 *  @author: carolmd
			 *  @date: Dec 12, 2013
			 *
			 */
			//echo 'carolmd is not admin ';
			//var_dump($this->session->userdata('arr_regions'));
				
			/* 
			 *  ----  END debugging code - for testing only------------------------------------
			 */
			$arr_group_obj = $this->region_model->get_region_by_field('id', $this->session->userdata('arr_regions'));
		}
		if(is_array($arr_group_obj)) {
			$ret_array[''] = "Select";
			foreach($arr_group_obj as $g){
				$ret_array[$g->id] = $g->region_name;
			}
			return $ret_array;
		}
		elseif(is_object($arr_group_obj)) {
			return $arr_group_obj;
		}
		else {
			return false;
		}
	}

	/**
	 * @method get_dhi_supervisor_dropdown_data()
	 * @param region number
	 * @return array 1d (key=>value) array of herds for populating options lists
	 * @access public
	 *
	 **/
	public function get_dhi_supervisor_dropdown_data($arr_region_id = FALSE){
		$arr_tech_obj = $this->ion_auth_model->get_dhi_supervisor_nums_by_region($arr_region_id);
		if(is_array($arr_tech_obj)) {
			$ret_array[''] = "Select one";
			foreach($arr_tech_obj as $g){
				$ret_array[$g->supervisor_num] = $g->supervisor_num;
			}
			return $ret_array;
		}
		elseif(is_object($arr_tech_obj)) {
			return $arr_tech_obj;
		}
		else {
			return false;
		}
	}

	/**
	 * @method has_permission
	 * @param string task name
	 * @return boolean
	 * @access public
	 *
	 **/
	public function has_permission($task_name){
		$tmp_array = $this->arr_task_permissions;//$this->session->userdata('arr_task_permissions');
		if(is_array($tmp_array) !== FALSE) return in_array($task_name, $tmp_array);
		else return FALSE;
	}

	/**
	 * @method subscribed_section
	 *
	 * @param int section id
	 * @return boolean - true if the user is signed up for the specified section
	 * @author Chris Tranel
	 **/
	public function subscribed_section($section_id){
		log_message('debug', 'DEBUG.......................libraries/as_ion_auth/subscribed_section('.$section_id.') ');
		
		return TRUE;
		$tmp_array = $this->arr_user_sections;
		if(isset($tmp_array) && is_array($tmp_array)){
			$this->load->helper('multid_array_helper');
			$tmp_arr_sections = array_extract_value_recursive('id', $tmp_array);
			return in_array($section_id, $tmp_arr_sections);
		}
		else return false;
	}


	/**
	 * @method get_promo_sections
	 *
	 * @param int user id
	 * @return array
	 * @author Chris Tranel
	 **/
	public function get_promo_sections($user_id = FALSE){
		log_message('debug', 'DEBUG.......................libraries/as_ion_auth/get_promo_sections('.$user_id.' ');
		if (!$user_id){
			$user_id = $this->session->userdata('user_id');
		}

		$arr_subscribed_sections = $this->arr_user_sections;
		// handle DM
		//$this->load->model('dm_model');
		//if($credentials = $this->dm_model->get_credentials()) $arr_subscribed_sections[] = array('name'=>'AgSourceDM');
		if(!is_array($arr_subscribed_sections)) return array();

		$this->load->helper('multid_array_helper');
		$arr_subscribed_sections = array_extract_value_recursive('name', $arr_subscribed_sections);
		$arr_all_sections = $this->ion_auth_model->get_herd_sections()->result_array();
		$arr_all_sections = array_extract_value_recursive('name', $arr_all_sections);
		//$arr_subscribed_sections[] = 'My Account';
		//$arr_subscribed_sections[] = 'Alert';

		return array_diff($arr_all_sections, $arr_subscribed_sections);
	}

/**
	 * @method record_section_inquiry()
	 * @param array of section names
	 * @param string comments
	 * @return boolean success
	 * @access public
	 *
	 **/
	public function record_section_inquiry($arr_sections_in, $comments){
		//send emails
		$data['sections'] = $arr_sections_in;
		$data['comments'] = $comments;
		$data['name'] = $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name');
		$data['email'] = $this->session->userdata('email');
		$herd_code = $this->session->userdata('herd_code');
		if(isset($herd_code) && !empty($herd_code)) $data['herd_code'] = $this->session->userdata('herd_code');

		$message = $this->load->view('auth/email/section_request.php', $data, true);
		$this->email->clear();
		$config['mailtype'] = $this->config->item('email_type', 'ion_auth');
		$this->email->initialize($config);
		$this->email->set_newline("\r\n");
		$this->email->from($this->config->item('admin_email', 'ion_auth'), $this->config->item('site_title', 'ion_auth'));
		$this->email->to($data['email']);
		$this->email->cc($this->config->item('cust_serv_email', 'ion_auth'));
		$this->email->subject($this->config->item('site_title', 'ion_auth') . ' - Product Inquiry');
		$this->email->message($message);

		if ($this->email->send()){
		//record in database?
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @method set_form_array()
	 * @param array of db result arrays
	 * @param string value field name
	 * @param string text field name
	 * @return array 1d (key=>value) array of herds for populating options lists
	 * @access public
	 *
	 **/
	public function set_form_array($array_in, $value_in, $text_in, $prepend_select = TRUE){
		$new_array = $prepend_select ? array(''=>'Select one') : array();
		if(is_array($array_in)) array_walk($array_in, create_function ('$value, $key, $obj', '$obj["obj_in"][$value[$obj["value_in"]]] = $value[$obj["text_in"]];'), array('obj_in' => &$new_array, 'value_in' => $value_in, 'text_in' => $text_in));
		return $new_array;
	}

	/**
	 * @method get_super_sections_array()
	 * @param int group id
	 * @param int user id
	 * @param string herd code
	 * @param array section scopes to include
	 * @return array 1d (key=>value) array of web sections for specified user or herd
	 * @access public
	 *
	 **/
	public function get_super_sections_array($group_id, $user_id, $herd_code, $arr_scope = NULL){
		if(isset($arr_scope) && is_array($arr_scope)){
			$tmp_array = array();
			foreach($arr_scope as $s){
				switch ($s) {
					case 'subscription':
						$a = $this->ion_auth_model->get_subscribed_super_sections_array($group_id, $user_id, $herd_code);
						if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
						break;
					case 'unmanaged':
						$a = $this->ion_auth_model->get_unmanaged_super_sections_array($group_id, $user_id, $herd_code);
						if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;
					default:
						$a = $this->ion_auth_model->get_super_sections_by_scope($s);
						if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;
				}
			}
		}
		else {
			$tmp_array = $this->ion_auth_model->get_subscribed_super_sections_array($group_id, $user_id, $herd_code);
		}
		if(!$this->has_permission("View Non-owned Herds") && $this->has_permission("View non-own w permission") && !$this->ion_auth_model->user_owns_herd($herd_code) && !empty($herd_code)){
			if(is_array($tmp_array) && !empty($tmp_array)){
				$arr_return = array();
				foreach($tmp_array as $k => $v){
					if($this->ion_auth_model->consultant_has_access($user_id, $herd_code, $v['id'])){
						$arr_return[] = $v;
					}
				}
				return $arr_return;
			}
			return FALSE;
		}

		else return $tmp_array;
	}

	/**
	 * @method get_sections_array()
	 * @param int group id
	 * @param int user id
	 * @param string herd code
	 * @param array section scopes to include
	 * @return array 1d (key=>value) array of web sections for specified user or herd
	 * @access public
	 *
	 **/
	public function get_sections_array($group_id, $user_id, $herd_code, $super_section_id = NULL, $arr_scope = NULL){
		if(isset($arr_scope) && is_array($arr_scope)){
			$tmp_array = array();
			foreach($arr_scope as $s){
				switch ($s) {
					case 'subscription':
						$a = $this->ion_auth_model->get_subscribed_sections_array($group_id, $user_id, $super_section_id, $herd_code);
						if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
						break;
					case 'unmanaged':
						$a = $this->ion_auth_model->get_unmanaged_sections_array($group_id, $user_id, $herd_code);
						if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;
//SKIP PUBLIC SCOPE FOR NOW
					case 'public':
						break;
					default:
						$a = $this->ion_auth_model->get_sections_by_scope($s);
						if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
					break;
				}
			}
		}
		else {
			$tmp_array = $this->ion_auth_model->get_subscribed_sections_array($group_id, $user_id, $super_section_id, $herd_code);
		}
		if(!$this->has_permission("View Non-owned Herds") && $this->has_permission("View non-own w permission") && !$this->ion_auth_model->user_owns_herd($herd_code) && !empty($herd_code)){
			if(is_array($tmp_array) && !empty($tmp_array)){
				$arr_return = array();
				foreach($tmp_array as $k => $v){
					if($this->ion_auth_model->consultant_has_access($user_id, $herd_code, $v['id'])){
						$arr_return[] = $v;
					}
				}
				return $arr_return;
			}
			return FALSE;
		}

		else return $tmp_array;
	}

	/**
	 * @method group_assignable()
	 * @param int form group id -- group id for which change is being attempted
	 * @return boolean
	 * @access public
	 * @todo fix this to allow only administrators? to assign a user's group??
	 * @todo change to accept single value instead of array.
	 *
	 **/
	public function group_assignable($arr_form_group_id){
		$session_group_id = $this->session->userdata('active_group_id');
		if($this->logged_in() === FALSE){
			$arr_tmp = array_intersect($arr_form_group_id, array($this->config->item('producer_group', 'ion_auth'), $this->config->item('consultant_group', 'ion_auth'))); //if select groups include only 2 and 9 (producer and consultant)
			if(count($arr_tmp) == count($arr_form_group_id)) return TRUE;
		}
		elseif($this->is_admin) return TRUE;
		elseif(in_array($session_group_id, $arr_form_group_id) && count($arr_form_group_id) == 1) return TRUE;
		elseif(!in_array($session_group_id, $arr_form_group_id) && count($arr_form_group_id) == 1 && $this->has_permission("Manage Other Accounts") === FALSE) {
			return FALSE;
		}
		elseif((!in_array($session_group_id, $arr_form_group_id) || count($arr_form_group_id) > 1) && $this->has_permission("Manage Other Accounts") === TRUE) {
			switch ($session_group_id) {
				case 1:
					return TRUE;
					break;
				case 5:
					$arr_tmp = array_intersect($arr_form_group_id, 
						array($this->config->item('producer_group', 'ion_auth'), 
							  $this->config->item('field_tech_group', 'ion_auth'), 
							  $this->config->item('consultant_group', 'ion_auth')));
					if(count($arr_tmp) == count($arr_form_group_id)) return TRUE;
//					if($arr_form_group_id == 2 || $arr_form_group_id == 5 || $arr_form_group_id == 9) return TRUE;
					break;
				case 8:
					$arr_tmp = array_intersect($arr_form_group_id, array($this->config->item('producer_group', 'ion_auth'), $this->config->item('consultant_group', 'ion_auth')));
					if(count($arr_tmp) == count($arr_form_group_id)) return TRUE;
//					if($arr_form_group_id == 2 || $arr_form_group_id == 8 || $arr_form_group_id == 9) return TRUE;
					break;
				default:
					return FALSE;
					break;
			}
		}
		return FALSE;
	}

	/**
	 * @method allow_consult()
	 * @abstract write consultant-herd relationship record
	 * @param string consultant user id
	 * @param string herd code
	 * @param array sections for which consultant should be give permissions
	 * @return boolean
	 * @access public
	 *
	 **/
	public function allow_consult($arr_relationship_data, $arr_section_id) {
		$old_relationship_id = $this->ion_auth_model->get_consult_relationship_id($arr_relationship_data['consultant_user_id'], $arr_relationship_data['herd_code']);
		//insert into consulants_herds
		$relationship_id = $this->ion_auth_model->set_consult_relationship($arr_relationship_data, $old_relationship_id);
		//insert each section into consulants_herds_sections
		if($relationship_id !== FALSE){
			$success = $this->ion_auth_model->set_consult_sections($arr_section_id, $relationship_id, $old_relationship_id);
			if($success){
				$this->set_message('consultant_status_update_successful');
				//send e-mail
				$arr_herd_info = $this->herd_model->header_info($this->session->userdata('herd_code'));
				$consultant_info = $this->ion_auth_model->user($arr_relationship_data['consultant_user_id'])->result_array();
				$consultant_info = $consultant_info[0];

				if($arr_relationship_data['request_status_id'] == 1) $message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('consult_granted', 'ion_auth'), $arr_herd_info, true);
				elseif($arr_relationship_data['request_status_id'] == 2) $message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('consult_denied', 'ion_auth'), $arr_herd_info, true);

				if(isset($message)){
					$this->email->clear();
					$this->email->from($this->config->item('admin_email', 'ion_auth'), $this->config->item('site_title', 'ion_auth'));
					$this->email->to($consultant_info['email']);
					$this->email->cc($this->session->userdata('email'));
					$this->email->subject($this->config->item('site_title', 'ion_auth') . ' - Consultant Access');
					$this->email->message($message);

					if ($this->email->send() == TRUE) {
						$this->set_message('consultant_status_email_successful');
						return TRUE;
					}
				}
				$this->set_error('consultant_status_email_unsuccessful');
				return TRUE; // Even if e-mail is not sent, consultant info was recorded
			}
		}
		$this->set_error('consultant_status_update_unsuccessful');
		return FALSE;
	}

	/**
	 * @method consult_request()
	 * @abstract write consultant-herd relationship record
	 * @param string herd code
	 * @param int consultant's user id
	 * @param array sections for which consultant should be give permissions
	 * @param date expiration date for access
	 * @return boolean
	 * @access public
	 *
	 **/
	public function consult_request($arr_relationship_data, $arr_section_id) {
		$old_relationship_id = $this->ion_auth_model->get_consult_relationship_id($arr_relationship_data['consultant_user_id'], $arr_relationship_data['herd_code']);
		//insert into consulants_herds
		$relationship_id = $this->ion_auth_model->set_consult_relationship($arr_relationship_data, $old_relationship_id);
		//insert each section into consulants_herds_sections
		if($relationship_id !== FALSE){
			$success = $this->ion_auth_model->set_consult_sections($arr_section_id, $relationship_id, $old_relationship_id);
			if($success){
				$this->set_message('consultant_request_recorded');
				$this->send_consultant_request($arr_relationship_data, $relationship_id);

				return TRUE; // Even if e-mail is not sent, consultant info was recorded
			}
		}
		return FALSE;
	}
	
	/**
	 * @method send_consultant_request()
	 * @abstract write consultant-herd relationship record
	 * @param string herd code
	 * @param int consultant's user id
	 * @param array sections for which consultant should be give permissions
	 * @param date expiration date for access
	 * @return boolean
	 * @access public
	 * @todo remove "die" before uploading to server
	 **/
	function send_consultant_request($arr_relationship_data, $relationship_id){
		//send e-mail
		$consultant_info = $this->ion_auth_model->user($arr_relationship_data['consultant_user_id'])->result_array();
		$consultant_info[0]['relationship_id'] = $relationship_id;
		$message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('consult_request', 'ion_auth'), $consultant_info[0], TRUE);
		$arr_herd_emails = $this->herd_model->get_herd_emails($arr_relationship_data['herd_code']);
		$arr_herd_emails = array_flatten($arr_herd_emails);

		if(isset($message)){
			$this->email->clear();
			$this->email->from($this->config->item('admin_email', 'ion_auth'), $this->config->item('site_title', 'ion_auth'));
			$this->email->to($arr_herd_emails);
			$this->email->cc($this->session->userdata('email'));
			$this->email->subject($this->config->item('site_title', 'ion_auth') . ' - Consultant Access');
			$this->email->message($message);

			if ($this->email->send() == TRUE) {
				$this->set_message('consultant_status_email_successful');
				return TRUE;
			}
		}
		$this->set_error('consultant_status_email_unsuccessful');
die($message);
		return FALSE;
	}

	//WHEN LOOKING UP HERDS FOR CONSULTANTS, ENSURE THAT IT IS NOT EXPIRED, AND THAT IT HAS BEEN APPROVED
}
