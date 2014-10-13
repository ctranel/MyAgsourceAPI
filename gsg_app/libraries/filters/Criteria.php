<?php
namespace myagsource\report_filters;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Filters Library File
*
* Author: ctranel
*
* Created:  10/3/2014
*
* Description:  Collection of filter criteria
*
* Requirements: PHP5 or above
*
*/

class Criteria{
	private $type;
	private $field_name;
	private $label;
	private $options;
	private $options_source;
	//private $list_order;
	private $default_value;
	private $log_filter_text;
	private $arr_selected_values = array();
	private $arr_options; //array of criteria objects
	private $filter_model;
	
	public function __construct(\Filter_model $filter_model, $criteria_data, $herd_code){
		$this->filter_model = $filter_model;
		$this->type = $criteria_data['type'];
		$this->field_name = $criteria_data['db_field_name'];
		$this->label = $criteria_data['name'];
		$this->default_value = $criteria_data['default_value'];
		$this->options_source = $criteria_data['options_source'];
		if(isset($criteria_data['arr_selected_values'])){
			if(!is_array($criteria_data['arr_selected_values'])){
				$criteria_data['arr_selected_values'] = array($criteria_data['arr_selected_values']);
			}
			$arr_tmp = array();
			foreach($criteria_data['arr_selected_values'] as $k=>$v){
				if(strpos($v, '|')){
					$arr_tmp += explode('|', $v);
				}
				else{
					$arr_tmp[] = $v;
				}
			}
				
			$this->arr_selected_values = $arr_tmp;
		}
		//$this->list_order = $criteria_data['list_order'];
		$this->setOptions($herd_code);
		$this->setDefaults();
	}

	/* -----------------------------------------------------------------
	*  Returns selected value

	*  Returns selected value

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 17, 2014
	*  @return: array 
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function getSelectedValue(){
		return $this->arr_selected_values;
	}

	/* -----------------------------------------------------------------
	*  Returns filter text

	*  Returns filter text

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 17, 2014
	*  @return: string filter text
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function get_filter_text(){
		if(empty($this->log_filter_text)){
			$this->set_filter_text();
		}
		return $this->log_filter_text;
	}

	/* -----------------------------------------------------------------
	 *  Returns array representation of object
	
	*  Returns array representation of object
	
	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 17, 2014
	*  @return: array
	*  @throws:
	* -----------------------------------------------------------------
	*/
	public function toArray(){
		$arr_return = array(
			'type' => $this->type,
			'field_name' => $this->field_name,
			'label' => $this->label,
			'arr_selected_values' => $this->arr_selected_values,
			'options' => $this->options,
		);
		return $arr_return;
	}
	
	
	/* -----------------------------------------------------------------
	*  setFilterFormCriteria() sets filter criteria based on filter form submission

	*  sets filter criteria based on filter form submission

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 17, 2014
	*  @param: array page-level filters
	*  @param: $arr_params
	*  @return void
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function setFilterFormCriteria($page_filter_value){
		if(!isset($page_filter_value)){
			return false;
		}
		//? if($field_name == 'page') $this->arr_criteria['page'] = $this->arr_pages[$this->$arr_params['page']]['name'];
		if($this->type === 'range' || $this->type === 'date range'){
			if(!isset($page_filter_value['dbfrom']) || !isset($page_filter_value['dbto'])){
				continue;
			}
			$this->arr_selected_values['dbfrom'] = $page_filter_value['dbfrom'];
			$this->arr_selected_values['dbto'] = $page_filter_value['dbto'];
		}
		elseif($this->type === 'select multiple'){
			if(is_array($page_filter_value)){
				foreach($page_filter_value as $k1=>$v1){
					$page_filter_value[$k1] = explode('|', $v1);
				}
				$page_filter_value = array_flatten($page_filter_value);
				$this->arr_selected_values = $page_filter_value;
			}
			else {
				$this->arr_selected_values = array($page_filter_value);
			}
		}
		elseif(strpos($page_filter_value, '|')){
			$this->arr_selected_values = explode('|', $page_filter_value);
		}
		else {
			$this->arr_selected_values = array($page_filter_value);
		}
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
	*/
	protected function setOptions($herd_code){
		if(isset($this->options_source) && !empty($this->options_source)){
			//if(strpos($this->type, 'herd') !== false){
			$this->options = $this->filter_model->getCriteriaOptions($this->options_source, $herd_code);
			//}
		}
	}

	/* -----------------------------------------------------------------
	*  setDefaults() sets looks up options and sets default criteria

	*  sets default filter criteria pulled from the database.  Defaults can either be set
	*  by the default property of the filter, or the default flag of the options

	*  @since: version 1
	*  @author: ctranel
	*  @date: Oct 9, 2014
	*  @return void
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	protected function setDefaults(){
		if(isset($this->arr_selected_values) && !empty($this->arr_selected_values)){
			return; //default not needed
		}
		
		//if default property of the filter is set, we don't need to look for the option flag
		if(!isset($this->default_value) || empty($this->default_value)){
			if(!isset($this->options)){//no defaults to be found
				return;
			}
			if(isset($this->options) && is_array($this->options)){
				foreach($this->options as $o){
					if($o['is_default']){
						$this->default_value = $o['value'];
					}
				}
			}
		}
		if(isset($this->default_value) && !empty($this->default_value)){
			if(is_array($this->default_value)){
				if($this->type === 'range' || $this->type === 'date range'){
					if(!isset($this->default_value)){
						$this->default_value = '|';
					}
					if(strpos($this->default_value, '|') !== FALSE){
						list($this->arr_selected_values['dbfrom'], $this->arr_selected_values['dbto']) = explode('|', $this->default_value);
					}
				}
			}
			else $this->arr_selected_values = array($this->default_value);
		}
	}

	/**
	 * set_filter_text
	 * @description sets arr_filter_text variable.  Composes filter text property
	 * @author ctranel
	 * @return void
	 * 
	 **/
	public function set_filter_text(){
		$this->log_filter_text = '';
		if(is_array($this->arr_selected_values) && !empty($this->arr_selected_values)){
			//@todo: filter null string out when criteria is set
			if(($tmp_key = array_search('NULL', $this->arr_selected_values)) !== FALSE){
				unset($this->arr_selected_values[$tmp_key]);
			}
			//if it is a range
			elseif(key($this->arr_selected_values) === 'dbfrom' || key($this->arr_selected_values) === 'dbto'){
				$this->log_filter_text = $this->label . ': Between ' . $this->arr_selected_values['dbfrom'] . ' and ' . $this->arr_selected_values['dbto'];
			}
			else{
				$this->log_filter_text = $this->label . ': ' . implode(', ', $this->arr_selected_values);
			}
		}
		elseif(!empty($this->arr_selected_values)){
			$arr_filter_text[] = $this->label . ': ' . $this->arr_selected_values;
		}
	}
}