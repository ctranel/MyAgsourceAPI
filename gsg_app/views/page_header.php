<?php 
header('strict-transport-security: max-age=31536000; includeSubdomains');
header("Content-Security-Policy:"
	. " default-src https://" . $_SERVER['HTTP_HOST'] . " https://*." . $_SERVER['HTTP_HOST'] . " https://myagsource.com feweb.verona.crinet https://maxcdn.bootstrapcdn.com https://*.uservoice.com https://www.google-analytics.com;"
	. " script-src 'unsafe-inline' 'unsafe-eval' https://" . $_SERVER['HTTP_HOST'] . " https://*." . $_SERVER['HTTP_HOST'] . " feweb.verona.crinet https://cdnjs.cloudflare.com https://ajax.googleapis.com https://netdna.bootstrapcdn.com https://cloud.github.com https://code.highcharts.com https://cdn.jsdelivr.net https://*.uservoice.com https://www.google-analytics.com http://*.uservoice.com http://www.google-analytics.com;"
	. " style-src 'unsafe-inline' https://" . $_SERVER['HTTP_HOST'] . " https://*." . $_SERVER['HTTP_HOST'] . " feweb.verona.crinet https://maxcdn.bootstrapcdn.com https://cdn.jsdelivr.net;"
	. " frame-src https://*.uservoice.com;"
	. " frame-ancestors 'none';"		
);
header('x-frame-options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Content-Type: text/html;charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-UA-Compatible: IE=edge,chrome=1');
?>
<!doctype html>
<html lang="en">
<head profile="http://www.w3.org/2005/10/profile">
	<title><?php if(isset($title)) echo $title; ?></title>
    <meta name="robots" content="NO FOLLOW,NO INDEX">
<!--    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="cache-control" content="no-cache">
    <meta name="googlebot" content="NOARCHIVE"> -->
    <meta name="description" content="<?php if(isset($description)) echo $description; ?>">
    <meta name="keywords" content="<?php echo $this->config->item('product_name'); ?> - <?php echo $this->config->item('cust_serv_company'); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
 	<link rel="stylesheet" href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css'>
	<link rel="icon" type="image/png" href="https://myagsource.com/favicon.ico">
<?php
	    $this->carabiner->css('corporate.css', 'screen');
	    $this->carabiner->css('navigation.css', 'screen');
	    $this->carabiner->css('print.css', 'print');
		$this->carabiner->css('myags.css', 'screen');
		$this->carabiner->css('myags.css', 'print');
		$this->carabiner->display('css');
	?>
<?php 
	if(!empty($arr_head_line) && is_array($arr_head_line) !== FALSE):
		 foreach ($arr_head_line as $hl):
			echo $hl . "\r\n";
		endforeach;
	endif; ?>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/headjs/1.0.3/head.min.js"></script>
	<script type="text/javascript">
		head.js(
			{jquery: "https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"},
			{bootstrap: "https://netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"},
			{sectionhelper: "<?php echo $this->config->item('base_url_assets'); ?>js/as_section_helper.js"},
			{formhelper: "<?php echo $this->config->item('base_url_assets'); ?>js/form_helper.js"},
			{ajaxforms: "<?php echo $this->config->item('base_url_assets'); ?>js/ajax_forms.js"},
			{ko: "https://cloud.github.com/downloads/knockout/knockout/knockout-2.1.0.js"},
			{navhelper: "<?php echo $this->config->item('base_url_assets'); ?>js/nav.js"},
			{navmodel: "<?php echo $this->config->item('base_url_assets'); ?>js/nav_viewmodel.js"}
			<?php
				if(!empty($arr_headjs_line) && is_array($arr_headjs_line) !== FALSE):
					$c = count($arr_headjs_line);
					for ($x = 0; $x < $c; $x++):
						echo ",\r\n" . $arr_headjs_line[$x];
						//if($x < ($c - 1)) echo ",\r\n";
					endfor;
				endif;
			?>
		);
	</script>
