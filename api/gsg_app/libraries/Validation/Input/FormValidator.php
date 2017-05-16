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

class FormValidator
{
    /**
     * name
     * @var string
     **/
    protected $name;

    /**
     * subject_control_name
     * @var string
     **/
    protected $subject_control_name;

    /**
     * subject_control_label
     * @var string
     **/
    protected $subject_control_label;

    /**
     * subject_operator
     * @var string
     **/
    protected $subject_operator;

    /**
     * subject_value
     * @var string
     **/
    protected $subject_value;

    /**
     * condition_control_name
     * @var string
     **/
    protected $condition_control_name;

    /**
     * condition_control_label
     * @var string
     **/
    protected $condition_control_label;

    /**
     * condition_operator
     * @var string
     **/
    protected $condition_operator;

    /**
     * condition_value
     * @var string
     **/
    protected $condition_value;


    /**
	 * Constructor
	 */
	public function __construct($name, $subject_control_name, $subject_control_label, $subject_operator, $subject_value, $condition_control_name, $condition_control_label, $condition_operator, $condition_value = null) {
		// Validation rules can be stored in a config file.
        $this->name = $name;
        $this->subject_control_name = $subject_control_name;
        $this->subject_control_label = $subject_control_label;
        $this->subject_operator = $subject_operator;
        $this->subject_value = $subject_value;
        $this->condition_control_name = $condition_control_name;
        $this->condition_control_label = $condition_control_label;
        $this->condition_operator = $condition_operator;
        $this->condition_value = $condition_value;
	}

	public function toArray(){
	    $ret = [
            'name' => $this->name,
            'subject_control_name' => $this->subject_control_name,
            'subject_control_label' => $this->subject_control_label,
            'subject_operator' => $this->subject_operator,
            'subject_value' => $this->subject_value,
            'condition_control_name' => $this->condition_control_name,
            'condition_control_label' => $this->condition_control_label,
            'condition_operator' => $this->condition_operator,
            'condition_value' => $this->condition_value,
        ];
        return $ret;
    }
}