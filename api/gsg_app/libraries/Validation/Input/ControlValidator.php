<?php
namespace myagsource\Validation\Input;

/**
* Data and methods for individual validators
* 
*
* 
* @author: ctranel
* @date: 2016-10-13
*
*/

class ControlValidator
{
    /**
     * name
     * @var string
     **/
    protected $name;

    /**
     * condition_value
     * @var string
     **/
    protected $condition_value;



    /**
	 * Constructor
	 */
	public function __construct($name, $condition_value = null) {
		// Validation rules can be stored in a config file.
        $this->name = $name;
        $this->condition_value = $condition_value;
	}

	public function toArray(){
	    $ret = [
            'name' => $this->name,
            'condition_value' => $this->condition_value,
        ];
        return $ret;
    }
}