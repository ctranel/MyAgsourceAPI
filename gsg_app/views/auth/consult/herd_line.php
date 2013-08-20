<tr>
	<td><?php echo form_checkbox('modify[]', $id); ?></td>
	<td><?php echo $herd_code; ?></td>
	<td><?php echo $herd_owner; ?></td>
	<td><?php echo $exp_date; ?></td>
	<?php if($is_editable): ?>
		<td><?php echo anchor('auth/consult_access/' . $consultant_user_id, 'edit'); ?></td>
	<?php endif; ?>
</tr>