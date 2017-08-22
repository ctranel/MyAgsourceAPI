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
	 * getColumns
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
	public function getColumns($db_name, $table_name){
        //$sql = "USE $db_name;";
        //$this->db->query($sql);
		//$sql = "SELECT * FROM INFORMATION_SCHEMA.Columns WHERE TABLE_NAME = '$table_name';";
		//$result = $this->db->query($sql)->result_array();
        $sql = "USE $db_name;";
        $this->db->query($sql);
        $result = $this->db->list_fields($table_name);//query($sql)->result_array();



        return $result;
	}
}
