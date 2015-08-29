<?php 
	$class = 'first';
	if($this->as_ion_auth->has_permission("Add All Users") || $this->as_ion_auth->has_permission("Add Users In Region")): ?>
		<li class="<?php echo $class; ?>"><?php echo anchor('auth/create_user','Add Account', 'class="teal_banner"') ?></li>
<?php	$class = '';
	endif; 
	if($this->as_ion_auth->has_permission("Edit All Users") || $this->as_ion_auth->has_permission("Edit Users In Region")): ?>
		<li><?php echo anchor('auth/list_accounts','List Accounts', 'class="teal_banner"') ?></li>
<?php	$class = '';
	endif; ?>
	<?php if($this->as_ion_auth->is_editable_user($this->session->userdata('user_id'), $this->session->userdata('user_id'))): ?>
		<li class="<?php echo $class; ?>"><?php echo anchor('auth/edit_user','Edit Account', 'class="teal_banner"') ?></li>
<?php	$class = '';
	endif; ?>
	<?php if($this->as_ion_auth->has_permission("View Access Log")): ?>
		<li class="<?php echo $class; ?>"><?php echo anchor('access_log/display', 'Access Log', 'class="teal_banner"'); ?></li>
	<?php endif; ?>
