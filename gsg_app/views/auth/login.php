<?php if(isset($page_header) !== false) echo $page_header; ?>
<div class='mainInfo'>

	<?php if(isset($page_heading) !== false) echo heading($page_heading); ?>
    
	<p>Please login with your email address and password below.</p>
	
    <?php echo form_open("auth/login");?>
    	
      <p>
      	<label for="identity">Email:</label>
      	<?php echo form_input($identity);?>
      </p>
      
      <p>
      	<label for="password">Password:</label>
      	<?php echo form_input($password);?>
      </p>
      
      <p>
	      <label for="remember">Remember Me:</label>
	      <?php echo form_checkbox('remember', '1', FALSE, 'id="remember"');?>
	  </p>
      
      
      <p><?php echo form_submit('submit', 'Login');?></p>

      
    <?php echo form_close();?>

    <p>Forgot your password?  <?php echo anchor('auth/forgot_password', 'Click here'); ?></p>
    
    <p>Not currently enrolled?  <?php echo anchor('auth/create_user', 'Register now.'); ?></p>

</div>
<?php if(isset($page_footer) !== false) echo $page_footer;