</head>
<body>
<?php $url = site_url(); ?>
<div id="nav-width-control">&nbsp;</div>
<div class="container" id="container">
	<div id="header">
		<a href="https://myagsource.com/"><div class="header-link">&nbsp;</div></a>
		<?php echo $navigation?>
	</div> <!-- header -->
	<div id="main-content">
		<ul id="session-nav" class="dropdown">
			<?php if(($this->as_ion_auth->logged_in())):
				$arr_groups = $this->session->userdata('arr_groups');
				if(isset($arr_groups) && is_array($arr_groups) && count($arr_groups) > 1): ?>
					<li class="groupnav"><a class="dropdown-toggle" data-toggle="dropdown"><?php echo $arr_groups[$this->session->userdata('active_group_id')]; ?></a>
						<ul class="dropdown-menu" role="menu">
							<?php foreach($arr_groups as $k=>$v): ?>
								<li role="presentation"><?php echo anchor($url . 'auth/set_role/'. $k, $v);?></li>
							<?php endforeach; ?>
						</ul>
					</li> <!-- close "Select Section" li -->
				<?php endif; ?>
			<li><?php echo anchor('auth/logout', 'Log Out'); ?></li>
				<li><?php echo anchor('', 'Dashboard'); ?></li>
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
					<?php if(false && $this->as_ion_auth->has_permission("View Access Log")): ?>
						<li class="<?php echo $class; ?>"><?php echo anchor('access_log/display', 'Access Log', 'class="teal_banner"'); ?></li>
					<?php endif; ?>
				<?php if($this->as_ion_auth->has_permission("View Assign w permission")): ?>
					<li><?php echo anchor('auth/service_grp_manage_herds', 'Manage Herd Access'); ?></li>
					<li><?php echo anchor('auth/service_grp_request', 'Request Herd Access'); ?></li>
				<?php endif; ?>
				<?php if($this->session->userdata('active_group_id') == 2): ?>
					<li><?php echo anchor('auth/manage_service_grp', 'Manage Consultant Access'); ?></li>
					<!-- <li><?php echo anchor('auth/service_grp_access', 'Grant Herd Access'); ?></li> -->
				<?php endif; ?>
				<?php if($this->as_ion_auth->has_permission("Select Herd") && isset($num_herds) && $num_herds > 1): ?>
					<li><?php echo anchor('dhi/change_herd/select', 'Change Herd'); ?></li>
				<?php endif; ?>
				<?php if($this->as_ion_auth->has_permission("Request Herd")): ?>
					<li><?php echo anchor('dhi/change_herd/request', 'Request Herd'); ?></li>
				<?php endif; ?>
			<?php elseif($this->router->fetch_method() != 'login'): ?>
				<li><?php echo anchor('auth/login', 'Log In');?></li>
				<li><?php echo anchor('auth/create_user', 'Register');?></li>
				<li><?php echo anchor('', 'Dashboard'); ?></li>
				<li><?php echo anchor('help', 'Help'); ?></li>
			<?php endif; ?>
		</ul>
	<div style="clear:both"></div>
	<?php
	if(!empty($page_heading)):
		echo heading($page_heading);
	endif;

	if(isset($error)):
		if (is_array($error) &&!empty($error)):
			foreach($error as $e) { ?>
				<div id="errors"><?php echo $e;?></div>
			<?php }
		else: ?>
			<div id="errors"><?php echo $error;?></div>
<?php 	endif;
	endif;

	if(isset($message)):
		if (is_array($message) && !empty($message)):
			foreach($message as $m) {?>
				<div id="info-message"><?php echo $m;?></div>
			<?php }
		elseif(!is_array($message)): ?>
			<div id="info-message"><?php echo $message;?></div>
<?php 	endif;
	elseif($this->session->flashdata('message') != ''): ?>
			<div id="info-message"><?php echo $this->session->flashdata('message');?></div>
<?php
	endif;
	
