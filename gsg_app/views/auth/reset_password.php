<?php if(isset($page_header) !== false) echo $page_header; ?>
<?php if(isset($page_heading) !== false) echo heading($page_heading); ?>

<?php echo form_open('auth/reset_password/' . $code);?>
      <?php if(isset($old_password)): ?>
      <p>Old Password:<br />
      <?php echo form_input($old_password);?>
      </p>
      <?php  endif; ?>
      <p>New Password:<br />
      <?php echo form_input($new_password);?>
      </p>
      
      <p>Confirm New Password:<br />
      <?php echo form_input($new_password_confirm);?>
      </p>
      
      <?php echo form_input($user_id);?>
      <?php echo form_hidden($csrf); ?>
      <p><?php echo form_submit('submit', 'Change');?></p>
      
<?php echo form_close();?>
<?php if(isset($page_footer) !== false) echo $page_footer;