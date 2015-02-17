<?php

namespace myagsource\Site;

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
	
	function setReportFields();
	
	function sortFieldNames();
	function sortOrders();
	function setDefaultSort();
}

?>