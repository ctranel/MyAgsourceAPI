<?php
namespace myagsource\reports\settings;

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
	 * @var string
	 */
	protected $value;
	
	/**
	 * @var string
	 */
	protected $default_value;
	
	function __construct($arr_setting_data) {
//		$this->category_id = $arr_setting_data['category_id'];
		$this->category = $arr_setting_data['category'];
		$this->grouping = $arr_setting_data['grouping'];
		$this->data_type = $arr_setting_data['data_type'];
		$this->name = $arr_setting_data['name'];
		$this->description = $arr_setting_data['description'];
		$this->value = $arr_setting_data['value'];
		$this->default_value = $arr_setting_data['default_value'];
	}
	
	/* -----------------------------------------------------------------
	*  Short Description

	*  Long Description

	*  @since: version 1
	*  @author: ctranel
	*  @date: Jun 23, 2014
	*  @param: string
	*  @param: int
	*  @param: array
	*  @return datatype
	*  @throws: 
	* -----------------------------------------------------------------
	*/
	protected function (){
		
	}
}