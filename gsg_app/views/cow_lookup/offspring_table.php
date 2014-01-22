<?php
if(isset($arr_offspring) && is_array($arr_offspring)): ?>
	<table>
		<thead>
			<tr>
				<th class="subcat-heading">Calf#</th>
				<th class="subcat-heading">DOB</th>
				<th class="subcat-heading">Calf Name</th>
				<th class="subcat-heading">Calf Vis ID</th>
				<th class="subcat-heading">Sex</th>
				<th class="subcat-heading">Twin</th>
				<th class="subcat-heading">Calving Ease</th>
				<th class="subcat-heading">Sire NAAB</th>
				<th class="subcat-heading">Sire Name</th>
			</tr>
		</thead>
		<tbody>
<?php
		$x = 1;
		foreach($arr_offspring as $t): 
			$class = $x % 2 == 1 ? 'odd' : 'even'; ?>
			<tr class="<?php echo $class; ?>">
				<td><?php echo $t['calf_control_num']; ?></td>
				<td><?php echo $t['calving_date']; ?></td>
				<td><?php echo $t['calf_name']; ?></td>
				<td><?php echo $t['calf_visible_id']; ?></td>
				<td><?php echo $t['sex_desc']; ?></td>
				<td><?php echo $t['twin_code']; ?></td>
				<td><?php echo $t['calving_ease_code']; ?></td>
				<td><?php echo $t['calf_sire_naab']; ?></td>
				<td><?php echo $t['calf_sire_name']; ?></td>
			</tr>
<?php 	$x++;
		endforeach; ?>
		</tbody>
	</table>
<?php 
else: ?>
	<div>No offspring found for <?php echo $barn_name; ?></div>
<?php 
endif;