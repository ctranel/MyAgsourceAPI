<?php echo validation_errors(); ?>
<?php echo form_open(site_url('dhi/settings/general/ajax_set'), array('name'=>'setting-form', 'id'=>'setting-form', 'class'=>'ajax-form'));	
	echo form_fieldset('Display Animals By:', ['id' => 'cow-id-fieldset', 'class' => $cow_id_field['class']]);
		echo form_dropdown('cow_id_field', $cow_id_field['options'], $cow_id_field['selected'], 'id="cow-id-field"');
	echo form_fieldset_close();
?>
	<input type="hidden" name="make_default" value="0" id="make_default">
	<div class="submit"><?php echo form_submit('dhi_submit', 'Save for Session', 'class="button" id="session"'); ?>&nbsp;&nbsp;&nbsp;<?php echo form_submit('dhi_submit', 'Save as Default', 'class="button" id="default"'); ?></div>
<?php echo form_close();?>

<script type="text/javascript">
<!--
window.onload = function(){
	if($('#setting-form')){ //if there is a filter form (only on pages with one table)
		$('#default').click(function(ev){
			$('#make_default').val('1');
		});
		
		$('#set').click(function(ev){
			$('#make_default').val('0');
		});
	}
}
//-->
</script>
