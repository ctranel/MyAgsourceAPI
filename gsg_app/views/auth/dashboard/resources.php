<div class="widget-content">
		<ul>
			<li><?php echo anchor('img/bench-all.pdf', 'All Breed Benchmarks', array('id' => 'all-benchmarks')); ?></li>
			<li><?php echo anchor('img/bench-ho.pdf', 'Holstein Benchmarks', array('id' => 'ho-benchmarks')); ?></li>
		</ul>
		<?php if(isset($inner_html)) echo $inner_html; ?>
</div>