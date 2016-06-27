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

require_once APPPATH . 'libraries/Form/Content/FormControls.php';

use \myagsource\Form\Content\FormControls;

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
     * form id
     * @var int
     **/
    protected $id;

    /**
     * page_id
     * @var int
     **/
    protected $page_id;

    /**
     * name
     * @var string
     **/
    protected $name;

    /**
     * description
     * @var string
     **/
    protected $description;

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

    public function __construct(FormControls $form_controls, $id, $page_id, $name, $description, $dom_id, $action){
        $this->form_controls = $form_controls;
        $this->id = $id;
        $this->page_id = $page_id;
        $this->name = $name;
        $this->description = $description;
        $this->dom_id = $dom_id;
        $this->action = $action;
        $this->setControls();
    }
    
    public function toArray(){
        $ret = [
            'page_id' => $this->page_id,
            'name' => $this->name,
            'description' => $this->description,
            'dom_id' => $this->dom_id,
            'action' => $this->action,
        ];
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
    
    public function toJson(){
        return json_encode($this->toArray());
    }
    
    protected function setControls(){
        $this->controls = $this->form_controls->getControls();
    }
}