<?php
require_once APPPATH . 'models/report_model.php';
class Transitioncowindex_model extends Report_model {
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
		$avg_avg_tci = $arr_dataset[0]['avg_tci'];
		$avg_tci_pct = $arr_dataset[0]['tci_pct'];
		$new_dataset = parent::pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column, $bool_sum_column, $bool_bench_column);
		//update total field in new dataset
		$new_dataset['avg_tci']['average'] = $avg_avg_tci;
		$new_dataset['tci_pct']['average'] = $avg_tci_pct;
		//Change Header Text
		$this->arr_fields['Annual Average'] = 'average';
		unset($this->arr_fields['Average']);
		
		return $new_dataset;
	}
}