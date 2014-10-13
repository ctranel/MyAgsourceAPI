<?php 
if(isset($page_header) !== false) echo $page_header;
if(isset($page_header)) echo $page_header; ?>
<div id="benchmarks" class="expand-group">
	<?php echo validation_errors(); ?>
	<div class="handle"><a id="set-benchmarks">Set Benchmarks </a></div>
	<div class="expand">
		<?php echo form_open('benchmarks/ajax_set', array('name'=>'benchmark-form', 'id'=>'benchmark-form'));	
			echo form_fieldset('Select Breed', array('id' => 'breed-fieldset', 'class' => $breed['class']));
				echo form_dropdown('breed', $breed['options'], $breed['selected'], 'id="breed"');
			echo form_fieldset_close();
			
			echo form_fieldset('Select Metric', array('id' => 'metric-fieldset', 'class' => $metric['class']));
				echo form_dropdown('metric', $metric['options'], $metric['selected'], 'id="metric"');
			echo form_fieldset_close();
			
			echo form_fieldset('Select Criteria', array('id' => 'criteria-fieldset', 'class' => $criteria['class']));
				echo form_dropdown('criteria', $criteria['options'], $criteria['selected'], 'id="criteria"');
			echo form_fieldset_close();
			
			echo form_fieldset('Enter Herd Size Range', array('id' => 'herd-size-fieldset', 'class' => $herd_size['class'])); ?>
				Between <?php echo form_input(array('name'=>"herd_size['dbfrom']", 'value'=>$herd_size['dbfrom'], 'size'=>'4', 'maxlength'=>'5', 'id'=>'herd_size_dbfrom')); ?>
				and <?php echo form_input(array('name'=>"herd_size['dbto']", 'value'=>$herd_size['dbto'], 'size'=>'4', 'maxlength'=>'5', 'id'=>'herd_size_dbto'));
			echo form_fieldset_close(); ?>
			<input type="hidden" name="make_default" value="0" id="make_default">
			<div class="submit"><?php echo form_submit('bench_submit', 'Set Benchmarks', 'class="button" id="set"'); ?>&nbsp;&nbsp;&nbsp;<?php echo form_submit('bench_submit', 'Save as Default', 'class="button" id="default"'); ?></div>
		<?php echo form_close();?>
	</div>
</div>
<?php
if(isset($page_footer)) echo $page_footer;