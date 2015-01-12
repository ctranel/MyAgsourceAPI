<div class="tip">
<?php
if(!isset($fresh) && empty($fresh) && !isset($tested) && empty($tested) && !isset($testedearly) && empty($testedearly) && !isset($numbertests) && empty($numbertests)){
?>
	No tips found.
<?php 
} else {

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