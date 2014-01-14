<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Name:  MY Ion Auth Model
 *
 * Author:  Chris Tranel (extends class by Ben Edmunds)
 * 		   ctranel@agsource.com
  *
 * Created:  06.29.2012
 *
 * Description:  Extends model created bu Ben Edmunds
 *
 * Requirements: PHP5 or above
 *
 */
/* -----------------------------------------------------------------
 *  UPDATE comment
 *  @author: carolmd
 *  @date: Nov 25, 2013
 *
 *  @description: Removed get_group_by_name. No longer needed anywhere, 
 *  				because default and active group fields are now group_id instead of name.
 *  
 *  @date: Jan 9, 2014
 *
 *  @description: removed some debugging code. 
 *  -----------------------------------------------------------------
 */
require_once APPPATH . 'models/ion_auth_parent_model.php';

class Ion_auth_model extends Ion_auth_parent_model
{
	/**
	 * Holds an array of user meta sections used
	 * @var array
	 **/
	public $meta_sections = array();

	/**
	 * Holds an array of herd meta sections used
	 * @var array
	 **/
	public $herd_meta_sections = array();

	/**
	 * Holds an array of arrays of columns included in each meta section
	 * @var array
	 **/
	public $columns = array();

	/**
	 * Holds an array with the join field for each meta section
	 * @var array
	 **/
	public $join = array();

	/**
	 * Holds an array of arrays with field that are required before each meta section is written to the DB
	 * @var array
	 **/
	public $towrite = array();

	/**
	 * key = group_id
	 * value = group_name
	 * @var array
	 **/
	public $arr_group_lookup = array();

	public function __construct()
	{
		parent::__construct();

		$this->meta_sections  = $this->config->item('meta_sections', 'ion_auth');
		$this->herd_meta_sections  = $this->config->item('herd_meta_sections', 'ion_auth');
		$this->columns = $this->config->item('columns', 'ion_auth');
		$this->join = $this->config->item('join', 'ion_auth');
		$this->towrite = $this->config->item('towrite', 'ion_auth');
		$this->arr_group_lookup = $this->_get_group_lookup();
	}

	/**
	 * @method register
	 *
	 * @param string usernamme
	 * @param string password
	 * @param string e-mail
	 * @param array additional account data
	 * @param array groups 
	 * @return bool
	 * @author Chris Tranel CDT
	 **/
	public function register($username, $password, $email, $additional_data = false, $arr_groups = array())
	{
		$this->db->trans_begin();
		$tran_status = parent::register($username, $password, $email, $additional_data, $arr_groups);
		if($tran_status){
			$id = $tran_status; //register returns id if it is successful
			$this->_update_meta($id, $additional_data);
			//$this->add_to_groups($arr_groups, $id);
			$tran_status = $this->db->trans_status();
		}
		if(!$tran_status){
				$this->db->trans_rollback();
	
				$this->trigger_events(array('post_register', 'post_register_unsuccessful'));
				$this->set_error('register_unsuccessful');
				return FALSE;
		}

		$this->set_message('register_successful');
		$this->db->trans_commit();
		return is_int($id) ? $id : FALSE;
	}

	/**
	 * @method login
	 *
	 * @param string identity
	 * @param string password
	 * @param boolean remember (store cookie)
	 * @return bool
	 * @author Chris Tranel CDT
	 **/
	public function login($identity, $password, $remember=FALSE){
		$this->ion_auth->set_hook('post_login_successful', 'set_session_meta', 'Ion_auth_model', '_set_session_meta');
		return parent::login($identity, $password, $remember);
	}

	/**
	 * @method _set_session_meta
	 *
	 * @abstract called on login success, writes session data specific to this section
	 * @return bool
	 * @author Chris Tranel CDT
	 **/
	/* -----------------------------------------------------------------
	 *  UPDATE comment
	 *  @author: carolmd
	 *  @date: Dec 9, 2013
	 *
	 *  @description: revised the arr_groups so it contains a single group_id.
	 *  
	 *  -----------------------------------------------------------------
	 */
	protected function _set_session_meta(){
		$group_id = $this->get_users_group($this->session->userdata('user_id'));
		$session_data['arr_groups'] = array($group_id);
		$arr_regions = $this->get_users_region_array($this->session->userdata('user_id'));
		$session_data['arr_regions'] = $arr_regions;
		$session_data['active_group_id'] = $group_id;

		// write select query for each meta section
		if (!empty($this->meta_sections)){
			foreach($this->meta_sections as $s){
				if (!empty($this->columns[$s])) {
					foreach ($this->columns[$s] as $field){
						$this->db->select($this->tables[$s] . '.' . $field);
					}
					${$s . '_row'} = $this->db->where($this->join[$s], $this->session->userdata('user_id'))->get($this->tables[$s])->row();
				}
			}
		}
/* -----------------------------------------------------------------
 *  UPDATE comment
 *  @author: carolmd
 *  @date: Dec 9, 2013
 *
 *  @description: changed to get herd meta data for everyone.
 *  
 *  -----------------------------------------------------------------
 */
//		if (!empty($this->herd_meta_sections) && in_array('2', $arr_groups)){ //get herd data for producers
		if (!empty($this->herd_meta_sections) ){ //get herd data
			foreach($this->herd_meta_sections as $s){
				if (!empty($this->columns[$s])) {
					foreach ($this->columns[$s] as $field){
						$this->db->select($this->tables[$s] . '.' . $field);
					}
					${$s . '_row'} = $this->db->where($this->join[$s], $this->session->userdata('user_id'))->get($this->tables[$s])->row();
				}
			}
		}
		//add columns for each meta section to session var
		if (!empty($this->meta_sections)){
			foreach($this->meta_sections as $s){
				if (!empty($this->columns[$s])) {
					foreach ($this->columns[$s] as $col){
						if(is_a(${$s . '_row'}, 'stdClass')){
							$session_data[$col] = ${$s . '_row'}->$col;
						}
					}
				}
			}
		}
		/* -----------------------------------------------------------------
		 *  UPDATE comment
		 *  @author: carolmd
		 *  @date: Dec 9, 2013
		 *
		 *  @description: Revised to gather herd meta data for all users.
		 *  
		 *  -----------------------------------------------------------------
		 */
		if (!empty($this->herd_meta_sections)){
			foreach($this->herd_meta_sections as $s){
				if (!empty($this->columns[$s])) {
					foreach ($this->columns[$s] as $col){
						if(is_a(${$s . '_row'}, 'stdClass')){
							$session_data[$col] = ${$s . '_row'}->$col;
						}
					}
				}
			}
		}
		if($session_data['active_group_id']) unset($session_data['herd_code']);
			

		$this->session->set_userdata($session_data);
	}

