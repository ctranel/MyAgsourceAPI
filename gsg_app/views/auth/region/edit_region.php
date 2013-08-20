<?php if(isset($page_header) !== FALSE) echo $page_header; ?>
<div class='mainInfo'>
	<?php if(isset($page_heading) !== FALSE) echo heading($page_heading); ?>
	<p>Please enter your field tech information below.  Required fields are denoted by an asterisk.</p>
	
    <?php echo form_open("region/edit_region/");?>
     <div id="association">
      	<?php if($this->as_ion_auth->is_admin): ?>
      		<p id="region"><?php echo form_label('Association/Region Number', 'region_id', NULL, $region_id) ?>
      		<?php echo form_dropdown('region_id', $region_options, $region_selected, $region_id)?>
      		</p>
      	<?php elseif($this->as_ion_auth->is_manager): ?>
      		<?php echo form_input($region_id);?>
      	<?php endif; ?>
    </div>
 
      <p><?php echo form_label('Region/Association Name', 'region_name', NULL, $region_name) ?>
      <?php echo form_input($region_name);?>
      </p>
      
      <p><?php echo form_label('Manager First Name', 'manager_first_name', NULL, $manager_first_name) ?>
      <?php echo form_input($manager_first_name);?>
      </p>
      
      <p><?php echo form_label('Last Name', 'manager_last_name', NULL, $manager_last_name) ?>
      <?php echo form_input($manager_last_name);?>
      </p>
      
      <p><?php echo form_label('Email', 'email', NULL, $email) ?>
      <?php echo form_input($email);?>
      </p>

      <p><?php echo form_label('Phone', 'phone1', NULL, $phone1) ?>
      <?php echo form_input($phone1);?>-<?php echo form_input($phone2);?>-<?php echo form_input($phone3);?>
      </p>

      <p><?php echo form_submit('submit', 'Submit');?></p>

      
    <?php echo form_close();?>

</div>
<?php if(isset($page_footer) !== FALSE) echo $page_footer;