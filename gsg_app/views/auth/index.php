<?php if(isset($page_header) !== false) echo $page_header; ?>
<div class='mainInfo'>

	<?php if(isset($page_heading) !== false) echo heading($page_heading); ?>
	<?php if(is_array($users)): ?>
		<p>Click on the name of the accounts below to edit that account information.</p>
		<table class="tbl" id="sortable">
			<thead><tr>
				<th class="subcat-heading">First Name</th>
				<th class="subcat-heading">Last Name</th>
				<th class="subcat-heading">Email</th>
				<th class="subcat-heading">Group</th>
				<th class="subcat-heading">Status</th>
			</tr></thead>
			<tbody>
			<?php foreach ($users as $user):?>
				<tr>
					<td><?php echo anchor('auth/edit_user/' . $user['id'], $user['first_name']) ?></td>
					<td><?php echo anchor('auth/edit_user/' . $user['id'], $user['last_name']) ?></td>
					<td><?php echo $user['email'];?></td>
					<td>
						<?php $arr_groups = explode(',', $user['arr_groups']);
						foreach ($arr_groups as $group): ?>
							<?php //if user has inactive group, we don't want to list it
							if(isset($arr_group_lookup[(int)$group])){
								echo $arr_group_lookup[(int)$group];
							} ?><br />
		                <?php endforeach?>
					</td>
					<td><?php echo ($user['active']) ? anchor("auth/deactivate/".$user['id'], 'Active') : anchor("auth/activate/". $user['id'], 'Inactive');?></td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
	<?php else: ?>
		<p>You do not have any editable accounts.</p>
	<?php endif; ?>
	
	<p><a href="<?php echo site_url('auth/create_user');?>">Create a new user</a></p>
	
	<p><a href="<?php echo site_url('auth/logout'); ?>">Logout</a></p>
	
</div>
<?php if(isset($page_footer) !== false) echo $page_footer;