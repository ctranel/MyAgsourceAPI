<tr>
	<td><?php echo form_checkbox('modify[]', $id); ?></td>
	<td><?php echo $herd_code; ?></td>
	<td><?php echo $herd_owner; ?></td>
	<td><?php echo $expires_date; ?></td>
	<?php if($is_editable): ?>
		<td><?php echo anchor('auth/service_grp_access/' . $sg_user_id, 'edit'); ?></td>
	<?php endif; ?>
</tr>