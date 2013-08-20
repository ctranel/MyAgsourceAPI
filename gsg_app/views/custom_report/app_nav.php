	<?php if($this->as_ion_auth->has_permission("View Access Log")): ?>
		<li><?php echo anchor('access_log/display/access_time/DESC/screen/10', 'Access Log', 'class="teal_banner"'); ?></li>
	<?php endif; ?>
		