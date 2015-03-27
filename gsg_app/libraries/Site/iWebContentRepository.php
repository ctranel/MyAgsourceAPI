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
	public function loadChildren(iWebContent $web_content, iWebContentRepository $child_repos, $user_id, Herd $herd, $arr_task_permissions);
}

?>