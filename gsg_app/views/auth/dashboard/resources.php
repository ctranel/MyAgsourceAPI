<div class="widget-content">
	<div id="resources">Resources that can enhance your bottom line:
		<form action="http://www.agsource.com/scripts/benchmark_submit.php" id="benchmark-form" method="get" target="_blank">
			<input type="hidden" name="Name" id="Name" value="<?php if(isset($name)) echo $name; ?>" />
			<input type="hidden" name="section_email" id="section_email" size="40" value="<?php if(isset($email)) echo $email; ?>" />
		</form>
		<ul>
			<li><?php echo anchor('#', 'View Benchmarks', array('id' => 'view-benchmarks')); ?></li>
			<li><?php echo anchor_popup('http://agsource.crinet.com/page3439/DG29', 'DG29<sup>TM</sup> Blood Preg Test'); ?></li>
			<li><?php echo anchor_popup('http://agsource.crinet.com/page2934/ProfitOpportunityAnalyzer', 'Profit Opportunity Analyzer<sup>&reg;</sup>'); ?></li>
		</ul>
		<?php if(isset($inner_html)) echo $inner_html; ?>
	</div>
</div>