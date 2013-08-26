<?php
require_once APPPATH . 'models/report_model.php';
class Test_day_rha_prod_model extends Report_model {
	public function __construct(){
		parent::__construct();
		$this->db_group_name = 'herd_summary';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);

		$this->primary_table_name = 'view_herd_summ_block_e';
		$this->arr_joins = array(
			//array('table' => '', 'join_text' => '')
		);
		$this->arr_unsortable_columns = array();
	    $this->arr_notnull_fields = array("herd_code", "pstring", "test_date"); //used for imports
	   // $this->arr_zero_is_null_fields = array();
	    $this->arr_numeric_fields = array('l0_cow_cnt', 'l0_milk_cow_cnt', 'lact_avg_dim', 'pct_dim', 'l0_avg_mlm_lbs', 'l0_mc_avg_milk_lbs',
			'l0_avg_fat_pct', 'l0_avg_pro_pct', 'l0_wtd_avg_scc', 'avg_mun', 'special_process_code', 'rha_cow_cnt', 'rha_dim', 'rha_milk_lbs',
			'rha_fat_pct', 'rha_fat_lbs', 'rha_pro_pct', 'rha_pro_lbs', 'rha_cheese_yld'); //used for imports
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
			'Test Day Average Production' => array(
				'Test Date' => 'test_date',
				'Cows' => array(
					'Total' => 'l0_cow_cnt',
					'Milk' => 'l0_milk_cow_cnt'
				),
				'Milking Cows Only' => array(
					'DIM' => 'lact_avg_dim',
					'%LacD' => 'pct_dim',
					'MLM' => 'l0_avg_mlm_lbs',
					'Milk' => 'l0_mc_avg_milk_lbs',
					'% Fat' => 'l0_avg_fat_pct',
					'% Pro' => 'l0_avg_pro_pct',
					'SCC' => 'l0_wtd_avg_scc',
					'MUN' => 'avg_mun'
				)
			),
			'Rolling Herd Averages' => array(
				'Entire Herd' => array(
					'Cd' => 'special_process_code',
					'Cows' => 'rha_cow_cnt',
					'LDIM' => 'rha_dim',
					'Milk' => 'rha_milk_lbs',
					'% Fat' => 'rha_fat_pct',
					'Fat' => 'rha_fat_lbs',
					'% Pro' => 'rha_pro_pct',
					'Pro' => 'rha_pro_lbs',
					'Chs Yld' => 'rha_cheese_yld'
				)
			)
		);
		$this->arr_field_sort = array(
			'pstring' => 'ASC',
			'test_date' => 'DESC',
			'l0_cow_cnt' => 'DESC',
			'l0_milk_cow_cnt' => 'DESC',
			'lact_avg_dim' => 'DESC',
			'pct_dim' => 'DESC',
			'l0_avg_mlm_lbs' => 'DESC',
			'l0_mc_avg_milk_lbs' => 'DESC',
			'l0_avg_fat_pct' => 'DESC',
			'l0_avg_pro_pct' => 'DESC',
			'l0_wtd_avg_scc' => 'DESC',
			'avg_mun' => 'DESC',
			'special_process_code' => 'DESC',
			'rha_cow_cnt' => 'DESC',
			'rha_dim' => 'DESC',
			'rha_milk_lbs' => 'DESC',
			'rha_fat_pct' => 'DESC',
			'rha_fat_lbs' => 'DESC',
			'rha_pro_pct' => 'DESC',
			'rha_pro_lbs' => 'DESC',
			'rha_cheese_yld' => 'DESC',
		);
		
		$this->arr_field_table = array(
			'pstring' => $this->primary_table_name,
			'test_date' => $this->primary_table_name,
			'l0_cow_cnt' => $this->primary_table_name,
			'l0_milk_cow_cnt' => $this->primary_table_name,
			'lact_avg_dim' => $this->primary_table_name,
			'pct_dim' => $this->primary_table_name,
			'l0_avg_mlm_lbs' => $this->primary_table_name,
			'l0_mc_avg_milk_lbs' => $this->primary_table_name,
			'l0_avg_fat_pct' => $this->primary_table_name,
			'l0_avg_pro_pct' => $this->primary_table_name,
			'l0_wtd_avg_scc' => $this->primary_table_name,
			'avg_mun' => $this->primary_table_name,
			'special_process_code' => $this->primary_table_name,
			'rha_cow_cnt' => $this->primary_table_name,
			'rha_dim' => $this->primary_table_name,
			'rha_milk_lbs' => $this->primary_table_name,
			'rha_fat_pct' => $this->primary_table_name,
			'rha_fat_lbs' => $this->primary_table_name,
			'rha_pro_pct' => $this->primary_table_name,
			'rha_pro_lbs' => $this->primary_table_name,
			'rha_cheese_yld' => $this->primary_table_name,
		);
		
		$this->arr_pdf_widths = array(
			'pstring' => '11',
			'test_date' => '11',
			'l0_cow_cnt' => '11',
			'l0_milk_cow_cnt' => '11',
			'lact_avg_dim' => '11',
			'pct_dim' => '13',
			'l0_avg_mlm_lbs' => '11',
			'l0_mc_avg_milk_lbs' => '11',
			'l0_avg_fat_pct' => '11',
			'l0_avg_pro_pct' => '11',
			'l0_wtd_avg_scc' => '11',
			'avg_mun' => '13',
			'special_process_code' => '11',
			'rha_cow_cnt' => '11',
			'rha_dim' => '11',
			'rha_milk_lbs' => '10',
			'rha_fat_pct' => '11',
			'rha_fat_lbs' => '13',
			'rha_pro_pct' => '11',
			'rha_pro_lbs' => '11',
			'rha_cheese_yld' => '11',
		);
		$this->adjust_fields($this->session->userdata('herd_code'));
	}
}