	/**
	 * @method users
	 *
	 * @param mixed group
	 * @param int limit
	 * @param int offset
	 * @return object Users
	 * @author Chris Tranel CDT
	 **/
	/* -----------------------------------------------------------------
	 *  UPDATE comment
	 *  @author: carolmd
	 *  @date: Dec 9, 2013
	 *
	 *  @description: removed join criteria on users_groups.
	 *  
	 *  -----------------------------------------------------------------
	 */
	public function users($group=FALSE, $limit=NULL, $offset=NULL, $arr_joins = NULL)
	{
		if (!empty($this->meta_sections)){
			foreach($this->meta_sections as $s){
				if($s != 'users_sections'){ //creates multiple records for users with multiple sections.
					if (!empty($this->columns[$s])) {
						foreach ($this->columns[$s] as $field){
							$this->db->select($this->tables[$s].'.'. $field);
						}
					}
					$this->db->join($this->tables[$s], $this->tables['users'].'.id = '.$this->tables[$s].'.'.$this->join[$s], 'left');
				}
			}
		}
		if (!empty($this->herd_meta_sections)){
			foreach($this->herd_meta_sections as $s){
				if($s != 'herds_sections'){ //creates multiple records for herds with multiple sections.
					if (!empty($this->columns[$s])) {
						foreach ($this->columns[$s] as $field){
							$this->db->select($this->tables[$s].'.'. $field);
						}
					}
					$this->db->join($this->tables[$s], $this->tables['users_herds'].'.herd_code = '.$this->tables[$s].'.'.$this->join[$s], 'left');
				}
			}
		}
		/* -----------------------------------------------------------------
		 *  UPDATE comment
		 *  @author: carolmd
		 *  @date: Dec 9, 2013
		 *
		 *  @description: removed table users_groups , use users table instead.
		 *  
		 *  -----------------------------------------------------------------
		 */
		$this->db->join(
		    $this->tables['groups'], 
		    $this->tables['groups'].'.id = ' . $this->tables['users'].'.group_id', 
		    'inner'
		);

		if(isset($arr_joins) && is_array($arr_joins)){
			foreach($arr_joins as $j){
				$this->db->join($j['table'], $j['condition'], $j['type']);
			}
		}
		/* -----------------------------------------------------------------
		 *  UPDATE comment
		 *  @author: carolmd
		 *  @date: Dec 9, 2013
		 *
		 *  @description: removed references to users_groups.
		 *  
		 *  -----------------------------------------------------------------
		 */
		//$this->db->select("CAST(REPLACE((SELECT group_id AS [data()] FROM " . $this->tables['users_groups'] . " AS groups2 WHERE " . $this->tables['users_groups'] . ".user_id = groups2.user_id ORDER BY group_id FOR xml path('')), ' ', ', ') AS VARCHAR(45)) AS arr_groups",FALSE);
		//$this->db->select("(SELECT GROUP_CONCAT(group_id) FROM " . $this->tables['users_groups'] . " AS groups2 WHERE " . $this->tables['users_groups'] . ".user_id = groups2.user_id) AS groups",FALSE);
		//$this->db->distinct();
		
		if($limit) $this->db->limit($limit, $offset);
		$this->db->distinct();
		return parent::users($group);
	}

	/**
	 * @method users
	 *
	 * @param mixed group
	 * @param int limit
	 * @param int offset
	 * @return object User
	 * @author Chris Tranel CDT
	 **/
	public function user($id = NULL){
		$this->trigger_events('user');

		//if no id was passed use the current users id
		$id || $id = $this->session->userdata('user_id');

		$this->limit(1);
		$this->where($this->tables['users'].'.id', $id);

		$this->users();

		return $this;
	}
	
	/**
	 * @method get_active_users
	 *
	 * @param string group name
	 * @return object
	 * @author Chris Tranel CDT
	 **/
	public function get_active_users($group_name = false)
	{
		$this->db->where($this->tables['users'].'.active', 1);

		$this->users($group_name);
		return $this;
	}

	/**
	 * @method get_inactive_users
	 *
	 * @param string group name
	 * @return object
	 * @author Ben Edmunds
	 **/
	public function get_inactive_users($group_name = false)
	{
		$this->db->where($this->tables['users'].'.active', 0);

		$this->users($group_name);
		return $this;
	}

	/**
	 * @method get_field_users_by_region
	 *
	 * @param int/array region number
	 * @return object
	 * @author Chris Tranel CDT
	 **/
	public function get_field_users_by_region($region_id)
	{
		
		if(!isset($region_id)) return FALSE;
		if(!is_array($region_id)) $region_id = array($region_id);
		$this->db->where($this->tables['users_regions'], $region_id);
		$this->users();
		return $this;
	}
	
	/**
	 * @method get_user_group
	 *
	 * @param int user id
	 * @return group_id
	 * @author Chris Tranel
	 **/
	/* -----------------------------------------------------------------
	 *  UPDATE comment
	*  @author: carolmd
	*  @date: Dec 9, 2013
	*
	*  @description: Changed this to find user's group_id from the users table.
	*  				Returns int.
	*  -----------------------------------------------------------------
	*/
	public function get_users_group($id=false) {
		$this->db
			->where('id',  $id)
		    ->select('group_id');
		$group_mdarray = $this->db
		           ->get($this->tables['users'])
		           ->result_array();
		$group_id = $group_mdarray[0]["group_id"];
		return $group_id;
	}

	/**
	 * @method get_users_region_array
	 *
	 * @param int user id
	 * @return array group_id=>group_name
	 * @author Chris Tranel
	 **/
	/* -----------------------------------------------------------------
	 *  UPDATE comment
	 *  @author: carolmd
	 *  @date: Dec 12, 2013
	 *
	 *  @description: Changed this function to get user's region_id (aka association_num) from the users table.
	 *  
	 *  -----------------------------------------------------------------
	 */
	public function get_users_region_array($id=false) {
		//if no id was passed use the current users id
		$id || $id = $this->session->userdata('user_id');
		$this->db
		->where('id',  $id)
		->select('association_num');
		$assoc_mdarray = $this->db
		->get($this->tables['users'])
		->result_array();
		$assoc_id = $assoc_mdarray[0]["association_num"];
		$assoc_arr = array($assoc_id);
		return array($assoc_arr);
	}
	
	
	/**
	 * @method get_child_groups
	 *
	 * @param int group id
	 * @return object
	 * @author Chris Tranel
	 **/
	public function get_child_groups($group_id_in)
	{
		$this->db->where('parent_group', '' . $group_id_in);
		$this->db->or_where('name', 'Producers');

		return $this->db->get($this->tables['groups'])
		->result();
	}


