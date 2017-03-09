<?php
namespace myagsource\Report\Content;

require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';

use \myagsource\Datasource\iDataField;
use myagsource\Supplemental\Content\Supplemental;

/**
 * Name:  ReportField
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  Metadata typically associated with data storage for data fields..
 *
 */
abstract class ReportField {
	/**
	 * id
	 * @var int
	 **/
	protected $id;
	
	/**
	 * data_field
	 * @var iDataField
	 **/
	protected $data_field;

	/**
	 * category_id
	 * @var int
	 **/
	protected $category_id;

	/**
	 * report field name
	 * @var string
	 **/
	protected $name;
		
	/**
	 * display_format
	 * @var string
	 **/
	protected $display_format;
		
	/**
	 * aggregate
	 * @var string
	 **/
	protected $aggregate;
		
	/**
	 * is_sortable
	 * @var boolean
	 **/
	protected $is_sortable;
		
	/**
	 * displayed
	 * @var boolean
	 **/
	protected $is_displayed;
	
	/**
	 * header_supp
	 * @var Supplemental
	 **/
	protected $header_supp;
	
	/**
	 * data_supp
	 * @var Supplemental
	 **/
	protected $data_supp;
	
	/**
	 * field_group
	 * @var int
	 **/
	protected $field_group;
	
	/**
	 * field_group_ref_key
	 * @var string
	 **/
	protected $field_group_ref_key;
	
	/**
	 */
	public function __construct($id, $name, iDataField $data_field, $category_id, $is_displayed, $display_format, $aggregate, $is_sortable, $header_supp, $data_supp, $field_group, $field_group_ref_key) {
		$this->id = $id;
		$this->name = $name;
		$this->data_field = $data_field;
		$this->category_id = $category_id;
		$this->is_displayed = $is_displayed;
		$this->display_format = $display_format;
		$this->aggregate = $aggregate;
		$this->is_sortable = $is_sortable;
		$this->header_supp = $header_supp;
		$this->data_supp = $data_supp;
		$this->field_group = $field_group;
		$this->field_group_ref_key = $field_group_ref_key;
	}
	
	public function id() {
		return $this->id;
	}
	
	public function dbFieldName() {
		return $this->data_field->dbFieldName();
	}

	public function displayName() {
		//@todo: if field group is set, use that label
		return $this->name;
	}

	public function categoryId() {
		return $this->category_id;
	}

	public function decimalScale() {
		return $this->data_field->decimalScale();
	}

	public function isSortable() {
		return $this->is_sortable;
	}

    public function isKey() {
        return $this->data_field->isKey();
    }

    public function isDisplayed() {
		return $this->is_displayed;
	}

	public function isNumeric() {
		return $this->data_field->isNumeric();
	}

	public function isAggregate() {
		return (isset($this->aggregate) && !empty($this->aggregate));
	}

	public function defaultSortOrder() {
		return $this->data_field->defaultSortOrder();
	}
	
	public function unitOfMeasure() {
		return $this->data_field->unitOfMeasure();
	}
	
	public function dbTableName() {
		return $this->data_field->dbTableName();
	}
	
	public function pdfWidth() {
		return $this->data_field->pdfWidth();
	}

	public function isNaturalSort() {
		return $this->data_field->isNaturalSort();
	}

	public function fieldGroup() {
		return $this->field_group;
	}

	public function fieldGroupRefKey() {
		return $this->field_group_ref_key;
	}

	public function dataSupplementalContent() {
		if(isset($this->data_supp)){
			return $this->data_supp->getContent();
		}
	}

	public function dataSupplementalProperties() {
		if(isset($this->data_supp)){
			return $this->data_supp->toArray();
		}
	}

	public function headerSupplemental() {
		if(isset($this->header_supp)){
			return $this->header_supp;
		}
	}

	public function headerSupplementalContent() {
		if(isset($this->header_supp)){
			return $this->header_supp->getContent();
		}
	}

	public function sort() {
		return null;
	}

    public function toArray(){
        $ret = $this->data_field->toArray();
		$ret['name'] = $this->name;
        $ret['is_displayed'] = $this->is_displayed;
        $ret['display_format'] = $this->display_format;
        $ret['aggregate'] = $this->aggregate;
        $ret['is_sortable'] = $this->is_sortable;

        if(is_a($this->header_supp, 'Supplemental')){
            $ret['header_supplemental'] = $this->header_supp->toArray();
        }
        if(is_a($this->data_supp, 'Supplemental')){
            $ret['data_supplemental'] = $this->data_supp->toArray();
        }

        return $ret;
    }

	public function selectFieldText() {
		//if(isset($this->display_format) && !empty($this->display_format) && false){ //test with no formatting on back end
		//	return "FORMAT(" . $this->data_field->dbTableName() . "." . $this->data_field->dbFieldName() . ", '" . $this->display_format . "', 'en-US') AS " . $this->data_field->dbFieldName();
		//}
		if($this->data_field->dataType() === "date"){
            return "FORMAT(" . $this->data_field->dbTableName() . "." . $this->data_field->dbFieldName() . ",  'yyyy-MM-dd', 'en-US') AS " . $this->data_field->dbFieldName();
        }
        if($this->data_field->dataType() === "datetime" || $this->data_field->dataType() === "smalldatetime"){
            return "FORMAT(" . $this->data_field->dbTableName() . "." . $this->data_field->dbFieldName() . ",  'yyyy-MM-dd HH:mm:ss', 'en-US') AS " . $this->data_field->dbFieldName();
        }
		if(isset($this->aggregate) && !empty($this->aggregate)){
			$alias_field_name = strtolower($this->aggregate) . '_' . $this->data_field->dbFieldName();
			$ret_val = $this->aggregate . '(' . $this->data_field->dbTableName() . '.' . $this->data_field->dbFieldName() . ') AS ' . $alias_field_name;
			$this->data_field->setDbFieldName($alias_field_name);
			return $ret_val;
		}
		return $this->data_field->dbFieldName();
	}
}

?>