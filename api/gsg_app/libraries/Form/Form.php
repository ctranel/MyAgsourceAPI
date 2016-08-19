<?php
namespace myagsource\Form;

/**
 * Form
 * 
 * Object representing individual form
 * 
 * Created by PhpStorm.
 * User: ctranel
 * Date: 6/20/2016
 * Time: 11:23 AM
 */

require_once APPPATH . 'libraries/Form/Control/FormControls.php';

use \myagsource\Form\Control\FormControls;

class Form 
{
    /**
     * form_controls
     * @var object
     **/
    protected $form_controls;

    /**
     * datasource
     * @var object
     **/
    protected $datasource;

    /**
     * block dom_id
     * @var string
     **/
    protected $dom_id;

    /**
     * block action
     * @var string
     **/
    protected $action;

    /**
     * array of control objects
     * @var Controls[]
     **/
    protected $controls;

    public function __construct($datasource, FormControls $form_controls, $dom_id, $action){
        $this->datasource = $datasource;
        $this->form_controls = $form_controls;
/*        $this->id = $id;
        $this->page_id = $page_id;
        $this->name = $name;
        $this->description = $description;
*/
        $this->dom_id = $dom_id;
        $this->action = $action;
        $this->setControls();
    }

    public function toArray(){
       $ret['dom_id'] = $this->dom_id;
        $ret['action'] = $this->action;

        if(isset($this->controls) && is_array($this->controls) && !empty($this->controls)){
            $controls = [];
            foreach($this->controls as $c){
                $controls[] = $c->toArray();
            }
            $ret['controls'] = $controls;
            unset($controls);
        }
        return $ret;
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
    protected function parseFormData($form_data){
        $ret_val = [];
        if(!isset($form_data) || !is_array($form_data)){
            throw new \Exception('No form data found');
        }
        foreach($this->controls as $c){
            foreach($form_data as $k=>$v){
                if($c->name() === $k){
                    $ret_val[$k] = $c->parseFormData($v);
                }
            }
        }
        return $ret_val;
    }



    protected function setControls(){
        $this->controls = $this->form_controls->getControls();
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

        $arr_data = [];

        $user_id = isset($this->user) ? $this->user : null;

        foreach($form_data as $k=>$v){
            if(is_array($v)){
                $v = implode('|', $v);
            }

            $arr_data[] = "SELECT '" . $user_id . "' AS user_id, '" . $this->herd_code . "' AS herd_code, '" . $this->arr_settings[$k]->id() . "' AS setting_id, '" . $v . "' AS value";
        }
        $this->datasource->upsert($arr_data);
    }


}