<?php

namespace myagsource\Page;

require_once APPPATH . 'libraries/Page/Content/Sort.php';
require_once APPPATH . 'libraries/Datasource/iDataField.php';

use \myagsource\Page\Content\Sort;
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