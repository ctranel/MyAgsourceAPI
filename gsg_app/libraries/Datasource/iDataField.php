<?php
namespace myagsource\Datasource;

/**
 * Name:  DbField
 *
 * Author: ctranel
 *
 * Created:  02-10-2015
 *
 * Description:  Interface for datasource fields.
 *
 */
interface iDataField {
	/**
	 */
	function __construct($id, $db_table, $db_field_name, $name, $description, $pdf_width, $default_sort_order,
			$datatype, $max_length, $decimal_scale, $unit_of_measure, $is_timespan, $is_foreign_key, $is_nullable, $is_natural_sort);
	
	function dbFieldName();
}

?>