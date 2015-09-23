<div id="tab-container">
	<h2><?php echo $cow_id; ?></h2>
	<!-- <div class="withheld"><?php //echo $withheld; ?></div> -->
	<ul id="cow-lookup-tabs" class="nav nav-tabs">
		<li><a data-target="#events" data-toggle="tab" href="<?php echo site_url('/dhi/ajax_cow_lookup/events/' . $serial_num); ?>">Events</a></li>
		<li><a data-target="#id" data-toggle="tab" href="<?php echo site_url('/dhi/ajax_cow_lookup/id/' . $serial_num); ?>">ID</a></li>
		<li><a id="dam-tab" data-target="#dam" data-toggle="tab" href="<?php echo site_url('/dhi/ajax_cow_lookup/dam/' . $serial_num); ?>">Dam</a></li>
		<li><a id="sire-tab" data-target="#sire" data-toggle="tab" href="<?php echo site_url('/dhi/ajax_cow_lookup/sire/' . $serial_num); ?>">Sire</a></li>
		<li><a data-target="#tests" data-toggle="tab" href="<?php echo site_url('/dhi/ajax_cow_lookup/tests/' . $serial_num); ?>">Tests</a></li>
		<li><a data-target="#lactations" data-toggle="tab" href="<?php echo site_url('/dhi/ajax_cow_lookup/lactations/' . $serial_num); ?>">Lactations</a></li>
		<li><a data-target="#graphs" data-toggle="tab" href="<?php echo site_url('/dhi/ajax_cow_lookup/graphs/' . $serial_num); ?>">Graphs</a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane" id="events"><?php if(isset($events_content)) echo $events_content; ?></div>
		<div class="tab-pane" id="id">Loading...</div>
		<div class="tab-pane" id="dam">Loading...</div>
		<div class="tab-pane" id="sire">Loading...</div>
		<div class="tab-pane" id="tests">Loading...</div>
		<div class="tab-pane" id="lactations">Loading...</div>
		<div class="tab-pane" id="graphs">Loading...</div>
	</div> <!-- end .tab-content -->
</div>

<script type="text/javascript">
window.onload = function() {
		  $("#cow-lookup-tabs").tab();
		  $("#cow-lookup-tabs").bind("click", function(e) {    
		    var contentID  = $(e.target).attr("data-target");
		    var contentURL = $(e.target).attr("href");
		    if (typeof(contentURL) != 'undefined' && $(contentID).html().length < 20)
		      $(contentID).load(contentURL, function(){ $("#cow-lookup-tabs").tab(); });
		    else
		      $(contentID).tab('show');
		  });
		  $('#cow-lookup-tabs a:first').tab("show");

	
	var date_from_string = function(str){
		    var pattern = "^(\\d{1,2})\/(\\d{1,2})\/(\\d{4})$";
		    var re = new RegExp(pattern);
		    var DateParts = re.exec(str).slice(1);
		
		    var Year = DateParts[2];
		    var Month = DateParts[0];
		    var Day = DateParts[1];
		    return new Date(Year, Month, Day);
		}
	
	var dateFunc = function(a,b){
		    // Get these into date objects for comparison.
		    aDate = date_from_string(a);
		    bDate = date_from_string(b);
		
		    return aDate - bDate;
		}
	
	var addRowClasses = function(){
			$(".simple-sort").each(function(){
				var cnt = 1;
				var cls = 'odd';
				$(this).find ('tbody  tr').each(function(){
					cls = 'even';
					if(cnt % 2 == 1) cls = 'odd';
					$(this).removeClass("odd even");
					$(this).addClass(cls);
					cnt++;
				});
			});
		}
	
	var loadTab = function(e){
		    var contentID  = $(e.target).attr("data-target");
		    var contentURL = $(e.target).attr("href");
		    $(contentID).load(contentURL, function(){ $("#cow-lookup-tabs").tab(); });
		}
	
	<?php
	if($tab == 'sire'): ?>
		$('#sire-tab').trigger('click');
	<?php
	endif; 
	if($tab == 'dam'): ?>
		$('#dam-tab').trigger('click');
	<?php
	endif; 
	?>
}
</script>