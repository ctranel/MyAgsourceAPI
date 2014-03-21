<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Name:  AS Ion Auth
* Author: ctranel
* Description:  Extends Ben Edmunds Ion Auth library.
* Requirements: PHP5.3 or above
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

		//reliably set the referrer, used when determining whether to set the redirect variable on pages like select herd
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
			$this->arr_user_super_sections = $this->get_super_sections_array($this->session->userdata('active_group_id'), $this->session->userdata('user_id'), $this->session->userdata('herd_code'), $arr_scope);
			$this->arr_user_sections = $this->get_sections_array($this->session->userdata('active_group_id'), $this->session->userdata('user_id'), $this->session->userdata('herd_code'), array($this->super_section_id), $arr_scope);
		}
		elseif($this->session->userdata('herd_code') == $this->config->item('default_herd')) {
/*			$this->session->set_userdata('herd_code', $this->config->item('default_herd'));
			$this->session->set_userdata('arr_pstring', $this->herd_model->get_pstring_array($this->config->item('default_herd'), FALSE));
			//$this->session->set_userdata('active_group_id', 2);
*/			$arr_scope = array('subscription','public','unmanaged');
			//the first parameter is group id--use producer (2) if no one is logged in, second param is user id. 
			$this->arr_user_super_sections = $this->get_super_sections_array(2, $this->session->userdata('user_id'), $this->session->userdata('herd_code'), $arr_scope);
			$this->arr_user_sections = $this->get_sections_array(2, $this->session->userdata('user_id'), $this->session->userdata('herd_code'), array($this->super_section_id), $arr_scope);
		}
	}
	
	//accessor
	public function get_super_section_id(){
		return $this->super_section_id;
	}

	//overridden functions below
	/**
	 * @method register
	 *
	 * @return boolean/void
	 * @author ctranel
	 **/
	public function register($username, $password, $email, $additional_data = array(), $group_name = array()) {
		$id = parent::register($username, $password, $email, $additional_data, $group_name);
		$herd_code = $additional_data['herd_code'];
		
		$this->load->model('tech_model');
		
		if($id && isset($herd_code) && !empty($herd_code)){
			$data = array(
				'email'     => $email,
				'herd_code'	=> $herd_code,
				'phone'		=> $additional_data['phone'],
				'best_time'	=> $additional_data['best_time'],
				'arr_herd'	=> $this->herd_model->get_herd($herd_code),
				'arr_tech'	=> $this->tech_model->get_tech_by_herd($herd_code),
			);
			if(!$this->config->item('use_ci_email', 'ion_auth')) {
				return $data;
			}
			else {
				$message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('user_herd_data', 'ion_auth'), $data, true);
				$this->email->clear();
				$this->email->from($this->config->item('admin_email'), $this->config->item('site_title'));
				$this->email->to($this->config->item('cust_serv_email'));
				$this->email->subject($this->config->item('site_title') . ' - Account Activation Info');
				$this->email->message($message);
				$this->email->send();
			}
		}
		return $id;
	}
	
	
	/**
	 * @method logout
	 *
	 * @return boolean/void
	 * @author ctranel
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
	 * @author ctranel
	 **/
	public function is_admin() {
		return $this->session->userdata('active_group_id') === $this->config->item('admin_group', 'ion_auth');
	}

	/**
	 * @method is_manager
	 *
	 * @return bool
	 * @author ctranel
	 **/
	public function is_manager() {
		return $this->session->userdata('active_group_id') === $this->config->item('manager_group', 'ion_auth');
	}

	/**
	 * @method is_editable_user()
	 * @param int user id
	 * @return boolean
	 * @access public
	 *
	 **/
	public function is_editable_user($user_id, $logged_user_id){
		$obj_user = $this->ion_auth_model->user($user_id)->row();
		//$obj_user->arr_groups = array_keys($this->ion_auth_model->get_users_group_array($obj_user->id));
		if($this->has_permission('Edit All Users') || $user_id == $logged_user_id){
			return TRUE;
		}
		if($this->has_permission('Edit Users In Region')){
			return ($this->ion_auth_model->is_child_user_by_association($obj_user->assoc_acct_num, $user_id));
		}
		if($this->has_permission('Edit Producers In Region')){
			return $this->ion_auth_model->is_child_user_by_group_and_association(2, $obj_user->assoc_acct_num, $user_id);
		}
		else {
			return false;
		}
	}

	/**
	 * @method get_editable_users()
	 * @return array of child user objects
	 * @access public
	 *
	 **/
	public function get_editable_users(){
		$obj_user = $this->ion_auth_model->user($this->session->userdata('user_id'))->row();
		//$obj_user->arr_groups = array_keys($this->ion_auth_model->get_users_group_array($obj_user->id));
		
		if($this->has_permission('Edit All Users')){
			return $this->ion_auth_model->users()->result_array();
		}
		if($this->has_permission('Edit Users In Region')){
			$arr_tmp2 = $this->ion_auth_model->get_users_by_association($obj_user->assoc_acct_num)->result_array();
			return $arr_tmp2;
		}
		if($this->has_permission('Edit Producers In Region')){
			return $this->ion_auth_model->get_users_by_group_and_association(2, $obj_user->assoc_acct_num)->result_array();
		}
		else { // Genex groups or producers
			return array();
		}
	}

	/**
	 * @method get_viewable_herds()
	 * @param int user_id
	 * @param int region_num (need to accept array?)
	 * @return mixed array of herds or boolean
	 * @access public
	 *
	 **/
	public function get_viewable_herds($user_id, $region_num = false, $limit_in = NULL){
		$arr_return_reg = array();
		$arr_return_user = array();
		$arr_return_permission = array();
		
		if($this->has_permission('View All Herds')){
			return $this->herd_model->get_herds();
		}
		if($this->has_permission('View Herds In Region')){
			$arr_return_reg = $this->herd_model->get_herds_by_region($region_num, $limit_in);
		}
		if($this->has_permission('View Assigned Herds')){
			$arr_return_user = $this->herd_model->get_herds_by_user($user_id, $limit_in);
		}
		if($this->has_permission('View non-own w permission')){
			$arr_return_permission = $this->herd_model->get_herds_by_consultant($user_id, $limit_in);
		}
		return array_merge($arr_return_reg, $arr_return_user, $arr_return_permission);
	}

	/**
	 * @method get_viewable_herd_codes()
	 * @param int user_id
	 * @param int region_num (need to accept array?)
	 * @return mixed array of herds or boolean
	 * @access public
	 *
	 **/
	public function get_viewable_herd_codes($user_id, $region_num = false, $limit_in = NULL){
		$arr_return_reg = array();
		$arr_return_user = array();
		$arr_return_permission = array();
		
		if($this->has_permission('View All Herds')){
			return $this->herd_model->get_herd_codes(null, null, $limit_in);
		}
		if($this->has_permission('View Herds In Region')){
			$tmp = $this->herd_model->get_herd_codes_by_region($region_num, $limit_in);
			if(isset($tmp) && is_array($tmp)) $arr_return_reg = $tmp;
			unset($tmp);
		}
		if($this->has_permission('View Assigned Herds')){
			$arr_return_user = $this->herd_model->get_herd_codes_by_user($user_id, $limit_in);
		}
		if($this->has_permission('View non-own w permission')){
			$arr_return_permission = $this->herd_model->get_herd_codes_by_consultant($limit_in);
		}
		return array_merge($arr_return_reg, $arr_return_user, $arr_return_permission);
	}

	/**
	 * @method get_group_dropdown_data()
	 * @param int active group id
	 * @return array (key=>value) array of groups for populating options lists
	 * @access public
	 * 
	 *
	 **/
	public function get_group_dropdown_data($active_group_id){
		$arr_groups = array();
		$ret_array = array();
		if($this->has_permission('Add All Users')){
			$arr_groups = $this->ion_auth_model->get_active_groups()->result_array();
		}
		elseif($this->logged_in()){
			if($this->has_permission('Add Users In Region')){
				$arr_groups = $this->ion_auth_model->get_editable_groups($active_group_id);
			}
		}
		if(empty($arr_groups)){
			$arr_groups[] = array('id'=>'2', 'name'=>'Producer');
			$arr_groups[] = array('id'=>'9', 'name'=>'Consultant');
		}
		if(is_array($arr_groups)) {
			$ret_array[''] = "Select one";
			foreach($arr_groups as $g){
				$ret_array[$g['id']] = $g['name'];
			}
			return $ret_array;
		}
		else {
			return false;
		}
	}

	/**
	 * @method get_assoc_dropdown_data()
	 * @param array of association numbers to which the user currently has access (array_keys($this->session->userdata('arr_regions')) in CI)
	 * @return array 1d (key=>value) array of herds for populating options lists
	 * @access public
	 *
	 **/
	public function get_assoc_dropdown_data($arr_users_regions){
		$ret_array = array();
		if($this->has_permission("Edit All Users") || $this->has_permission("Add All Users")){
			$arr_assn_obj = $this->region_model->get_regions();
		}
		else{
			$arr_assn_obj = $this->region_model->get_region_by_field('assoc_acct_num', $arr_users_regions);
		}
		if(is_array($arr_assn_obj)) {
			$ret_array[''] = "Select";
			foreach($arr_assn_obj as $g){
				$ret_array[$g->account_num] = $g->assoc_name;
			}
			return $ret_array;
		}
		elseif(is_object($arr_assn_obj)) {
			return $arr_assn_obj;
		}
		else {
			return false;
		}
	}

	/**
	 * @method get_dhi_supervisor_dropdown_data()
	 * @param array of tech objects
	 * @return array 1d (key=>value) array of herds for populating options lists
	 * @access public
	 *
	 **/
	public function get_dhi_supervisor_dropdown_data($arr_tech_obj){
		if(is_array($arr_tech_obj)) {
			$ret_array[''] = "Select one";
			foreach($arr_tech_obj as $g){
				$ret_array[$g->account_num] = $g->name;
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
		$tmp_array = $this->arr_task_permissions;
		if(is_array($tmp_array) !== FALSE) return in_array($task_name, $tmp_array);
		else return FALSE;
	}

	/**
	 * @method subscribed_section
	 *
	 * @param int section id
	 * @return boolean - true if the user is signed up for the specified section
	 * @author ctranel
	 **/
	public function subscribed_section($section_id){
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
	 * @author ctranel
	 **/
	public function get_promo_sections($user_id = FALSE){
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
		$arr_all_sections = $this->ion_auth_model->get_sections_by_herd()->result_array();
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
		$this->email->from($this->config->item('admin_email'), $this->config->item('site_title'));
		$this->email->to($data['email']);
		$this->email->cc($this->config->item('cust_serv_email'));
		$this->email->subject($this->config->item('site_title') . ' - Product Inquiry');
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
/*	for now, consultant have access to all super sections
 * 
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
 */
		return $tmp_array;
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
	public function get_sections_array($group_id, $user_id, $herd_code, $arr_super_section_id = array(), $arr_scope = NULL){
		if(isset($arr_scope) && is_array($arr_scope)){
			$tmp_array = array();
			foreach($arr_scope as $s){
				switch ($s) {
					case 'subscription':
						$a = $this->ion_auth_model->get_subscribed_sections_array($group_id, $user_id, $arr_super_section_id, $herd_code);
						if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
						break;
					case 'unmanaged':
						$a = $this->ion_auth_model->get_unmanaged_sections_array($group_id, $user_id, $herd_code);
						if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
						break;
//					case 'admin':
//						if($this->is_admin){
//							$a = $this->ion_auth_model->get_child_sections_by_scope($s, $super_section_id);
//							if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
//						}
//						break;
					default: //public, account, user-specific
						$a = $this->ion_auth_model->get_child_sections_by_scope($s, $arr_super_section_id);
						if(!empty($a)) $tmp_array = array_merge($tmp_array, $a);
						break;
				}
			}
		}
		else {
			$tmp_array = $this->ion_auth_model->get_subscribed_sections_array($group_id, $user_id, $arr_super_section_id, $herd_code);
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
	 *
	 **/
	public function group_assignable($arr_form_group_id){
		$session_group_id = $this->session->userdata('active_group_id');
		if($this->logged_in() === FALSE){
			$arr_tmp = array_intersect($arr_form_group_id, array('2', '9', '13')); //if select groups include only 2 and 9 (producer and consultant)
			if(count($arr_tmp) == count($arr_form_group_id)) return TRUE;
			return false;
		}
		if($this->is_admin){
			return TRUE;
		}
		if(in_array($session_group_id, $arr_form_group_id) && count($arr_form_group_id) == 1){
			return TRUE;
		}
		if(!in_array($session_group_id, $arr_form_group_id) && count($arr_form_group_id) == 1
			&& $this->has_permission("Edit All Users") === FALSE && $this->has_permission("Edit Users In Region") === FALSE
			&& $this->has_permission("Add All Users") === FALSE && $this->has_permission("Add Users In Region") === FALSE
		) {
			return FALSE;
		}

		$tmp = $this->ion_auth_model->get_editable_groups($session_group_id);
		//get_editable_groups returns a multidimensional array, need to extract ids
		$arr_child_groups = get_elements_by_key('id', $tmp);
		unset($tmp);
		unset($arr_form_group_id[$session_group_id]);
		$arr_tmp = array_intersect($arr_form_group_id, $arr_child_groups);
		if(count($arr_tmp) == count($arr_form_group_id)) return TRUE;
		return FALSE;
	}

	/**
	 * @method allow_service_grp()
	 * @abstract write consultant-herd relationship record
	 * @param string consultant user id
	 * @param string herd code
	 * @param array sections for which consultant should be give permissions
	 * @return boolean
	 * @access public
	 *
	 **/
	public function allow_service_grp($arr_relationship_data, $arr_section_id) {
		$old_relationship_id = $this->ion_auth_model->get_consult_relationship_id($arr_relationship_data['sg_user_id'], $arr_relationship_data['herd_code']);
		//insert into consulants_herds
		$relationship_id = $this->ion_auth_model->set_consult_relationship($arr_relationship_data, $old_relationship_id);
		//insert each section into consulants_herds_sections
		if($relationship_id !== FALSE){
			$success = $this->ion_auth_model->set_consult_sections($arr_section_id, $relationship_id, $old_relationship_id);
			if($success){
				$this->set_message('consultant_status_update_successful');
				//send e-mail
				$arr_herd_info = $this->herd_model->header_info($this->session->userdata('herd_code'));
				$consultant_info = $this->ion_auth_model->user($arr_relationship_data['sg_user_id'])->result_array();
				$consultant_info = $consultant_info[0];

				if($arr_relationship_data['request_status_id'] == 1) $message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('consult_granted', 'ion_auth'), $arr_herd_info, true);
				elseif($arr_relationship_data['request_status_id'] == 2) $message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('consult_denied', 'ion_auth'), $arr_herd_info, true);

				if(isset($message)){
					$this->email->clear();
					$this->email->from($this->config->item('admin_email'), $this->config->item('site_title'));
					$this->email->to($consultant_info['email']);
					$this->email->cc($this->session->userdata('email'));
					$this->email->subject($this->config->item('site_title') . ' - Consultant Access');
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
	 * @method service_grp_request()
	 * @abstract write consultant-herd relationship record
	 * @param string herd code
	 * @param int consultant's user id
	 * @param array sections for which consultant should be give permissions
	 * @param date expiration date for access
	 * @return boolean
	 * @access public
	 *
	 **/
	public function service_grp_request($arr_relationship_data, $arr_section_id, $cust_serv_email) {
		$old_relationship_id = $this->ion_auth_model->get_consult_relationship_id($arr_relationship_data['sg_user_id'], $arr_relationship_data['herd_code']);
		//insert into consulants_herds
		$relationship_id = $this->ion_auth_model->set_consult_relationship($arr_relationship_data, $old_relationship_id);
		//insert each section into consulants_herds_sections
		if($relationship_id !== FALSE){
			$success = $this->ion_auth_model->set_consult_sections($arr_section_id, $relationship_id, $old_relationship_id);
			if($success){
				$this->set_message('consultant_request_recorded');
				$this->send_consultant_request($arr_relationship_data, $relationship_id, $cust_serv_email);

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
	function send_consultant_request($arr_relationship_data, $relationship_id, $cust_serv_email){
		//send e-mail
		$consultant_info = $this->ion_auth_model->user($arr_relationship_data['sg_user_id'])->result_array();
		$consultant_info[0]['relationship_id'] = $relationship_id;
		$email_data = array(
			'id' => $consultant_info[0]['id'],
			'company' => '',//$consultant_info[0]['company'],
			'first_name' => $consultant_info[0]['first_name'],
			'last_name' => $consultant_info[0]['last_name'],
			'herd_code' => $arr_relationship_data['herd_code'],
		);
		$message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('service_grp_request', 'ion_auth'), $email_data, TRUE);
		$arr_herd_emails = $this->herd_model->get_herd_emails($arr_relationship_data['herd_code']);
		//If there are no myagsource users, send email to customer service
		if(isset($arr_herd_emails) && is_array($arr_herd_emails)){
			$arr_herd_emails = array_flatten($arr_herd_emails);
		}
		else{
			$arr_herd_emails = array($cust_serv_email);
		}

		if(isset($message)){
			$this->email->clear();
			$this->email->from($this->config->item('admin_email'), $this->config->item('site_title'));
			$this->email->to($arr_herd_emails);
			$this->email->cc($this->session->userdata('email'));
			$this->email->subject($this->config->item('site_title') . ' - Consultant Access');
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