	/**
	 * @method add_to_groups
	 *
	 * @param array of groups
	 * @param string user id
	 * @return boolean
	 * @author Chris Tranel
	 **/
	public function add_to_groups($groups_in, $user_id){
		if(is_array($groups_in)){
			foreach($groups_in as $g){
				$this->add_to_group($g, $user_id);
			}
			return TRUE;
		}
	}

	/**
	 * @method update
	 *
	 * @param int user id
	 * @param array user data
	 * @return bool
	 * @author Chris Tranel
	 **/
	public function update($id, array $data)
	{
		$this->db->trans_begin();
		$tran_status = parent::update($id, $data);
		if($tran_status){
			$this->_update_meta($id, $data);
			$current_groups = array_extract_value_recursive('id', $this->get_users_groups($id)->result_array());
			$arr_groups_to_delete = is_array($data['group_id']) ? array_diff($current_groups, $data['group_id']) : FALSE;
			$arr_groups_to_add = is_array($data['group_id']) ? array_diff($data['group_id'], $current_groups) : FALSE;
			if(is_array($arr_groups_to_delete) && !empty($arr_groups_to_delete)) $this->remove_from_group($arr_groups_to_delete, $id);
			if(is_array($arr_groups_to_add) && !empty($arr_groups_to_add)) $this->add_to_groups($arr_groups_to_add, $id);
			$tran_status = $this->db->trans_status();
		}
		if(!$tran_status){
				$this->db->trans_rollback();
	
				$this->trigger_events(array('post_update_user', 'post_update_user_unsuccessful'));
				$this->set_error('update_unsuccessful');
				return FALSE;
		}
		$this->set_message('update_successful');
		$this->db->trans_commit();
		$this->_set_session_meta();
		return TRUE;
	}

	/**
	 * @method _update_meta
	 * 
	 * @abstract helper function for "update"
	 * @param int user id
	 * @param array user data
	 * @return bool
	 * @author Chris Tranel (CDT)
	 **/
	protected function _update_meta($id, array $data){
		$has_data = is_array($data); 
		if (!empty($this->meta_sections)){
			foreach($this->meta_sections as $s){
				$skip_section[$s] = FALSE;
				if (!empty($this->columns[$s])) {
					$meta_fields = array();
					$arr_meta_fields = array();
					foreach ($this->columns[$s] as $field) {
						if ($has_data && isset($data[$field]) && (in_array($field, $this->towrite[$s]) !== FALSE && $data[$field] != '')) {
							if(is_array($data[$field])) $arr_meta_fields[$field] = $data[$field];
							else $meta_fields[$field] = $data[$field];
						}
						elseif(in_array($field, $this->towrite[$s]) !== FALSE && (isset($data[$field]) === FALSE || $data[$field] == '')) {
							$skip_section[$s] = TRUE;
						}
						//unset($data[$field]);
					}
					if(!$skip_section[$s]){
					//update the meta data
						if(count($arr_meta_fields) > 0){
							//@todo rather than deleting and re-inserting, only delete/add/update changes 
							//delete from $this->tables[$s]
							$this->db->delete($this->tables[$s], array($this->join[$s] => $id));
							foreach($arr_meta_fields as $field_name => $arr){
								foreach($arr as $value){
									$this->db->set($this->join[$s], $id);
									$this->db->set($field_name, $value);
									foreach($meta_fields as $k => $v){
										$this->db->set($k, $v);
									}
									$this->db->insert($this->tables[$s]);
								}
							}
						}
						else {
							if (count($meta_fields) > 0) {
								$cnt = $this->db->where($this->join[$s], $id)
									->from($this->tables[$s])
									->count_all_results();
								
								if($cnt == 0){
									$this->db->set($meta_fields);
									$this->db->set(array($this->join[$s] => $id));
									$this->db->insert($this->tables[$s]);
								}
								else{
									$this->db->where($this->join[$s], $id);
									$this->db->set($meta_fields);
									$this->db->update($this->tables[$s]);
								}
							}
						}
					}
					unset($arr_meta_fields);
					unset($meta_fields);
				}
			}
		}

		if (!empty($this->herd_meta_sections)){
			foreach($this->herd_meta_sections as $s){
				if (!empty($this->columns[$s])) {
					$meta_fields = array();
					$arr_meta_fields = array();
					foreach ($this->columns[$s] as $field) {
						if ($has_data && isset($data[$field]) && (in_array($data[$field], $this->towrite[$s]) === false || $data[$field] != '')) {
							if(is_array($data[$field])) $arr_meta_fields[$field] = $data[$field];
							else $meta_fields[$field] = $data[$field];
						}
						unset($data[$field]);
					}

					//update the meta data
					if(count($arr_meta_fields) > 0){
						//delete from $this->tables[$s]
						$this->db->delete($this->tables[$s], array($this->join[$s] => $id));
						foreach($arr_meta_fields as $field_name => $arr){
							foreach($arr as $value){
								$this->db->set($this->join[$s], $id);
								$this->db->set($field_name, $value);
								foreach($meta_fields as $k => $v){
									$this->db->set($k, $v);
								}
								$this->db->insert($this->tables[$s]);
							}
						}
					}
					else {
						if (count($meta_fields) > 0) {
							// 'user_id' = $id
							$this->db->where($this->join[$s], $id);
							$this->db->set($meta_fields);
							$this->db->update($this->tables[$s]);
						}
					}
					unset($arr_meta_fields);
					unset($meta_fields);
				}
			}
		}
	}
	
	
	/**
	 * @method delete_user
	 *
	 * @param int user id
	 * @return bool
	 * @author Chris Tranel
	 **/
	public function delete_user($id)
	{
		$this->db->trans_begin();
		$tran_status = parent::delete_user($id);
		if($tran_status){
			if (!empty($this->meta_sections)){
				foreach($this->meta_sections as $s){
					$this->db->delete($this->tables[$s], array($this->join[$s] => $id));
				}
			}
			$tran_status = $this->db->trans_status();
		}
		if(!$tran_status){
			$this->db->trans_rollback();
			$this->trigger_events(array('post_delete_user', 'post_delete_user_unsuccessful'));
			$this->set_error('delete_unsuccessful');
			return FALSE;
		}

		$this->db->trans_commit();
		$this->set_message('delete_successful');
		return TRUE;
	}


	/**
	 * @method login_remembed_user
	 *
	 * @return bool
	 * @author Chris Tranel
	 * 
	 **/
	public function login_remembered_user()
	{
		if(parent::login_remembered_user()){
			$this->_set_session_meta();
			return TRUE;
		}
		else return FALSE;
	}

