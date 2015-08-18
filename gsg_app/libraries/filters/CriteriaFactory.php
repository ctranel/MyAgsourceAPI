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
//var_dump($options_conditions);
		}
		//$options = $self->setOptions($filter_model, $criteria_data['options_source'], $options_conditions);
		return new Criteria($criteria_data, $options);
	}

	/* -----------------------------------------------------------------
	*  setOptions() sets looks up options

	*  sets possible filter options pulled from the database

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 17, 2014
	*  @param: string current herd code
	*  @param: array page-level filters
	*  @return void
	*  @throws: 
	* -----------------------------------------------------------------
	protected function setOptions(\Filter_model $filter_model, $options_source, $options_conditions){
		$options = null;
		if(isset($options_source) && !empty($options_source)){
			$options = $filter_model->getCriteriaOptions($options_source, $options_conditions);
		}
		return $options;
	}
	*/
}

?>