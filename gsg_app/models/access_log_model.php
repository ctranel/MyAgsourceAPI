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
	 * @param int event id
	 * @param string herd code
	 * @param string most recent test date for herd
	 * @param int herd enrollment status (id represents none, paid or trial)
	 * @param int user_id
	 * @param int group_id
	 * @param string format (web, pdf or csv) defaults to web
	 * @param string sort order (NULL, ASC or DESC) defaults to NULL
	 * @param string filter text, defaults to NULL
	 * @return boolean
	 * @author Chris Tranel
	 **/
	function write_entry($event_id, $herd_code, $recent_test_date, $herd_enroll_status_id, $user_id, $group_id, $format='web', $report_page_id = NULL, $sort=NULL, $filters=NULL){
		//if($this->as_ion_auth->is_admin()) return 1; //do not record admin action
		$tmp_array = array(
			'event_id' => $event_id,
			'herd_code' => $herd_code,
			'recent_test_date' => $recent_test_date,
			'herd_enroll_status_id' => $herd_enroll_status_id,
			'user_id' => $user_id,
			'group_id' => $group_id,
			'format' => $format,
			'report_page_id' => $report_page_id,
			'access_time' => date('Y-m-d H:i:s')
		);
		if ($report_page_id) $tmp_array['report_page_id'] = $report_page_id;
		if ($sort) $tmp_array['sort_text'] = $sort;
		if ($filters) $tmp_array['filter_text'] = $filters;
		return $this->{$this->db_group_name}->insert($this->tables['access_log'], $tmp_array);
	}
}
