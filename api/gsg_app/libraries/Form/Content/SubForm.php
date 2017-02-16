<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 8/22/2016
 * Time: 11:34 AM
 */

namespace myagsource\Form\Content;

require_once APPPATH . 'libraries/Form/iSubForm.php';

use myagsource\Form\iSubForm;
use myagsource\Form\iForm;

class SubForm implements iSubForm
{
    /**
     * @var array of SubContentConditionGroup objects
     */
    protected $condition_groups;

    /**
     * @var iForm
     */
    protected $form;

    public function __construct($condition_groups, iForm $form)
    {
        $this->condition_groups = $condition_groups;
        $this->form = $form;
    }
    
    public function toArray(){
        $ret['form'] = $this->form->toArray();

        if(isset($this->condition_groups) && is_array($this->condition_groups) && !empty($this->condition_groups)){
            $ret['condition_groups'] = [];
            foreach($this->condition_groups as $s){
                $ret['condition_groups'][] = $s->toArray();
            }
        }

        return $ret;
    }
    
    public function write($form_data)    {
        return $this->form->write($form_data);
    }

    public function delete($form_data)    {
        $this->form->delete($form_data);
    }

    public function action(){
        return $this->form->action();
    }

    public function batchVariableControl(){
        return $this->form->batchVariableControl();
    }

    public function parseFormData($form_data){
        return $this->form->parseFormData($form_data);
    }

    public function controlsMetaArray(){
        return $this->form->controlsMetaArray();
    }

    public function keyMetaArray(){
        return $this->form->keyMetaArray();
    }

    /**
     * conditionsMet
     *
     * are all subform conditions met?
     *
     * @param $control_value
     * @return bool
     */
    public function conditionsMet($form_values){
        foreach($this->condition_groups as $cg){
            if(!$cg->conditionsMet($form_values)){
                return false;
            }
        }

        return true;
    }
}