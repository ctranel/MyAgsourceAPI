<?php
namespace myagsource\Site;

require_once APPPATH . 'libraries/Site/iWebContent.php';

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
	public function hasBenchmark();
	public function toArray();
}

?>