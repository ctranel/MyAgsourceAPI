<?php if(isset($page_header) !== FALSE) echo $page_header; ?>
<div class='mainInfo'>
	<?php if(isset($page_heading) !== FALSE) echo heading($page_heading); ?>
	<p>Please enter your user account information below.  Required fields are denoted by an asterisk.</p>
	
    <?php echo form_open("auth/edit_user/".$this->uri->segment(3)); ?>
      <p><?php echo form_label('First Name', 'first_name', NULL, $first_name); ?>
      <?php echo form_input($first_name); ?>
      </p>
      
      <p><?php echo form_label('Last Name', 'last_name', NULL, $last_name); ?>
      <?php echo form_input($last_name);?>
      </p>
      
     <?php if(isset($group_id)): ?>
     	<p><?php echo form_label('User Group', 'group_id', NULL, $group_id) ?>
      	<?php echo form_dropdown('group_id[]', $group_options, $group_selected, $group_id)?>
      	</p>
     <?php endif;
     if(isset($assoc_acct_num) || isset($supervisor_acct_num)): ?>
     	<div id="association">
	      	<?php if(isset($assoc_acct_num)): ?>
	      	<p id="region"><?php
	      			echo form_label('Association/Region', 'assoc_acct_num', NULL, $assoc_acct_num);
      				echo form_dropdown('assoc_acct_num[]', $assoc_acct_options, $assoc_acct_selected, $assoc_acct_num);
	      	?></p>
	      	<?php endif; 
	      	if(isset($supervisor_acct_num)):?>
		      	<p id="tech"><?php 
	     			echo form_label('Link to DHI Supervisor', 'supervisor_acct_num', NULL, $supervisor_acct_num);
	     			echo form_dropdown('supervisor_acct_num', $supervisor_acct_num_options, $supervisor_acct_num_selected, $supervisor_acct_num);
			    ?></p>
			<?php endif; ?>
	    </div><?php
	    if(isset($herd_code)): ?>
	 		<div id="herd">
		      <p><?php echo form_label('Herd Code', 'herd_code', NULL, $herd_code); ?>
		      <?php echo form_input($herd_code); ?>
		      </p>
		    </div>
<?php
		endif;
	endif; ?>



      <p><?php echo form_label('Phone', 'phone1', NULL, $phone1); ?>
      <?php echo form_input($phone1);?>-<?php echo form_input($phone2);?>-<?php echo form_input($phone3);?>
      </p>

      <p><?php echo form_label('Best Time to Call', 'best_time', NULL, $best_time) ?>
      <?php echo form_input($best_time);?>
      </p>
      
      <p><?php echo form_label('Email', 'email', NULL, $email); ?>
      <?php echo form_input($email); ?>
      </p>
      
      <p>Leave the password fields blank to keep your current password.</p>
      <p><?php echo form_label('Password (at least 8 characters)', 'password', NULL, $password); ?>
      <?php echo form_input($password); ?>
      </p>
      
      <p><?php echo form_label('Confirm Password', 'password_confirm', NULL, $password_confirm) ?><?php echo form_input($password_confirm);?>
      </p>
      
      <?php if($this->permissions->hasPermission("Assign Sections")): // this is currently tracked in the SQL database
			if(!empty($section_options)):
				echo form_fieldset('Sections', array('id' => 'sections-fieldset'));
				foreach($section_options as $k=>$v):
					if(!empty($k)): ?>
							<span class="form-checkbox"><?php echo form_checkbox('section_id[]', $k, in_array($k, $sections_selected) !== false, 'class = "section-checkbox"'); echo $v; ?></span>
					<?php endif;
				endforeach;
				echo form_fieldset_close();
			endif; ?>

	      <!-- <p><?php echo form_checkbox('terms', 'Y', set_checkbox('terms','Y'))?> By checking this box, I am confirming that I understand that the account associated with the herd number entered above will be billed according to (<?php echo anchor('gsg/animal_report/billing', $this->config->item('cust_serv_company') . ' billing procedures')?>) for the requested reports.</p>
	      <p>
	      	<input type=checkbox name="reset_password"> <?php echo form_label('Reset Password', 'reset_password') ?>
	      </p> -->
       <?php endif; ?>
      
      <?php echo form_input($user_id);?>
      <p><?php echo form_submit('submit', 'Submit', 'class="button"');?></p>

      
    <?php echo form_close(); ?>

</div>
<?php if(isset($page_footer) !== FALSE) echo $page_footer;