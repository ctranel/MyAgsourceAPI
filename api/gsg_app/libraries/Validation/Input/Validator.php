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

class Validator
{
    /**
     * name
     * @var string
     **/
    protected $name;

    /**
     * comparison_value
     * @var string
     **/
    protected $comparison_value;



    /**
	 * Constructor
	 */
	public function __construct($name, $comparison_value = null) {
		// Validation rules can be stored in a config file.
        $this->name = $name;
        $this->comparison_value = $comparison_value;
	}

	public function toArray(){
	    $ret = [
            'name' => $this->name,
            'label' => $this->comparison_value,
        ];
        return $ret;
    }
}