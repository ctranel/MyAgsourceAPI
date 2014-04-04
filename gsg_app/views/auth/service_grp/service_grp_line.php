<tr>
	<td><?php echo form_checkbox('modify[]', $id); ?></td>
	<td><?php echo $first_name; ?> <?php echo $last_name; ?></td>
	<td><?php echo $account_name; ?></td>
	<td><?php echo $exp_date; ?></td><?php
	 if($is_editable)
		: ?><td><?php echo anchor('auth/service_grp_access/' . $sg_user_id, 'edit'); ?></td><?php
	 endif;
 ?></tr>