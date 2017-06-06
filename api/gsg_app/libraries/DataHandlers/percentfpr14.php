<?php
namespace myagsource\Page\Content\Table;

require_once APPPATH . 'libraries/Report/Content/Table/TableData.php';

use \myagsource\Report\Content\Table\TableData;

class Percentfpr14 extends TableData {
	/*  
	 * @method pivot() overrides report_model
	 * @param array dataset
	 * @return array pivoted resultset
	 * @author ctranel
	 */
	public function pivot($arr_dataset){
		//Top row is summary data.  Pull relevant info and remove row
		$avg_l1_ffp_ratio_gt14_pct = $arr_dataset[0]['l1_ffp_ratio_gt14_pct'];
		$avg_l4_ffp_ratio_gt14_pct = $arr_dataset[0]['l4_ffp_ratio_gt14_pct'];
		unset($arr_dataset[0]);
		
		$new_dataset = parent::pivot($arr_dataset);

		//Insert summary data into dataset
		$new_dataset[2][] = 'Annual Average'; //fresh_month
		$new_dataset[0][] = $avg_l1_ffp_ratio_gt14_pct; //l1_ffp_ratio_gt14_pct
		$new_dataset[1][] = $avg_l4_ffp_ratio_gt14_pct; //l4_ffp_ratio_gt14_pct

		return $new_dataset;
	}
}