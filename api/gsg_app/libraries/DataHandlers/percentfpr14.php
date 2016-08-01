<?php
namespace myagsource\Page\Content\Table;

require_once APPPATH . 'libraries/Page/Content/Table/TableData.php';

use \myagsource\Datasource\DbObjects\DbTable;
use \myagsource\Benchmarks\Benchmarks;

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
		$new_dataset['fresh_month'][] = 'Annual Average';
		$new_dataset['l1_ffp_ratio_gt14_pct'][] = $avg_l1_ffp_ratio_gt14_pct;
		$new_dataset['l4_ffp_ratio_gt14_pct'][] = $avg_l4_ffp_ratio_gt14_pct;

		return $new_dataset;
	}
}