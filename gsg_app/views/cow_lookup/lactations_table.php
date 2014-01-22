<?php
if(isset($arr_lacts) && is_array($arr_lacts)): ?>
	<table>
		<thead>
			<tr>
				<th class="subcat-heading">Lact#</th>
				<th class="subcat-heading">Age</th>
				<th class="subcat-heading">Fresh Date</th>
				<th class="subcat-heading">LTD DIM</th>
				<th class="subcat-heading">LTD Milk</th>
				<th class="subcat-heading">LTD Fat</th>
				<th class="subcat-heading">LTD Pro</th>
				<th class="subcat-heading">DIM 1st Bred</th>
				<th class="subcat-heading">Days Open</th>
				<th class="subcat-heading">Calv Intvl</th>
				<th class="subcat-heading">Avg LSSCC</th>
				<th class="subcat-heading">305 Milk</th>
				<th class="subcat-heading">305 Fat</th>
				<th class="subcat-heading">305 Pro</th>
				<th class="subcat-heading">305 Milk ME</th>
				<th class="subcat-heading">305 Fat ME</th>
				<th class="subcat-heading">305 Pro ME</th>
				<th class="subcat-heading">365 Milk</th>
				<th class="subcat-heading">365 Fat</th>
				<th class="subcat-heading">365 Pro</th>
				<th class="subcat-heading">Grade Milk</th>
				<th class="subcat-heading">Grade Fat</th>
				<th class="subcat-heading">Grade Pro</th>
			</tr>
		</thead>
		<tbody>
<?php
		$x = 1;
		foreach($arr_lacts as $t): 
			$class = $x % 2 == 1 ? 'odd' : 'even'; ?>
			<tr class="<?php echo $class; ?>">
				<td><?php echo $t['lact_num']; ?></td>
				<td><?php echo $t['age']; ?></td>
				<td><?php echo $t['fresh_date']; ?></td>
				<td><?php echo $t['ltd_dim']; ?></td>
				<td><?php echo $t['ltd_milk_lbs']; ?></td>
				<td><?php echo $t['ltd_fat_lbs']; ?></td>
				<td><?php echo $t['ltd_pro_lbs']; ?></td>
				<td><?php echo $t['first_bred_dim']; ?></td>
				<td><?php echo $t['days_open']; ?></td>
				<td><?php echo $t['calving_int_days']; ?></td>
				<td><?php echo $t['avg_linear_score']; ?></td>
				<td><?php echo $t['d305_milk_lbs']; ?></td>
				<td><?php echo $t['d305_fat_lbs']; ?></td>
				<td><?php echo $t['d305_pro_lbs']; ?></td>
				<td><?php echo $t['me_milk_lbs']; ?></td>
				<td><?php echo $t['me_fat_lbs']; ?></td>
				<td><?php echo $t['me_pro_lbs']; ?></td>
				<td><?php echo $t['d365_milk_lbs']; ?></td>
				<td><?php echo $t['d365_fat_lbs']; ?></td>
				<td><?php echo $t['d365_pro_lbs']; ?></td>
				<td><?php echo $t['letter_grade_milk']; ?></td>
				<td><?php echo $t['letter_grade_fat']; ?></td>
				<td><?php echo $t['letter_grade_pro']; ?></td>
			</tr>
<?php 	$x++;
		endforeach; ?>
		</tbody>
	</table><!-- end #EVENTS_EVENTS -->
<?php 
else: ?>
	<div>No lactations found for <?php echo $barn_name; ?></div>
<?php 
endif;