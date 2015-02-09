<?php
namespace libraries\Site\WebContent;

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
class DbField {
	/**
	 * id
	 * @var int
	 **/
	protected $id;
	
	/**
	 * label_text
	 * @var string
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
	function __construct() {
	}
}

?>