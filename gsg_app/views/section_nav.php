<?php 	$class = 'first';
		if(!isset($section_id)){
			$section_id = '';
		}
		if(isset($subsections) && is_a($subsections, 'SplObjectStorage') && $subsections->count() > 0){
			foreach($subsections as $a): 
				if($section_path == $a->path()):
					$class .= ' current';
				endif; ?>
				<li class = "<?php echo $class; ?>"><?php echo anchor(site_url() . $a->path(), $a->name(), 'class="section-nav"'); ?></li>
<?php	 		$class = '';
			endforeach;
		}
		if($this->as_ion_auth->has_permission("View Access Log") && FALSE): ?>
			<li class = "<?php echo $class; ?>"><?php echo anchor('access_log/display/access_time/DESC/screen/' . $section_id, 'Access Log', 'class="section-nav"'); ?></li>
<?php		$class = '';
		endif; 
