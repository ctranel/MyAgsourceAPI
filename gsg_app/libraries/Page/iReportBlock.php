<?php

namespace myagsource\Report;

require_once APPPATH . 'libraries/Page/Content/Sort.php';
require_once APPPATH . 'libraries/Datasource/iDataField.php';

use \myagsource\Page\Content\Sort;
use \myagsource\Datasource\iDataField;

/**
 *
 * @author ctranel
 *        
 */
interface iReportBlock {
//	function childKeyValuePairs();
	function id();
	function path();
	function title();
	
	function reportFields();
	function setReportFields();
	
	function resetSort();
	function addSort(Sort $sort);
//	function addSortField(iDataField $datafield, $sort_order);
//	function sorts();
//	function joins();
	function setDefaultSort();
}

?>