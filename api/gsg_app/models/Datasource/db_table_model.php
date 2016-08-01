<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* -----------------------------------------------------------------
 *  Database table object
 *
 *  Database table object
 *
 *  @category: 
 *  @package: 
 *  @author: ctranel
 *  @date: May 19, 2014
 *  @version: 1.0
 * -----------------------------------------------------------------
 */
 
 class Db_table_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	/**
	 * Retrieves column metadata for give db and table
	 * 
	 * Retrieves column metadata for give db and table

	*  @since: 1.0
	*  @author: ctranel
	*  @date: May 19, 2014
	*  @param: string database name
	*  @param: string table name
	 * @return boolean
	*  @throws:
	 **/
	public function get_columns($db_name, $table_name){
		$sql = "USE $db_name; SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION FROM INFORMATION_SCHEMA.Columns WHERE TABLE_NAME = '$table_name'";
		$result = $this->db->query($sql)->result_array();
		return $result;
	}
}
