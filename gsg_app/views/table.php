<?php//section of reports with nested report_table view(s)	if(!isset($div_id)) $div_id = 'table-canvas';	if(isset($before_chart)) echo $before_chart;	if(!isset($block_num)) $block_num = '1';	?><div id="table-wrapper<?php echo $block_num; ?>" class="table-wrapper">	<div class="table-section">			<div class="downloads-container" id="downloads-container<?php echo $block_num; ?>">				<?php echo img(array('src'=>'img/download.png','title'=>"Export file", 'id'=>"open-download-menu" .  $block_num, 'class'=>"open-download-menu")); ?>				<ul class="download-links-container">					<!-- <li class="download-link-wrapper"><a href="<?php echo $link_url . '/pdf/' . $sort_by . '/' . $sort_order; ?>" id="pdf-link" class="download-links">Download PDF document</a></li> -->					<li class="download-link-wrapper"><a href="<?php echo $link_url . '/csv/' . $sort_by . '/' . $sort_order; ?>" id="csv-link<?php echo $block_num; ?>" class="download-links">Download CSV file</a></li>				</ul>			</div><!-- 			<h2 id="table-title-line<?php echo $block_num; ?>" class="block"><?php if(isset($table_title)) echo $table_title; ?></h2>			<h3 id="table-subtitle-line<?php echo $block_num; ?>" class="block"><?php if(isset($table_subtitle)) echo $table_subtitle; ?></h3>			<h3 id="table-benchmark-line<?php echo $block_num; ?>" class="block"><?php if(isset($table_benchmark_text)) echo $table_benchmark_text; ?></h3> -->		<div id="<?php echo $div_id . $block_num; ?>" class="table-container" data-block="<?php echo $block; ?>">			<?php if(isset($data_table)) echo $data_table;?>		</div>		<?php echo img(array('src'=>'img/waiting.gif','title'=>"loading...", 'id'=>"waiting-icon" . $block_num, 'class'=>"waiting-icon")); ?>	</div></div><?php if(isset($after_chart)) echo $after_chart; ?>