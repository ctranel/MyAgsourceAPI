<?php if(isset($page_header) !== false) echo $page_header; ?>
<?php if(isset($page_heading) !== false) echo heading($page_heading);
//error messages are not generated until after the page header, so we need to include the error messages here
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
?>
<p>Please enter your email address so we can send you an email to reset your password.</p>

<?php echo form_open("auth/forgot_password");?>

      <p>Email Address:<br />
      <?php echo form_input($email);?>
      </p>
      
      <p><?php echo form_submit('submit', 'Submit', 'class="button"');?></p>
      
<?php echo form_close();?>
<?php if(isset($page_footer) !== false) echo $page_footer;