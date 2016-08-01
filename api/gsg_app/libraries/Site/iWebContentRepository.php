<?php

namespace myagsource\Site;

require_once(APPPATH . 'libraries/dhi/Herd.php');

use myagsource\dhi\Herd;
/**
 *
 * @author ctranel
 *        
 */
interface iWebContentRepository {
//	public function childKeyValuePairs();
	public function getByPath($path, $parent_id = null);
}

?>