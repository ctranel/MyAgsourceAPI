<?php
if(isset($lact_table) && !empty($lact_table)): ?> 
	<h3 class="tight-to-table">Lactation Records</h3>
<?php
	echo $lact_table;
endif;
if(isset($offspring_table) && !empty($offspring_table)): ?>
	<h3 class="tight-to-table">Offspring Records</h3>
<?php
	echo $offspring_table;
endif;
