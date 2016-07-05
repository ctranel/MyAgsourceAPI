<?php
namespace myagsource\Report\Content\Table;

require_once APPPATH . 'libraries/Report/Content/Table/TableData.php';

//use \myagsource\Datasource\DbObjects\DbTable;
//use \myagsource\Benchmarks\Benchmarks;

class Transitioncowindex extends TableData {

	/*  
	 * @method pivot() overrides report_model
	 * @param array dataset
	 * @return array pivoted resultset
	 * @author ctranel
	 */
	public function pivot($arr_dataset){
		//Top row is summary data.  Pull relevant info and remove row
		$avg_avg_tci = $arr_dataset[0]['avg_tci'];
		$avg_tci_pct = $arr_dataset[0]['tci_pct'];
		unset($arr_dataset[0]);

		$new_dataset = parent::pivot($arr_dataset);

		//Insert summary data into dataset
		$new_dataset['fresh_month'][] = 'Annual Average';
		$new_dataset['avg_tci'][] = $avg_avg_tci;
		$new_dataset['tci_pct'][] = $avg_tci_pct;
		
		return $new_dataset;
	}
}