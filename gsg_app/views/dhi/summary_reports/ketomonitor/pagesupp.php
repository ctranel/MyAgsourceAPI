<div class="tip">
<?php
if(!isset($fresh) && empty($fresh) && !isset($tested) && empty($tested) && !isset($testedearly) && empty($testedearly) && !isset($numbertests) && empty($numbertests) && !isset($test_date) && empty($test_date)) {
?>
	No tips found.
<?php 
} else {

	echo '<strong>KetoMonitor Statistics</strong><br/>';

	if(isset($test_date) && !empty($test_date)) {
		echo $test_date.'<br/>';
	}
	
	if(isset($fresh) && !empty($fresh)) {	
		echo $fresh.'<br/>';
	}
	if(isset($tested) && !empty($tested)) {
		echo $tested.'<br/>';
	}
	if(isset($testedearly) && !empty($testedearly)) {
		echo $testedearly.'<br/>';
	}
	if(isset($numbertests) && !empty($numbertests)) {
		echo $numbertests.'<br/>';
	}

}
?>
</div>