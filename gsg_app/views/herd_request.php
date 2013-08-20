<?php if(isset($page_header)) echo $page_header; ?>
<p>Please enter herd information below.  Required fields are denoted by an asterisk.</p>
	<?php echo form_open('change_herd/request', array('name'=>'request_herd', 'id'=>'request_herd')); ?>

	<p><?php echo form_label('Herd Code', 'herd_code', NULL, $herd_code); ?><?php echo form_input($herd_code); ?></p>
	<p><?php echo form_label('Herd Release Code', 'herd_release_code', NULL, $herd_release_code); ?><?php echo form_input($herd_release_code); ?></p>
<!-- 	<p><?php echo form_label('Select Report', 'report_path', NULL, $report_path); ?><?php echo form_dropdown('report_path', array('gsg/cow_report'=>'Cow', 'gsg/heifer_report'=>'Heifer')); ?></p>
 -->	<p><?php echo form_submit('herd_submit', 'View Herd'); ?></p>
 
	<?php echo form_close(); ?>
<?php
if(isset($page_footer)) echo $page_footer;