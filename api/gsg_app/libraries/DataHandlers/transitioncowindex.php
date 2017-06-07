<?php
namespace myagsource\Page\Content\Table;

require_once APPPATH . 'libraries/Report/Content/Table/TableData.php';

use \myagsource\Report\Content\Table\TableData;

class Transitioncowindex extends TableData {

	/*  
	 * @method pivot() overrides report_model
	 * @param array dataset
	 * @return array pivoted resultset
	 * @author ctranel
	 */
	public function pivot($arr_dataset){
		//Top row is summary data.  Pull relevant info and remove row
		$avg_avg_tci = $arr_dataset[0]['avg_tci']; //
		$avg_tci_pct = $arr_dataset[0]['tci_pct']; //
        unset($arr_dataset[0]);
        //reset array index to 0
        $arr_dataset = array_values($arr_dataset);

		$new_dataset = parent::pivot($arr_dataset);

		//Insert summary data into dataset
		$new_dataset[2][] = 'Annual Average'; //fresh month
		$new_dataset[0][] = $avg_avg_tci; //avg_tci
		$new_dataset[1][] = $avg_tci_pct; //tci_pct
		
		return $new_dataset;
	}
}