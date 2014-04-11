<?php if(isset($page_header)) echo $page_header;
if(isset($page_heading)) echo heading($page_heading);
if(isset($association_options) && is_array($association_options)):
	if ($this->as_ion_auth): ?>
		<p><?php echo anchor('region/create_region', 'Add New Region/Association'); ?></p>
	<?php endif; ?>
	<h2>Edit Region/Association</h2>
	<?php echo form_open('region', array('name'=>'select_region', 'id'=>'select_region')); ?>

	<p><?php echo form_label('Select Region/Association to Edit', 'association_num'); ?><?php echo form_dropdown('association_num', $association_options, $association_selected, $association_num)?></p>
	<p><?php echo form_submit('region_submit', 'Edit Region', 'class="button"'); ?></p>
 
	<?php echo form_close(); ?>
<?php
else: ?>
	<p>No regions/associations available.</p>
<?php
endif;
if(isset($tech_form)): ?>
	<hr>
	<?php echo $tech_form;
endif;

if(isset($page_footer)) echo $page_footer;