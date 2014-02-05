<?php
/**
 * @method get_infect_summary_data()
 * @param string pstring
 * @param string last summary date
 * @return array of benchmark data for the production graph
 * @access public
 *
 **/
function get_boxplot_data($arr_fieldname_base, $herd_code, $pstring, $num_tests = 12, $num_boxplots = 1){
	if(is_array($arr_fieldname_base) && !empty($arr_fieldname_base)) {
		$this->{$this->db_group_name}
		->select("CONCAT_WS('-', tci_year, tci_month, '01') AS test_date", FALSE)
		->select(implode(', ', $arr_fieldname_base), FALSE);
	}

	$data = $this->{$this->db_group_name}
	->where('herd_code', $this->session->userdata('herd_code'))
	->where('pstring', $pstring)
	->where('tci_month IS NOT NULL')
	->order_by("CAST(CONCAT_WS('-', tci_year, tci_month, '01') AS DATE)", 'desc')
	->limit($num_tests)
	->get($this->tables['fresh_cow_summary'])
	->result_array();
	$return_val = $this->set_boxplot_data($data, $num_boxplots);
	return $return_val;
}
