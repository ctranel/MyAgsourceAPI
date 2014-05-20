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
	 * Checks whether given field exists in table
	 * 
	 * Checks whether given field exists in table

	*  @since: 1.0
	*  @author: ctranel
	*  @date: May 19, 2014
	 * @return boolean
	*  @throws:
	 **/
	public function field_exists($table_name, $col_name){
		return $this->db->field_exists($col_name, $table_name);
	}
}
