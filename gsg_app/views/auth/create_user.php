<?php if(isset($page_header) !== FALSE) echo $page_header; ?>
<div class='mainInfo'>
	<?php if(isset($page_heading) !== FALSE) echo heading($page_heading); ?>
	<p>Please enter your user account information below.  Required fields are denoted by an asterisk.</p>
	
    <?php echo form_open("auth/create_user");?>
      <p><?php echo form_label('First Name', 'first_name', NULL, $first_name) ?>
      <?php echo form_input($first_name);?>
      </p>
      
      <p><?php echo form_label('Last Name', 'last_name', NULL, $last_name) ?>
      <?php echo form_input($last_name);?>
      </p>
      
     <?php if(isset($group_id)): ?>
     	<p><?php echo form_label('User Group', 'group_id', NULL, $group_id) ?>
      	<?php echo form_dropdown('group_id[]', $group_options, $group_selected, $group_id)?>
      	</p>
     <?php endif;
     if(isset($assoc_acct_num) || isset($supervisor_acct_num)): ?>
     	<div id="association">
	      	<?php
	      	if(isset($assoc_acct_num)): 
	      		if(isset($assoc_acct_options)): ?>
	      	<p id="region"><?php
	      			echo form_label('Association/Region', 'assoc_acct_num', NULL, $assoc_acct_num);
      				echo form_dropdown('assoc_acct_num[]', $assoc_acct_options, $assoc_acct_selected, $assoc_acct_num);
	      	?></p>
	      	<?php
	      		else:
	      			echo form_input($assoc_acct_num);
	      		endif;
	      	endif;
	      	 
	      	if(isset($supervisor_acct_num)):?>
		      	<p id="tech"><?php
	     			echo form_label('Link to DHI Supervisor', 'supervisor_acct_num', NULL, $supervisor_acct_num);
	     			echo form_dropdown('supervisor_acct_num', $supervisor_acct_num_options, $supervisor_acct_num_selected, $supervisor_acct_num);
			    ?></p>
			<?php endif; ?>
	    </div>
     <?php endif; ?>
      

		<div id="herd">
	      <p><?php echo form_label('Herd Code', 'herd_code', NULL, $herd_code) ?>
	      <?php echo form_input($herd_code);?>
	      </p>
	      
	      <p><?php echo form_label('Herd Release Code', 'herd_release_code', NULL, $herd_release_code) ?>
	      <?php echo form_input($herd_release_code);?>
	      <?php echo anchor($base_url_assets . 'img/find_release_code.png', 'Find release code', 'class="image-link"'); ?>
	      </p>
	    </div>
      
	  <div id="sg">
	      <p><?php echo form_label('Service Group Acct Num', 'sg_acct_num', NULL, $sg_acct_num) ?>
	      <?php echo form_input($sg_acct_num);?>
	      Account numbers are 8 character long and start with &quot;SG&quot;.  If you don't have a Service Group account, please call <?php echo $cs_phone; ?>.
	      </p>
      </div>
	      
	  <p><?php echo form_label('Phone', 'phone1', NULL, $phone1) ?>
      <?php echo form_input($phone1);?>-<?php echo form_input($phone2);?>-<?php echo form_input($phone3);?>
      </p>
      
      <p><?php echo form_label('Best Time to Call', 'best_time', NULL, $best_time) ?>
      <?php echo form_input($best_time);?>
      </p>
      
      <p><?php echo form_label('Email', 'email', NULL, $email) ?>
      <?php echo form_input($email);?>
      </p>
      
      <p><?php echo form_label('Password (at least 8 characters)', 'password', NULL, $password) ?>
      <?php echo form_input($password);?>
      </p>
      
      <p><?php echo form_label('Confirm Password', 'password_confirm', NULL, $password_confirm) ?><?php echo form_input($password_confirm);?>
      </p>
      
      <?php if($this->as_ion_auth->has_permission("Assign Sections")): // this is currently tracked in the SQL database only
		if(!empty($section_options)):
			echo form_fieldset('Sections', array('id' => 'sections-fieldset'));
			foreach($section_options as $k=>$v):
				if(!empty($k)): ?>
						<span class="form-checkbox"><?php echo form_checkbox('section_id[]', $k, in_array($k, $sections_selected) !== false, 'class = "section-checkbox"'); echo $v; ?></span>
				<?php endif;
			endforeach;
			echo form_fieldset_close();
		endif; ?>
      <?php endif; ?>
      <p><?php echo form_checkbox('terms', 'Y', set_checkbox('terms','Y'), 'class="required"')?> By checking this box, I confirm that I have read and understand the <?php echo anchor(site_url('auth/ajax_terms'), 'terms of use', 'title="terms of use" class="popup-ajax"')?> for the requested reports.</p>
      <p><?php echo form_submit('submit', 'Create User', 'class="button"');?></p>
   <?php echo form_close();?>

</div>
<?php if(isset($page_footer) !== false) echo $page_footer;