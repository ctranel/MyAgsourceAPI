<?php
namespace myagsource\Settings;

require_once APPPATH . 'libraries/Form/Content/Control/FormControl.php';

use \myagsource\Form\Content\Control\FormControl;

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

class SettingFormControl extends FormControl {
    /**
     * @var boolean
     */
    protected $for_user;
    /**
     * @var boolean
     */
    protected $for_herd;


    function __construct($datasource, $control_data, $subforms = null) {
        parent::__construct($datasource, $control_data, $subforms);
        $this->for_user = (boolean)$control_data['for_user'];
        $this->for_herd = (boolean)$control_data['for_herd'];
    }

    public function forUser(){
        return $this->for_user;
    }

    public function forHerd(){
        return $this->for_herd;
    }
}
