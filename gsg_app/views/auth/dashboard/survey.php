<?php $this->load->helper('form_helper'); ?>
<div class="widget-content">
	<div id="survey">
		<?php echo form_open('', array('name'=>'survey', 'id'=>'survey')); ?>
		<p>Have you utilized the Genetic Selection Guide in your herd management?</p>
		<p> <?php
			echo form_radio(array('name'=>'gsg_use'), 'Yes') . 'Y&nbsp;&nbsp;';
			echo form_radio(array('name'=>'gsg_use'), 'No') . 'N'; ?>
		</p>
		<p>On a scale of 1-5, how valuable has the Genetic Selection Guide been to your herd management?</p>
		<p class="likert">not valuable &nbsp;&nbsp;<?php 
			echo form_radio(array('name'=>'gsg_value'), '1') . '&nbsp;&nbsp;';
			echo form_radio(array('name'=>'gsg_value'), '2') . '&nbsp;&nbsp;';
			echo form_radio(array('name'=>'gsg_value'), '3') . '&nbsp;&nbsp;';
			echo form_radio(array('name'=>'gsg_value'), '4') . '&nbsp;&nbsp;';
			echo form_radio(array('name'=>'gsg_value'), '5') . '&nbsp;&nbsp;';
		?> very valuable
		</p>
		<?php echo form_close(); ?>
		<?php if(isset($inner_html)) echo $inner_html; ?>
	</div>
</div>
