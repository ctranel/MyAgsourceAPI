<?php
class Region_model extends CI_Model {
	public $error;
	/**
	 * get_region_by_name
	 *
	 * @return array of objects
	 * @author Chris Tranel
	 **/
	public function get_region_by_field($field, $value) {
		/* ----  BEGIN debugging code - for testing only --------DEBUG_SEARCH_TAG
		 *  Remove before deploying
		 *  @author: carolmd
		 *  @date: Nov 20, 2013
		 *
		 */
		echo ' get_region_by_field. $field: ';
		echo $field;
		echo ' $value: ';
		var_dump ($value);
		/* 
		 *  ----  END debugging code - for testing only------------------------------------
		 */
		if(is_array($value)) $this->db->where_in($field, $value);
		else $this->db->where($field, $value);
	    $this->db->limit(1);
		return $this->get_regions();
	}

	/**
	 * get_regions
	 *
	 * @return array of region objects
	 * @author Chris Tranel
	 **/
	public function get_regions($limit=NULL, $offset=NULL) {
		if (isset($limit) && isset($offset))
		$this->db->limit($limit, $offset);
		/* ----  BEGIN debugging code - for testing only --------DEBUG_SEARCH_TAG
		 *  Remove before deploying
		 *  @author: carolmd
		 *  @date: Nov 19, 2013
		 *
		 */
		echo 'dump results: ';
		$results = array();
		$results = $this->db->get($this->ion_auth_model->tables['regions'])->result();
		var_dump ($results);
		/* 
		 *  ----  END debugging code - for testing only------------------------------------
		 */
		return $this->db->get($this->ion_auth_model->tables['regions'])->result();
	}
	/**
	 * create_region
	 *
	 * @return bool
	 * @author Chris Tranel
	 **/
	public function create_region($data) {
		if($this->is_duplicate($data['id'])) {
			$this->error = "The region number entered already exists.  Please edit that record or enter a different region number.";
			return FALSE;
		}
		$this->db->insert($this->ion_auth_model->tables['regions'], $data);
		if($this->db->affected_rows() <= 0){
			$this->error = "Could not add record";
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * update_region
	 *
	 * @return bool
	 * @author Chris Tranel
	 **/
	public function update_region($data) {
		$has_data = is_array($data);

		if (array_key_exists('id', $data)) {
			if($this->db->update($this->ion_auth_model->tables['regions'], $data, array('id' => $data['id']))) return TRUE;
		}

		return FALSE;
	}

	/**
	 * Checks for duplicate record
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function is_duplicate($region_id = '') {
		if (empty($region_id)) return TRUE;
		return $this->db->where('id', $region_id)->count_all_results($this->ion_auth_model->tables['regions']) > 0;
	}

	/**
	 * Returns array to be passed to form helper
	 *
	 * @return array
	 * @author Mathew
	 **/
	public function get_dropdown_data(){
		$ret_array = array();
		if($this->as_ion_auth->is_admin){
			$arr_group_obj = $this->get_regions();
		}
		else{
			$arr_group_obj = $this->get_region_by_field('id', $this->session->userdata('arr_regions'));
		}
		if(is_array($arr_group_obj)) {
			$ret_array[''] = "Select one";
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
}