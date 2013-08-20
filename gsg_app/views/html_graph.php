<?php // vars: id, arr_series[id, label, value, arr_benchmarks[10, 50, 90]]?>
<div id="<?php if(isset($id)) echo $id; ?>" class="html-graph">
	<?php if(isset($arr_series) && is_array($arr_series)) {
		foreach($arr_series as $s) { ?>
			<div id="<?php if(isset($s['id'])) echo $s['id']; ?>-label-box" class="series-label-box">
				<div id="<?php if(isset($s['id'])) echo $id; ?>-label" class="series-label"><?php echo $s['label']; ?></div>
				<div id="<?php if(isset($s['id'])) echo $id; ?>-value" class="series-value"><?php echo $s['value']; ?></div>
			</div>
			<?php
			for($x=0; $x<=100; $x+10){
				//each of 10 blocks ?>
				<div class="graph-segment"></div>
<?php 			//percentile values at 10, 50 and 90 ?>
				<div class="graph-segment"><?php if(isset($s['arr_benchmarks'][$x])) echo $s['arr_benchmarks'][$x]; ?></div>
<?php 		}
		}
	}
	?>
</div>