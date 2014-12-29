<?php

namespace myagsource\Site;

require_once(APPPATH . 'libraries/dhi/herd.php');

use myagsource\dhi\Herd;
/**
 *
 * @author ctranel
 *        
 */
interface iWebContentRepository {
//	public function childKeyValuePairs();
	public function getByPath($path);
	public function loadChildren(iWebContent $web_content, iWebContentRepository $child_repos, $user_id, Herd $herd, $arr_task_permissions);
}

?>