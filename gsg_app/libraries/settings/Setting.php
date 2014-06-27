<?php
namespace myagsource\settings;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * 
* Name:  setting class
*
* Author: ctranel
*
* Created:  2014-06-20
*
*/

class Setting {
	/**
	 * @var int
	 */
	protected $id;
	
	/**
	 * @var int
	 */
	//protected $category_id;
	/**
	 * category refers to the logical structure of settings
	 * 
	 * @var string
	 */
	protected $category;
	
	/**
	 * grouping is used for layout-related groups (visual groups)
	 * 
	 * @var string
	 */
	protected $grouping;
	
	/**
	 * @var string
	 */
	protected $data_type;
	
	/**
	 * @var string
	 */
	protected $name;
	
	/**
	 * @var string
	 */
	protected $description;
	
	/**
	 * 
	 * @var mixed
	 */
	protected $value;
	
	/**
	 * @var mixed
	 */
	protected $default_value;
	
	/**
	 * @var array
	 */
	protected $lookup_options;
	
	/**
	 * obj setting_model
	 *
	 * @var object
	 */
	protected $setting_model;
	
	function __construct($arr_setting_data, $setting_model) {
//		$this->category_id = $arr_setting_data['category_id'];
		$this->setting_model = $setting_model;
		$this->id = $arr_setting_data['id'];
		$this->category = $arr_setting_data['category'];
		$this->grouping = $arr_setting_data['group'];
		$this->data_type = $arr_setting_data['type'];
		$this->name = $arr_setting_data['name'];
		$this->description = $arr_setting_data['description'];
		if($this->data_type === 'array' && isset($arr_setting_data['value']) && !is_array($arr_setting_data['value'])){
			$this->value = array($arr_setting_data['value']);
		}
		else{
			$this->value = $arr_setting_data['value'];
		}
		if($this->data_type === 'array' && isset($arr_setting_data['default_value']) && !is_array($arr_setting_data['default_value'])){
			$this->default_value = $arr_setting_data['default_value'];
		}
		else{
			$this->default_value = $arr_setting_data['default_value'];
		}
		if($this->data_type === 'data_lookup'){
			$this->loadLookupOptions();
		}
	}
	
	/* -----------------------------------------------------------------
	*  Returns setting value if set, else default value

	*  Returns setting value if set, else default value

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 25, 2014
	*  @return mixed value
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function getCurrValue(){
		return (!isset($this->value)) ? $this->default_value : $this->value;
	}

	/* -----------------------------------------------------------------
	 *  Set setting value
	
	*  Long Description
	
	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 25, 2014
	*  @param: mixed new value
	*  @return void
	*  @throws:
	* -----------------------------------------------------------------
	*/

	public function setValue($new_value){
		$this->value = $new_value;
	}

	/* -----------------------------------------------------------------
	 *  Return text used for display
	
	*  Return text used for display
	
	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 26, 2014
	*  @param: string display text for setting
	*  @return array of key=>value pairs
	*  @throws:
	* -----------------------------------------------------------------
	*/
	public function getDisplayText(){
		if($this->data_type === 'range'){
			list($from, $to) = explode('|', $this->value);
			return 'between ' . $from . ' and ' . $to;
		}
		elseif($this->data_type === 'array'){
			return implode(', ', $this->value);
		}
		else{
			return $this->value;
		}
	}
	
	
	/* -----------------------------------------------------------------
	 *  Set setting default value
	
	*  Set setting default value
	
	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 26, 2014
	*  @param: mixed new value
	*  @return void
	*  @throws:
	* -----------------------------------------------------------------
	*/

	public function setDefaultValue($new_value){
		$this->default_value = $new_value;
	}

	/* -----------------------------------------------------------------
	*  Returns all options
	
	*  Returns all options
	
	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 26, 2014
	*  @param: string setting name
	*  @return array of key=>value pairs
	*  @throws:
	* -----------------------------------------------------------------
	*/
	public function getLookupOptions(){
		if($this->data_type !== 'data_lookup'){
			return false;
		}
		return $this->lookup_options;
	}
	
	/* -----------------------------------------------------------------
	*  Loads all options
	
	*  Returns all options
	
	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 26, 2014
	*  @param: string setting name
	*  @return array of key=>value pairs
	*  @throws:
	* -----------------------------------------------------------------
	*/
	protected function loadLookupOptions(){
		if($this->data_type !== 'data_lookup'){
			return false;
		}
		$options = $this->setting_model->getLookupOptions($this->id);
		if(isset($options) && is_array($options)){
			foreach($options as $o){
				$this->lookup_options[$o['value']] = $o['description'];
			}
		}
	}
	
	/* -----------------------------------------------------------------
	*  Returns form population data for setting

	*  Returns form population data for setting

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 26, 2014
	*  @return mixed
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function getFormData(){
		if($this->data_type === 'data_lookup'){
			$ret_val['options'] = $this->lookup_options;
			$ret_val['selected'] = $this->getCurrValue();
			return $ret_val;
		}
		if($this->data_type === 'range'){
			list($from, $to) = explode('|', $this->getCurrValue());
			$ret_val['dbfrom'] = $from;
			$ret_val['dbto'] = $to;
			return $ret_val;
		}
		if($this->data_type === 'array'){
			return $this->getCurrValue();
		}
		/*
		 * @todo: add remaining data types
		 */
		die("Sorry, I don't recognize the form field data type in Settings\Form");
	}
}