<?php 
if(isset($page_header) !== false) echo $page_header;
if(isset($page_header)) echo $page_header; ?>
<div id="benchmarks" class="expand-group">
	<?php echo validation_errors(); ?>
	<div class="handle"><a id="set-benchmarks" class="handle">Set Benchmarks </a></div>
	<div class="expand">
		<?php echo form_open('benchmarks', array('name'=>'benchmark-form', 'id'=>'benchmark-form'));	
			echo form_fieldset('Select Breed', array('id' => 'breed-fieldset'));
				echo form_dropdown('breed', $arr_breed_options, $arr_breed_selected, 'id="breed"');
			echo form_fieldset_close();
			
			echo form_fieldset('Select Metric', array('id' => 'metric-fieldset'));
				echo form_dropdown('metric', $arr_metric_options, $arr_metrics_selected, 'id="metric"');
			echo form_fieldset_close();
			
			echo form_fieldset('Select Criteria', array('id' => 'criteria-fieldset'));
				echo form_dropdown('criteria', $arr_criteria_options, $arr_criteria_selected, 'id="criteria"');
			echo form_fieldset_close();
			
			echo form_fieldset('Enter Herd Size Range', array('id' => 'herd-size-fieldset')); ?>
				Between <?php echo form_input(array('name'=>'herd_size_dbfrom', 'value'=>$herd_size_dbfrom, 'size'=>'4', 'maxlength'=>'5', 'id'=>'herd_size_dbfrom')); ?>
				and <?php echo form_input(array('name'=>'herd_size_dbto', 'value'=>$herd_size_dbto, 'size'=>'4', 'maxlength'=>'5', 'id'=>'herd_size_dbto'));
			echo form_fieldset_close(); ?>
			<div class="submit"><?php echo form_submit('bench_submit', 'Set Benchmarks', 'class="button"'); ?>&nbsp;&nbsp;&nbsp;<?php echo form_submit('bench_submit', 'Save as Default', 'class="button"'); ?></div>
		<?php echo form_close();?>
	</div>
</div>
<?php
if(isset($page_footer)) echo $page_footer;