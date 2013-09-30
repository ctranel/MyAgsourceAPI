<?php 	$class = 'first';
		if(!isset($section_id)) $section_id = '';
		foreach($arr_pages as $p): ?>
			<li class = "<?php echo $class; ?>"><?php echo anchor($p['path'], $p['name'], 'class="section-nav"'); ?></li>
<?php 		$class = '';
		endforeach;
		if($this->as_ion_auth->has_permission("View Access Log") && FALSE): ?>
			<li class = "<?php echo $class; ?>"><?php echo anchor('access_log/display/access_time/DESC/screen/' . $section_id, 'Access Log', 'class="section-nav"'); ?></li>
<?php		$class = '';
		endif; 
