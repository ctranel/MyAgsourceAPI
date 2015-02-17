<?php
namespace myagsource\Report;

require_once APPPATH . 'libraries/Report/iBlock.php';

use \myagsource\Datasource\iDataField;
/**
 * Name:  iSort
 *
 * Author: ctranel
 *
 * Created:  02-10-2015
 *
 * Description:  Interface for report sorting.
 *
 */
interface iSort {
	/**
	 */
	function __construct(iDataField $datafield, $order);
	function fieldName();
	function order();
	
}

?>