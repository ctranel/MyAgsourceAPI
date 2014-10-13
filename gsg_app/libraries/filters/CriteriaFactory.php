<?php
namespace myagsource\report_filters;
require_once 'Criteria.php';


class CriteriaFactory {
	
	public function _construct(){
		
	}

	/* -----------------------------------------------------------------
	 *  criteria factory.
	
	*  Parses form data according to data type conventions.
	
	*  @since: version 1
	*  @author: ctranel
	*  @date: Oct 6, 2014
	*  @param array of criteria data
	*  @return void 
	*  @throws:
	* -----------------------------------------------------------------
	*/
	public static function createCriteria($filter_model, $criteria_data, $herd_code){
		return new Criteria($filter_model, $criteria_data, $herd_code);
	}
}

?>