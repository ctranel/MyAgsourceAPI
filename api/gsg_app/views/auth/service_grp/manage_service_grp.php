<?php if(isset($page_header) !== false) echo $page_header; ?>
<div class='mainInfo'>
	<?php if(isset($page_heading) !== false) echo heading($page_heading);
	if(FALSE):?>
		<p><?php echo anchor('auth/service_grp_access', 'Add New Consultant Access'); ?></p>
	<?php endif; ?>
	<div class="accordion">
		<?php if(isset($arr_sections) && is_array($arr_sections)):
			foreach($arr_sections as $k => $v): 
				echo $v;
			endforeach;
		endif; ?>
	</div>
</div>
<?php if(isset($page_footer) !== false) echo $page_footer;