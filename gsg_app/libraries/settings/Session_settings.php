<?php
namespace myagsource\reports\settings;

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
	
	
	function __construct($user_id, $herd_code, $category) {
		$this->user_id = $user_id;
		$this->herd_code = $herd_code;
		$this->category = $category;
	}

	/* -----------------------------------------------------------------
	*  Returns array of setting objects for the object

	*  Returns array of setting objects for the object

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 23, 2014
	*  @param: object settings model
	*  @return array of setting objects
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	public function getSettings($setting_model) {
		if(!isset($this->arr_settings)){
			$this->loadSettings($setting_model);
		}
		return $this->arr_settings;
	}
	
	/* -----------------------------------------------------------------
	*  Populates arr_settings property

	*  Populates arr_settings property

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 23, 2014
	*  @param: object settings model
	*  @return void
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	protected function loadSettings($setting_model){
		$setting_data = $setting_model->getSettingsByCategory($this->category, $this->user_id, $this->herd_id);
		foreach($setting_data as $s){
			$this->arr_settings = new Setting($s);
		}
	}
}