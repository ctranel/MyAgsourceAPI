<div id="<?php echo str_replace(' ', '-', $title)?>" class="accordion-section">
	<h2><a href="<?php echo '#' . str_replace(' ', '-', $title) ?>" title="Show <?php echo $title; ?>"><?php echo $title; ?></a></h2>
	<div>
		<?php echo $content; ?>
	</div>
</div>