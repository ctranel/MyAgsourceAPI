<?php
namespace myagsource\Filters;

use myagsource\Settings\Settings;

require_once(APPPATH . 'helpers/multid_array_helper.php');

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
//@todo: extends FormControl
class Criteria{
	private $type;
	private $field_name;
	private $label;
	private $user_editable;
	private $options;
	private $options_source;
	//private $list_order;
    private $default_value;
	private $log_filter_text;
	private $selected_values;
    /*
     * var Settings
     */
    private $settings;
	private $arr_options; //array of criteria objects
	
	public function __construct($criteria_data, $options, Settings $settings = NULL){
		$this->type = $criteria_data['type'];
		$this->options = $options;
		$this->field_name = $criteria_data['db_field_name'];
		$this->label = $criteria_data['name'];
		$this->default_value = $criteria_data['default_value'];
		$this->options_source = $criteria_data['options_source'];
//		$this->options_filter_form_field_name = $criteria_data['options_filter_form_field_name'];
		$this->user_editable = (bool)$criteria_data['user_editable'];
        $this->settings = $settings;
		if(isset($criteria_data['selected_values'])){
			$this->setFilterCriteria($criteria_data['selected_values']);
		}

		
		

		//$this->list_order = $criteria_data['list_order'];
		//$this->setOptions($options_conditions);
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
		return $this->selected_values;
	}

	/* -----------------------------------------------------------------
	*  isDisplayed

	*  Returns boolean

	*  @author: ctranel
	*  @date: 05-06-2015
	*  @return: boolean 
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function isDisplayed(){
		return ((count($this->options) > 1 || !isset($this->options_source)) && $this->user_editable);
	}

    /* -----------------------------------------------------------------
    *  Returns operator based on type

    *  Returns selected value

    *  @since: version 1
    *  @author: ctranel
    *  @date: Jun 17, 2014
    *  @return: array
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function operator(){
        $operator = '=';
        if(strpos($this->type, 'array') !== false){
            $operator = 'IN';
        }
        if(strpos($this->type, 'range') !== false){
            $operator = 'BETWEEN';
        }

        return $operator;
    }

    /* -----------------------------------------------------------------
    *  Returns filter text

    *  Returns filter text

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
			'selected_values' => $this->selected_values,
			'options' => $this->options,
            'editable' => $this->user_editable,
		);
		return $arr_return;
	}
	
	
	/* -----------------------------------------------------------------
	*  Sets selected value

	*  Sets selected value

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 17, 2014
	*  @param $value
	*  @return: void 
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function setSelectedValue($value){
		if(!isset($value)){
			return false;
		}
		if(!is_array($value) && strpos($this->type, 'array') !== false){
			$value = [$value];
		}
		$this->selected_values = $value;
	}

	/* -----------------------------------------------------------------
	*  setFilterCriteria() sets filter criteria based on filter form submission

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
	public function setFilterCriteria($page_filter_value){
		if(!isset($page_filter_value)){
			//nothing to do here
            return;
		}
		//? if($field_name == 'page') $this->arr_criteria['page'] = $this->arr_pages[$this->$arr_params['page']]['name'];
		if(strpos($this->type, 'range') !== false){ //data should be a 2 element assoc array
			if(!isset($page_filter_value[0]) || !isset($page_filter_value[1])){
				//can't set a range unless both ends are set
                return;
			}
			$this->selected_values[0] = $page_filter_value[0];
			$this->selected_values[1] = $page_filter_value[1];
		}
		elseif(strpos($this->type, 'array') !== false){ //data should be an array
			if(is_array($page_filter_value)){
                foreach($page_filter_value as $k1=>$v1){
                    $page_filter_value[$k1] = explode('|', $v1);
                }
                $page_filter_value = \array_flatten($page_filter_value);
                $this->selected_values = $page_filter_value;
            }
            elseif(strpos($page_filter_value, '|')){
                $this->selected_values = explode('|', $page_filter_value);
            }
            else {
                $this->selected_values = [$page_filter_value];
            }
        }
		else{ //take data as-is
            $this->selected_values = $page_filter_value;
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
		if(isset($this->selected_values) && !empty($this->selected_values)){
			return; //default not needed
		}
		
		//if default property of the filter is not set, and the filter has options, look for the default flag on options
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
		if(isset($this->default_value)){
            if(strpos($this->type, 'range') !== false) {
                //selected values should use numeric index so it is sent to client as an array
                if(isset($this->default_value['dbfrom']['FUNC'])){
                    if($this->default_value['dbfrom']['FUNC'] === 'CURRDATE') {
                        $this->selected_values[0] = date('Y-m-d');
                    }
                }
                elseif(isset($this->default_value['dbfrom']['SET'])){
                    $this->selected_values[0] = $this->settings->getValue($this->default_value['dbfrom']['SET']);
                }
                else{
                    $this->selected_values[0] = $this->default_value['dbfrom'];
                }

                if(isset($this->default_value['dbto']['FUNC'])){
                    if($this->default_value['dbto']['FUNC'] === 'CURRDATE') {
                        $this->selected_values[1] = date('Y-m-d');
                    }
                }
                elseif(isset($this->default_value['dbto']['SET'])){
                    $this->selected_values[1] = $this->settings->getValue($this->default_value['dbto']['SET']);
                }
                else{
                    $this->selected_values[1] = $this->default_value['dbto'];
                }
                return;
            }
            elseif(strpos($this->type, 'array') !== false){
                if(isset($this->default_value) && is_array($this->default_value)) {
                    $this->selected_values = $this->default_value;
                }
                if(isset($this->default_value) && !is_array($this->default_value)){
                    $this->selected_values = [$this->default_value];
                }
                if(isset($this->default_value)){
                    $this->selected_values = [];
                }
            }
            else {
                if(isset($this->default_value['FUNC'])){
                    if($this->default_value['FUNC'] === 'CURRDATE') {
                        $this->selected_values = date('Y-m-d');
                    }
                }
                elseif(isset($this->default_value['SET'])){
                    $this->selected_values = $this->settings->getValue($this->default_value['SET']);
                }
                else {
                    $this->selected_values = $this->default_value;
                }
            }
		}
	}

	/**
	 * set_filter_text
	 * 
	 * Sets arr_filter_text variable.  Composes filter text property
	 * 
	 * @author ctranel
	 * @return void
	 * 
	 **/
	public function set_filter_text(){
		$this->log_filter_text = '';
		$val = array_filter($this->selected_values);
		if(is_array($val) && !empty($val)){
			if(($tmp_key = array_search('NULL', $val)) !== FALSE){
				unset($val[$tmp_key]);
			}
			//if it is a range
			elseif(key($val) === 'dbfrom' || key($val) === 'dbto'){
				if(isset($val['dbfrom']) && isset($val['dbto'])){
                    $this->log_filter_text = $this->label . ': Between ' . $val['dbfrom'] . ' and ' . $val['dbto'];
                }
			}
			else{
				$this->log_filter_text = $this->label . ': ' . implode(', ', $val);
			}
		}
		elseif(!empty($val)){
			$this->log_filter_text = $this->label . ': ' . $val;
		}
	}
}