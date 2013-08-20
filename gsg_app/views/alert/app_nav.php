		<li class="first"><?php echo anchor('alert/display', 'Alert Report', 'class="teal_banner"'); ?></li>
		<li><?php echo anchor('alert/graph', 'SCC Graph', 'class="teal_banner"'); ?></li>
	<?php if($this->as_ion_auth->has_permission("View Access Log")): ?>
		<li><?php echo anchor('access_log/display/access_time/DESC/screen/5', 'Access Log', 'class="teal_banner"'); ?></li>
	<?php endif; ?>
