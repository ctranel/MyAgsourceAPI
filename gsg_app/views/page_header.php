<!doctype html>
<html lang="en">
<head profile="http://www.w3.org/2005/10/profile">
	<title><?php if(isset($title)) echo $title; ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">    
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="cache-control" content="no-cache">
    <meta http-equiv="content-type" content="text/html;charset=UTF-8">
<!--     <meta name="robots" content="NO FOLLOW,NO INDEX">
    <meta name="googlebot" content="NOARCHIVE"> -->
    <meta name="description" content="<?php if(isset($description)) echo $description; ?>">
    <meta name="keywords" content="<?php echo $this->config->item('product_name'); ?> - <?php echo $this->config->item('cust_serv_company'); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
 	<link rel="stylesheet" href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css'>
	<link rel="icon" type="image/png" href="https://myagsource.com/favicon.ico">
<?php
	    $this->carabiner->css('corporate.css', 'screen');
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
			{navhelper: "<?php echo $this->config->item('base_url_assets'); ?>js/nav.js"}
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
<div class="navbar-background"></div>
<div class="container" id="container">
	<nav class="navbar navbar-inverse" id="top-nav" role="navigation">
		<ul class="nav navbar-nav">
		<?php
			//@todo MOVE THIS BLOCK TO THE CONTROLLERS OR MAYBE A LIBRARY?
			if(isset($top_sections) && is_a($top_sections, 'SplObjectStorage')):
				$first = TRUE;
				foreach($top_sections as $a):
					$class_name = $first?'first':'';
					$first = FALSE;
					$path = $this->router->fetch_directory();// . $this->router->fetch_class();
					if(substr($a->path(), 0, strrpos( $a->path(), '/')) . '/' === $path):
						$class_name .= ' current'; 
					endif;
					$href = $url . $a->path(); ?>
					<li<?php if(!empty($class_name)) echo ' class="' . $class_name . '"'; ?>><?php echo anchor($href, $a->name());?></li>
				<?php endforeach;
			endif; ?>

			<?php
			// AGSOURCE DM
			if(false): //$credentials = $this->dm_model->get_credentials()): //AgSourceDM is not fully integrated so we need to use a manual process
				$class_name = $first?'first':'';
				$first = FALSE; ?>
			 	<form action="http://newdata.crinet.com/agsourcedm/" method="post" name="agsourcedm" id="agsourcedm" style="display:none;" target="_blank">
				  <input type="hidden" name="UserID" value="<?php echo $credentials['UserID']; ?>">
				  <input type="hidden" name="Password" value="<?php echo $credentials['Password']; ?>">
				  <!-- <input type="submit" value="LOG IN"> -->
				</form>
				<li<?php if(!empty($class_name)) echo ' class="' . $class_name . '"'; ?>><?php echo anchor('', 'AgSource DM', 'id="dm-anchor"'); ?></li>
			<?php endif;
			//END AGSOURCE DM ?>
		</ul>
	</nav>
	<div id="header">
		<ul id="session-nav">
			<!-- <li><?php echo anchor('http://agsource.crinet.com', 'AgSource Site'); ?></li> -->
			<?php if(($this->as_ion_auth->logged_in())): ?>
				<li><?php echo anchor('auth/logout', 'Log Out'); ?></li>
				<li><?php echo anchor('', 'Home/Account'); ?></li>
				<li><?php echo anchor('help', 'Help'); ?></li>
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
				<li><?php echo anchor('', 'Home/Account'); ?></li>
				<li><?php echo anchor('help', 'Help'); ?></li>
			<?php endif; ?>
		</ul>
		<?php echo anchor('', '<div class="header-link">&nbsp;</div>'); ?>
	</div> <!-- header -->
	<!--top navigation-->
		<ul class="primary-nav navbar dropdown">
			<?php if(isset($section_nav) && !empty($section_nav)):
				echo $section_nav;
			endif;
			if(($this->as_ion_auth->logged_in())):
				if(false && isset($user_sections) && is_a($user_sections, 'SplObjectStorage') && $user_sections->count() > 0): ?>
					<li class="sectionnav"><a class="dropdown-toggle" data-toggle="dropdown" name="section-nav">Select Report</a><br />
						<ul class="dropdown-menu" role="menu">
							<?php
							foreach($user_sections as $a): ?>
								<li role="presentation"><?php echo anchor($url . $a->path(), $a->name());?></li>
							<?php endforeach; ?>
						</ul>
					</li> <!-- close "Select Section" li -->
				<?php endif;
				$arr_groups = $this->session->userdata('arr_groups');
				if(isset($arr_groups) && is_array($arr_groups) && count($arr_groups) > 1): ?>
					<li class="groupnav"><a class="dropdown-toggle" data-toggle="dropdown"><?php echo $arr_groups[$this->session->userdata('active_group_id')]; ?></a><br />
						<ul class="dropdown-menu" role="menu">
							<?php foreach($arr_groups as $k=>$v): ?>
								<li role="presentation"><?php echo anchor($url . 'auth/set_role/'. $k, $v);?></li>
							<?php endforeach; ?>
						</ul>
					</li> <!-- close "Select Section" li -->
				<?php endif; ?>
			<?php endif; ?>
		</ul>
	<!--left side of page-->
	<div id="main-content">
	<?php if (!empty($page_heading)) echo heading($page_heading);

	if(isset($error)):
		if (is_array($error) &&!empty($error)):
			foreach($error as $e) {?>
				<div id="errors"><?php echo $e;?></div>
			<?php }
		else: ?>
			<div id="errors"><?php echo $error;?></div>
<?php 	endif;
	endif;
	if(isset($message)):
		if (is_array($message) && !empty($message)):
			foreach($message as $m) {?>
				<div id="infoMessage"><?php echo $m;?></div>
			<?php }
		elseif(!is_array($message)): ?>
			<div id="infoMessage"><?php echo $message;?></div>
<?php 	endif;
	elseif($this->session->flashdata('message') != ''): ?>
			<div id="infoMessage"><?php echo $this->session->flashdata('message');?></div>
<?php
	endif;
	
