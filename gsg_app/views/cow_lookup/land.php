<div id="tab-container">
	<h2><?php echo $barn_name; ?></h2>
	<!-- <div class="withheld"><?php //echo $withheld; ?></div> -->
	<ul id="cow-lookup-tabs" class="nav nav-tabs">
		<li><a data-target="#events" data-toggle="tab" href="<?php echo site_url('/cow_lookup/events/' . $serial_num); ?>">Events</a></li>
		<li><a data-target="#id" data-toggle="tab" href="<?php echo site_url('/cow_lookup/id/' . $serial_num); ?>">ID</a></li>
		<li><a data-target="#dam" data-toggle="tab" href="<?php echo site_url('/cow_lookup/dam/' . $serial_num); ?>">Dam</a></li>
		<li><a data-target="#sire" data-toggle="tab" href="<?php echo site_url('/cow_lookup/sire/' . $serial_num); ?>">Sire</a></li>
		<li><a data-target="#tests" data-toggle="tab" href="<?php echo site_url('/cow_lookup/tests/' . $serial_num); ?>">Tests</a></li>
		<li><a data-target="#lactations" data-toggle="tab" href="<?php echo site_url('/cow_lookup/lactations/' . $serial_num); ?>">Lactations</a></li>
		<li><a data-target="#graphs" data-toggle="tab" href="<?php echo site_url('/cow_lookup/graphs/' . $serial_num); ?>">Graphs</a></li>
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
$(function() {
	  $("#cow-lookup-tabs").tab();
	  $("#cow-lookup-tabs").bind("click", function(e) {    
	    var contentID  = $(e.target).attr("data-target");
	    var contentURL = $(e.target).attr("href");
	    if (typeof(contentURL) != 'undefined' && $(contentID).html().length < 20) //@todo: add condition to prevent multiple loads of the same data
	      $(contentID).load(contentURL, function(){ $("#cow-lookup-tabs").tab(); });
	    else
	      $(contentID).tab('show');
	  });
	  $('#cow-lookup-tabs a:first').tab("show");
	});

</script>