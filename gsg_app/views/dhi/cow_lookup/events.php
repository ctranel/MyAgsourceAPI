<div class="gen-info">
	<div class="row">
		<div class="col-sm-4 col-xs-6">
			<label>Barn Name</label> <?php echo $barn_name; ?>
		</div>
		<div class="col-sm-4 col-xs-6">
			<label>Lact #</label> <?php echo $curr_lact_num; ?>
		</div>
		<div class="col-sm-4 col-xs-6">
			<label>Test Day DIM</label> <?php echo $curr_ltd_dim; ?>
		</div>
		<div class="col-sm-4 col-xs-6">
			<label>Visible ID</label> <?php echo $visible_id; ?>
		</div>
		<div class="col-sm-4 col-xs-6">
			<label>Curr Milk Lbs</label> <?php echo $curr_milk_lbs; ?>
		</div>
		<div class="col-sm-4 col-xs-6">
			<label>Proj 305 Milk Lbs</label> <?php echo $curr_305_milk_lbs; ?>
		</div>
		<div class="col-sm-4 col-xs-6">
			<label>Net Merit</label> <?php echo $net_merit_amt; ?>
		</div>
		<div class="col-sm-4 col-xs-6">
			<label>Curr % Last Milk</label> <?php echo $curr_pct_last_milk; ?>
		</div>
		<div class="col-sm-4 col-xs-6">
			<label>Proj 305 Fat Lbs</label> <?php echo $curr_305_fat_lbs; ?>
		</div>
		<div class="col-sm-4 col-xs-6">
			<label>Pen</label> <?php echo $tstring; ?>
		</div>
		<div class="col-sm-4 col-xs-6">
			<label>Curr SCC</label> <?php echo $curr_scc_cnt; ?>
		</div>
		<div class="col-sm-4 col-xs-6">
			<label>Proj 305 Pro Lbs</label> <?php echo $curr_305_pro_lbs; ?>
		</div>
	</div>
</div>
<?php if($show_all_events):?>
<a class="button incr-lact-tests" data-target="#events" data-toggle="tab" href="<?php echo site_url('dhi/ajax_cow_lookup/events/' . $serial_num); ?>">Show Current Lact Events</a>
<?php else:?>
<a class="button incr-lact-tests" data-target="#events" data-toggle="tab" href="<?php echo site_url('dhi/ajax_cow_lookup/events/' . $serial_num . '/1'); ?>">Show All Events</a>
<?php
endif;
if(isset($arr_events) && is_array($arr_events)): ?>
	<table class="simple-sort tbl">
		<thead>
			<tr>
				<th class="subcat-heading sort_desc" data-sort="date">Date</th>
				<th class="subcat-heading" data-sort="string">Event</th>
				<th class="subcat-heading" data-sort="string">Comment/Sire</th>
			</tr>
		</thead>
		<tbody>
<?php
		$cnt = 1;
		foreach($arr_events as $e): 
			$class = $cnt % 2 == 1 ? 'odd' : 'even'; ?>
			<tr class="<?php echo $class; ?>">
				<td><?php echo $e['event_date']; ?></td>
				<td><?php echo $e['short_desc']; ?></td>
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
<script type="text/javascript">
//links that reload tab content
window.onload = function() {
	$(".incr-lact-tests").bind("click", function(e) {loadTab(e)} );

	//add simple column sorting
	var table = $(".simple-sort").stupidtable({
	    "date":function(a,b){return dateFunc(a,b);}
	});
	table.bind('aftertablesort', function (event, data) {addRowClasses();} );
};
</script>
