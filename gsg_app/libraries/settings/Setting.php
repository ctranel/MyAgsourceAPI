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
	 * id of the actual setting, not the id of the user-herd instance
	 * 
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
	protected $session_value;
	 */
	
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
		if(($this->data_type === 'array' || $this->data_type === 'data_lookup_arr') && isset($arr_setting_data['value']) && !is_array($arr_setting_data['value'])){
			$this->value = array($arr_setting_data['value']);
		}
		else{
			$this->value = $arr_setting_data['value'];
		}
		if(($this->data_type === 'array' || $this->data_type === 'data_lookup_arr') && isset($arr_setting_data['default_value']) && !is_array($arr_setting_data['default_value'])){
			$this->default_value = array($arr_setting_data['default_value']);
		}
		else{
			$this->default_value = $arr_setting_data['default_value'];
		}
		if($this->data_type === 'data_lookup' || $this->data_type === 'data_lookup_arr'){
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
	public function getCurrValue($session_value = null){
		//if a string is sent for array type, insert string into array
		if(($this->data_type === 'array' || $this->data_type === 'data_lookup_arr') && isset($session_value) && !is_array($session_value)){
			$session_value = array($session_value);
		}
		
		if(isset($session_value)){
			return $session_value;
		}
		if(isset($this->value)){
			return $this->value;
		}
		return $this->default_value;
	}
	
	/* -----------------------------------------------------------------
	*  Returns setting ID

	*  Returns setting ID

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jul 2, 2014
	*  @return int
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function getSettingID(){
		return $this->id;
	}

	/* -----------------------------------------------------------------
	 *  Set setting value
	
	*  Set setting value
	
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
	public function getDisplayText($session_value){
		if($this->data_type === 'range'){
			list($from, $to) = explode('|', $this->getCurrValue($session_value));
			return 'between ' . $from . ' and ' . $to;
		}
		elseif($this->data_type === 'array' || $this->data_type === 'data_lookup_arr'){
			return implode(', ', $this->getCurrValue($session_value));
		}
		else{
			return $this->getCurrValue($session_value);
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
		if($this->data_type !== 'data_lookup' && $this->data_type !== 'data_lookup_arr'){
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
		if($this->data_type !== 'data_lookup' && $this->data_type !== 'data_lookup_arr'){
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
	public function getFormData($session_value = null){
		if($this->data_type === 'data_lookup' || $this->data_type === 'data_lookup_arr'){
			$ret_val['options'] = $this->lookup_options;
			$ret_val['selected'] = $this->getCurrValue($session_value);
			return $ret_val;
		}
		if($this->data_type === 'range'){
			list($from, $to) = explode('|', $this->getCurrValue($session_value));
			$ret_val['dbfrom'] = $from;
			$ret_val['dbto'] = $to;
			return $ret_val;
		}
//		if($this->data_type === 'array'){
//			return $this->getCurrValue($session_value);
//		}
		/*
		 * @todo: add remaining data types
		 */

		die("Sorry, I don't recognize the form field data type (" . $this->data_type . ") in Settings\Form");
	}
}