<?php
require_once APPPATH . 'models/report_model.php';
class Percentfpr14_model extends Report_model {
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
		$avg_l1_ffp_ratio_gt14_pct = $arr_dataset[0]['l1_ffp_ratio_gt14_pct'];
		$avg_l4_ffp_ratio_gt14_pct = $arr_dataset[0]['l4_ffp_ratio_gt14_pct'];
		$new_dataset = parent::pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column, $bool_sum_column, $bool_bench_column);
		//update total field in new dataset
		$new_dataset['l1_ffp_ratio_gt14_pct']['average'] = $avg_l1_ffp_ratio_gt14_pct;
		$new_dataset['l4_ffp_ratio_gt14_pct']['average'] = $avg_l4_ffp_ratio_gt14_pct;
		//Change Header Text
		$this->arr_fields['Annual Average'] = 'average';
		unset($this->arr_fields['Average']);
		
		return $new_dataset;
	}
}