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
// KLM 20140314 Attempting to trigger post_update_hook on 5nines to repopulate var/www/myagsource folders
?>
