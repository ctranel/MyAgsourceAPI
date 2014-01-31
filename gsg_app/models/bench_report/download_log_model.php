<?php
require_once APPPATH . 'models/report_model.php';
class Download_log_model extends Report_model {
	public function __construct()
	{
		parent::__construct();
		$this->db_group_name = 'bench';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);

		$this->arr_pages = $this->access_log_model->get_page_links('2');
		$this->primary_table_name = 'download_log';
		$this->arr_joins = array(
			//array('table' => '', 'join_text' => '')
		);
		$this->arr_unsortable_columns = array();
	    $this->arr_notnull_fields = array(); //used for imports
	    $this->arr_zero_is_null_fields = array();
	    $this->arr_numeric_fields = array(); //used for imports
		$this->arr_mixed_sort_fields = array();
		$this->arr_date_fields = array();
		$this->arr_datetime_fields = array('access_time');
		$this->arr_auto_filter_field[] = 'access_time';
		$this->arr_auto_filter_operator[] = ' > ';
		$this->arr_auto_filter_criteria[] = date('Y-m-d', strtotime("-1 week"));
		$this->arr_auto_filter_alert[] = 'The current search would have resulted in over 1000 results, and would have significantly slowed the performance of the report.  
			To maintain the best performance, results from the past week that match your selected criteria are being shown.  
			If you would like to see more results despite the negative effect on performance, please go to the filter section and select the quartiles you would like to include in your results.';
			
		$this->arr_fields = Array(
			'Access Time'=>'access_time',
			'Event'=>'page_id',
			'User'=>'user_id'
		);
		
		$this->arr_fields_sort = array(
			'access_time' => 'DESC',
			'page_id' => 'ASC',
			'user_id' => 'ASC'
		);
	
		$this->arr_field_table = array(
			'access_time' => $this->primary_table_name,
			'page_id' => $this->primary_table_name,
			'user_id' => $this->primary_table_name
		);
	
		$this->arr_pdf_widths = array(
			'access_time' => '20',
			'page_id' => '120',
			'user_id' => '120'
		);
	}
		
	/**
	 * @method prep_select_fields() allows you to override basic defaults for fields included in results, including adding joins.
	 * @param array fields to include
	 * @return modified array of fields
	 * @author ctranel
	 **/
	function prep_select_fields($arr_fields) {
		if(is_array($arr_fields)){
			// resolve field name/data/format exceptions
			if (($key = array_search('page_id', $arr_fields)) !== false) {
				$arr_fields[$key] = 'download_log_pages.name AS page_id';
				$this->{$this->db_group_name}->join('download_log_pages', 'download_log.page_id = download_log_pages.id');
			}
			if (($key = array_search('user_id', $arr_fields)) !== false) {
				$arr_fields[$key] = "CONCAT(users.name, ' - ', users.company, ' - ', users.email) AS user_id";
				$this->{$this->db_group_name}->join('users', 'download_log.user_id = users.id');
			} 
			//convert dates
			if (($key = array_search('access_time', $arr_fields)) !== FALSE) $arr_fields[$key] = "FORMAT(access_time,'%c-%e-%y, %k:%i') AS 'access_time'";
		}
		return $arr_fields;
	}
		
	/**
	 * get_keyed_page_array
	 *
	 * @return 1d array (id=>name)
	 * @author ctranel
	 **/
	public function get_keyed_page_array() {
		$ret_array = array();
		$this->{$this->db_group_name}->select('id, name')
		->order_by('id', 'asc');
		$arr_page_obj = $this->{$this->db_group_name}->get('download_log_pages')->result();
		if(is_array($arr_page_obj)) {
			foreach($arr_page_obj as $e){
				$ret_array[$e->id] = $e->name;
			}
			return $ret_array;
		}
		else return false;
	}
}