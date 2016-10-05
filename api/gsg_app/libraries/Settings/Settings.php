<?php
namespace myagsource\Settings;

require_once APPPATH . 'libraries/Settings/Setting.php';

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
*
*/

class Settings {
	/**
	 * @var int
	 */
	protected $user_id;
	/**
	 * @var string
	 */
	protected $herd_code;
		
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

	
	function __construct($user_id, $herd_code, $setting_model, $session_values = NULL) {
		$this->user_id = $user_id;
		$this->herd_code = $herd_code;
		$this->setting_model = $setting_model;
		$this->loadSettings($session_values);
		$this->session_values = $session_values;
	}

	public function toArray(){
        $ret = [];
        if(isset($this->arr_settings) && is_array($this->arr_settings) && !empty($this->arr_settings)){
            foreach($this->arr_settings as $k=>$set){
                $ret[$k] = $set->toArray();
            }
        }
        return $ret;
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
		
		$ret_val = [];
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
		$setting_data = $this->setting_model->getSettingsData($this->user_id, $this->herd_code);
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
	public function setSessionValue($setting_name, $value)
    {
        $this->arr_settings[$setting_name]->setSessionValue($value);
    }
}
