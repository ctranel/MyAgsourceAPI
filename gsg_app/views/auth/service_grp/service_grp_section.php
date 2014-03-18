		<?php echo form_open($this->uri->uri_string(), $attributes); ?>
			<table>
				<tr>
					<th>&nbsp;</th>
					<th>Consultant</th>
					<th>Company</th>
					<th>Expiration</th>
					<th>&nbsp;</th>
				</tr>
				<?php foreach($arr_records as $r):
					echo $r;
				endforeach; ?>
			
			</table>
		<?php if(isset($arr_submit_options) && is_array($arr_submit_options)): ?>
			With selected:
			<?php foreach($arr_submit_options as $so): ?>
				&nbsp;&nbsp;&nbsp;<?php echo form_submit(array('type' => 'submit', 'name' => 'submit', 'value' => $so));?>
			<?php endforeach; ?>
		<?php endif; ?>
		
		<?php echo form_close(); ?>