<?php
namespace myagsource\Report\Content;

require_once APPPATH . 'libraries/Report/Content/BlockData.php';



class Newinfectionsanddrycures extends BlockData {
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
		$avg_l1_1st_new_infection_pct = $arr_dataset[0]['l1_1st_new_infection_pct'];
		$avg_l4_1st_new_infection_pct = $arr_dataset[0]['l4_1st_new_infection_pct'];
		$avg_l4_dry_cow_cured_pct = $arr_dataset[0]['l4_dry_cow_cured_pct'];
		$new_dataset = parent::pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column, $bool_sum_column, $bool_bench_column);
		//update total field in new dataset
		$new_dataset['l1_1st_new_infection_pct']['average'] = $avg_l1_1st_new_infection_pct;
		$new_dataset['l4_1st_new_infection_pct']['average'] = $avg_l4_1st_new_infection_pct;
		$new_dataset['l4_dry_cow_cured_pct']['average'] = $avg_l4_dry_cow_cured_pct;
		//Change Header Text
		$this->arr_fields['Annual Average'] = 'average';
		unset($this->arr_fields['Average']);
		
		return $new_dataset;
	}
}