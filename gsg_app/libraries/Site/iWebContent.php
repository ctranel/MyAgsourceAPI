<?php

namespace myagsource\Site;

/**
 *
 * @author ctranel
 *        
 */
interface iWebContent {
//	public function childKeyValuePairs();
	public function id();
	public function path();
	public function name();
	public function children();
	public function loadChildren(\SplObjectStorage $children);
}

?>