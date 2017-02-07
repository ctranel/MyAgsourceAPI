<?php
namespace myagsource\Report\Content\Chart;

use \myagsource\Datasource\DbObjects\DbField;
use \myagsource\Datasource\iDataField;

/**
 * Name:  XAxis
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  XAxis display.
 *
 */
class XAxis {
	/**
	 * min
	 * @var numeric
	 **/
	protected $min;
	
	/**
	 * max
	 * @var numeric
	 **/
	protected $max;
	
	/**
	 * opposite
	 * @var boolean
	 **/
	protected $opposite;
	
	/**
	 * datafield
	 * @var iDataField
	 **/
	protected $datafield;
	
	/**
	 * data_type
	 * @var string
	 **/
	protected $data_type;
	
	/**
	 * label_text
	 * @var string
	 **/
	protected $label_text;
	
	/**
	 * um
	 * @var string
	 **/
	//protected $um;
	
	/**
	 * category
	 * @var string
	 **/
	protected $category;

	/**
	 */
	function __construct($min, $max, $opposite, DbField $datafield = null, $data_type, $text, $category = NULL) {
		$this->min = $min;
		$this->max = $max;
		$this->opposite = $opposite;
		$this->datafield = $datafield;
		$this->data_type = $data_type;
		$this->label_text = $text;
		//$this->um = $um;
		$this->category = $category;
	}
	
	function category(){
		return $this->category;
	}

	function dataType(){
		return $this->data_type;
	}

	function dbFieldName(){
		if($this->datafield instanceof iDataField){
			return $this->datafield->dbFieldName();
		}
		return null;
	}

	/**
	 * @method getOutputData
	 * @return array of output data for axis
	 * @access public
	 *
	 **/
	public function getOutputData(){
		$dbfield_type = $this->datafield instanceof iDataField ? $this->datafield->dataType() : null;
		$dbfield_name = $this->datafield instanceof iDataField ? $this->datafield->dbFieldName() : null;
		return [
			'min' => $this->min,
			'max' => $this->max,
			'opposite' => $this->opposite,
			'data_type' => $this->data_type,
			'db_field_name' => $dbfield_name,
			'text' => $this->label_text,
//			'um' => $this->um,
		];
	}
	
	/**
	 * @method toArray
	 * @return array of output data for axis
	 * @access public
	 *
	 **/
	public function toArray(){
        $ret = [
            'min' => $this->min,
            'max' => $this->max,
            'opposite' => $this->opposite,
            'data_type' => $this->data_type,
            'label_text' => $this->label_text,
            'text' => $this->label_text,
            //'' => $this->um,
            'category' => $this->category,
        ];
        
        if($this->datafield instanceof iDataField){
            $ret['datafield'] = $this->datafield->toArray();
        }
        
        return $ret;
    }
}

?>