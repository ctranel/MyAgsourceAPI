<?php if(isset($page_header) !== false) echo $page_header; ?>
<?php $this->load->helper('html_helper'); ?>
<div id="filters" class="expand-group">
	<?php echo validation_errors(); ?>
	<?php if(isset($form_url) === false) $form_url = current_url(); ?>
	<div class="handle"><a id="set-filters">Set Filters </a></div>
	<div class="expand">
	<?php //echo anchor("#","Apply Filters"); ?>
	<?php echo form_open($form_url, array('name'=>'filter-form', 'id'=>'filter-form'));?>
	<?php if(is_array($arr_filters)):
		 foreach($arr_filters as $f):
//var_dump($f['type']);
		 	if(strpos($f['type'], 'select multiple') !== false):
				if(!empty($f['options']) && count($f['options']) > 1):
					echo form_fieldset($f['label'], array('id' => $f['field_name'] . '-fieldset'));
						foreach($f['options'] as $o): ?>
							<span class= "filter-item checkbox">
								<?php echo form_checkbox($f['field_name'] . '[]', $o['value'], in_array($o['value'], $f['arr_selected_values']) !== FALSE);
								echo $o['label']; ?>
							</span>
						<?php endforeach;
					echo form_fieldset_close();
				endif;
			elseif(strpos($f['type'], 'select') !== false):
				if(!empty($f['options']) && count($f['options']) > 1):
					echo form_fieldset($f['label'], array('id' => $f['field_name'] . '-fieldset'));
						foreach($f['options'] as $o): ?>
							<span class= "filter-item checkbox">
								<?php echo form_radio($f['field_name'], $o['value'], in_array($o['value'], $f['arr_selected_values']) !== FALSE);
								echo $o['label']; ?>
							</span>
						<?php endforeach;
					echo form_fieldset_close();
				endif;
			elseif(strpos($f['type'], 'range') !== false):
				$input_array_from = array('name'=>$f['field_name'] . "[]", 'size'=>'5','maxlength'=>'5');
				$input_array_to = array('name'=>$f['field_name'] . "[]", 'size'=>'5', 'maxlength'=>'5');
				if(strpos($f['type'], 'date')):
					$input_array_from['id'] = 'datepickfrom';
					$input_array_to['id'] = 'datepickto';
				endif;
				if(isset($f['arr_selected_values']['dbfrom'])):
					$input_array_from['value'] = $f['arr_selected_values']['dbfrom'];
				endif;
				if(isset($f['arr_selected_values']['dbto'])):
					$input_array_to['value'] = $f['arr_selected_values']['dbto'];
				endif;
				echo form_fieldset($f['label'], array('id' => $f['field_name'] . '-fieldset')); ?>
					Between <?php echo form_input($input_array_from) ?> and <?php echo form_input($input_array_to);
					echo form_fieldset_close();
			endif;	
		endforeach;
	endif; ?>
	<div class="submit"><?php echo form_submit('filter_submit', 'Apply Filter', 'class="button"');?>&nbsp;&nbsp;&nbsp;<?php echo form_button('reset_filter', 'Reset Filter', 'onclick="form_reset()" class="button"')?>
	</div>
	<?php echo form_close();?>
	</div>
</div>

<?php if(isset($page_footer) !== false) echo $page_footer;
