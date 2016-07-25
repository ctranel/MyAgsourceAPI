<?php
namespace myagsource\Report;

/**
 * Name:  iSort
 *
 * Author: ctranel
 *
 * Created:  04-02-2015
 *
 * Description:  Interface for report sorting.
 *
 */
interface iSort {
	/**
	 */
	function __construct(\myagsource\Datasource\iDataField $datafield, $order);
	function fieldName();
	function order();
	function sortText($is_first);
	function sortTextBrief($is_first);
	function isDate();
}

?>