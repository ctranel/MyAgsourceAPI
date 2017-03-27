<?php
namespace myagsource\Form\Content\Control;

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

class FormControlGroup
{
    /**
     * form label
     * @var string
     **/
    protected $label;

    /**
     * form action
     * @var string
     **/
    protected $list_order;

    /**
     * array of control objects
     * @var FormControl[]
     **/
    protected $controls;

    public function __construct($label, $list_order, $controls){
        $this->label = $label;
        $this->list_order = $list_order;
        $this->controls = $controls;
    }

    public function controls(){
        return $this->controls;
    }

    public function toArray(){
        $ret['label'] = $this->label;
        //$ret['list_order'] = $this->list_order;

        if(isset($this->controls) && is_array($this->controls)){
            $ret_controls = [];
            foreach($this->controls as $c){
                $ret_controls[] = $c->toArray();
            }
            $ret['controls'] = $ret_controls;
        }
        return $ret;
    }
}