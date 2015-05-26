<?php if(isset($page_header) !== false) echo $page_header; ?>
<div class='mainInfo'>
	<?php if(isset($page_heading) !== false) echo heading($page_heading); ?>
	
	<!--  <div id="infoMessage">MyAgsource will be down for maintenance from 1:00 to 3:00am on May 24, 2015.  We apologize for any inconvenience.</div> -->
	<div class="row">
		<div class="col-sm-6">
			<div class="box login">
				<h2>DHI</h2>
			    <h3>Dairy, Milk and Animal Health Diagnostics</h3>
			    <p class="emph">DHI Members, register for a FREE 90 day trial of <?php echo $this->config->item('product_name'); ?>, <?php echo anchor('auth/create_user', 'click here'); ?>!</p>
			    <?php echo form_open("auth/login");?>
			      <p>
			      	<label for="identity">Email</label>
			      	<?php echo form_input($identity);?>
			      </p>
			      <p>
			      	<label for="password">Password</label>
			      	<?php echo form_input($password);?>
			      </p>
			      <p>
				      <label for="remember">Remember Me</label>
				      <?php echo form_checkbox('remember', '1', FALSE, 'id="remember"');?>
				  </p>
			      <p><?php echo form_submit('submit', 'Log In', 'class="button"');?></p>
			    <?php echo form_close();?>
			
			    <p>Forgot your password?  <?php echo anchor('auth/forgot_password', 'Click here'); ?></p>
			    <p>Not currently enrolled?  <?php echo anchor('auth/create_user', 'Register now.'); ?></p>
			    <p>Want to see more?  <?php echo anchor('dhi/demo', 'View Demo Herd.'); ?></p>

		        <p>To learn more about <?php echo $this->config->item('product_name'); ?> or other DHI services, please call <?php echo $this->config->item('cust_serv_phone'); ?>, email <a href="mailto:<?php echo $this->config->item('cust_serv_email'); ?>"><?php echo $this->config->item('cust_serv_email'); ?></a>, or visit <a href="http://www.agsource.com">www.agsource.com</a>. </p>
        	</div>
		</div>
		<div class="col-sm-6">
			<div class="box login">
				<h2>Laboratories</h2>
				<h3>Soil, Water, Plant Tissue, Manure and Media</h3>
				<form action="https://mylabresults.agsource.com/User/Login?loginModel=AgSource.NET.ViewModels.Shared.LoginModel" class="home" method="post">
				    <p>
				    	<label for="txtEmail">Email</label>
				        <input id="UserName" name="UserName" type="text" value="" />
				    </p>
				    <p>
				    	<label for="txtLoginPass">Password</label>
				        <input id="Password" name="Password" type="password" />
				    </p>
				    <p>
				        <label for="rememberMe" style="width: 100px;">Remember Me</label>
				        <input data-val="true" data-val-required="The RememberMe field is required." id="RememberMe" name="RememberMe" type="checkbox" value="true" />
				        <input name="RememberMe" type="hidden" value="false" />
				    </p>
				    <p>
				        <input type="submit" id="login" value="Log In" class="button" />
				    </p>
				</form>
			    <p>Forgot your password?  <?php echo anchor('http://mylabresults.agsource.com/User/ForgotPassword', 'Click here'); ?></p>
			    <p>Not currently enrolled?  <?php echo anchor('http://mylabresults.agsource.com/User/AddUserRequest', 'Register now.'); ?></p>
			    
			    <p>Having trouble?  Please call AgSource Laboratories:</p>
			    <ul>
				    <li>Bonduel, Wis: 715-758-2178</li>
				    <li>Ellsworth, Iowa: 515-836-4444</li>
				    <li>Lincoln, Neb: 402-476-0300</li>
				    <li>Umatilla, Ore: 541-922-4894</li>
			    </ul>
			    
          		<p>To learn how our services can help improve your production email <a href="mailto:labinfo@agsource.com">labinfo@agsource.com</a> or visit <a href="http://www.agsource.com/laboratories">www.agsource.com\laboratories</a>.</p>
			</div>
		</div>
	</div>
</div>

<?php if(isset($page_footer) !== false) echo $page_footer;