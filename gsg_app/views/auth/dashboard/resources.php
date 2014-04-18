<div class="widget-content">
		<ul>
			<li><?php echo anchor(site_url('download/index/bench_all.pdf'), 'All Breed Benchmarks', array('id' => 'all-benchmarks')); ?></li>
			<li><?php echo anchor(site_url('download/index/bench_ho.pdf'), 'Holstein Benchmarks', array('id' => 'ho-benchmarks')); ?></li>
		</ul>
		<?php if(isset($inner_html)) echo $inner_html; ?>
</div>