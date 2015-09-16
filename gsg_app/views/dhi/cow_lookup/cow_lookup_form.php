<?php 
if(isset($cow_options) && is_array($cow_options)):?>
	<?php echo form_open('dhi/cow_lookup', array('name'=>'select_cow', 'id'=>'select_cow')); ?>

	<?php //echo form_label('ID Field to Use', 'id_field'); ?><?php //echo form_radio($id_field);?>
	<?php echo form_label('Type Cow ID', 'cow_fill'); ?><?php echo form_input($cow_fill);?>
	<?php echo form_label('Select Cow', 'cow_ref'); ?><?php echo form_dropdown('cow_ref', $cow_options, $cow_selected, $cow_ref); ?>
	<?php echo form_submit('cow_submit', 'View Cow', 'class="button"'); ?>
 
	<?php echo form_close(); ?>
<?php
else: ?>
	<p>No cows available.</p>
<?php
endif;
