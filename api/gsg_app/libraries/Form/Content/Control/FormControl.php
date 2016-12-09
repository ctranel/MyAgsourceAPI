<?php
namespace myagsource\Form\Content\Control;

require_once APPPATH . 'libraries/Form/iFormControl.php';

use \myagsource\Form\iFormControl;

/**
 * FormControl
 * 
 * Created by PhpStorm.
 * User: ctranel
 * Date: 6/20/2016
 * Time: 11:31 AM
 */


class FormControl implements iFormControl
{
    /**
     * id
     * @var int
     **/
    protected $id;

    /**
     * name
     * @var string
     **/
    protected $name;

    /**
     * label
     * @var string
     **/
    protected $label;

    /**
     * value
     * @var mixed
     **/
    protected $value;

    /**
     * default_value
     * @var mixed
     **/
    protected $default_value;

    /**
     * options
     * @var Option[]
     **/
    protected $options;

    /**
     * dom_id
     * @var string
     **/
//    protected $dom_id;

    /**
     * control_type
     * @var string (can handle date, datetime, string, int, decimal)
     **/
    protected $control_type;

    /**
     * is_editable
     * @var boolean
     **/
    protected $is_editable;

    /**
     * is_generated
     * @var boolean
     **/
    protected $is_generated;

    /**
     * is_key
     * @var boolean
     **/
    protected $is_key;

    /**
     * biz_validation_url
     * @var string
     **/
    protected $biz_validation_url;

    /**
     * validators
     * @var Validator[]
     **/
    protected $validators;

    /**
     * subforms
     * @var iForm[]
     **/
    protected $subforms;

    //@todo: implement validators
    public function __construct($control_data, $validators = null, $options = null, $subforms = null){
        $this->id = $control_data['id'];
        $this->name = $control_data['name'];
        $this->label = $control_data['label'];
        $this->value = isset($control_data['value']) ? $control_data['value'] : $control_data['default_value'];
        $this->default_value = $control_data['default_value'];
        $this->control_type = $control_data['control_type'];
        $this->is_editable = (bool)(isset($control_data['is_editable']) ? $control_data['is_editable'] : true);
        $this->is_generated = (bool)(isset($control_data['is_generated']) ? $control_data['is_generated'] : false);
        $this->is_key = (bool)(isset($control_data['is_key']) ? $control_data['is_key'] : false);
        $this->biz_validation_url = (isset($control_data['biz_validation_url']) ? $control_data['biz_validation_url'] : null);
        $this->validators = $validators;
        $this->options = $options;
        $this->subforms = $subforms;
        //handle ranges
        if($this->control_type === 'range'){
            if(strpos($this->value, '|') !== false){
                $tmp = [];
                list($tmp['dbfrom'], $tmp['dbto']) = explode('|', $this->value);
                $this->value = $tmp;
            }
            else{
                $this->value = null;
            }
            if(strpos($this->default_value, '|') !== false){
                $tmp = [];
                list($tmp['dbfrom'], $tmp['dbto']) = explode('|', $this->default_value);
                $this->default_value = $tmp;
            }
            else{
                $this->default_value = null;
            }
        }
        //if type is array and value is not an array, wrap it in an array
        if(strpos($this->control_type, 'array') !== false && isset($this->value) && !is_array($this->value)){
            if(isset($this->value) && !is_array($this->value)){
                if(strpos($this->value, '|')){
                    $this->value = explode('|', $this->value);
                }
                else{
                    $this->value = [$this->value];
                }
            }
            if(isset($this->default_value) && !is_array($this->default_value)){
                if(strpos($this->default_value, '|')){
                    $this->default_value = explode('|', $this->default_value);
                }
                else{
                    $this->default_value = [$this->default_value];
                }
            }
        }

        if($this->control_type === 'boolean' || $this->control_type === 'integer'){
            if(isset($this->value)){
                $this->value = (int)$this->value;
            }
            if(isset($this->default_value)) {
                $this->default_value = (int)$this->default_value;
            }
        }

        if($this->control_type === 'currency' || $this->control_type === 'decimal'){
            if(isset($this->value)){
                $this->value = (float)$this->value;
            }
            if(isset($this->default_value)) {
                $this->default_value = (float)$this->default_value;
            }
        }
    }

    /* -----------------------------------------------------------------
    *  Returns setting value if set, else default value

    *  Returns setting value if set, else default value

    *  @since: version 1
    *  @author: ctranel
    *  @date: Jun 25, 2014
    *  @return mixed value
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function getCurrValue($session_value = null){
        //if a string is sent for array type, insert string into array
        if(strpos($this->control_type, 'array') !== false && isset($session_value) && !is_array($session_value)){
            $session_value = [$session_value];
        }
        if(strpos($this->control_type, 'range') !== false || strpos($this->control_type, 'array') !== false){
            if(isset($session_value) && is_array($session_value) && !empty($session_value)){
                return $session_value;
            }
        }
        elseif(isset($session_value)){
            return $session_value;
        }
        if(strpos($this->control_type, 'range') !== false || strpos($this->control_type, 'array') !== false){
            if(isset($this->value) && is_array($this->value) && !empty($this->value)){
                return $this->value;
            }
        }
        elseif(isset($this->value)){
            return $this->value;
        }
        return $this->default_value;
    }

    /* -----------------------------------------------------------------
    *  Returns control ID

    *  Returns control ID

    *  @author: ctranel
    *  @return int
    *  @throws: 
    * -----------------------------------------------------------------
    */
    public function id(){
        return $this->id;
    }

