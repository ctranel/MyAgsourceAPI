<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* -----------------------------------------------------------------
 *  Database table object
 *
 *  Database table object
 *
 *  @category: 
 *  @package: 
 *  @author: ctranel
 *  @date: 3/18/2015
 * -----------------------------------------------------------------
 */
 
 class Db_field_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	/**
	 * Retrieves column metadata for give db and table
	 * 
	 * Retrieves column metadata for give db and table

	*  @author: ctranel
	*  @date: 3/18/2015
	*  @param: int field id
	 * @return array of field data
	*  @throws:
	 **/
	public function getFieldData($id){
		$result = $this->db
			->select('f.id, f.db_field_name, f.name, f.description, f.pdf_width, f.default_sort_order, f.data_type AS datatype, f.max_length, f.is_timespan_field AS is_timespan, f.is_fk_field AS is_foreign_key, f.is_nullable, f.decimal_points AS decimal_scale, f.is_natural_sort, f.unit_of_measure, t.name AS db_table
			    ,mc.name AS conversion_name,mc.metric_label,mc.metric_abbrev,mc.to_metric_factor, mc.metric_rounding_precision,mc.imperial_label,mc.imperial_abbrev,mc.to_imperial_factor, mc.imperial_rounding_precision')
			->from('users.dbo.db_fields f')
			->join('users.dbo.db_tables t', 'f.db_table_id = t.id','inner')
            ->join('users.dbo.metric_conversion mc', 'f.conversion_id = mc.id', 'left')
            ->where('f.id', $id)
			->get()
			->result_array();
		return $result[0];
	}
}
