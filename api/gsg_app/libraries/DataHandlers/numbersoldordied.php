<?php
namespace myagsource\Page\Content\Table;

require_once APPPATH . 'libraries/Report/Content/Table/TableData.php';

use \myagsource\Report\Content\Table\TableData;

class Numbersoldordied extends TableData {
	
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
		unset($new_dataset[4]); //l1_left_60_dim_pct
		unset($new_dataset[5]); //l4_left_60_dim_pct
		unset($new_dataset[6]); //l1_died_60_dim_pct
		unset($new_dataset[7]); //l4_died_60_dim_pct

		//Insert summary data into dataset
		$new_dataset[8][] = 'Total'; //fresh_month
		$new_dataset[0][] = $l1_sold_cnt; //l1_sold_60_dim_cnt
		$new_dataset[1][] = $l1_died_cnt; //l1_died_60_dim_cnt
		$new_dataset[2][] = $l4_sold_cnt; //l4_sold_60_dim_cnt
		$new_dataset[3][] = $l4_died_cnt; //l4_died_60_dim_cnt
		$new_dataset[8][] = 'Percent';
		$new_dataset[0][] = $l1_sold_pct;
		$new_dataset[1][] = $l1_died_pct;
		$new_dataset[2][] = $l4_sold_pct;
		$new_dataset[3][] = $l4_died_pct;

		return $new_dataset;
	}
}