	/**
	 * @method get_child_regions()
	 * @param int region id
	 * @return array with region ids
	 * @author Chris Tranel
	 **/
	public function get_child_regions_array($region_in = FALSE) {
		$region_id = $region_in ? $region_in : $this->session->userdata('arr_regions');
		if(empty($region_id) === FALSE){
			$reg_txt = implode(',', array_keys($region_id));
			$return = $this->db->query( "WITH subregions(id, parent_region_id) AS (
				SELECT id, parent_region_id
				FROM " . $this->tables['regions'] . "
				WHERE id IN(" . $reg_txt . ")
				UNION ALL
				SELECT c.id, c.parent_region_id
				FROM " . $this->tables['regions'] . " c
				INNER JOIN subregions p ON c.parent_region_id = p.id
			  )
			  SELECT id FROM subregions")->result_array();
			  
			return is_array($return) ? array_flatten($return) : false;
		}
		return false;
	}
	
	
	/**
	 * @method get_subscribed_super_sections_array
	 * @param int $group_id for active session
	 * @param int $user_id
	 * @param string $herd_code
	 * @return array of section data for given user
	 * @author Chris Tranel
	 **/
	public function get_subscribed_super_sections_array($group_id, $user_id, $herd_code = FALSE) {
		if(is_array($group_id)) {
			if(in_array(2, $group_id) && count($group_id) == 1) $group_id = 2;
		}
		else if(!isset($group_id) || !$group_id) $group_id = $this->session->userdata('active_group_id');
		
		if(isset($group_id) && !empty($group_id)){
			$tmp_arr_sections = array();
			$this->db
			->where($this->tables['super_sections'] . '.active', 1)
			->where($this->tables['super_sections'] . '.scope_id', 2); // 2 = subscription
			//if($this->has_permission("View Unsubscribed Herds")){ //if the logged in user has permission to view reports to which the herd is not subscribed
			if($group_id == 2){ //if this is a producer
			//if(!$this->has_permission("View Non-owned Herds")){
				if(isset($herd_code) && !empty($herd_code)){
					//$tmp_arr_sections = $this->get_super_sections_by_herd($herd_code)->result_array();
					$tmp_arr_sections = $this->get_super_sections_by_herd($herd_code);
				}
			}
			else{
				if(!isset($user_id) || !$user_id){
					$user_id = $this->session->userdata('user_id');
				}
				if(isset($user_id) && !empty($user_id)){
					$tmp_arr_sections = $this->get_super_sections_by_user($user_id);
				}
			}
			return $tmp_arr_sections;
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
		$tmp_array = $this->get_task_permissions();
		if(is_array($tmp_array) !== FALSE) return in_array($task_name, $tmp_array);
		else return FALSE;
	}

	/**
	 * @method get_subscribed_sections_array
	 * @param int $group_id for active session
	 * @param int $user_id
	 * @param string $herd_code
	 * @return array of section data for given user
	 * @author Chris Tranel
	 * 11/14/13: CMMD: remove result array.
	 **/
	public function get_subscribed_sections_array($group_id, $user_id, $super_section_id, $herd_code = FALSE) {
		if(is_array($group_id)) {
			if(in_array(2, $group_id) && count($group_id) == 1) $group_id = 2;
		}
		else if(!isset($group_id) || !$group_id) $group_id = $this->session->userdata('active_group_id');
		
		if(isset($group_id) && !empty($group_id)){
			$tmp_arr_sections = array();
			$this->db
			->where($this->tables['sections'] . '.active', 1)
			->where($this->tables['sections'] . '.super_section_id', $super_section_id)
			->where($this->tables['sections'] . '.scope_id', 2); // 2 = subscription
			//if($this->has_permission("View Unsubscribed Herds")){ //if the logged in user has permission to view reports to which the herd is not subscribed
			if($group_id == 2){ //if this is a producer
			//if(!$this->has_permission("View Non-owned Herds")){
				if(isset($herd_code) && !empty($herd_code)){
					//$tmp_arr_sections = $this->get_sections_by_herd($herd_code)->result_array();
					$tmp_arr_sections = $this->get_sections_by_herd($herd_code);
				}
			}
			else{
				if(!isset($user_id) || !$user_id){
					$user_id = $this->session->userdata('user_id');
				}
				if(isset($user_id) && !empty($user_id)){
					$tmp_arr_sections = $this->get_sections_by_user($user_id);
				}
			}
			return $tmp_arr_sections;
		}
	}
		
	/**
	 * @method get_unmanaged_super_sections_array
	 * @param int $group_id
	 * @param int $user_id
	 * @param string $herd_code
	 * @return array of section data for given user
	 * @author Chris Tranel
	 **/
	public function get_unmanaged_super_sections_array($group_id, $user_id, $herd_code = FALSE) {
		//$this->load->model('dm_model');
		if(false){//$credentials = $this->dm_model->get_credentials()) {
			$this->db->where($this->tables['super_sections'] . '.id', 6);
			return $this->get_super_sections()->result_array();
		}
		else return array();
	}
	
	/**
	 * @method get_unmanaged_sections_array
	 * @param int $group_id
	 * @param int $user_id
	 * @param string $herd_code
	 * @return array of section data for given user
	 * @author Chris Tranel
	 **/
	public function get_unmanaged_sections_array($group_id, $user_id, $herd_code = FALSE) {
		//$this->load->model('dm_model');
		if(false){//$credentials = $this->dm_model->get_credentials()) {
			$this->db->where($this->tables['sections'] . '.id', 6);
			return $this->get_sections()->result_array();
		}
		else return array();
	}

	/**
	 * @method user_owns_herd by currently logged in user?
	 * @param string herd_code
	 * @return boolean
	 * @author Chris Tranel
	 * 11/14/13: CMMD: Fixed FROM clause.
	 **/
	public function user_owns_herd($herd_code) {
		$results = $this->db
		->select('id')
		//->from('users')
		->from($this->tables['users'])
		->join($this->tables['users_herds'], $this->tables['users'] . '.id = ' . $this->tables['users_herds'] . '.user_id')
		->where($this->tables['users_herds'] . ".herd_code = '" . $herd_code . "'")
		->where($this->tables['users'] . '.id = ' . $this->session->userdata('user_id'))
		->count_all_results();

		if($results > 0) return TRUE;
		else return FALSE;
	}

	/**
	 * @method herd_is_subscribed
	 * @param int section_id
	 * @param string herd_code
	 * @return array of section data for given user
	 * @author Chris Tranel
	 * 11/14/13: CMMD remove result array -- already in an array.
	 **/
	public function herd_is_subscribed($section_id, $herd_code) {
		//return $this->get_sections()->result_array();
		return $this->get_sections();
	}
	
	/**
	 * @method get_keyed_section_array
	 *
	 * @param array scope options to include
	 * @return 1d array (id=>name)
	 * @author Chris Tranel
	 **/
	public function get_keyed_section_array($arr_scope = NULL) {
		$this->db
		->select($this->tables['sections'] . '.id, ' . $this->tables['sections'] . '.name AS name')
		->join($this->tables['lookup_scopes'], $this->tables['sections'] . '.scope_id = ' . $this->tables['lookup_scopes'] . '.id', 'left')
		->order_by('name', 'asc');
		if(isset($arr_scope) && is_array($arr_scope)){
			$this->db->where_in($this->tables['lookup_scopes'] . '.name', $arr_scope);
		}
		$arr_section_obj = $this->get_sections()->result();
		if(is_array($arr_section_obj)) {
			foreach($arr_section_obj as $e){
				$ret_array[$e->id] = $e->name;
			}
			return $ret_array;
		}
		else return false;
	}

	/**
	 * @method get_blocks_by_section array
	 * @param int section
	 * @return array of block for given section
	 * @author Chris Tranel
	 **/
	public function get_blocks_by_section($section, $display = NULL) {
		$this->db
			->join($this->tables['pages_blocks'], $this->tables['blocks'] . '.id = ' . $this->tables['pages_blocks'] . '.block_id', 'left')
			->join($this->tables['pages'], $this->tables['pages_blocks'] . '.page_id = ' . $this->tables['pages'] . '.id', 'left')
			->join($this->tables['lookup_display_types'], $this->tables['blocks'] . '.display_type_id = ' . $this->tables['lookup_display_types'] . '.id', 'left')
			->where($this->tables['pages'] . '.section_id', $section);
		if(isset($display)) $this->db->where($this->tables['lookup_display_types'] . '.name', $display);
		return $this->get_blocks()->result_array();
	}

    /**
	 * @method get_blocks
	 * @return array of block data
	 * @author Chris Tranel
	 **/
	public function get_blocks() {
		$this->db
			->select('name,[description],url_segment,display_type_id')
			->where($this->tables['blocks'] . '.active', 1)
			->order_by('list_order', 'asc')
			->from($this->tables['blocks']);
		return $this->db->get();
	}

		/**
	 * @method get_super_sections_by_scope array
	 * @param string scope
	 * @return array of section data for given user
	 * 	 * 11/14/13: CMMD: return all active super_sections all the time.
	 * @author Chris Tranel
	 **/
	public function get_super_sections_by_scope($scope) {
		return $this->get_super_sections();
	}
	
		/**
	 * @method get_sections_by_scope array
	 * @param string scope
	 * @return array of section data for given user
	 * @author Chris Tranel
	 **/
	public function get_sections_by_scope($scope) {
		$this->db->join($this->tables['lookup_scopes'], $this->tables['sections'] . '.scope_id = ' . $this->tables['lookup_scopes'] . '.id', 'left')
		->where($this->tables['lookup_scopes'] . '.name', $scope);
		return $this->get_sections();
	}
	
	/**
	 * @method get_section_id_by_path()
	 * @param string section path
	 * @return int id of section
	 * @access public
	 *
	 **/
	public function get_section_id_by_path($section_path){
		$arr_res = $this->db
			->select('id')
			->like('path', $section_path)
			->get($this->tables['sections'])
			->result_array();
		if(is_array($arr_res) && !empty($arr_res)){
			return $arr_res[0]['id'];
		}
		return FALSE;
	}

	/**
	 * @method get_super_section_id_by_path()
	 * @param string super section path
	 * @return int id of super section
	 * @access public
	 *
	 **/
	public function get_super_section_id_by_path($super_section_path){
		$arr_res = $this->db
			->select('id')
			->like('path', $super_section_path)
			->get($this->tables['super_sections'])
			->result_array();
		if(is_array($arr_res) && !empty($arr_res)){
			return $arr_res[0]['id'];
		}
		return FALSE;
	}
	
	/**
	 * @method get_super_sections
	 * @return array of section data
	 * @author Chris Tranel
	 * Revised 11/14/13: CMMD return array
	 **/
	public function get_super_sections() {
		$this->db
			->where($this->tables['super_sections'] . '.active', 1)
			->order_by($this->tables['super_sections'] . '.list_order', 'asc')
			->from($this->tables['super_sections']);
		return $this->db->get()->result_array();
	}
	
    /**
	 * @method get_sections
	 * @return array of section data
	 * @author Chris Tranel
	 **/
	public function get_sections() {
		$this->db
			->where($this->tables['sections'] . '.active', 1)
			->order_by('list_order', 'asc')
			->from($this->tables['sections']);
		return $this->db->get()->result_array();
	}
	
	/**
	 * get_user_herd_settings
	 *
	 * @return array of user-herd settings
	 * @author Chris Tranel
	 **/
	public function get_user_herd_settings(){
		//REMOVE IF WE DECIDE TO STORE PREFERENCES
		$arr_sess_benchmarks = $this->session->userdata('benchmarks');
		if(isset($arr_sess_benchmarks) && !empty($arr_sess_benchmarks)){
			$arr_tmp['metric'] = $arr_sess_benchmarks['metric'];
			$arr_tmp['criteria'] = $arr_sess_benchmarks['criteria'];
			$arr_tmp['arr_states'] = $arr_sess_benchmarks['arr_states'];
			$arr_tmp['arr_herd_size'] = $arr_sess_benchmarks['arr_herd_size'];
			return $arr_tmp;
		}
		else return FALSE; 
/*		$user_id = $this->session->userdata('user_id');
		$result = $this->db
		->select()
		->get($this->tables['users_herds_settings'])
		->return_array();
		
		if(is_array($result) && !empty($result)) return $result[0];
		else return FALSE;*/
	}

	/**
	 * get_super_sections_by_user
	 *
	 * @return array of super_section data for given user
	 * @author Chris Tranel
	 * Revised: 11/14/13: CMMD: get all super sections all the time.
	 **/
	public function get_super_sections_by_user($user_id){
		return $this->get_super_sections();
	}
	
	/**
	 * get_super_sections_by_herd
	 *
	 * @return array of super_section data for given herd
	 * @author Chris Tranel
	 * Revised: 11/14/13: CMMD: renamed function. 
	 * 							Get all supersections all the time.
	 **/
	public function get_super_sections_by_herd(){
		return $this->get_super_sections();
	}

		/**
	 * get_sections_by_user
	 *
	 * @return array of section data for given user
	 * @author Chris Tranel
	 **/
	public function get_sections_by_user($user_id){
		$this->db
		->select($this->tables['sections'] . '.id, ' . $this->tables['sections'] . '.name, ' . $this->tables['sections'] . '.path')
		//->join($this->tables['users_sections'], $this->tables['users_sections'] . '.section_id = ' . $this->tables['sections'] . '.id', 'left')
		->where("(" . $this->tables['sections'] . '.user_id IS NULL OR ' . $this->tables['sections'] . '.user_id = ' . $user_id . ")");
		//->where("(" . $this->tables['sections'] . '.user_id IS NULL OR ' . $this->tables['sections'] . '.user_id = ' . $user_id . ' OR ' . $this->tables['users_sections'] . '.user_id = ' . $user_id . ")")
		//->where($this->tables['users_sections'] . '.user_id', $user_id);
		 return $this->get_sections();
	}
	
	/**
	 * get_herd_sections
	 *
	 * @return array of section data for given herd
	 * @author Chris Tranel
	 **/
	public function get_herd_sections(){
		$this->db
		->where($this->tables['sections'] . '.scope_id', 2); // 2 = subscription
		return $this->get_sections();
	}

	/**
	 * @method get_task_permissions
	 *
	 * @param int section id FOR FUTURE USE
	 * @return array tasks for which  the group of logged in user has permissions
	 * @author Chris Tranel
	 **/
	public function get_task_permissions($section_id = ''){
		
		$this->load->helper('multid_array_helper');
		$results = $this->db->select('name, description')
		->join($this->tables['groups_tasks'] . ' gt', 't.id = gt.task_id', 'left')
		//->where_in('group_id', array_keys($this->session->userdata('arr_groups')))
		->where('group_id', $this->session->userdata('active_group_id'))
		//->where('section_id', $section_id)
		//->where('permission', '1')
		->get($this->tables['tasks'] . ' t')
		->result_array();
		return array_extract_value_recursive('name', $results);
	}

	/**
	 * @method _filter_data
	 * @param string table
	 * @param array of data to filter
	 * @access protected
	 * @return array of filtered data (filtered out if field is not present in table)
	 * @author Chris Tranel
	 **/
	protected function _filter_data($table, $data)
	{
		$filtered_data = array();
		$columns = $this->db
			->select('column_name')
			->where(array('table_name' => 'users'))
			->get('users.information_schema.columns')
			->result_array();
		$columns = array_flatten($columns); 
		if (is_array($data))
		{
			foreach ($columns as $column)
			{
				if (array_key_exists($column, $data))
					$filtered_data[$column] = $data[$column];
			}
		}

		return $filtered_data;
	}
	
	/**
	 * @method _get_group_lookup
	 * @access protected
	 * @return array of groups (id=>name)
	 * @author Chris Tranel
	 **/
		protected function _get_group_lookup(){
		$arr_return = array();
		$this->db->where('status', TRUE);
		$tmp = $this->groups()->result_array();
		foreach($tmp as $g){
			$arr_return[$g['id']] = $g['name'];
		}
		return $arr_return;
	}

//DHI SUPERVISOR FUNCTIONS
	/**
	 * @method get_dhi_supervisors()
	 * @return array of objects
	 * @author Chris Tranel
	 **/
	public function get_dhi_supervisors() {
		return $this->db->get($this->tables['users_dhi_supervisors']);
	}

	/**
	 * @method get_dhi_supervisor_nums_by_region()
	 * @param string region number
	 * @return array of objects with field tech numbers
	 * @author Chris Tranel
	 **/
	/* -----------------------------------------------------------------
	 *  UPDATE comment
	 *  @author: carolmd
	 *  @date: Nov 25, 2013
	 *
	 *  @description: changed lookup from region_id to affiliate_num.
	 *  
	 *  -----------------------------------------------------------------
	 */
	public function get_dhi_supervisor_nums_by_region($arr_region_id = FALSE) {
		$this->db->select('supervisor_num');
		if($arr_region_id) $this->db->where_in('CONVERT(int, association_num)', $arr_region_id);
		$db_tmp_obj = $this->get_dhi_supervisors();
		if(is_array($db_tmp_obj)) {
			return $db_tmp_obj;
		}
		return false;
	}
	
	/**
	 * @method get_producer_users_by_tech
	 *
	 * @param int tech number
	 * @return object
	 * @author Chris Tranel CDT
	 **/
	public function get_producer_users_by_tech($tech_num)
	{
		if(!isset($tech_num)) return FALSE;
		if(!is_array($tech_num)) $tech_num = array($tech_num);
		$this->db->join($this->tables['users_herds'], $this->tables['users_herds'] . '.herd_code = ' . $this->tables['users_herds'] . '.herd_code', 'left')
			->where('supervisor_num', $tech_num);
		$this->users();
		return $this;
	}

	/**
	 * @method is_child_field_users_by_region
	 *
	 * @param int Region number
	 * @param int child user id
	 * @return object
	 * @author Chris Tranel
	 **/
	public function is_child_field_user_by_region($region_id, $child_id)
	{
		$this->db->select('user_id')
			->from($this->tables['users_dhi_supervisors'])
			->where('region_id', $region_id)
			->where('user_id', $child_id);
		return ($this->db->get()->num_rows() > 0);
	}

	/**
	 * @method is_child_producer_user_by_tech
	 *
	 * @param int tech number
	 * @param int child user id
	 * @return object
	 * @author Chris Tranel
	 **/
	public function is_child_producer_user_by_tech($tech_num, $child_id)
	{
		$this->db->select('user_id')
			->from($tables['herds'])
			->join($this->tables['users_herds'], $this->tables['users_herds'] . '.herd_code = ' . $this->tables['herds'] . '.herd_code', 'left')
			->where('supervisor_num', $tech_num)
			->where('user_id', $child_id);
		return ($this->db->get()->num_rows() > 0);
	}

//PRODUCER FUNCTIONS
	/**
	 * get_sections_by_herd
	 *
	 * @param string herd code
	 * @return array of section data for given herd
	 * @author Chris Tranel
	 **/
	private function get_sections_by_herd($herd_code){
		$this->db->select($this->tables['sections'] . '.id, ' . $this->tables['sections'] . '.name, ' . $this->tables['sections'] . '.path, ' . $this->tables['herds_sections'] . '.access_level')
		->join($this->tables['herds_sections'], $this->tables['herds_sections'] . '.section_id = ' . $this->tables['sections'] . '.id', 'left')
		->where('herd_code', $herd_code);
		return $this->get_sections();
	}

	/**
	 * @method get_field_users_by_region
	 * 
	 * @param int/array region number
	 * @return object
	 * @author Chris Tranel CDT
	 **/
	public function get_producer_users_by_region($region_id)
	{
		if(!isset($region_id)) return FALSE;
		if(!is_array($region_id)) $region_id = array($region_id);
		$arr_joins = array(
			0=>array('table'=>$this->tables['users_herds'], 'condition'=>$this->tables['users_herds'] . '.herd_code = ' . $this->tables['users_herds'] . '.herd_code', 'type'=>'left'),
			1=>array('table'=>$this->tables['herds_regions'], 'condition'=>$this->tables['herds'] . '.herd_code = ' . $this->tables['herds_regions'] . '.herd_code', 'type'=>'left')
		);	
		$this->db->where($this->tables['herds_regions'] . '.region_id', $region_id);
		$this->users(FALSE, NULL, NULL, $arr_joins);
		return $this;
	}

	/**
	 * @method is_child_producer_user_by_region
	 *
	 * @param int region number
	 * @param int child user id
	 * @return object
	 * @author Chris Tranel
	 **/
	public function is_child_producer_user_by_region($region_id, $child_id)
	{
		$this->db->select('user_id')
			->from($this->tables['herds'])
			->join($this->tables['users_herds'], $this->tables['users_herds'] . '.herd_code = ' . $this->tables['users_herds'] . '.herd_code', 'left')
			->join($this->tables['herds_regions'], $this->tables['herds'] . '.herd_code = ' . $this->tables['herds_regions'] . '.herd_code', 'left')
			->where($this->tables['herds_regions'] . '.region_id', $region_id)
			->where('user_id', $child_id);
		return ($this->db->get()->num_rows() > 0);
	}

//SERVICE GROUP FUNCTIONS
	/**
	 * @method get_consult_relationship_id
	 *
	 * @param string consultant user id
	 * @param string herd code
	 * @return int/bool id of record matching params
	 * @author Chris Tranel
	 **/
	public function get_consult_relationship_id($consultant_user_id, $herd_code){
		$result = $this->db
		->select('id')
		->get_where($this->tables['consultants_herds'], array('user_id' => $consultant_user_id, 'herd_code' => $herd_code))->result();
		if(!empty($result)) return $result[0]->id;
		else return FALSE;
	}

	/**
	 * @method get_consult_relationship
	 *
	 * @param string consultant user id
	 * @param string herd code
	 * @return array/bool of fields of record matching params, false if none found
	 * @author Chris Tranel
	 **/
	public function get_consult_relationship($consultant_user_id, $herd_code){
		$result = $this->db->get_where($this->tables['consultants_herds'], array('user_id' => $consultant_user_id, 'herd_code' => $herd_code))->result_array();
		if(!empty($result)) return $result[0];
		else return FALSE;
	}

	/**
	 * @method get_consult_relationship_by_id
	 *
	 * @param int relationship id
	 * @return array/bool of fields of record matching params, false if none found
	 * @author Chris Tranel
	 **/
	public function get_consult_relationship_by_id($id){
		$result = $this->db->get_where($this->tables['consultants_herds'], array('id' => $id))->result_array();
		if(!empty($result)) return $result[0];
		else return FALSE;
	}

	/**
	 * @method get_herds_by_consult page
	 *
	 * @param int consultant user id
	 * @return array of herd records, keyed by consultant status
	 * @author Chris Tranel
	 **/
	public function get_herds_by_consult($consultant_user_id){
		$result = $this->db
		->select($this->tables['consultants_herds'] . '.*, ' . $this->tables['herds'] . '.herd_owner')
		->from($this->tables['consultants_herds'])
		->join($tables['herds'], $this->tables['consultants_herds'] . '.herd_code = herd.herd_code')
		->where('(' . $this->tables['consultants_herds'] . '.exp_date IS NULL OR ' . $this->tables['consultants_herds'] . '.exp_date > GETDATE())')
		->where('user_id', $consultant_user_id)
		->get()
		->result_array();
		if(!empty($result)){
//			$arr_return = array();
			foreach($result as $r){
				if(empty($r['request_status_id'])) $r['request_status_id'] = 5;
				$arr_return[$r['request_status_id']][] = $r;
			}
		}
		$result = $this->db
		->select($this->tables['consultants_herds'] . '.*, ' . $this->tables['herds'] . '.herd_owner')
		->from($this->tables['consultants_herds'])
		->join($tables['herds'], $this->tables['consultants_herds'] . '.herd_code = ' . $this->tables['herds'] . '.herd_code')
		->where("((" . $this->tables['consultants_herds'] . ".exp_date IS NOT NULL AND " . $this->tables['consultants_herds'] . ".exp_date <= GETDATE()) AND " . $this->tables['consultants_herds'] . ".request_status_id = 1)")
		->where('user_id', $consultant_user_id)
		->get()
		->result_array();
		if(!empty($result)){
//			$arr_return = array();
			foreach($result as $r){
				$arr_return['expired'][] = $r;
			}
		}

		if(isset($arr_return)) return $arr_return;
		else return FALSE;
	}

	/**
	 * @method get_consultants_by_herd
	 *
	 * @param string herd code
	 * @return array of consultant records, keyed by consultant status
	 * @author Chris Tranel
	 **/
	/* -----------------------------------------------------------------
	 *  UPDATE comment
	 *  @author: carolmd
	 *  @date: Nov 14, 2013
	 *
	 *  @description: fixed the JOIN stmt 
	 *  
	 *  -----------------------------------------------------------------
	 */
	public function get_consultants_by_herd($herd_code){
		$result = $this->db
		->select($this->tables['consultants_herds'] . '.*, ' . $this->tables['users'] . '.first_name, ' . $this->tables['users'] . '.last_name, ' . $this->tables['service_groups'] . '.account_name')
		->from($this->tables['consultants_herds'])
		->join($this->tables['users'], $this->tables['consultants_herds'] . '.user_id = ' . $this->tables['users'] . '.id')
		->join($this->tables['users_service_groups'], $this->tables['users'] . '.id = ' . $this->tables['users_service_groups'] . '.user_id')
		->join($this->tables['service_groups'], $this->tables['users_service_groups'] . '.sg_account_num = ' . $this->tables['service_groups'] . '.account_num')
		->where('(' . $this->tables['consultants_herds'] . '.exp_date IS NULL OR ' . $this->tables['consultants_herds'] . '.exp_date > GETDATE())')
		->where('herd_code', $herd_code)
		->get()
		->result_array();
		if(!empty($result)){
			foreach($result as $r){
				if(empty($r['request_status_id'])) $r['request_status_id'] = 5;
				$arr_return[$r['request_status_id']][] = $r;
			}
		}
		$result = $this->db
		->select($this->tables['consultants_herds'] . '.*, ' . $this->tables['users'] . '.first_name, ' . $this->tables['users'] . '.last_name, ' . $this->tables['service_groups'] . '.account_name')
		->from($this->tables['consultants_herds'])
		->join($this->tables['users'], $this->tables['consultants_herds'] . '.user_id = ' . $this->tables['users'] . '.id')
		->join($this->tables['users_service_groups'], $this->tables['users'] . '.id = ' . $this->tables['users_service_groups'] . '.user_id')
		->join($this->tables['service_groups'], $this->tables['users_service_groups'] . '.sg_account_num = ' . $this->tables['service_groups'] . '.account_num')
		->where("((" . $this->tables['consultants_herds'] . ".exp_date IS NOT NULL AND " . $this->tables['consultants_herds'] . ".exp_date <= GETDATE()) AND " . $this->tables['consultants_herds'] . ".request_status_id = 1)")
		->where('herd_code', $herd_code)
		->get()
		->result_array();
		if(!empty($result)){
			foreach($result as $r){
				$arr_return['expired'][] = $r;
			}
		}

		if(isset($arr_return)) return $arr_return;
		else return FALSE;
			}

	/**
	 * @method batch_consult_revoke
	 *
	 * @param array permission record ids to be modified
	 * @return boolean
	 * @author Chris Tranel
	 **/
	public function batch_consult_revoke($arr_modify_ids){
		if(isset($arr_modify_ids) && is_array($arr_modify_ids)){
			$result = $this->db
			->where_in('id', $arr_modify_ids)
			->update($this->tables['consultants_herds'], array('request_status_id' => 3));
			return $result;
		}
		else return FALSE;
	}

	/**
	 * @method batch_grant_consult
	 *
	 * @param array permission record ids to be modified
	 * @return boolean
	 * @author Chris Tranel
	 **/
	public function batch_grant_consult($arr_modify_ids){
		if(isset($arr_modify_ids) && is_array($arr_modify_ids)){
			$result = $this->db
			->where_in('id', $arr_modify_ids)
			->update($this->tables['consultants_herds'], array('request_status_id' => 1));
			return $result;
		}
		else return FALSE;
	}

	/**
	 * @method batch_deny_consult
	 *
	 * @param array permission record ids to be modified
	 * @return boolean
	 * @author Chris Tranel
	 **/
	public function batch_deny_consult($arr_modify_ids){
		if(isset($arr_modify_ids) && is_array($arr_modify_ids)){
			$result = $this->db
			->where_in('id', $arr_modify_ids)
			->update($this->tables['consultants_herds'], array('request_status_id' => 1));
			return $result;
		}
		else return FALSE;
	}

	/**
	 * @method batch_remove_consult_expire
	 *
	 * @param array permission record ids to be modified
	 * @return boolean
	 * @author Chris Tranel
	 **/
	public function batch_remove_consult_expire($arr_modify_ids){
		if(isset($arr_modify_ids) && is_array($arr_modify_ids)){
			$result = $this->db
			->where_in('id', $arr_modify_ids)
			->update($this->tables['consultants_herds'], array('exp_date' => NULL));
			return $result;
		}
		else return FALSE;
	}

	/**
	 * @method get_consult_status_text
	 *
	 * @param int relationship id
	 * @return string consultant request status
	 * @author Chris Tranel
	 **/
	public function get_consult_status_text($id){
		$result = $this->db
		->select('users.dbo.lookup_sg_request_status.name')
		->join('users.dbo.lookup_sg_request_status', $this->tables['consultants_herds'] . '.request_status_id = users.dbo.lookup_sg_request_status.id', 'left')
		->get_where($this->tables['consultants_herds'], array('id' => $id))->result();
		if(!empty($result)) return $result[0]->name;
		else return FALSE;
	}
	
	/**
	 * @method set_consult_relationship
	 *
	 * @param array of data to be inserted into DB
	 * @param int/NULL original id of relationship record
	 * @return int/bool insert id if insert is successful, FALSE if not
	 * @author Chris Tranel
	 **/
	public function set_consult_relationship($arr_relationship_data, $old_id = NULL){
		$success = FALSE;
		if(isset($old_id) && !empty($old_id)) {
			$this->db->where('id', $old_id);
			if($this->db->update($this->tables['consultants_herds'], $arr_relationship_data)) $success = $old_id;
		}
		else {
			if($this->db->insert($this->tables['consultants_herds'], $arr_relationship_data)) $success = $this->db->insert_id();
		}
		return $success;
	}

	/**
	 * @method get_consult_rel_sections
	 *
	 * @param int id of relationship record
	 * @return array/bool array of section ids, FALSE if none found
	 * @author Chris Tranel
	 **/
	function get_consult_rel_sections($rel_id){
		$result = $this->db
		->select('section_id')
		->get_where($this->tables['consultants_herds_sections'], array('service_groups_herds_id'=>$rel_id))
		->result_array();
		if(!empty($result)) return array_flatten($result);
		else return FALSE;
	}

	/**
	 * @method set_consult_sections
	 *
	 * @param array of sections to be inserted into DB
	 * @param int id of relationship record for current update
	 * @param int/NULL original id of relationship record
	 * @return bool TRUE if insert is successful, FALSE if not
	 * @author Chris Tranel
	 **/
	public function set_consult_sections($arr_section_id, $consult_relationship_id, $old_id = NULL){
		if(isset($old_id) && !empty($old_id)) {
			$this->db->delete($this->tables['consultants_herds_sections'], array('service_groups_herds_id' => $old_id));
		}
		if(is_array($arr_section_id) && !empty($arr_section_id)){
			foreach($arr_section_id as $a){
				$success = $this->db->insert($this->tables['consultants_herds_sections'], array('service_groups_herds_id' => $consult_relationship_id, 'section_id' => $a));
				if(!$success) return FALSE;
			}
			return TRUE;
		}
		else return TRUE;
	}

	/**
	 * @method consultant_has_access
	 *
	 * @param int consultant id
	 * @param string herd code
	 * @param int section id
	 * @return bool
	 * @author Chris Tranel
	 **/
	public function consultant_has_access($consultant_user_id, $herd_code, $section_id){
		$results = $this->db
		->select('id')
		->from($this->tables['consultants_herds'])
		->join($this->tables['consultants_herds_sections'], $this->tables['consultants_herds'] . '.id = ' . $this->tables['consultants_herds_sections'] . '.service_groups_herds_id')
		->where($this->tables['consultants_herds'] . '.user_id = ' . $consultant_user_id)
		->where($this->tables['consultants_herds'] . '.herd_code = ' . $herd_code)
		->where('(' . $this->tables['consultants_herds'] . '.exp_date IS NULL OR ' . $this->tables['consultants_herds'] . '.exp_date > GETDATE())')
		->where($this->tables['consultants_herds'] . '.request_status_id', 1)
		->where($this->tables['consultants_herds_sections'] . '.section_id = ' . $section_id)
		->count_all_results();

		if($results > 0) return TRUE;
		else return FALSE;
	}
}