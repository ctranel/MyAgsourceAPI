<?php
namespace myagsource\Form\Content;

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

require_once APPPATH . 'libraries/Form/iForm.php';

use \myagsource\Form\iForm;

class Form implements iForm
{
    /**
     * id
     * @var int
     **/
    protected $id;

    /**
     * datasource
     * @var object
     **/
    protected $datasource;

    /**
     * form dom_id
     * @var string
     **/
    protected $dom_id;

    /**
     * form action
     * @var string
     **/
    protected $action;

    /**
     * array of control objects
     * @var FormControl[]
     **/
    protected $controls;

    public function __construct($id, $datasource, $controls, $dom_id, $action){
        $this->id = $id;
        $this->datasource = $datasource;
        $this->controls = $controls;
        $this->dom_id = $dom_id;
        $this->action = $action;
    }

    public function action(){
        return $this->action;
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

        $generated_cols = $this->getGeneratedCols($form_data);
        $form_data = $this->parseFormData($form_data);

        //SEND KEY FIELDS AND VALUES
        $this->datasource->upsert($this->id, $form_data, $generated_cols);
    }

    /* -----------------------------------------------------------------
*  delete

*  delete record from database

*  @author: ctranel
*  @date: 2016-11-30
*  @param: array of key=>value pairs that have been processed by the parseFormData static function
*  @return void
*  @throws:
* -----------------------------------------------------------------
*/
    public function delete($key_data){
        if(!isset($key_data) || !is_array($key_data)){
            throw new \UnexpectedValueException('No form data received');
        }

        $key_data = $this->parseKeyData($key_data);

        //SEND KEY FIELDS AND VALUES
        $this->datasource->delete($this->id, $key_data);
    }

    /* -----------------------------------------------------------------
*  deactivate

*  deactivate record from database

*  @author: ctranel
*  @date: 2016-11-30
*  @param: array of key=>value pairs that have been processed by the parseFormData static function
*  @return void
*  @throws:
* -----------------------------------------------------------------
*/
    public function deactivate($key_data){
        if(!isset($key_data) || !is_array($key_data)){
            throw new \UnexpectedValueException('No form data received');
        }

        $key_data['isactive'] = 0;

        $this->write($key_data);
    }

    /* -----------------------------------------------------------------
     *  parses form data according to data type conventions.

    *  Parses form data according to data type conventions.

    *  @since: version 1
    *  @author: ctranel
    *  @date: July 1, 2014
    *  @param array of key-value pairs from form submission
    *  @return array of formatted key-value pairs from form submission
    *  @throws:
    * -----------------------------------------------------------------
    */
    protected function parseKeyData($key_data){
        $ret_val = [];
        if(!isset($key_data) || !is_array($key_data)){
            throw new \Exception('No form data found');
        }
        foreach($this->controls as $c){
            if($c->isKey() && isset($key_data[$c->name()])){
                $ret_val[$c->name()] = $c->parseFormData($key_data[$c->name()]);
            }
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
    *  @return array of formatted key-value pairs from form submission
    *  @throws:
    * -----------------------------------------------------------------
    */
    protected function parseFormData($form_data){
        $ret_val = [];
        if(!isset($form_data) || !is_array($form_data)){
            throw new \Exception('No form data found');
        }
        foreach($this->controls as $c){
            if(!$c->isGenerated() && isset($form_data[$c->name()])){
                $ret_val[$c->name()] = $c->parseFormData($form_data[$c->name()]);
                //@todo: not the right place to write subforms, but it will do for now
                $c->writeSubforms($form_data);
            }
        }
        return $ret_val;
    }

    /* -----------------------------------------------------------------
    *  extracts generated column data

    *  extracts generated column data from submitted form data, as it is not included in the form data sent to datasource,
    * but is needed for

    *  @author: ctranel
    *  @date: 2016-10-13
    *  @param array of key-value pairs from form submission
    *  @return key-value pairs
    *  @throws:
    * -----------------------------------------------------------------
    */
    protected function getGeneratedCols($form_data){
        $ret_val = [];
        if(!isset($form_data) || !is_array($form_data)){
            throw new \Exception('No form data found');
        }
        foreach($this->controls as $c){
            if($c->isGenerated()){
                $ret_val[$c->name()] = $c->parseFormData($form_data[$c->name()]);
            }
        }
        return $ret_val;
    }

    /* -----------------------------------------------------------------
    *  animalOptions

    *  returns an array of animal options contained in form keyed by field name of control.

    *  @author: ctranel
    *  @date: 2016-11-02
    *  @return array animal options
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function animalOptions(){
        $ret_val = [];

        foreach($this->controls as $c){
            if($c->controlType() === "animal_lookup"){
                $ret_val[$c->name()] = $c->options();
            }
        }

        return $ret_val;
    }

    /* -----------------------------------------------------------------
    *  extracts keys

    *  extracts keys and corresponding values from submitted form data

    *  @author: ctranel
    *  @date: 2016-10-13
    *  @param array of key-value pairs from form submission
    *  @return key-value pairs
    *  @throws:
    * -----------------------------------------------------------------
    protected function getEntityKeys($form_data){
        $ret_val = [];
        if(!isset($form_data) || !is_array($form_data)){
            throw new \Exception('No form data found');
        }
        foreach($this->controls as $c){
            if($c->isKey()){
                $ret_val[$c->name()] = $c->parseFormData($form_data[$c->name()]);
            }
        }
        return $ret_val;
    }
*/
}