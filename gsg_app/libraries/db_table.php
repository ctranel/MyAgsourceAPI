<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* -----------------------------------------------------------------
 *  Library for db tables
 *
 *  Library for db tables
 *
 *  @category: 
 *  @package: 
 *  @author: ctranel
 *  @date: May 19, 2014
 *  @version: 1.0
 * -----------------------------------------------------------------
 */
 
 class db_table {
 	/**
	 * table name
	 * @var string
	 **/
	protected $table_name;

 	/**
	 * db_table_model
	 * @var object
	 **/
	protected $db_table_model;
	
	public function __construct($params){
		$this->table_name = $params['table_name'];
		$this->db_table_model = $params['db_table_model'];
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
		return $this->db_table_model->field_exists($col_name, $this->table_name);
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