<?php
require_once APPPATH . 'models/report_model.php';
class Access_log_model extends Report_Model {
	public function __construct(){
		parent::__construct();
		$this->db_group_name = 'default';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
	}
	
	/**
	 * write_entry
	 *
	 * @param array of access log data
	 * @return boolean
	 * @author ctranel
	 **/
	function write_entry_to_db($data){
		return $this->{$this->db_group_name}->insert($this->tables['access_log'], $data);
	}

	/**
	 * getLogBySgHerdTest
	 *
	 * @method log_by_user_herd_test
	 * @param int user id
	 * @param string herd code
	 * @param string test date (defaults to null)
	 * @return boolean
	 * @author ctranel
	 **/
	function getLogBySgHerdTest($sg_acct_num, $herd_code, $test_date = NULL){
		if(isset($test_date)){
			$this->{$this->db_group_name}->where('recent_test_date', $test_date);
		}
		return $this->{$this->db_group_name}
			->join($this->tables['access_log_events'] . ' ale', 'al.event_id = ale.id', 'inner')
			->join($this->tables['users_service_groups'] . ' usg', 'al.user_id = usg.user_id', 'left')
			->where('usg.sg_acct_num', $sg_acct_num)
			->where('al.herd_code', $herd_code)
			->where('ale.tally_herd_access', 1)
			->get($this->tables['access_log'] . ' al')
			->result_array();
	}
}
