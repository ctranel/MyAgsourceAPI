<?php
namespace myagsource\Report;

/**
 * Name:  iWhereCriteria
 *
 * Author: ctranel
 *
 * Created:  06-04-2015
 *
 * Description:  Interface for report where criteria.
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