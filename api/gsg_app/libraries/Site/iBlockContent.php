<?php

namespace myagsource\Site;

/**
 *
 * @author ctranel
 *        
 */
interface iBlockContent{// extends iWebContent {
//	public function id();
//	public function name();
//	public function description();
//	public function displayType();
	public function toArray();
}

?>