<?php

namespace myagsource\Site;

/**
 *
 * @author ctranel
 *        
 */
interface iPageAccess {
	public function hasAccess($is_subscribed);
}

?>