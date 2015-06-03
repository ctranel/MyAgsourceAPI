<?php
namespace myagsource\Report\Content\Table;

require_once APPPATH . 'libraries/Report/Content/Table/TableData.php';

use \myagsource\Datasource\DbObjects\DbTable;
use \myagsource\Benchmarks\Benchmarks;

class Numbersoldordied extends TableData {
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
	 * @return array pivoted resultset
	 * @author ctranel
	 */
	public function pivot($arr_dataset){
		//Top row is summary data.  Pull relevant info and remove row
		$l1_sold_cnt = $arr_dataset[0]['l1_sold_60_dim_cnt'];
		$l1_died_cnt = $arr_dataset[0]['l1_died_60_dim_cnt'];
		$l4_sold_cnt = $arr_dataset[0]['l4_sold_60_dim_cnt'];
		$l4_died_cnt = $arr_dataset[0]['l4_died_60_dim_cnt'];
		$l1_sold_pct = $arr_dataset[0]['l1_left_60_dim_pct'];
		$l1_died_pct = $arr_dataset[0]['l1_died_60_dim_pct'];
		$l4_sold_pct = $arr_dataset[0]['l4_left_60_dim_pct'];
		$l4_died_pct = $arr_dataset[0]['l4_died_60_dim_pct'];
		
		$new_dataset = parent::pivot($arr_dataset);

		//Remove columns that were used only to pull summary data
		unset($new_dataset['l1_left_60_dim_pct']);
		unset($new_dataset['l4_left_60_dim_pct']);
		unset($new_dataset['l1_died_60_dim_pct']);
		unset($new_dataset['l4_died_60_dim_pct']);

		//Insert summary data into dataset
		$new_dataset['fresh_month'][] = 'Total';
		$new_dataset['l1_sold_60_dim_cnt'][] = $l1_sold_cnt;
		$new_dataset['l1_died_60_dim_cnt'][] = $l1_died_cnt;
		$new_dataset['l4_sold_60_dim_cnt'][] = $l4_sold_cnt;
		$new_dataset['l4_died_60_dim_cnt'][] = $l4_died_cnt;
		$new_dataset['fresh_month'][] = 'Percent';
		$new_dataset['l1_sold_60_dim_cnt'][] = $l1_sold_pct;
		$new_dataset['l1_died_60_dim_cnt'][] = $l1_died_pct;
		$new_dataset['l4_sold_60_dim_cnt'][] = $l4_sold_pct;
		$new_dataset['l4_died_60_dim_cnt'][] = $l4_died_pct;

		return $new_dataset;
	}
}