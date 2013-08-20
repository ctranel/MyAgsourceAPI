<?php if(isset($page_header) !== false) echo $page_header; ?>
<?php if(isset($page_heading) !== false) echo heading($page_heading); ?>
<p>Please enter your email address so we can send you an email to reset your password.</p>

<?php echo form_open("auth/forgot_password");?>

      <p>Email Address:<br />
      <?php echo form_input($email);?>
      </p>
      
      <p><?php echo form_submit('submit', 'Submit');?></p>
      
<?php echo form_close();?>
<?php if(isset($page_footer) !== false) echo $page_footer;