    /* -----------------------------------------------------------------
    *  Returns control name

    *  Returns control name

    *  @author: ctranel
    *  @return string
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function name(){
        return $this->name;
    }

    /* -----------------------------------------------------------------
    *  controlType

    *  Returns control type

    *  @author: ctranel
    *  @return string
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function controlType(){
        return $this->control_type;
    }

    /* -----------------------------------------------------------------
    *  options

    *  Returns options

    *  @author: ctranel
    *  @return string
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function options(){
        return $this->options;
    }

    /* -----------------------------------------------------------------
    *  Returns true if control is generated by datasource, else false

    *  Returns true if control is generated by datasource, else false

    *  @author: ctranel
    *  @return boolean
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function isGenerated(){
        return $this->is_generated;
    }

    /* -----------------------------------------------------------------
    *  Returns true if control is editable in datasource, else false

    *  Returns true if control is editable in datasource, else false

    *  @author: ctranel
    *  @return boolean
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function isEditable(){
        return $this->is_editable;
    }

    /* -----------------------------------------------------------------
    *  Returns true if control is editable, else false

    *  Returns true if control is editable, else false

    *  @author: ctranel
    *  @return boolean
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function isKey(){
        return $this->is_key;
    }

    public function toArray(){
        $ret = [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->label,
            'value' => $this->value,
            'control_type' => $this->control_type,
            'is_key' => $this->is_key,
            'is_editable' => $this->is_editable,
            'default_value' => $this->default_value,
            'biz_validation_url' => $this->biz_validation_url,
        ];

        if(isset($this->options)){
            $ret['options'] = $this->options;
        }

        if(isset($this->subforms) && is_array($this->subforms) && !empty($this->subforms)){
            $ret['subforms'] = [];
            foreach($this->subforms as $s){
                $ret['subforms'][] = $s->toArray();
            }
        }
        // validator
        if(isset($this->validators) && is_array($this->validators) && !empty($this->validators)){
            $validator = [];
            foreach($this->validators as $v){
                $validator[] = $v->toArray();
            }
            $ret['validators'] = $validator;
            unset($validator);
        }
        return $ret;
    }

    /* -----------------------------------------------------------------
     *  Return text used for display
    
    *  Return text used for display
    
    *  @since: version 1
    *  @author: ctranel
    *  @date: Jun 26, 2014
    *  @param: string display text for setting
    *  @return array of key=>value pairs
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function getDisplayText($session_value){
        if($this->control_type === 'range'){
            $range = $this->getCurrValue($session_value);
            return 'between ' . $range['dbfrom'] . ' and ' . $range['dbto'];
        }
        elseif(strpos($this->control_type, 'array') !== false){
            return implode(', ', $this->getCurrValue($session_value));
        }
        else{
            return $this->getCurrValue($session_value);
        }
    }


    /* -----------------------------------------------------------------
     *  Returns all options
     
     *  Returns all options
     
     *  @since: version 1
     *  @author: ctranel
     *  @date: Jun 26, 2014
     *  @param: string setting name
     *  @return array of key=>value pairs
     *  @throws:
     * -----------------------------------------------------------------
     */
    public function getLookupOptions(){
        if(strpos($this->control_type, 'lookup') === false){
            return false;
        }
        return $this->options;
    }

    /* -----------------------------------------------------------------
    *  Returns form population data for setting

    *  Returns form population data for setting

    *  @since: version 1
    *  @author: ctranel
    *  @date: Jun 26, 2014
    *  @return mixed
    *  @throws: 
    * -----------------------------------------------------------------
    public function getFormData($session_value = null){
        if(strpos($this->control_type, 'lookup') !== false){
            $ret_val['options'] = $this->options;
            $ret_val['selected'] = $this->getCurrValue($session_value);
            return $ret_val;
        }
//		if($this->control_type === 'range'){
        $ret_val = $this->getCurrValue($session_value);
        return $ret_val;
//		}

        //@todo: add remaining data types

        die("Sorry, I don't recognize the form field data type (" . $this->control_type . ") in Settings\Form");
    }
*/

    public function parseFormData($value){
 //@todo: check validators (also in getCurrValue?)
        $ret_val = null;

        if(is_array($value)){
            //if($this->control_type === 'range'){
            $ret_val = implode('|', $value);
            //handle range notation
            //    $ret_val = $value['dbfrom'] . '|' . $value['dbto'];
            //}
        }
        else{
            $ret_val = $value;
        }
        if(strpos($this->control_type, 'lookup') !== false && empty($ret_val)){
            $ret_val = null;
        }
        return $ret_val;
    }

    public function writeSubforms($form_data){
        if(isset($this->subforms) && is_array($this->subforms)){
            foreach($this->subforms as $s){
                //only subforms that do not have an action of their own are written with their parent form
                if($s->action() === null && $s->conditionsMet($form_data[$this->name])){
                    return $s->write($form_data);
                }
            }
        }
    }
}