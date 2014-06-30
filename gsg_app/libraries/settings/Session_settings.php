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

class Session_settings {
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
	 */
	protected $session_values;
	
	
	function __construct($user_id, $herd_code, $setting_model, $category, $session_values = NULL) {
		$this->user_id = $user_id;
		$this->herd_code = $herd_code;
		$this->setting_model = $setting_model;
		$this->category = $category;
		$this->session_values = $session_values;
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
			$this->loadSettings();
		}
		return $this->arr_settings;
	}
	
	/* -----------------------------------------------------------------
	*  Returns array of setting key=>value pairs

	*  Returns array of setting objects for the object

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 26, 2014
	*  @return array of setting setting key=>value pairs
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function getSettingKeyValues() {
		if(!isset($this->arr_settings)){
			$this->loadSettings();
		}
		
		$ret_val = array();
		if(isset($this->arr_settings) && is_array($this->arr_settings)){
			foreach($this->arr_settings as $k=>$s){
				$ret_val[$k] = $s->getCurrValue();
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
			$this->arr_settings[$s['name']] = new Setting($s, $this->session_values[$s['name']], $this->setting_model);
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
	public function getValue($setting_name){
		return $this->arr_settings[$setting_name]->getCurrValue();
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
	*  Sets all session setting value in collection

	*  Sets all session setting value in collection

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 30, 2014
	*  @param: array k=>v array of settings
	*  @return void
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function setSessionValues($arr_session_settings){
		if(!isset($arr_session_settings) || !is_array()){
			return false;
		}
		foreach($arr_session_settings as $k=>$s){
			$this->arr_settings[$k]->setSessionValue($value);
		}
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
	public function getFormData(){
		$ret_val = array();
		if(!isset($this->arr_settings)){
			$this->loadSettings();
		}
		foreach($this->arr_settings as $k=>$set){
			$ret_val[$k] = $set->getFormData();
		}
		return $ret_val;
	}
	
}