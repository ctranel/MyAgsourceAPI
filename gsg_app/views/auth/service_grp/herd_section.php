		<?php echo form_open('auth/service_grp_manage_herds', $attributes); ?>
			<table>
				<tr>
					<th>&nbsp;</th>
					<th>Herd</th>
					<th>Owner</th>
					<th>Expiration</th>
					<th>&nbsp;</th>
				</tr>
				<?php foreach($arr_records as $r):
					echo $r;
				endforeach; ?>
			
			</table>
		<?php if(isset($arr_submit_options) && is_array($arr_submit_options)): ?>
			<label>With selected</label>
			<?php foreach($arr_submit_options as $so): ?>
				&nbsp;&nbsp;&nbsp;<?php echo form_submit(array('type' => 'submit', 'name' => 'submit', 'class' => 'button', 'value' => $so));?>
			<?php endforeach; ?>
		<?php endif; ?>
		
		<?php echo form_close(); ?>
