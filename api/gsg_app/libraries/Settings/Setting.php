<?php
namespace myagsource\Settings;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * 
* Name:  setting class
*
* Author: ctranel
*
* Created:  2014-06-20
*
*/

class Setting {
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
     * @var string
     **/
    protected $value;

    /**
     * default_value
     * @var string
     **/
    protected $default_value;

    /**
     * control_type
     * @var string (can handle date, datetime, string, int, decimal)
     **/
    protected $control_type;

    /**
     * datasource
     * @var CI_Model
     **/
    protected $datasource;

    public function __construct($control_data, $datasource){
        $this->datasource = $datasource;
        $this->id = $control_data['id'];
        $this->name = $control_data['name'];
        $this->label = $control_data['label'];
        $this->value = $control_data['value'];
        $this->default_value = $control_data['default_value'];
        $this->control_type = $control_data['control_type'];

        if(strpos($this->control_type, 'range') !== false){
            if(strpos($control_data['value'], '|') !== false){
                $this->value = array_combine(['dbfrom', 'dbto'], explode('|', $control_data['value']));
            }
            else{
                $this->value = null;
            }
            if(strpos($control_data['default_value'], '|') !== false){
                $this->default_value = array_combine(['dbfrom', 'dbto'], explode('|', $control_data['default_value']));
            }
            else{
                $this->default_value = null;
            }
        }
        //if type is array and value is not an array, wrap it in an array
        if(strpos($this->control_type, 'array') !== false){
            if(isset($control_data['value']) && !is_array($control_data['value'])) {
                if (strpos($control_data['value'], '|') !== false) {
                    $this->value = explode('|', $control_data['value']);
                } else {
                    $this->value = [$control_data['value']];
                }
            }
            if(isset($control_data['default_value']) && !is_array($control_data['default_value'])) {
                if (strpos($control_data['default_value'], '|') !== false) {
                    $this->default_value = explode('|', $control_data['default_value']);
                } else {
                    $this->default_value = [$control_data['default_value']];
                }
            }
        }
	}

	public function toArray(){
        $ret = [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->label,
            'value' => $this->value,
            'default_value' => $this->default_value,
            'control_type' => $this->control_type,
        ];

        return $ret;
    }

    /* -----------------------------------------------------------------
    *  Returns label

    *  Returns setting label

    *  @since: version 1
    *  @author: ctranel
    *  @date: Jun 25, 2014
    *  @return mixed value
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function label(){
        return $this->label;
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
    public function value(){
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
     *  Set setting default value

    *  Set setting default value

    *  @since: version 1
    *  @author: ctranel
    *  @date: Jun 26, 2014
    *  @param: mixed new value
    *  @return void
    *  @throws:
    * -----------------------------------------------------------------
    */

    public function setDefaultValue($new_value){
        $this->default_value = $new_value;

        if($this->control_type === 'range'){
            if(strpos($new_value, '|') !== false){
                $tmp = [];
                list($tmp['dbfrom'], $tmp['dbto']) = explode('|', $new_value);
                $new_value = $tmp;
            }
            else{
                $new_value = null;
            }
        }
        //if type is array and value is not an array, wrap it in an array
        if(strpos($this->control_type, 'array') !== false && isset($new_value) && !is_array($new_value)){
            if(strpos($new_value, '|') !== false){
                $new_value = explode('|', $new_value);
            }
            else{
                $new_value = [$new_value];
            }
        }
        $this->default_value = $new_value;

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
 //var_dump($this->name, $session_value, $this->value, $this->default_value);
        if(isset($session_value)){
            if(strpos($this->control_type, 'array') !== false){
                if(!is_array($session_value)){
                    $session_value = [$session_value];
                }
                return $session_value;
            }
            if(strpos($this->control_type, 'range') !== false || strpos($this->control_type, 'array') !== false){
//                if(isset($session_value) && is_array($session_value) && !empty($session_value)){
//                    return $session_value;
//                }
                if(strpos($this->control_type, 'range') && strpos($session_value, '|') !== false){
                    return array_combine(['dbfrom', 'dbto'], explode('|', $session_value));
                }
            }
            return $session_value;
        }
        //if session_value is null
        if(strpos($this->control_type, 'array') !== false){
            if(!is_array($this->value)){
                $this->value = [$this->value];
            }
            return $this->value;
        }
        if(strpos($this->control_type, 'range') !== false || strpos($this->control_type, 'array') !== false){
            if(isset($this->value) && is_array($this->value) && !empty($this->value)){
                return $this->value;
            }
            if(strpos($this->control_type, 'range') && strpos($this->value, '|') !== false){
                return array_combine(['dbfrom', 'dbto'], explode('|', $this->value));
            }
        }
        elseif(isset($this->value)){
            return $this->value;
        }
        return $this->default_value;
    }
}