<?php
namespace myagsource\Datasource\DbObjects;

require_once(APPPATH . 'libraries/Datasource/iDataTable.php');

use \myagsource\Datasource\iDataTable;

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
 
 class DbTable implements iDataTable
 {
 	/**
	 * database name
	 * @var string
	 **/
	protected $database_name;

 	/**
	 * schema name
	 * @var string
	 **/
	protected $schema_name;

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
	
 	/**
	 * arr_fields
	 * @var array of field names
	 **/
	protected $arr_fields;
	
	public function __construct($full_table_name, $db_table_model){
		list($this->database_name, $this->schema_name, $this->table_name) = explode('.', $full_table_name);
		$this->db_table_model = $db_table_model;
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
		if(!isset($this->arr_fields)){
			$this->arr_fields = $this->db_table_model->getColumns($this->database_name, $this->table_name);
		}

		return (bool)in_array($col_name, $this->arr_fields);
	}

     /**
      * columnNames
      *
      * returns all columns from given table

      *  @author: ctranel
      *  @date: 2017-02-20
      * @return array of column names
      *  @throws:
      **/
     public function columnNames(){
         if(!isset($this->arr_fields)){
             $this->arr_fields = $this->db_table_model->getColumns($this->database_name, $this->table_name);
         }
         return $this->arr_fields;
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
	function full_table_name($full_table_name = null) {
		if(isset($full_table_name)){
			list($this->database_name, $this->schema_name, $this->table_name) = explode('.', $full_table_name);
			return true;
		}
		if(isset($this->table_name)){
			return $this->database_name . '.' . $this->schema_name . '.' . $this->table_name;
		}
		return false;
	}
}