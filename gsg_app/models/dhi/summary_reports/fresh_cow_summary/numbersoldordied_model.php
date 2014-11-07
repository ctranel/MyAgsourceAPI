<?php
require_once APPPATH . 'models/report_model.php';
class Numbersoldordied_model extends Report_model {
	public function __construct($section_path){
		parent::__construct($section_path);
	}
	
	//Overriding parent function to add total and percent columns
	function get_table_header_data(){
		$this->load->library('table_header');
		$this->arr_unsortable_columns[] = 'Total';
		$this->arr_unsortable_columns[] = 'Percent';
		$this->arr_fields['Total'] = 'total';
		$this->arr_fields['Percent'] = 'percent';
		$table_header_data = array(
				'arr_unsortable_columns' => $this->arr_unsortable_columns,
				'arr_field_sort' => $this->arr_field_sort,
				'arr_header_data' => $this->arr_fields,
		);
		$table_header_data['structure'] = $this->table_header->get_table_header_array($table_header_data['arr_header_data']);
		$table_header_data['num_columns'] = $this->table_header->get_column_count();
		return $table_header_data;
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
		$l1_sold_cnt = $arr_dataset[0]['l1_sold_60_dim_cnt'];
		$l1_died_cnt = $arr_dataset[0]['l1_died_60_dim_cnt'];
		$l4_sold_cnt = $arr_dataset[0]['l4_sold_60_dim_cnt'];
		$l4_died_cnt = $arr_dataset[0]['l4_died_60_dim_cnt'];
		$l1_sold_pct = $arr_dataset[0]['l1_left_60_dim_pct'];
		$l1_died_pct = $arr_dataset[0]['l1_died_60_dim_pct'];
		$l4_sold_pct = $arr_dataset[0]['l4_left_60_dim_pct'];
		$l4_died_pct = $arr_dataset[0]['l4_died_60_dim_pct'];
		
		
		$new_dataset = parent::pivot($arr_dataset, $header_field, $header_field_width, $label_column_width, $bool_avg_column, $bool_sum_column, $bool_bench_column);
		//update total field in new dataset
		unset($new_dataset['l1_left_60_dim_pct']);
		unset($new_dataset['l4_left_60_dim_pct']);
		unset($new_dataset['l1_died_60_dim_pct']);
		unset($new_dataset['l4_died_60_dim_pct']);
		$new_dataset['l1_sold_60_dim_cnt']['total'] = $l1_sold_cnt;
		$new_dataset['l1_died_60_dim_cnt']['total'] = $l1_died_cnt;
		$new_dataset['l4_sold_60_dim_cnt']['total'] = $l4_sold_cnt;
		$new_dataset['l4_died_60_dim_cnt']['total'] = $l4_died_cnt;
		$new_dataset['l1_sold_60_dim_cnt']['percent'] = $l1_sold_pct;
		$new_dataset['l1_died_60_dim_cnt']['percent'] = $l1_died_pct;
		$new_dataset['l4_sold_60_dim_cnt']['percent'] = $l4_sold_pct;
		$new_dataset['l4_died_60_dim_cnt']['percent'] = $l4_died_pct;

		return $new_dataset;
	}
}