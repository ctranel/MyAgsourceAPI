<?php
namespace myagsource\Listings;

/**
 * Name:  iWhereCriteria
 *
 * Author: ctranel
 *
 * Created:  2017-08-01
 *
 * Description:  Interface for listing where criteria.
 *
 */
interface iWhereCriteria {
	/**
	 */
	function __construct(\myagsource\Datasource\iDataField $datafield, $operator, $operand);
	function fieldName();
//	function operator();
	function criteria();
}

?>