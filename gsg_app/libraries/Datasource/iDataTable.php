<?php
namespace myagsource\Datasource;

/**
 * Name:  iDataTable
 *
 * Author: ctranel
 *
 * Created:  02-10-2015
 *
 * Description:  Interface for datasource tables.
 *
 */
interface iDataTable {
	/**
	 */
	function __construct($full_table_name, $db_table_model);
}

?>