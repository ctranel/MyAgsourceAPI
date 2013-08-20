	<?php if(($this->as_ion_auth->logged_in())):
	if($this->as_ion_auth->has_permission("View Non-owned Herds") || $access_level == 'cow' || $access_level == 'both'): ?>
		<li class="first"><?php echo anchor('gsg/cow_report/display','Cow Report', 'class="teal_banner"'); ?></li>
		<?php endif;
		if($this->as_ion_auth->has_permission("View Non-owned Herds") || $access_level == 'heifer' || $access_level == 'both'):?>
		<li><?php echo anchor('gsg/heifer_report/display', 'Heifer Report', 'class="teal_banner"'); ?></li>
	<?php endif; 
		else:?>
		<li><?php echo anchor_popup('http://documents.crinet.com/AgSource-Cooperative-Services/DHI/F2785-021-GeneticSelectionGuide.pdf', 'User Manual', array('class'=>"teal_banner"));?></td>
		<li><?php echo anchor('gsg/cow_report/display','Sample Cow Report', 'class="teal_banner"'); ?></li>
		<li><?php echo anchor('gsg/heifer_report/display', 'Sample Heifer Report', 'class="teal_banner"'); ?></li>
	<?php endif; ?>
	<?php if($this->as_ion_auth->has_permission("Upload Herd")): ?>
		<li><?php echo anchor('gsg/file_upload/upload_form', 'Upload Herd File', 'class="teal_banner"'); ?></li>
	<?php endif; ?>
	<?php if($this->as_ion_auth->has_permission("View Access Log")): ?>
		<li><?php echo anchor('access_log/display/access_time/DESC/screen/1', 'Access Log', 'class="teal_banner"'); ?></li>
	<?php endif; ?>
