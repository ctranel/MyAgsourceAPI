<?php
namespace myagsource\Settings;

require_once APPPATH . 'libraries/Form/Content/Form.php';

use \myagsource\Form\Content\Form;
use myagsource\Form\iForm;

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

class SettingForm extends Form implements iForm {
	/**
	 * @var int
	 */
	protected $user_id;
	/**
	 * @var string
	 */
	protected $herd_code;
		
	
	function __construct($id, $datasource, $controls_groups, $name, $dom_id, $action, $user_id, $herd_code) {
		parent::__construct($id, $datasource, $controls_groups, $name, $dom_id, $action);
        $this->user_id = $user_id;
		$this->herd_code = $herd_code;
	}

    /* -----------------------------------------------------------------
*  write

*  write form to datasource

*  @author: ctranel
*  @date: Jul 1, 2014
*  @param: array of key=>value pairs
*  @return void
*  @throws: * -----------------------------------------------------------------
*/
    public function write($form_data){
        if(!isset($form_data) || !is_array($form_data)){
            throw new \UnexpectedValueException('No form data received');
        }

        $form_data = $this->parseFormData($form_data);

        $user_id = isset($this->user_id) ? $this->user_id : 'NULL';
        $herd_code = $this->herd_code;

        //DB functions use server time, will do the same to be consistent
        $date = new \DateTime("now");
        $logdttm = $date->format("Y-m-d\TH:i:s");

        $controls = $this->controls();
        foreach($controls as $c){
            if(isset($form_data[$c->name()])) {
                if(!$c->forUser()){
                    $user_id = 'NULL';
                }
                if(!$c->forHerd()){
                    $herd_code = 'NULL';
                }
                $data = $this->datasource->composeSettingSelect($user_id, $herd_code, $c->id(), $form_data[$c->name()], $logdttm, $this->user_id);
                $this->datasource->upsert($this->id, $data);
            }
        }
    }

    public function delete($form_data){
        throw new Exception('Cannot delete settings data.');
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
    */

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
//    public static function parseFormData($form_data){
//        return $form_data;
        /*		$ret_val = [];
                if(!isset($form_data) || !is_array($form_data)){
                    return false;
                }
                foreach($form_data as $k=>$set){
                    if(is_array($set)){
                        //handle range notation
                        if(key($set) === 'dbfrom' || key($set) === 'dbto'){
                            $obj_key = substr($k, 0, $split);
                            $ret_val[$obj_key] = $set['dbfrom'] . '|' . $set['dbto'];
                        }
                    }
                    //if it is not a range data type
                    else{
                        $ret_val[$k] = $set;
        //			}
                    //$this->arr_settings[$k]->parseFormData($set);
                }
                return $ret_val; */
//   }

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
    public function save_as_default($arr_settings){
        if(!isset($arr_settings) || !is_array($arr_settings)){
            return false;
        }
        $arr_data = [];

        $user_id = isset($this->user) ? $this->user : null;

        foreach($arr_settings as $k=>$v){
            if(is_array($v)){
                $v = implode('|', $v);
            }

            $arr_data[] = "SELECT '" . $user_id . "' AS user_id, '" . $this->herd_code . "' AS herd_code, '" . $this->arr_settings[$k]->id() . "' AS setting_id, '" . $v . "' AS value";
        }
        $this->setting_form_model->upsert($arr_data);
    }
*/

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
    protected function prepForMerge(){

    }
*/
}
