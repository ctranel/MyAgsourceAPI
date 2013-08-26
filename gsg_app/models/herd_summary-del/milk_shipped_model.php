<?php
require_once APPPATH . 'models/report_model.php';
class Milk_shipped_model extends Report_model {
	public function __construct(){
		parent::__construct();
		$this->db_group_name = 'herd_summary';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);

		$this->primary_table_name = 'misc_herd_summary';
		$this->arr_joins = array(
			array('table' => 'herd_test_turnaround', 'join_text' => 'misc_herd_summary.herd_code = herd_test_turnaround.herd_code AND misc_herd_summary.test_date = herd_test_turnaround.test_date')
		);
		$this->arr_unsortable_columns = array();
	    $this->arr_notnull_fields = array("herd_code", "pstring", "test_date"); //used for imports
	   // $this->arr_zero_is_null_fields = array();
	    $this->arr_numeric_fields = array('tank_milk_lbs', 'dhi_milk_lbs', 'milk_shipped_dev_pct', 'prev_milk_shipped_dev_pct', 'prev_4test_milk_shipped_pct', 'h24hr_milk_freq_cnt'); //used for imports
		$this->arr_natural_sort_fields = array();
		$this->arr_date_fields = array('test_date');
		$this->arr_datetime_fields = array();
		$this->arr_auto_filter_field[] = 'test_date';
		$this->arr_auto_filter_operator[] = ' > ';
		$this->arr_auto_filter_criteria[] = '2000-01-01';
		$this->arr_auto_filter_alert[] = 'The current search would have resulted in over 1000 results, and would have significantly slowed the performance of the report.  
			To maintain the best performance, only recent test dates matching your selected criteria are being shown.  
			If you would like to see more results despite the negative effect on performance, please go to the filter section and select the desired filters.';
		$this->arr_fields = array(
			'PString' => 'pstring',
			'Bulk Tank' => 'tank_milk_lbs',
			'DHI Weight' => 'dhi_milk_lbs',
			'% of Bulk' => 'milk_shipped_dev_pct',
			'Prev Mo Bulk %' => 'prev_milk_shipped_dev_pct',
			'4 Mo Avg Bulk %' => 'prev_4test_milk_shipped_pct',
			'Milking Freq' => 'h24hr_milk_freq_cnt'
		);
		$this->arr_field_sort = array(
			'pstring' => 'ASC',
			'test_date' => 'DESC',
			'tank_milk_lbs' => 'DESC',
			'dhi_milk_lbs' => 'DESC',
			'milk_shipped_dev_pct' => 'DESC',
			'prev_milk_shipped_dev_pct' => 'DESC',
			'prev_4test_milk_shipped_pct' => 'DESC',
			'h24hr_milk_freq_cnt' => 'DESC'
		);
		
		$this->arr_field_table = array(
			'pstring' => $this->primary_table_name,
			'test_date' => $this->primary_table_name,
			'tank_milk_lbs' => $this->primary_table_name,
			'dhi_milk_lbs' => $this->primary_table_name,
			'milk_shipped_dev_pct' => $this->primary_table_name,
			'prev_milk_shipped_dev_pct' => $this->primary_table_name,
			'prev_4test_milk_shipped_pct' => $this->primary_table_name,
			'h24hr_milk_freq_cnt' => $this->arr_joins[0]['table']
		);
		
		$this->arr_pdf_widths = array(
			'pstring' => '13',
			'tank_milk_lbs' => '13',
			'dhi_milk_lbs' => '13',
			'milk_shipped_dev_pct' => '13',
			'prev_milk_shipped_dev_pct' => '13',
			'prev_4test_milk_shipped_pct' => '13',
			'h24hr_milk_freq_cnt' => '13'
		);
		$this->adjust_fields($this->session->userdata('herd_code'));
	}
}