<?php
require_once APPPATH . 'models/report_model.php';
class Cowpopulations_model extends Report_model {
	public function __construct($section_path){
		parent::__construct($section_path);
	}
	
	/*  
	 * @method pivot() overrides report_model
	 * @param array dataset
	 * @param string header field
	 * @param int pdf with of header field
	 * @param bool add average column
	 * @param bool add sum column
	 * @return array pivoted resultset
	 * @author Chris Tranel
	 */
	public function pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column = FALSE, $bool_sum_column = FALSE, $bool_bench_column = FALSE){
//var_dump($arr_dataset);
		$avg_l1_calving_cnt = $arr_dataset[0]['l1_calving_cnt'];
		$avg_l4_calving_cnt = $arr_dataset[0]['l4_calving_cnt'];
		$avg_l0_calving_cnt = $arr_dataset[0]['l0_calving_cnt'];
		$new_dataset = parent::pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column, $bool_sum_column, $bool_bench_column);
		//update total field in new dataset
		$new_dataset['l1_calving_cnt']['total'] = round($avg_l1_calving_cnt);
		$new_dataset['l4_calving_cnt']['total'] = round($avg_l4_calving_cnt);
		$new_dataset['l0_calving_cnt']['total'] = round($avg_l0_calving_cnt);
		$new_dataset['l1_calving_cnt']['average'] = round($avg_l1_calving_cnt / 12);
		$new_dataset['l4_calving_cnt']['average'] = round($avg_l4_calving_cnt / 12);
		$new_dataset['l0_calving_cnt']['average'] = round($avg_l0_calving_cnt / 12);
		//Change Header Text
		$this->arr_fields['Annual Average'] = 'average';
		unset($this->arr_fields['Average']);
		$this->arr_fields['Annual Total'] = 'total';
		unset($this->arr_fields['Total']);
		
		return $new_dataset;
	}
}