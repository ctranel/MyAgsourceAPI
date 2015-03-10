<?php

namespace myagsource\Report;

require_once APPPATH . 'libraries/Report/Content/Sort.php';
require_once APPPATH . 'libraries/Datasource/iDataField.php';

use \myagsource\Report\Content\Sort;
use \myagsource\Datasource\iDataField;

/**
 *
 * @author ctranel
 *        
 */
interface iBlock {
//	function childKeyValuePairs();
	function id();
	function path();
	function name();
	
	function reportFields();
	function setReportFields();
	
	function resetSort();
	function addSort(Sort $sort);
	function addSortField(iDataField $datafield, $sort_order);
	function sorts();
	function setDefaultSort();
}

?>