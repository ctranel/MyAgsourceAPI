<?php if(isset($page_header) !== FALSE) echo $page_header; ?>
<div class='mainInfo'>
	<p>Please enter herd information below.  Required fields are denoted by an asterisk.</p>
	<?php if(!empty($error)): 
		if(is_array($error)): 
			foreach($error as $e):?>
				<div id="infoMessage"><?php echo $e;?></div><?php
	 		endforeach;
		endif;
	endif; ?>
	<p>Please enter the herd information below.  Shaded fields are required.</p>
	
	<?php echo form_open_multipart('gsg/file_upload/upload_form');?>
	<p><?php echo form_label('Herd Code', 'herd_code', NULL, $herd_code); ?><?php echo form_input($herd_code);?></p>
	<p><?php echo form_label('Test Date', 'test_date', NULL, $test_date); ?><?php echo form_input($test_date);?></p>
	<p><?php echo form_label('Herd Owner', 'herd_owner', NULL, $herd_owner); ?><?php echo form_input($herd_owner);?></p>
	<p><?php echo form_label('Farm Name', 'farm_name', NULL, $farm_name); ?><?php echo form_input($farm_name);?></p>
	<p><?php echo form_label('Data Source', 'data_source', NULL, $data_source); ?><?php echo form_dropdown('data_source', $data_source_options, $data_source_selected, $data_source)?></p>
	<p><?php echo form_label('Cow File', 'cow_file'); ?><input type="file" name="cow_file" id="cow_file" /></p>
	<p><?php echo form_label('Heifer File', 'heifer_file'); ?><input type="file" name="heifer_file" id="heifer_file" /></p>
	
	<input type="submit" value="upload" />
	
	<?php echo form_close();?>
</div>
<?php if(isset($page_footer) !== FALSE) echo $page_footer;