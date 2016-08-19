<?php
namespace myagsource\Settings;

require_once APPPATH . 'libraries/Form/Form.php';
//require_once APPPATH . 'libraries/Settings/SettingFormControls.php';

//use \myagsource\Settings\SettingFormControls;
use \myagsource\Form\Form;

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

class SettingForm extends Form {
	/**
	 * @var int
	 */
	protected $user_id;
	/**
	 * @var string
	 */
	protected $herd_code;
		
	
	function __construct($datasource, SettingFormControls $form_controls, $dom_id, $action, $user_id, $herd_code) {
		parent::__construct($datasource, $form_controls, $dom_id, $action);
        $this->user_id = $user_id;
		$this->herd_code = $herd_code;
	}

    /* -----------------------------------------------------------------
*  write

*  write form to datasource

*  @author: ctranel
*  @date: Jul 1, 2014
*  @param: array of key=>value pairs that have been processed by the parseFormData static function
*  @return void
*  @throws: * -----------------------------------------------------------------
*/
    public function write($form_data){
        if(!isset($form_data) || !is_array($form_data)){
            throw new \UnexpectedValueException('No form data received');
        }
        $form_data = $this->parseFormData($form_data);

        $data = [];

        $user_id = isset($this->user_id) ? $this->user_id : 'NULL';

        foreach($this->controls as $c){
            if(isset($form_data[$c->name()])) {
                if(!$c->forUser()){
                    $user_id = 'NULL';
                }
                if(!$c->forHerd()){
                    $herd_code = 'NULL';
                }
                else {
                    $herd_code = "'$this->herd_code'";
                }
                $data[] = "SELECT " . $user_id . " AS user_id, " . $herd_code . " AS herd_code, " . $c->id() . " AS setting_id, '" . $form_data[$c->name()] . "' AS value";
            }
        }
        $this->datasource->upsert($data);
    }
}
