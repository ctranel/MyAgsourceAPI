<?php
namespace myagsource\Report;

require_once APPPATH . 'libraries/Report/iSort.php';

//use \myagsource\Datasource\iSort;
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
	
}

?>