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

	echo form_open("auth/change_password");?>

      <p>Old Password (at least 8 characters):<br />
      <?php echo form_input($old_password);?>
      </p>
      
      <p>New Password (at least <?php echo $min_password_length;?> characters long):<br />
      <?php echo form_input($new_password);?>
      </p>
      
      <p>Confirm New Password:<br />
      <?php echo form_input($new_password_confirm);?>
      </p>
      
      <?php echo form_input($user_id);?>
      <p><?php echo form_submit('submit', 'Change', 'class="button"');?></p>
      
<?php echo form_close();?>
<?php if(isset($page_footer) !== false) echo $page_footer;