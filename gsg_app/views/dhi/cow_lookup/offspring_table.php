<?php
if(isset($arr_offspring) && is_array($arr_offspring)): ?>
	<table class="simple-sort tbl">
		<thead>
			<tr>
				<th class="subcat-heading" data-sort="int">Calf#</th>
				<th class="subcat-heading sort_asc" data-sort="date">DOB</th>
				<th class="subcat-heading" data-sort="string">Calf Name</th>
				<th class="subcat-heading" data-sort="int">Calf Vis ID</th>
				<th class="subcat-heading" data-sort="string">Sex</th>
				<th class="subcat-heading" data-sort="string">Twin/ET</th>
				<th class="subcat-heading" data-sort="int">Calving Ease</th>
				<th class="subcat-heading" data-sort="string">Sire NAAB</th>
				<th class="subcat-heading" data-sort="string">Sire Name</th>
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
	<div>No offspring found for <?php echo $cow_id; ?></div>
<?php 
endif; ?>

<script type="text/javascript">
	//add simple column sorting
	var table = $(".simple-sort").stupidtable({
  	  "date":function(a,b){return dateFunc(a,b);}
	});
	table.bind('aftertablesort', function (event, data) {addRowClasses();} );
</script>