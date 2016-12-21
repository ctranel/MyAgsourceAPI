<?php
namespace myagsource\Filters;
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
	*  @param \Filter_model
	*  @param array of criteria data
	*  @param criteria option conditions
	*  @return void 
	*  @throws:
	*  @todo: options should be passed to constructor rather than filter_model and options_conditions
	* -----------------------------------------------------------------
	*/
	public static function createCriteria(\Filter_model $filter_model, $criteria_data, $options_conditions){
		$options = null;
		if(isset($criteria_data['options_source']) && !empty($criteria_data['options_source'])){
			$options = $filter_model->getCriteriaOptions($criteria_data['options_source'], $options_conditions);
		}
		return new Criteria($criteria_data, $options);
	}

}

?>