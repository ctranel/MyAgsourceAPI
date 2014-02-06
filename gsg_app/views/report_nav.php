<div id="report-links">
<?php
if(isset($arr_pstring) && !empty($arr_pstring) && count($arr_pstring) > 1): ?>
	<div id="pstring-links">
		<a name="pstring-nav" class="section-header" id="select-pstring">Select PString:</a> 
		<ul class="pstring-nav">
			<?php $top = count($arr_pstring);
			$first = TRUE;
			for( $c=0; $c < $top; $c++):
				if($first):
					$li_class = 'first';
					$first = FALSE;
				else :
					$li_class = FALSE; 
				endif; ?>
			<li<?php if($li_class) echo ' class="first" style="font-weight: bold"'; ?>><a href="#chart" class="pstring-link<?php if($arr_pstring[$c]['pstring'] == $curr_pstring) echo ' current'; ?>" id="<?php echo $arr_pstring[$c]['pstring']; ?>" onclick="$('.pstring-filter-item > input').prop('checked', false); $('input:checkbox[value=<?php echo $arr_pstring[$c]['pstring']; ?>]').prop('checked', true); return updatePage(this);"><?php echo $arr_pstring[$c]['publication_name']; // . ' - ' . $arr_pstring[$c]['publication_name']; ?></a></li>
			<?php endfor; ?>
		</ul>
	</div>
<?php 
endif;
if(isset($arr_pages) && is_array($arr_pages) && count($arr_pages) > 1): ?>
	<div id="block-links">
		<a class="section-header" id="select-block">Select Report Page:</a>
		<ul class="report-nav" id="current">
		<?php
		$first = TRUE;
			foreach($arr_pages as $e):
				if($first):
					$li_class = 'first';
					$first = FALSE;
				else :
					$li_class = FALSE; 
				endif; ?>
				<li<?php if($li_class) echo ' class="first"'; ?>><a href="<?php echo site_url($section_path . '/' . $e['url_segment']); ?>" id="<?php echo $e['url_segment']; ?>"<?php if($e['url_segment'] == $curr_page) echo ' class="current"'; ?>><?php echo $e['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php
endif; ?>
</div>