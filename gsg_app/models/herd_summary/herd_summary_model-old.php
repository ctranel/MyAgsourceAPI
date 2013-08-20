<?php
require_once APPPATH . 'models/report_model.php';
class Herd_summary_model extends Report_model {
	//charts are handled within this class, tables need child class (define data fields, etc.)
	
	public function __construct() {
		parent::__construct();
		$this->db_group_name = 'herd_summary';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
//		$this->arr_unsortable_columns = array();
		$this->arr_pages = $this->access_log_model->get_page_links('9');
//		$this->primary_table_name = 'misc_herd_summary';
//		$this->arr_joins = array(
//			array('table' => 'herd_test_turnaround', 'join_text' => 'misc_herd_summary.herd_code = herd_test_turnaround.herd_code AND misc_herd_summary.test_date = herd_test_turnaround.test_date')
//		);
//		$this->tables = array(
//			'test_day_rha_prod' => 'view_herd_summ_block_e',
//		);
	}
	
	function get_pdf_widths(){
		$this->arr_pdf_widths = array(
		);
		return $this->arr_pdf_widths;
	}

	/**
	 * @method get_field_sort()
	 * @return array (field name => sort order)
	 * @author Chris Tranel
	 **/
	function get_field_sort(){
		$this->arr_field_sort = array(
		);
		return $this->arr_field_sort;
	}
	
	/**
	 * @method get_test_date()
	 * @return string test date
	 * @access public
	 *
	 **/
	function get_test_date(){
		return $this->test_date;
	}
	
	//block b
	/**
	 * @method get_me305_avg_data()
	 * @param array fieldnames of series' to be graphed
	 * @param string herd code
	 * @param int pstring id
	 * @param int number of test dates to include
	 * @return array of data for the graph
	 * @access public
	 *
	 **/
	function get_me305_avg_data($arr_fieldname_base, $herd_code, $pstring, $num_tests = 12) {
		if(is_array($arr_fieldname_base) && !empty($arr_fieldname_base)) {
			$this->{$this->db_group_name}
			->select('herd_rha.test_date')
			->select(implode(', ', $arr_fieldname_base));	
		}
		
		$data = $this->{$this->db_group_name}
		->where('herd_rha.herd_code', $this->session->userdata('herd_code'))
		->where('herd_rha.pstring', $pstring)
//		->where('test_date >=', $summary_start_date)
		->order_by('herd_rha.test_date', 'desc')
		->limit($num_tests)
		->from('herd_rha')
		->join('rpm_me_305_summary', 'herd_rha.herd_code = rpm_me_305_summary.herd_code AND herd_rha.pstring = rpm_me_305_summary.pstring AND herd_rha.test_date = rpm_me_305_summary.test_date', 'left')
		->get()->result_array();
		
		$return_val = $this->set_longitudinal_data($data, $arr_fieldname_base);
		return $return_val;
	}
	

}
