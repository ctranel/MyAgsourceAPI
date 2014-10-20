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
}