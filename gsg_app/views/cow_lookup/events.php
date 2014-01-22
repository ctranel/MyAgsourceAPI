<div class="gen-info">
	<div class="row">
		<div class="col-sm-4">
			<label>Barn Name</label> <?php echo $barn_name; ?>
		</div>
		<div class="col-sm-4">
			<label>Lact #</label> <?php echo $curr_lact_num; ?>
		</div>
		<div class="col-sm-4">
			<label>Test Day DIM</label> <?php echo $curr_305_dim; ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-4">
			<label>Visible ID</label> <?php echo $visible_id; ?>
		</div>
		<div class="col-sm-4">
			<label>Curr Milk Lbs</label> <?php echo $curr_milk_lbs; ?>
		</div>
		<div class="col-sm-4">
			<label>Proj 305 Milk Lbs</label> <?php echo $curr_305_milk_lbs; ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-4">
			<label>Net Merit</label> <?php echo $net_merit_amt; ?>
		</div>
		<div class="col-sm-4">
			<label>Curr % Last Milk</label> <?php echo $curr_pct_last_milk; ?>
		</div>
		<div class="col-sm-4">
			<label>Proj 305 Fat Lbs</label> <?php echo $curr_305_fat_lbs; ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-4">
			<label>Pen</label> <?php echo $tstring; ?>
		</div>
		<div class="col-sm-4">
			<label>Curr SCC</label> <?php echo $curr_scc_cnt; ?>
		</div>
		<div class="col-sm-4">
			<label>Proj 305 Pro Lbs</label> <?php echo $curr_305_pro_lbs; ?>
		</div>
	</div>
</div>
<?php
if(isset($arr_events) && is_array($arr_events)): ?>
	<!-- <a href="index.php?action=EVENTS&amp;bShow=ALL&amp;comp_num=1688&amp;token=686969169">Show All Events</a> -->
	<table>
		<thead>
			<tr>
				<th class="subcat-heading">Date</th>
				<th class="subcat-heading">Event</th>
				<th class="subcat-heading">Comment/Sire</th>
			</tr>
		</thead>
		<tbody>
<?php
		$cnt = 1;
		foreach($arr_events as $e): 
			$class = $cnt % 2 == 1 ? 'odd' : 'even'; ?>
			<tr class="<?php echo $class; ?>">
				<td><?php echo $e['event_date']; ?></td>
				<td><?php echo $e['event_desc']; ?></td>
				<td><?php echo $e['event_data']; ?></td>
			</tr>
<?php 	$cnt++;
		endforeach; ?>
		</tbody>
	</table>
<?php 
else: ?>
	<div>No events found for <?php echo $barn_name; ?></div>
<?php 
endif; ?>
