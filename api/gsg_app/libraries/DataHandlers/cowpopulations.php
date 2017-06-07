<?php
namespace myagsource\Page\Content\Table;

require_once APPPATH . 'libraries/Report/Content/Table/TableData.php';

use \myagsource\Report\Content\Table\TableData;

class Cowpopulations extends TableData {
	
	/*  
	 * @method pivot() overrides report_model
	 * @param array dataset
	 * @return array pivoted resultset
	 * @author ctranel
	 */
	public function pivot($arr_dataset){
		//Top row is summary data.  Pull relevant info and remove row
		$l1_avg_calving_cnt = $arr_dataset[0]['l1_avg_calving_cnt'];
		$l4_avg_calving_cnt = $arr_dataset[0]['l4_avg_calving_cnt'];
		$l0_avg_calving_cnt = $arr_dataset[0]['l0_avg_calving_cnt'];
		$l1_tot_calving_cnt = $arr_dataset[0]['l1_calving_cnt'];
		$l4_tot_calving_cnt = $arr_dataset[0]['l4_calving_cnt'];
		$l0_tot_calving_cnt = $arr_dataset[0]['l0_calving_cnt'];
        unset($arr_dataset[0]);
        //reset array index to 0
        $arr_dataset = array_values($arr_dataset);

		$new_dataset = parent::pivot($arr_dataset);

		//Remove columns that were used only to pull summary data
		unset($new_dataset[3]); //l1_avg_calving_cnt
		unset($new_dataset[4]); //l4_avg_calving_cnt
		unset($new_dataset[5]); //l0_avg_calving_cnt

		//Insert summary data into dataset
		$new_dataset[6][] = 'Annual Average'; //fresh_month
		$new_dataset[0][] = $l1_avg_calving_cnt; //l1_calving_cnt
		$new_dataset[1][] = $l4_avg_calving_cnt; //l4_calving_cnt
		$new_dataset[2][] = $l0_avg_calving_cnt; //l0_calving_cnt
		$new_dataset[6][] = 'Annual Total';
		$new_dataset[0][] = $l1_tot_calving_cnt;
		$new_dataset[1][] = $l4_tot_calving_cnt;
		$new_dataset[2][] = $l0_tot_calving_cnt;

		return $new_dataset;
	}
}