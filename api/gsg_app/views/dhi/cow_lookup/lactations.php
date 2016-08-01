<?php
if(isset($lact_table) && !empty($lact_table)): ?> 
	<h2 class="table">Lactation Records</h2>
<?php
	echo $lact_table;
endif;
if(isset($offspring_table) && !empty($offspring_table)): ?>
	<h2 class="table">Offspring Records</h2>
<?php
	echo $offspring_table;
endif;
