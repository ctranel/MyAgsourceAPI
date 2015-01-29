<?php
require_once APPPATH . 'models/report_model.php';
class Fresh_cow_summary_model extends Report_model {
	public function __construct($section_path){
		parent::__construct($section_path);
	}

	/**
	 * @method get_graph_data()
	 * @param array of field names
	 * @param string herd code
	 * @param int number of dates to include
	 * @param string name of field that represents date/x axis
	 * @param string block url
	 * @param array categories
	 * @return array of benchmark data for the production graph
	 * @access public
	 * 
	 * overrides parent function for percentile-based boxplots
	 *
	 **/
	function get_graph_data($arr_fieldname_base, $herd_code, $num_dates = 12, $date_field, $block_url, $categories = NULL){
		$data = $this->get_graph_dataset($herd_code, $num_dates, $date_field, $block_url);
		$num_boxplots = (count($arr_fieldname_base) / 3);
		$return_val = $this->set_boxplot_data($data, 'fresh_month', $num_boxplots);
		return $return_val;
	}
	
	function getFCPageTip($herd_code) {
		$page_tip = array();
		$statement = 'Cow Populations and Number of Sold or Died events tables report actual numbers by month.  All other results determined using ';
		$statement2 = ' test day';
		$resultset = $this->db
		->select('herd_size_code')
		->where('herd_code', $herd_code)
		->get('vma.dbo.vma_Fresh_Cow_Num_Tests')
		->result_array();
		if(isset($resultset[0]) && !empty($resultset[0])) {
			if ($resultset[0]['herd_size_code'] > 1) $statement2.= 's';
			$page_tip['numbertests'] = $statement.$resultset[0]['herd_size_code'].$statement2;
		}
		else {
			$page_tip['numbertests'] = 'Number of tests in composite not found';
		}
		return $page_tip;
	
	}
	
}