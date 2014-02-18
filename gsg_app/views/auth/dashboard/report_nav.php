<?php if(!empty($arr_pstring) && count($arr_pstring) > 1): ?>
<div id="report-links">
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
			<li<?php if($li_class) echo ' class="first" style="font-weight: bold"'; ?>><a href="<?php echo site_url('land/index/' . $arr_pstring[$c]['pstring']); ?>" class="pstring-link<?php if($arr_pstring[$c]['pstring'] == $curr_pstring) echo ' current'; ?>" id="<?php echo $arr_pstring[$c]['pstring']; ?>"><?php echo $arr_pstring[$c]['publication_name']; // . ' - ' . $arr_pstring[$c]['publication_name']; ?></a></li>
			<?php endfor; ?>
		</ul>
	</div>
</div>
<?php endif;
