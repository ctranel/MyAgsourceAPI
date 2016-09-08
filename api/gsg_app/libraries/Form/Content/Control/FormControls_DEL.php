<?php
namespace myagsource\Form\Content\Control;

//require_once APPPATH . 'libraries/Form/Content/Control/FormControl.php';

//use \myagsource\Form\Content\Control\FormControl;
use myagsource\Form\iFormControls;

/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 6/20/2016
 * Time: 11:53 AM
 */

class FormControls implements iFormControls
{
    /**
     * datasource
     * @var datasource object
     **/
    protected $datasource;

    /**
     * form_id
     * @var int
     **/
    protected $form_id;

    public function __construct($datasource, $form_id){
        $this->datasource = $datasource;
        $this->form_id = $form_id;
    }
    
    public function getControls(){
        $ret = [];
        $data = $this->datasource->getFormControlData($this->form_id);

        if(is_array($data) && !empty($data) && is_array($data[0])){
            foreach($data as $d){
                $ret[] = $this->getControl($d);
            }
        }

        return $ret;
    }
    
    public function getControl($control_data){
        return new FormControl($this->datasource, $control_data);
    }
}