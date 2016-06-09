<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(BASEPATH.'database/DB_active_rec'.EXT);
/**
* Name:  DB Batch Insert
*
* Author: ctranel
*		  ctranel@agsource.com
*
* Location: http://www.agsource.com
*
* Created:  2.22.2011
*
* Description:  Overrides active record batching to increase the number of records per batch and 
* consider null and numeric fields.  USE OF THIS LIBRARY REQUIRES A CHANGE TO THE DB.php CORE FILE TO POINT TO THIS FILE RATHER THAN THE DEFAULT DB_active_record.
* SEE COPY OF UPDATED FILE IN 'MODIFIED CORE FILES' DIERECTORY.
*
* Requirements: PHP5 or above
*
*/
class MY_DB_active_record extends CI_DB_active_record {
	var $ar_set_var				= array();
	var $ar_cache_set_var				= array();
	

	// --------------------------------------------------------------------

	/**
	 * Sets the ORDER BY value
	 * 
	 * ctranel, 6/27/2012:  Added 3rd param to block escaping in cases where SQL functions are used in the order by clause
	 *
	 * @param	string
	 * @param	string	direction: asc or desc
	 * @return	object
	 */
	public function order_by($orderby, $direction = '', $protect_identifiers = TRUE)
	{
		if (strtolower($direction) == 'random')
		{
			$orderby = ''; // Random results want or don't need a field name
			$direction = $this->_random_keyword;
		}
		elseif (trim($direction) != '')
		{
			$direction = (in_array(strtoupper(trim($direction)), array('ASC', 'DESC'), TRUE)) ? ' '.$direction : ' ASC';
		}


		if (strpos($orderby, ',') !== FALSE && $protect_identifiers)
		{
			$temp = array();
			foreach (explode(',', $orderby) as $part)
			{
				$part = trim($part);
				if ( ! in_array($part, $this->ar_aliased_tables))
				{
					$part = $this->_protect_identifiers(trim($part));
				}

				$temp[] = $part;
			}

			$orderby = implode(', ', $temp);
		}
		else if ($direction != $this->_random_keyword && $protect_identifiers)
		{
			$orderby = $this->_protect_identifiers($orderby);
		}

		$orderby_statement = $orderby.$direction;

		$this->ar_orderby[] = $orderby_statement;
		if ($this->ar_caching === TRUE)
		{
			$this->ar_cache_orderby[] = $orderby_statement;
			$this->ar_cache_exists[] = 'orderby';
		}

		return $this;
	}
	
	// --------------------------------------------------------------------
	/**
	 * Insert_Batch
	 *
	 * Compiles batch insert strings and runs the queries
	 *
	 * @access	public
	 * @param	string	the table to retrieve the results from
	 * @param	array	an associative array of insert values
	 * @param	array	an array of fields that cannot be null
	 * @param	array	an array of fields that are numeric
	 * @param	array	an array of fields where zero should be converted to NULL
	 * @return	object
	 */
	function insert_batch($table = '', $set = NULL, $arr_notnull_fields = array(), $arr_numeric_fields = array(), $arr_zero_is_null_fields = array()) {
		if ( ! is_null($set)) {
			$this->set_insert_batch($set,'',FALSE, $arr_notnull_fields, $arr_numeric_fields, $arr_zero_is_null_fields);
		}
		if (count($this->ar_set) == 0) {
			if ($this->db_debug) {
				//No valid data array.  Folds in cases where keys and values did not match up
				return $this->display_error('db_must_use_set');
			}
			return FALSE;
		}

		if ($table == '') {
			if ( ! isset($this->ar_from[0])) {
				if ($this->db_debug) {
					return $this->display_error('db_must_set_table');
				}
				return FALSE;
			}

			$table = $this->ar_from[0];
		}

		// Batch this baby
		for ($i = 0, $total = count($this->ar_set); $i < $total; $i = $i + 2500) {
			$sql = $this->_insert_batch($this->_protect_identifiers($table, TRUE, NULL, FALSE), $this->ar_keys, array_slice($this->ar_set, $i, 2500));

			//echo $sql;

			$this->query($sql);
		}

		$this->_reset_write();


		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * The "set_insert_batch" function.  Allows key/value pairs to be set for batch inserts
	 *
	 * @access	public
	 * @param	mixed key
	 * @param	string value
	 * @param	boolean escape value?
	 * @param	array	an array of fields that cannot be null
	 * @param	array	an array of fields that are numeric
	 * @param	array	an array of fields where zero should be converted to NULL
	 * @return	object
	 */

	function set_insert_batch($key, $value = '', $escape = TRUE, $arr_notnull_fields = array(), $arr_numeric_fields = array(), $arr_zero_is_null_fields = array()) {
		$key = $this->_object_to_array_batch($key);

		if ( ! is_array($key)) {
			$key = array($key => $value);
		}

		$keys = array_keys(current($key));
		sort($keys);

		foreach ($key as $row) {
			if (count(array_diff($keys, array_keys($row))) > 0 OR count(array_diff(array_keys($row), $keys)) > 0) {
				// batch function above returns an error on an empty array
				$this->ar_set[] = array();
				return;
			}
		
			ksort($row); // puts $row in the same order as our keys

			$value = '';
			foreach($row as $k=>$v) {
				if ($escape !== FALSE) $v = $this->escape($v);
    			$tmp_is_numeric = in_array($k,$arr_numeric_fields);
    			if($tmp_is_numeric && $v == 0 && in_array($k, $arr_zero_is_null_fields)) $value .= 'NULL, ';
    			elseif($tmp_is_numeric && $v == 0 && $v != '') $value .= '0, ';
    			elseif(empty($v) && strlen($v) == 0 && !in_array($k, $arr_notnull_fields)) $value .= 'NULL, ';
    			elseif(empty($v) && $tmp_is_numeric) $value .= '0, ';
   				elseif(in_array($k,$arr_numeric_fields)) $value .= $v . ', ';
    			else $value .= "'" . $v . "', ";
   			}
			if(!empty($value)) $this->ar_set[] = '(' . substr($value, 0, -2) . ')';
		}

		foreach ($keys as $k) {
			$this->ar_keys[] = $this->_protect_identifiers($k);
		}

		return $this;
	}
}