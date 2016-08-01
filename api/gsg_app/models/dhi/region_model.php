<?php
class Region_model extends CI_Model {
	public $error;
	/* -----------------------------------------------------------------
	 *  UPDATE comment
	 *  @author: carolmd
	 *  @date: Nov 25, 2013
	 *
	 *  @description: Added custruct function to allow loading ion_auth tables config.
	 *  
	 *  -----------------------------------------------------------------
	 */
	public function __construct(){
		parent::__construct();
		//initialize db tables data
		$this->tables  = $this->config->item('tables', 'ion_auth');
	}
	
	/**
	 * get_region_by_name
	 *
	 * @return array of objects
	 * @author ctranel
	 **/
	public function get_region_by_field($field, $value) {
		if(is_array($value)) $this->db->where_in($field, $value);
		else $this->db->where($field, $value);
	    $this->db->limit(1);
		return $this->get_regions();
	}

	/**
	 * get_regions
	 *
	 * @return array of region objects
	 * @author ctranel
	 **/
	public function get_regions($limit=NULL, $offset=NULL) {
		if (isset($limit) && isset($offset))
		$this->db->limit($limit, $offset);
		return $this->db->get($this->tables['regions'])->result();
	}

	/**
	 * Returns array to be passed to form helper
	 *
	 * @return array association_num => assoc_name
	 * @author Mathew
	 **/
	public function get_dropdown_data(){
		$ret_array = array();
		if($this->as_ion_auth->is_admin){
			$arr_group_obj = $this->get_regions();
		}
		else{
			$arr_group_obj = $this->get_region_by_field('association_num', array_keys($this->session->userdata('arr_regions')));
		}
		if(is_array($arr_group_obj)) {
			$ret_array[''] = "Select one";
			foreach($arr_group_obj as $g){
				$ret_array[$g->association_num] = $g->assoc_name;
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
}