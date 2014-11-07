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
	 * @author ctranel
	 */
	public function pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column = FALSE, $bool_sum_column = FALSE, $bool_bench_column = FALSE){
		$l1_avg_calving_cnt = $arr_dataset[0]['l1_avg_calving_cnt'];
		$l4_avg_calving_cnt = $arr_dataset[0]['l4_avg_calving_cnt'];
		$l0_avg_calving_cnt = $arr_dataset[0]['l0_avg_calving_cnt'];
		$l1_tot_calving_cnt = $arr_dataset[0]['l1_calving_cnt'];
		$l4_tot_calving_cnt = $arr_dataset[0]['l4_calving_cnt'];
		$l0_tot_calving_cnt = $arr_dataset[0]['l0_calving_cnt'];
		$new_dataset = parent::pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column, $bool_sum_column, $bool_bench_column);
		//update total field in new dataset
		unset($new_dataset['l1_avg_calving_cnt']);
		unset($new_dataset['l4_avg_calving_cnt']);
		unset($new_dataset['l0_avg_calving_cnt']);
		$new_dataset['l1_calving_cnt']['total'] = $l1_tot_calving_cnt;
		$new_dataset['l4_calving_cnt']['total'] = $l4_tot_calving_cnt;
		$new_dataset['l0_calving_cnt']['total'] = $l0_tot_calving_cnt;
		$new_dataset['l1_calving_cnt']['average'] = $l1_avg_calving_cnt;
		$new_dataset['l4_calving_cnt']['average'] = $l4_avg_calving_cnt;
		$new_dataset['l0_calving_cnt']['average'] = $l0_avg_calving_cnt;
		//Change Header Text
		$this->arr_fields['Annual Average'] = 'average';
		unset($this->arr_fields['Average']);
		$this->arr_fields['Annual Total'] = 'total';
		unset($this->arr_fields['Total']);
		
		return $new_dataset;
	}
}