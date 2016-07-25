<?php
namespace myagsource\Page\Content\Table;

require_once APPPATH . 'libraries/Page/Content/Table/TableData.php';

use \myagsource\Datasource\DbObjects\DbTable;
//use \myagsource\Benchmarks\Benchmarks;


class Newinfectionsanddrycures extends TableData {
	/*  
	 * @method pivot() overrides report_model
	 * @param array dataset
	 * @param string header field
	 * @param int pdf with of header field
	 * @param bool add average column
	 * @param bool add sum column
	 * @return array pivoted resultset
	 * @author ctranel
	 * 
	 * @todo: update code to match refactored code
	 */
	public function pivot($arr_dataset){
		$avg_l1_1st_new_infection_pct = $arr_dataset[0]['l1_1st_new_infection_pct'];
		$avg_l4_1st_new_infection_pct = $arr_dataset[0]['l4_1st_new_infection_pct'];
		$avg_l4_dry_cow_cured_pct = $arr_dataset[0]['l4_dry_cow_cured_pct'];
		unset($arr_dataset[0]);
		
		$new_dataset = parent::pivot($arr_dataset);

		//update total field in new dataset
		$new_dataset['fresh_month'][] = 'Average';
		$new_dataset['l1_1st_new_infection_pct'][] = $avg_l1_1st_new_infection_pct;
		$new_dataset['l4_1st_new_infection_pct'][] = $avg_l4_1st_new_infection_pct;
		$new_dataset['l4_dry_cow_cured_pct'][] = $avg_l4_dry_cow_cured_pct;
		
		return $new_dataset;
	}
}