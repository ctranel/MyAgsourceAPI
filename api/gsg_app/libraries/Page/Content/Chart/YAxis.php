<?php
namespace myagsource\Page\Content\Chart;

use \myagsource\Datasource\DbObjects\DbField;

/**
 * Name:  YAxis
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  YAxis display.
 *
 */
class YAxis {
	/**
	 * min
	 * @var int
	 **/
	protected $min;
	
	/**
	 * max
	 * @var int
	 **/
	protected $max;
	
	/**
	 * opposite
	 * @var boolean
	 **/
	protected $opposite;
	
	/**
	 * label_text
	 * @var string
	 **/
	protected $label_text;
	
	
	/**
	 */
	function __construct($min, $max, $opposite, $text, iDataField $datafield = null) {
		$this->min = $min;
		$this->max = $max;
		$this->opposite = $opposite;
		$this->datafield = $datafield;
		$this->label_text = $text;
	}

	/**
	 * @method getOutputData
	 * @return array of output data for block
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
			'data_type' => $dbfield_type,
			'db_field_name' => $dbfield_name,
			'text' => $this->label_text,
		];
	}

	/**
	 * @method toArray
	 * @return array of output data for block
	 * @access public
	 *
	 **/
	public function toArray(){
		$ret = [
			'min' => $this->min,
            'max' => $this->max,
			'opposite' => $this->opposite,
			//'data_type' => $this->data_type,
			'label_text' => $this->label_text,
		];

		if($this->datafield instanceof iDataField){
			$ret['datafield'] = $this->datafield->toArray();
		}

		return $ret;
	}
}

?>