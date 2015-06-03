<?php
namespace myagsource\Report\Content\Table;

require_once APPPATH . 'libraries/Report/Content/Table/TableData.php';

use \myagsource\Datasource\DbObjects\DbTable;
use \myagsource\Benchmarks\Benchmarks;

class Cowpopulations extends TableData {
	
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
	public function pivot($arr_dataset){
		$l1_avg_calving_cnt = $arr_dataset[0]['l1_avg_calving_cnt'];
		$l4_avg_calving_cnt = $arr_dataset[0]['l4_avg_calving_cnt'];
		$l0_avg_calving_cnt = $arr_dataset[0]['l0_avg_calving_cnt'];
		$l1_tot_calving_cnt = $arr_dataset[0]['l1_calving_cnt'];
		$l4_tot_calving_cnt = $arr_dataset[0]['l4_calving_cnt'];
		$l0_tot_calving_cnt = $arr_dataset[0]['l0_calving_cnt'];
		unset($arr_dataset[0]);
		
		$new_dataset = parent::pivot($arr_dataset);
		//update total field in new dataset
		unset($new_dataset['l1_avg_calving_cnt']);
		unset($new_dataset['l4_avg_calving_cnt']);
		unset($new_dataset['l0_avg_calving_cnt']);

		$new_dataset['fresh_month'][] = 'Annual Average';
		$new_dataset['l1_calving_cnt'][] = $l1_avg_calving_cnt;
		$new_dataset['l4_calving_cnt'][] = $l4_avg_calving_cnt;
		$new_dataset['l0_calving_cnt'][] = $l0_avg_calving_cnt;
		$new_dataset['fresh_month'][] = 'Annual Total';
		$new_dataset['l1_calving_cnt'][] = $l1_tot_calving_cnt;
		$new_dataset['l4_calving_cnt'][] = $l4_tot_calving_cnt;
		$new_dataset['l0_calving_cnt'][] = $l0_tot_calving_cnt;
		return $new_dataset;
	}
}