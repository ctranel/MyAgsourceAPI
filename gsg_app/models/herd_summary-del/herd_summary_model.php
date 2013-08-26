<?php
require_once APPPATH . 'models/report_model.php';
class Herd_summary_model extends Report_model {
	//charts are handled within this class, tables need child class (define data fields, etc.)
	public function __construct() {
		$this->section_id = '9'; //corresponds with id in "section" table of DB.  Must be B4 parent constructor
		parent::__construct();
		$this->db_group_name = 'reports';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
	}
}
