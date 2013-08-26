<?php
require_once APPPATH . 'models/herd_summary/herd_summary_model.php';
class Current_cow_avg_model extends Herd_summary_model {
	public function __construct() {
		parent::__construct();
		//$this->arr_pages = $this->access_log_model->get_page_links('9');
		$this->db_group_name = 'herd_summary';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);

		$this->primary_table_name = 'rpm_me_305_summary';
		$this->arr_joins = array(
			array('table' => 'rpm_lactation_summary', 'join_text' => 'rpm_me_305_summary.herd_code = rpm_lactation_summary.herd_code AND rpm_me_305_summary.pstring = rpm_lactation_summary.pstring AND rpm_me_305_summary.test_date = rpm_lactation_summary.test_date')
		);
	    $this->arr_notnull_fields = array("herd_code", "pstring", "test_date"); //used for imports
	    $this->arr_numeric_fields = array('cow_cnt', 'avg_me_305_milk_lbs', 'avg_me_305_fat_lbs', 'avg_me_305_pro_lbs', 'avg_calving_age_months',
			'avg_peak_milk_lbs', 's0_avg_mun', 'early_cow_cnt', 'early_avg_milk_lbs', 'early_avg_pct_last_milk', 'midl_cow_cnt', 'midl_avg_milk_lbs',
			'midl_avg_pct_last_milk', 'late_cow_cnt', 'late_avg_milk_lbs', 'late_avg_pct_last_milk'); //used for imports
		$this->arr_date_fields = array('test_date');
		$this->arr_auto_filter_field[] = 'test_date';
		$this->arr_auto_filter_operator[] = ' > ';
		$this->arr_auto_filter_criteria[] = '2000-01-01';
		$this->arr_auto_filter_alert[] = 'The current search would have resulted in over 1000 results, and would have significantly slowed the performance of the report.  
			To maintain the best performance, only recent test dates matching your selected criteria are being shown.  
			If you would like to see more results despite the negative effect on performance, please go to the filter section and select the desired filters.';
		$this->arr_fields = array(
			'Lact Group' => 'test_date',
			'Cow Lacts' => 'cow_cnt',
			'305 Day ME Lactation Avg' => array(
				'Milk' => 'avg_me_305_milk_lbs',
				'Fat' => 'avg_me_305_fat_lbs',
				'Pro' => 'avg_me_305_pro_lbs'
			),
			'Age Mos' => 'avg_calving_age_months',
			'Peak Milk' => 'avg_peak_milk_lbs',
			'MUN' => 's0_avg_mun',
			'Early (1 - 100 days)' => array(
					'Num' => 'early_cow_cnt',
					'Milk' => 'early_avg_milk_lbs',
					'%Last' => 'early_avg_pct_last_milk'
			),
			'Mid (101 - 240 days)' => array(
					'Num' => 'midl_cow_cnt',
					'Milk' => 'midl_avg_milk_lbs',
					'%Last' => 'midl_avg_pct_last_milk'
			),
			'Late (241 + days)' => array(
					'Num' => 'late_cow_cnt',
					'Milk' => 'late_avg_milk_lbs',
					'%Last' => 'late_avg_pct_last_milk'
			),
		);
		$this->arr_pdf_widths = array(
			'test_date' => '18',
			'cow_cnt' => '9',
			'avg_me_305_milk_lbs' => '9',
			'avg_me_305_fat_lbs' => '9',
			'avg_me_305_pro_lbs' => '9',
			'avg_calving_age_months' => '9',
			'avg_peak_milk_lbs' => '9',
			's0_avg_mun' => '9',
			'early_cow_cnt' => '9',
			'early_avg_milk_lbs' => '9',
			'early_avg_pct_last_milk' => '9',
			'midl_cow_cnt' => '9',
			'midl_avg_milk_lbs' => '9',
			'midl_avg_pct_last_milk' => '9',
			'late_cow_cnt' => '9',
			'late_avg_milk_lbs' => '9',
			'late_avg_pct_last_milk' => '9',
		);
		$this->adjust_fields($this->session->userdata('herd_code'));
	}

	/**
	 * @method get_herd_data()
	 * @param string pstring
	 * @param string last summary date
	 * @return array of benchmark data for the production graph
	 * @access public
	 *
	 **/
	function get_herd_data($pstring, $summary_start_date){
		$result = $this->{$this->db_group_name}
		->select("l1_cow_cnt, l1_avg_me_305_milk_lbs, l1_avg_me_305_fat_lbs, l1_avg_me_305_pro_lbs,
			l1_avg_calving_age_months, l1_avg_peak_milk_lbs, l1s0_avg_mun, 
			l1_early_cow_cnt, l1_early_avg_milk_lbs, l1_early_avg_pct_last_milk,
			l1_midl_cow_cnt, l1_midl_avg_milk_lbs, l1_midl_avg_pct_last_milk,
			l1_late_cow_cnt, l1_late_avg_milk_lbs, l1_late_avg_pct_last_milk,
			l2_cow_cnt, l2_avg_me_305_milk_lbs, l2_avg_me_305_fat_lbs, l2_avg_me_305_pro_lbs,
			l2_avg_calving_age_months, l2_avg_peak_milk_lbs, l2s0_avg_mun, 
			l2_early_cow_cnt, l2_early_avg_milk_lbs, l2_early_avg_pct_last_milk,
			l2_midl_cow_cnt, l2_midl_avg_milk_lbs, l2_midl_avg_pct_last_milk,
			l2_late_cow_cnt, l2_late_avg_milk_lbs, l2_late_avg_pct_last_milk,
			l3_cow_cnt, l3_avg_me_305_milk_lbs, l3_avg_me_305_fat_lbs, l3_avg_me_305_pro_lbs,
			l3_avg_calving_age_months, l3_avg_peak_milk_lbs, l3s0_avg_mun, 
			l3_early_cow_cnt, l3_early_avg_milk_lbs, l3_early_avg_pct_last_milk,
			l3_midl_cow_cnt, l3_midl_avg_milk_lbs, l3_midl_avg_pct_last_milk,
			l3_late_cow_cnt, l3_late_avg_milk_lbs, l3_late_avg_pct_last_milk,
			cow_cnt, avg_me_305_milk_lbs, avg_me_305_fat_lbs, avg_me_305_pro_lbs,
			avg_calving_age_months, avg_peak_milk_lbs, l0s0_avg_mun, 
			l0_early_cow_cnt, l0_early_avg_milk_lbs, l0_early_avg_pct_last_milk,
			l0_midl_cow_cnt, l0_midl_avg_milk_lbs, l0_midl_avg_pct_last_milk,
			l0_late_cow_cnt, l0_late_avg_milk_lbs, l0_late_avg_pct_last_milk,
			FORMAT(" . $this->primary_table_name . ".test_date, '%m/%d/%Y') AS test_date", FALSE)
		->from($this->primary_table_name)
		->join('rpm_lactation_summary', 'rpm_me_305_summary.herd_code = rpm_lactation_summary.herd_code AND rpm_me_305_summary.pstring = rpm_lactation_summary.pstring AND rpm_me_305_summary.test_date = rpm_lactation_summary.test_date', 'left')
		->join('rpm_mun_summary', 'rpm_me_305_summary.herd_code = rpm_mun_summary.herd_code AND rpm_me_305_summary.pstring = rpm_mun_summary.pstring AND rpm_me_305_summary.test_date = rpm_mun_summary.test_date', 'left')
		->where($this->primary_table_name . '.herd_code', $this->session->userdata('herd_code'))
		->where($this->primary_table_name . '.pstring', $pstring)
		->where($this->primary_table_name . '.test_date =', $summary_start_date)
		->get();
		$return_val = $result->result_array();
		return $return_val[0];
	}

	/**
	 * @method get_breed_data()
	 * @param string breed code
	 * @return array of benchmark data for the graph
	 * @access public
	 *
	 **/
	function get_breed_data($breed_code, $type_test_code){
		$result = $this->{$this->db_group_name}
		->select("avg_me_305_milk_lbs, avg_me_305_fat_lbs, avg_me_305_pro_lbs,
			avg_calving_age_months, avg_peak_milk_lbs,
			avg_early_milk_lbs, avg_early_pct_last_milk,
			avg_middle_milk_lbs, avg_middle_pct_last_milk,
			avg_late_milk_lbs, avg_late_pct_last_milk", FALSE)
		->where('breed_code', $breed_code)
		->where('type_test_code', $type_test_code)
		->get('dhi_tables.production_sire_avgs');
		$return_val = $result->result_array();
		return $return_val[0];
	}
}
