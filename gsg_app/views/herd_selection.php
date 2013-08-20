<?php 
if(isset($page_header)) echo $page_header;
if(isset($arr_herd_data) && is_array($arr_herd_data)):?>
	<h1>Select Herd</h1>	
	<?php echo form_open('change_herd/select', array('name'=>'select_herd', 'id'=>'select_herd')); ?>

	<p><?php echo form_label('Select Herd', 'herd_code'); ?><?php echo form_dropdown('herd_code', $arr_herd_data, NULL, 'id="herd_code"'); ?></p>
	<p>If you know the first numbers or an entire herd code, you can enter them in the box below to bring those herds up in the Select Herd box:</p>
	<p><?php echo form_label('Herd Code to Select', 'herd_code_fill'); ?><?php echo form_input($herd_code_fill);?></p>
	<p>&nbsp;</p>
	<!--  <p><?php echo form_label('Select Report', 'report_path'); ?><?php echo form_dropdown('report_path', array('gsg/cow_report'=>'Cow', 'gsg/heifer_report'=>'Heifer')); ?></p>
 -->	<p><?php echo form_submit('herd_submit', 'View Herd'); ?></p>
 
	<?php echo form_close(); ?>
<?php
else: ?>
	<p>No herds available.</p>
<?php
endif;
if(isset($page_footer)) echo $page_footer;