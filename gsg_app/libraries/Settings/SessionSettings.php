<?php
namespace myagsource\settings;

require_once APPPATH . 'libraries' . FS_SEP . 'settings' . FS_SEP . 'Setting.php';

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  setting class
*
* Author: ctranel
*

*
* Created:  2014-06-20
*
* Description:  Setting
*
*/

class SessionSettings {
	/**
	 * @var int
	 */
	protected $user_id;
	/**
	 * @var string
	 */
	protected $herd_code;
		
	/**
	 * use a separate object for each category
	 * 
	 * @var int
	 */
	protected $category;
		
	/**
	 * array of settings objects
	 * 
	 * @var object
	 */
	protected $arr_settings;
	
	/**
	 * obj setting_model
	 *
	 * @var object
	 */
	protected $setting_model;
	
	/**
	 * array session_values
	 *
	 * @var array
	protected $session_values;
	 */
	
	
	function __construct($user_id, $herd_code, $setting_model, $category, $session_values = NULL) {
		$this->user_id = $user_id;
		$this->herd_code = $herd_code;
		$this->setting_model = $setting_model;
		$this->category = $category;
		$this->loadSettings($session_values);
		//$this->session_values = $session_values;
	}

	/* -----------------------------------------------------------------
	*  Returns array of setting objects for the object

	*  Returns array of setting objects for the object

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 23, 2014
	*  @return array of setting objects
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function getSettings() {
		if(!isset($this->arr_settings)){
			return false;//$this->loadSettings();
		}
		return $this->arr_settings;
	}
	
	/* -----------------------------------------------------------------
	*  Returns array of setting key=>value pairs

	*  Returns array of setting objects for the object

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 26, 2014
	*  @param: array session values
	*  @return array of setting setting key=>value pairs
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function getSettingKeyValues($session_values = null) {
		if(!isset($this->arr_settings)){
			return false;//$this->loadSettings();
		}
		
		$ret_val = array();
		if(isset($this->arr_settings) && is_array($this->arr_settings)){
			foreach($this->arr_settings as $k=>$s){
				$sess_val = isset($session_values[$k]) ? $session_values[$k] : null;
				$ret_val[$k] = $s->getCurrValue($sess_val);
			}
		}
		return $ret_val;
	}
	
	/* -----------------------------------------------------------------
	*  Populates arr_settings property

	*  Populates arr_settings property

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 23, 2014
	*  @return void
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	protected function loadSettings(){
		$setting_data = $this->setting_model->getSettingsByCategory($this->category, $this->user_id, $this->herd_code);
		foreach($setting_data as $s){
			$this->arr_settings[$s['name']] = new Setting($s, $this->setting_model);
		}
	}
	
	/* -----------------------------------------------------------------
	*  Gets the specified setting from collection

	*  Gets the specified setting from collection

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 25, 2014
	*  @param: string setting name
	*  @return mixed
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function getValue($setting_name, $session_value = null){
		return $this->arr_settings[$setting_name]->getCurrValue($session_value);
	}

	/* -----------------------------------------------------------------
	*  Sets the specified setting in collection

	*  Sets the specified setting in collection

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 25, 2014
	*  @param: string setting name
	*  @param: mixed value
	*  @return void
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function setValue($setting_name, $value){
		$this->arr_settings[$setting_name]->setValue($value);
	}
	
	/* -----------------------------------------------------------------
	*  Sets the specified session setting value in collection

	*  Sets the specified session setting value in collection

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 30, 2014
	*  @param: string setting name
	*  @param: mixed value
	*  @return void
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function setSessionValue($setting_name, $value){
		$this->arr_settings[$setting_name]->setSessionValue($value);
	}
	
	/* -----------------------------------------------------------------
	*  Returns array with data needed to populate forms
	
	*  Returns array with data needed to populate forms
	
	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 27, 2014
	*  @return array with options and selected data for each setting
	*  @throws:
	* -----------------------------------------------------------------
	*/
	public function getFormData($session_data = null){
		$ret_val = array();
		if(!isset($this->arr_settings)){
			return false;//$this->loadSettings();
		}
		foreach($this->arr_settings as $k=>$set){
			$session_value = isset($session_data[$k]) ? $session_data[$k] : null;
			$ret_val[$k] = $set->getFormData($session_value);
		}
		return $ret_val;
	}
	
	/* -----------------------------------------------------------------
	 *  parses form data according to data type conventions.
	
	*  Parses form data according to data type conventions.
	
	*  @since: version 1
	*  @author: ctranel
	*  @date: July 1, 2014
	*  @param array of key-value pairs from form submission
	*  @return void 
	*  @throws:
	* -----------------------------------------------------------------
	*/
	public static function parseFormData($form_data){
		return $form_data;
/*		$ret_val = array();
		if(!isset($form_data) || !is_array($form_data)){
			return false;
		}
		foreach($form_data as $k=>$set){
			if(is_array($set)){
				//handle range notation
				if(key($set) === 'dbfrom' || key($set) === 'dbto'){
					$obj_key = substr($k, 0, $split);
die($obj_key);
					$ret_val[$obj_key] = $set['dbfrom'] . '|' . $set['dbto'];
				}
			}
			//if it is not a range data type
			else{
				$ret_val[$k] = $set;
//			}
			//$this->arr_settings[$k]->parseFormData($set);
		}
var_dump($form_data, $ret_val);die;
		return $ret_val; */
	}
	
	/* -----------------------------------------------------------------
	*  Preps data and calls function to insert/update session setting

	*  Long Description

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jul 1, 2014
	*  @param: string
	*  @param: int
	*  @param: array of key=>value pairs that have been processed by the parseFormData static function
	*  @return void
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function save_as_default($arr_settings){
		if(!isset($arr_settings) || !is_array($arr_settings)){
			return false;
		}
		$arr_data = array();
		
		$user_id = isset($this->user) ? $this->user : null;
		
		foreach($arr_settings as $k=>$v){
			if(is_array($v)){
				$v = implode('|', $v);
			}
			
			$arr_data[] = "SELECT '" . $this->user_id . "' AS user_id, '" . $this->herd_code . "' AS herd_code, '" . $this->arr_settings[$k]->getSettingID() . "' AS setting_id, '" . $v . "' AS value";
		}
		$this->setting_model->mergeUserHerdSettings($arr_data);
	}
	
	/* -----------------------------------------------------------------
	 *  returns syntax for creating a merge query (sql server)
	
	*  returns syntax for creating a merge query (sql server)
	
	*  @since: version 1
	*  @author: ctranel
	*  @date: Jul 2, 2014
	*  @param: string
	*  @param: int
	*  @param: array
	*  @return datatype
	*  @throws:
	* -----------------------------------------------------------------
	*/
	protected function prepForMerge(){
	
	}
}