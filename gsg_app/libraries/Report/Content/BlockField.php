<?php
namespace myagsource\Report\Content;

require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';

use \myagsource\Datasource\DbObjects\DbField;

/**
 * Name:  BlockField
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  Metadata typically associated with data storage for data fields..
 *
 */
abstract class BlockField {
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
	 * block name
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
	 */
	public function __construct($id, $name, DbField $data_field, $is_displayed, $display_format, $aggregate, $is_sortable, $header_supp, $data_supp) {
		$this->id = $id;
		$this->name = $name;
		$this->data_field = $data_field;
		$this->is_displayed = $is_displayed;
		$this->display_format = $display_format;
		$this->aggregate = $aggregate;
		$this->is_sortable = $is_sortable;
	}
	
	public function dbFieldName() {
		return $this->data_field->dbFieldName();
	}

	public function isSortable() {
		return $this->is_sortable;
	}

	public function isAggregate() {
		return (isset($this->aggregate) && !empty($this->aggregate));
	}

	public function dbTableName() {
		return $this->data_field->dbTableName();
	}

	public function selectFieldText() {
		if(isset($this->display_format) && !empty($this->display_format)){
			return "FORMAT(" . $this->data_field->dbTableName() . "." . $this->data_field->dbFieldName() . ", '" . $this->display_format . "', 'en-US') AS " . $this->data_field->dbFieldName();
		}
		if(isset($this->aggregate) && !empty($this->aggregate)){
			$this->name = strtolower($this->aggregate) . '_' . $this->data_field->dbFieldName();
			return $this->aggregate . '(' . $this->data_field->dbTableName() . '.' . $this->data_field->dbFieldName() . ') AS ' . $this->name;
		}
		return $this->data_field->dbFieldName();
	}
}

?>