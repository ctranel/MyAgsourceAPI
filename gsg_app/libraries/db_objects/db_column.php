<?php
namespace libraries\db_objects;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* -----------------------------------------------------------------
 *  Library for db column
 *
 *  Library for db column
 *
 *  @category: 
 *  @package: 
 *  @author: ctranel
 *  @date: June 10, 2014
 *  @version: 1.0
 * -----------------------------------------------------------------
 */
 
 class db_column {
  	/**
	 * column_name
	 * @var string
	 **/
	protected $column_name;
	
	/**
	 * data_type
	 * @var string
	 **/
	protected $data_type;

 	/**
	 * char_length
	 * @var int
	 **/
	protected $char_length;
	
 	/**
	 * precision
	 * @var int
	 **/
	protected $precision;
	
	public function __construct(){
	}

	/**
	 * Checks whether given field exists in table
	 * 
	 * Checks whether given field exists in table

	*  @since: 1.0
	*  @author: ctranel
	*  @date: May 19, 2014
	 * @return boolean
	*  @throws:
	 **/
	public function field_exists($col_name){
	}
	
	/* -----------------------------------------------------------------
	*  Gets or sets table name
	
	*  If a value is passed, the object variable is set to that value.
	*  If no value is passed, the current table name is passed.
	*  Returns false on failure
	
	*  @since: 1.0
	*  @author: ctranel
	*  @date: May 19, 2014
	*  @return: bool / string
	*  @throws:
	* -----------------------------------------------------------------*/
	function table_name($value = null) {
		if(isset($value)){
			$this->table_name = $value;
			return true;
		}
		if(isset($this->table_name)){
			return $this->table_name;
		}
		return false;
	}
}