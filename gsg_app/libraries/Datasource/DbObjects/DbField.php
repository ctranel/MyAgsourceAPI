<?php
namespace myagsource\Datasource\DbObjects;

require_once APPPATH . 'libraries/Datasource/iDataField.php';

use \myagsource\Datasource\iDataField;

/**
 * Name:  DbField
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  Metadata typically associated with data storage for data fields.
 *
 */
class DbField implements iDataField {
	/**
	 * id
	 * @var int
	 **/
	protected $id;
	
	/**
	 * label_text
	 * @var iDatasourceTable
	 **/
	protected $db_table;
	
	/**
	 * min
	 * @var int
	 **/
	protected $db_field_name;
	
	/**
	 * block name
	 * @var string
	 **/
	protected $name;
	
	/**
	 * block description
	 * @var string
	 **/
	protected $description;
	
	/**
	 * pdf_width
	 * @var int
	 **/
	protected $pdf_width;
	
	/**
	 * default_sort_order
	 * @var string
	 **/
	protected $default_sort_order;
	
	/**
	 * datatype
	 * @var string
	 **/
	protected $datatype;
	
	/**
	 * max_length
	 * @var int
	 **/
	protected $max_length;
	
	/**
	 * decimal_scale
	 * @var int
	 **/
	protected $decimal_scale;
	
	/**
	 * unit_of_measure
	 * @var string
	 **/
	protected $unit_of_measure;
	
	/**
	 * is_timespan
	 * @var boolean
	 **/
	protected $is_timespan;
	
	/**
	 * is_foreign_key
	 * @var boolean
	 **/
	protected $is_foreign_key;
	
	/**
	 * is_nullable
	 * @var boolean
	 **/
	protected $is_nullable;
	
	/**
	 * is_natural_sort
	 * @var boolean
	 **/
	protected $is_natural_sort;
	
	
	/**
	 */
	function __construct($id, $db_table, $db_field_name, $name, $description, $pdf_width, $default_sort_order,
			$datatype, $max_length, $decimal_scale, $unit_of_measure, $is_timespan, $is_foreign_key, $is_nullable, $is_natural_sort) {
		$this->id =  $id;
		$this->db_table = $db_table;
		$this->db_field_name = $db_field_name;
		$this->name = $name;
		$this->description = $description;
		$this->pdf_width = $pdf_width;
		$this->default_sort_order = $default_sort_order;
		$this->datatype = $datatype;
		$this->max_length = $max_length;
		$this->decimal_scale = $decimal_scale;
		$this->unit_of_measure = $unit_of_measure;
		$this->is_timespan = $is_timespan;
		$this->is_foreign_key = $is_foreign_key;
		$this->is_nullable = $is_nullable;
		$this->is_natural_sort = $is_natural_sort;
	}
}

?>