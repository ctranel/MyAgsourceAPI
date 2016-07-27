<?php

namespace myagsource\Site;

/**
 *
 * @author ctranel
 *        
 */
interface iBlock extends iWebContent {
	public function id();
	public function name();
	public function description();
	public function path();
	public function displayType();
	public function toArray();
}

?>