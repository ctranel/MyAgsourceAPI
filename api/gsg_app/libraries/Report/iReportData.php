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
interface iReportData {
	function getData();
}

?>