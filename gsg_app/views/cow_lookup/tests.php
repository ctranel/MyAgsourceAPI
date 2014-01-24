<?php
if(isset($lact_num)): ?>
	<h3>Lact Num <?php echo $lact_num; ?></h3>	
	
<?php	
endif;
if(isset($lact_num)):
	if($lact_num > 1):?>
		<a class="button incr-lact-tests" data-target="#tests" data-toggle="tab" href="<?php echo site_url('cow_lookup/tests/' . $serial_num . '/' . ($lact_num - 1)); ?>">Previous Lactation</a>
<?php 
	endif;
	if($lact_num < $curr_lact_num):?>
		<a class="button incr-lact-tests" data-target="#tests" data-toggle="tab" href="<?php echo site_url('cow_lookup/tests/' . $serial_num . '/' . ($lact_num + 1)); ?>">Next Lactation</a>
<?php
	endif;
endif;

if(isset($arr_tests) && is_array($arr_tests)): ?>
	<table class="simple-sort">
		<thead>
			<tr>
				<th class="subcat-heading sort_desc" data-sort="date">Test Date</th>
				<th class="subcat-heading" data-sort="int">DIM</th>
				<th class="subcat-heading" data-sort="int">Milk</th>
				<th class="subcat-heading" data-sort="int">Fat</th>
				<th class="subcat-heading" data-sort="int">Pro</th>
				<th class="subcat-heading" data-sort="int">FCM</th>
				<th class="subcat-heading" data-sort="int">ECM</th>
				<th class="subcat-heading" data-sort="int">MLM</th>
				<th class="subcat-heading" data-sort="int">SCC</th>
				<th class="subcat-heading" data-sort="int">LS SCC</th>
				<th class="subcat-heading" data-sort="int">% Last Milk</th>
				<th class="subcat-heading" data-sort="int">CAR</th>
				<th class="subcat-heading" data-sort="int">MUN</th>
			</tr>
		</thead>
		<tbody>
<?php
		$x = 1;
		foreach($arr_tests as $t): 
			$class = $x % 2 == 1 ? 'odd' : 'even'; ?>
			<tr class="<?php echo $class; ?>">
				<td><?php echo $t['date']; ?></td>
				<td><?php echo $t['lact_dim']; ?></td>
				<td><?php echo $t['td_milk_lbs']; ?></td>
				<td><?php echo $t['fat_pct']; ?></td>
				<td><?php echo $t['pro_pct']; ?></td>
				<td><?php echo $t['fcm_lbs']; ?></td>
				<td><?php echo $t['ecm_lbs']; ?></td>
				<td><?php echo $t['mlm_lbs']; ?></td>
				<td><?php echo $t['scc_cnt']; ?></td>
				<td><?php echo $t['linear_score']; ?></td>
				<td><?php echo $t['pct_last_milk']; ?></td>
				<td><?php echo $t['car_1']; ?></td>
				<td><?php echo $t['mun']; ?></td>
			</tr>
<?php 	$x++;
		endforeach; ?>
		</tbody>
	</table><!-- end #EVENTS_EVENTS -->
<?php 
else: ?>
	<div>No tests found for <?php echo $barn_name; ?></div>
<?php 
endif; ?>
<script type="text/javascript">
	//links that reload tab content
	$(function() {
		  $(".incr-lact-tests").bind("click", function(e) {loadTab(e)} );
		});

	//add simple column sorting
	var table = $(".simple-sort").stupidtable({
		  "date":function(a,b){return dateFunc(a,b);}
	});
	table.bind('aftertablesort', function (event, data) {addRowClasses();} );
</script>
