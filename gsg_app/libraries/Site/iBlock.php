<?php

namespace myagsource\Site;

/**
 *
 * @author ctranel
 *        
 */
interface iBlock extends iWebContent {
	public function displayType();
	//public function hasBenchmark();
	public function toArray();
}

?>