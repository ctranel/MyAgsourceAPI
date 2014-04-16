<?php
@ini_set('zend_monitor.enable', 0);
if(@function_exists('output_cache_disable')) {
	@output_cache_disable();
}
if(isset($_GET['debugger_connect']) && $_GET['debugger_connect'] == 1) {
	if(function_exists('debugger_connect'))  {
		debugger_connect();
		exit();
	} else {
		echo "No connector is installed afafaghkgkgsfa.";
	}
}
// KLM 20140416 Creating bogus change to push thru new post-update hook on FEWEB/5nines
?>
