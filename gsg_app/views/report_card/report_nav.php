<?php $base_url = site_url(); ?>
<a name="#report-card-nav"></a>
<div id="report-links">
	<div id="pstring-links">
	<?php if(!empty($arr_pstring) && count($arr_pstring) > 1): ?>
		<a name="pstring-nav" class="label section-header" id="select-pstring">Select PString:</a> 
		<ul class="pstring-nav">
	
	
			<?php $top = count($arr_pstring);
			for( $c=0; $c < $top; $c++): ?>
				<li class="first"><a href="#report-card-nav" id="<?php echo $arr_pstring[$c]['pstring']; ?>" onclick="return updateBlock(event, this, 'pstring-links', 'pstring', '<?php echo $arr_pstring[$c]['pstring']; ?>');"><?php echo $arr_pstring[$c]['publication_name']; // . ' - ' . $arr_pstring[$c]['publication_name']; ?></a></li>
					
			<?php endfor; ?>
		</ul>
	<?php endif; ?>
	</div>
	<div id="chart-links">
		<a class="label section-header" id="select-chart">Select Chart:</a><br>
		<a class="label subnav">Current:</a>
		<ul class="report-card-nav" id="current">
			<li class="first"><a href="#chart" id="production" onclick="return updateBlock(event, this, 'chart-links', 'chart', 'production');">Production</a></li>
			<li><a href="#chart" id="reproduction" onclick="return updateBlock(event, this, 'chart-links', 'chart', 'reproduction');">Reproduction &amp; Genetics</a></li>
			<li><a href="#chart" id="inventory" onclick="return updateBlock(event, this, 'chart-links', 'chart', 'inventory');">Inventory</a></li>
			<li><a href="#chart" id="uhm" onclick="return updateBlock(event, this, 'chart-links', 'chart', 'uhm');">Udder Health</a></li>
			<li><a href="#chart" id="fresh-cow" onclick="return updateBlock(event, this, 'chart-links', 'chart', 'fresh-cow');">Fresh Cow Transition</a></li>
		</ul><br>
		<a class="label subnav">Historical:</a>
		<ul class="report-card-nav" id="historical">
			<li class="first"><a href="#chart" id="long-milk" onclick="return updateBlock(event, this, 'chart-links', 'chart', 'long-milk');">Milk Quantity &amp; Quality</a></li>
			<li><a href="#chart" id="long-trans" onclick="return updateBlock(event, this, 'chart-links', 'chart', 'long-trans');">Transition - Mastitis - Reproduction</a></li>
			<li><a href="#chart" id="long-genetics" onclick="return updateBlock(event, this, 'chart-links', 'chart', 'long-genetics');">Genetics - Inventory</a></li>
		</ul>
	</div>
	<div id="bench-links">
		<a name="benchmark-nav" class="label section-header" id="select-benchmarks">Select Benchmarks:</a><br>
		<a class="label subnav">Holstein:</a>
		<ul class="benchmark-nav">
			<li class="first"><a href="#chart" id="1-1" onclick="return updateBlock(event, this, 'bench-links', 'benchmarks_id', '1-1');">&lt; 100</a></li>
			<li><a href="#chart" id="1-2" onclick="return updateBlock(event, this, 'bench-links', 'benchmarks_id', '1-2');">100-250</a></li>
			<li><a href="#chart" id="1-3" onclick="return updateBlock(event, this, 'bench-links', 'benchmarks_id', '1-3');">251-500</a></li>
			<li><a href="#chart" id="1-4" onclick="return updateBlock(event, this, 'bench-links', 'benchmarks_id', '1-4');">501-1000</a></li>
			<li><a href="#chart" id="1-5" onclick="return updateBlock(event, this, 'bench-links', 'benchmarks_id', '1-5');">1001+</a></li>
		</ul><br>
		<a class="label subnav">Jersey:</a>
		<ul class="benchmark-nav">
			<li class="first"><a href="#chart" id="1-0" onclick="return updateBlock(event, this, 'bench-links', 'benchmarks_id', '1-0');">All Jersey Herds</a></li>
		</ul><br>
		<a class="label subnav">All Breeds:</a>
		<ul class="benchmark-nav">
			<li class="first"><a href="#chart" id="2-1" onclick="return updateBlock(event, this, 'bench-links', 'benchmarks_id', '2-1');">&lt; 100</a></li>
			<li><a href="#chart" id="2-2" onclick="return updateBlock(event, this, 'bench-links', 'benchmarks_id', '2-2');">100-250</a></li>
			<li><a href="#chart" id="2-3" onclick="return updateBlock(event, this, 'bench-links', 'benchmarks_id', '2-3');">251-500</a></li>
			<li><a href="#chart" id="2-4" onclick="return updateBlock(event, this, 'bench-links', 'benchmarks_id', '2-4');">501-1000</a></li>
			<li><a href="#chart" id="2-5" onclick="return updateBlock(event, this, 'bench-links', 'benchmarks_id', '2-5');">1001+</a></li>
		</ul>
	</div>
</div>
<!-- <p id="comp-info"></p> -->
<p id="approx">Percentile rankings are approximate when comparing your herd to alternate benchmarks.</p>