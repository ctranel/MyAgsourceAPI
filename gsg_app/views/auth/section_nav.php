<?php $class = 'first';
	 if($this->as_ion_auth->has_permission("Manage Staff") && FALSE): ?>
		<li class="<?php echo $class; ?>"><?php echo anchor('region','Manage Assoc/Regions', 'class="teal_banner"') ?></li>
<?php	$class = '';
	endif; ?>
	<?php if($this->as_ion_auth->has_permission("Manage Other Accounts")): ?>
		<li class="<?php echo $class; ?>"><?php echo anchor('auth/create_user','Add Account', 'class="teal_banner"') ?></li>
		<li><?php echo anchor('auth/list_accounts','List Accounts', 'class="teal_banner"') ?></li>
<?php	$class = '';
	endif; ?>
	<?php if(($this->as_ion_auth->logged_in())): ?>
		<li class="<?php echo $class; ?>"><?php echo anchor('auth/edit_user','Edit Account', 'class="teal_banner"') ?></li>
<?php	$class = '';
	endif; ?>
	<?php if($this->as_ion_auth->has_permission("View Access Log")): ?>
		<li class="<?php echo $class; ?>"><?php echo anchor('access_log/display', 'Access Log', 'class="teal_banner"'); ?></li>
	<?php endif; ?>
