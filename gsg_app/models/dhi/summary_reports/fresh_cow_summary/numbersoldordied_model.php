<?php
require_once APPPATH . 'models/report_model.php';
class Numbersoldordied_model extends Report_model {
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
		$avg_l1_sold_60_dim_cnt = $arr_dataset[0]['l1_sold_60_dim_cnt'];
		$avg_l1_died_60_dim_cnt = $arr_dataset[0]['l1_died_60_dim_cnt'];
		$avg_l4_sold_60_dim_cnt = $arr_dataset[0]['l4_sold_60_dim_cnt'];
		$avg_l4_died_60_dim_cnt = $arr_dataset[0]['l4_died_60_dim_cnt'];
		$new_dataset = parent::pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column, $bool_sum_column, $bool_bench_column);
		//update total field in new dataset
		$new_dataset['l1_sold_60_dim_cnt']['total'] = $avg_l1_sold_60_dim_cnt;
		$new_dataset['l1_died_60_dim_cnt']['total'] = $avg_l1_died_60_dim_cnt;
		$new_dataset['l4_sold_60_dim_cnt']['total'] = $avg_l4_sold_60_dim_cnt;
		$new_dataset['l4_died_60_dim_cnt']['total'] = $avg_l4_died_60_dim_cnt;
		$new_dataset['l1_sold_60_dim_cnt']['average'] = round($avg_l1_sold_60_dim_cnt / 12);
		$new_dataset['l1_died_60_dim_cnt']['average'] = round($avg_l1_died_60_dim_cnt / 12);
		$new_dataset['l4_sold_60_dim_cnt']['average'] = round($avg_l4_sold_60_dim_cnt / 12);
		$new_dataset['l4_died_60_dim_cnt']['average'] = round($avg_l4_died_60_dim_cnt / 12);
		//Change Header Text
		$this->arr_fields['Total'] = 'average';
		unset($this->arr_fields['Average']);
		$this->arr_fields['Pct'] = 'total';
		unset($this->arr_fields['Total']);
		
		return $new_dataset;
	}

	/**
	 * @method prep_select_fields()
	 * @param arr_fields: copy of fields array to be formatted into SQL
	 * @return array of sql-prepped select fields
	 * @author ctranel
	 **/
	protected function prep_select_fields($arr_select_fields){
		if (($key = array_search('test_date', $arr_select_fields)) !== FALSE) {
			$arr_select_fields[$key] = "FORMAT(" . $this->primary_table_name . ".test_date, 'MM-dd-yy', 'en-US') AS test_date";//MMM-dd-yy
		}
		if (($key = array_search('calving_date', $arr_select_fields)) !== FALSE) {
			$arr_select_fields[$key] = "FORMAT(" . $this->primary_table_name . ".calving_date, 'MM-dd-yy', 'en-US') AS calving_date";//MMM-dd-yy
		}
		if (($key = array_search('fresh_month', $arr_select_fields)) !== FALSE) {
			$arr_select_fields[$key] = "FORMAT(" . $this->primary_table_name . ".fresh_month, 'MMM-yy', 'en-US') AS fresh_month";//MMM-dd-yy
		}
		if (($key = array_search('cycle_date', $arr_select_fields)) !== FALSE) {
			$arr_select_fields[$key] = "FORMAT(" . $this->primary_table_name . ".cycle_date, 'MM-dd-yy', 'en-US') AS cycle_date";//MMM-dd-yy
		}
		if (($key = array_search('summary_date', $arr_select_fields)) !== FALSE) {
			$arr_select_fields[$key] = "FORMAT(" . $this->primary_table_name . ".summary_date, 'MM-dd-yy', 'en-US') AS summary_date";//MMM-dd-yy
		}
		foreach($arr_select_fields as $k => $v){
			if(!empty($this->arr_aggregates[$k])){
				$new_name = strtolower($this->arr_aggregates[$k]) . '_' . $v;
				$arr_select_fields[$k] = $this->arr_aggregates[$k] . '(' . $this->primary_table_name . '.' . $v . ') AS ' . $new_name;
				$this->arr_db_field_list[$k] = $new_name;
				//$arr_select_fields[$k] = $new_name;
			}
		}
		return($arr_select_fields);
	}
}