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
      
      <p><?php echo form_label('Email', 'email', NULL, $email); ?>
      <?php echo form_input($email); ?>
      </p>
      <?php if($this->as_ion_auth->is_admin || $this->as_ion_auth->is_manager): ?>
      	<p><?php echo form_label('User Group', 'group_id', NULL, $group_id); ?>
      	<?php echo form_dropdown('group_id[]', $group_options, $group_selected, $group_id); ?>
      	</p>
	  <?php else: ?>
       	<?php echo form_input($group_id); ?>
      <?php endif; ?>

     <?php if($this->as_ion_auth->is_admin || $this->as_ion_auth->is_manager): ?>
     	<div id="association">
	      	<p id="region"><?php //if($this->as_ion_auth->is_admin && (in_array('3', $group_selected) || in_array('5', $group_selected) || in_array('6', $group_selected) || in_array('7', $group_selected) || in_array('8', $group_selected))): ?>
	      		<?php echo form_label('Association/Region', 'region_id', NULL, $region_id) ?>
      			<?php echo form_dropdown('region_id', $region_options, $region_selected, $region_id)?>
	      	<?php //elseif(in_array('3', $group_selected) || in_array('5', $group_selected) || in_array('6', $group_selected) || in_array('7', $group_selected) || in_array('8', $group_selected)): ?>
	      		<?php //echo form_input($region_id);?>
	      	<?php //endif; ?></p>
	      	<p id="tech">
		      	<?php //if (($this->as_ion_auth->is_admin || $this->as_ion_auth->is_manager) && (in_array('5', $group_selected) || in_array('7', $group_selected) || in_array('8', $group_selected))): ?>
		     		<?php if(($this->as_ion_auth->is_admin || $this->as_ion_auth->is_manager) && !empty($supervisor_num_options)): ?>
			      		<?php echo form_label('DHI Supervisor', 'supervisor_num', NULL, $supervisor_num); ?>
		      			<?php echo form_dropdown('supervisor_num', $supervisor_num_options, $supervisor_num_selected, $supervisor_num); ?>
			      	<?php else: //if($this->as_ion_auth->is_admin || $this->as_ion_auth->is_manager): ?>
			      		<?php echo form_label('DHI Supervisor', 'supervisor_num', NULL, $supervisor_num); ?>
			      		<?php echo form_input($supervisor_num);?>
			      	<?php //elseif(in_array('5', $group_selected) || in_array('7', $group_selected) || in_array('8', $group_selected)): ?>
			      		<?php //echo form_input($supervisor_num);?>
			      	<?php endif; ?>
		      	<?php //elseif(in_array('5', $group_selected) || in_array('7', $group_selected) || in_array('8', $group_selected)): ?>
		      		<?php //echo form_input($supervisor_num);?>
				<?php //endif; ?>
			</p>
	    </div>
 		<div id="herd">
	      <p><?php echo form_label('Herd Code', 'herd_code', NULL, $herd_code); ?>
	      <?php echo form_input($herd_code);?>
	      </p>
	      
	      <p><?php echo form_label('Herd Release Code (for validation purposes only)', 'herd_release_code', NULL, $herd_release_code); ?>
	      <?php echo form_input($herd_release_code);?>
	      </p>
	    </div>
      
     <?php endif; ?>


      <p><?php echo form_label('Phone', 'phone1', NULL, $phone1); ?>
      <?php echo form_input($phone1);?>-<?php echo form_input($phone2);?>-<?php echo form_input($phone3);?>
      </p>
      <p>Leave the password fields blank to keep your current password.</p>
      <p><?php echo form_label('Password (at least 8 characters)', 'password', NULL, $password); ?>
      <?php echo form_input($password); ?>
      </p>
      
      <p><?php echo form_label('Confirm Password', 'password_confirm', NULL, $password_confirm) ?><?php echo form_input($password_confirm);?>
      </p>
      
      <?php if($this->as_ion_auth->has_permission("Assign Sections")): // this is currently tracked in the SQL database
			if(!empty($section_options)):
				echo form_fieldset('Sections', array('id' => 'sections-fieldset'));
				foreach($section_options as $k=>$v):
					if(!empty($k)): ?>
							<span class="form-checkbox"><?php echo form_checkbox('section_id[]', $k, in_array($k, $sections_selected) !== false, 'class = "section-checkbox"'); echo $v; ?></span>
					<?php endif;
				endforeach;
				echo form_fieldset_close();
			endif; ?>

	      <!-- <p><?php echo form_checkbox('terms', 'Y', set_checkbox('terms','Y'))?> By checking this box, I am confirming that I understand that the account associated with the herd number entered above will be billed according to (<?php echo anchor('gsg/animal_report/billing', $this->config->item("cust_serv_company","ion_auth") . ' billing procedures')?>) for the requested reports.</p>
	      <p>
	      	<input type=checkbox name="reset_password"> <?php echo form_label('Reset Password', 'reset_password') ?>
	      </p> -->
       <?php endif; ?>
      
      <?php echo form_input($user_id);?>
      <p><?php echo form_submit('submit', 'Submit');?></p>

      
    <?php echo form_close();?>

</div>
<?php if(isset($page_footer) !== FALSE) echo $page_footer;