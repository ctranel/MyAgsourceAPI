<?php
namespace myagsource\Page\Content\Form;

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

require_once APPPATH . 'libraries/Page/Content/Form/Control/FormControls.php';

use \myagsource\Page\Content\Form\Control\FormControls;
use \myagsource\Site\WebContent\Block as SiteBlock;

class Form 
{
    /**
     * SiteBlock object which contains properties and methods related to the block context within the site
     * @var SiteBlock
     **/
    protected $site_block;

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

    public function __construct(SiteBlock $site_block, FormControls $form_controls, $dom_id, $action){
        $this->site_block = $site_block;
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

    public function displayType(){
        return $this->site_block->displayType();
    }


    public function toArray(){
        $ret = $this->site_block->toArray();
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
    
    protected function setControls(){
        $this->controls = $this->form_controls->getControls();
    }
}