		<!-- <li class="first"><?php echo anchor('report_card/display', 'View Report Card', 'class="teal_banner"'); ?></li> -->
	<?php if($this->as_ion_auth->has_permission("View Access Log")): ?>
		<li><?php echo anchor('access_log/display/access_time/DESC/screen/4', 'Access Log', 'class="teal_banner"'); ?></li>
	<?php endif; ?>
		