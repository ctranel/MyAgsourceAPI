<?php
/* Highcharts and the other usual report assets need to be included on any page that retrieves this content */	
if(isset($lact_num)):
	if($lact_num > 1):?>
		<a class="button incr-lact-tests" data-target="#graphs" data-toggle="tab" href="<?php echo site_url('dhi/ajax_cow_lookup/graphs/' . $serial_num . '/' . ($lact_num - 1)); ?>">Previous Lactation</a>
<?php 
	endif;
	if($lact_num < $curr_lact_num):?>
		<a class="button incr-lact-tests" data-target="#graphs" data-toggle="tab" href="<?php echo site_url('dhi/ajax_cow_lookup/graphs/' . $serial_num . '/' . ($lact_num + 1)); ?>">Next Lactation</a>
<?php
	endif;
endif; ?>
<div class="chart-container odd">
	<?php if(isset($before_chart)) echo $before_chart ?>
	<div class="jqPlot jqplot-target" id="chart1"></div>
	<a name="chart1"><div id="block-canvas0" data-block="cow_lookup" class="chart"></div></a>
	<?php if(isset($after_chart)) echo $after_chart ?>
</div>

<script type="text/javascript">
var options = global_options;

$('.chart-container').css('width', '560px');

options = get_chart_options(options, 'line');
options.tooltip = {
		'formatter': function() {
			return 'DIM =<b>'+ this.x +'</b>  : '+ this.point.series.name + '= <b>' + this.y;
		}
	};
options.title = {
		'useHTML': false,
		'text': 'Name = <?php if(isset($barn_name)) echo $barn_name; ?>'
	};
options.subtitle = {
		'text': 'Lactation <?php if(isset($lact_num)) echo $lact_num; ?>'
	};
options.yAxis = [
		{
			'title': {
				'text': 'Lbs Milk'
			}
		},{
			'title': {
				'text': 'Lab Units'
			},
			'opposite': true
		}, {
			'title': {
				'text': 'SCC'
			},
			'opposite': true
		}
	];
options.xAxis = {
		'title': {
			'text': 'Days in Milk',
			'type': 'linear'
		},
		'labels': {
			'formatter': function(){return this.value;}
		}
	};

options.series = [
		{
			'name': 'Milk lbs',
			'yAxis': 0,
			'data': <?php echo json_encode($arr_tests['td_milk_lbs']); ?>
		},{
			'name': 'Fat Corrected Milk',
			'yAxis': 0,
			'data': <?php echo json_encode($arr_tests['fcm_lbs']); ?>
		},{
			'name': 'Energy Corrected Milk',
			'yAxis': 0,
			'data': <?php echo json_encode($arr_tests['ecm_lbs']); ?>
		},{
			'name': 'Management Milk',
			'yAxis': 0,
			'data': <?php echo json_encode($arr_tests['mlm_lbs']); ?>
		},{
			'name': 'Linear SCC',
			'yAxis': 1,
			'data': <?php echo json_encode($arr_tests['linear_score']); ?>
		},{
			'name': '%Protein',
			'yAxis': 1,
			'data': <?php echo json_encode($arr_tests['pro_pct']); ?>
		},{
			'name': '% Fat',
			'yAxis': 1,
			'data': <?php echo json_encode($arr_tests['fat_pct']); ?>
		},{
			'name': 'SNF',
			'yAxis': 1,
			'data': <?php echo json_encode($arr_tests['snf_pct']); ?>
		},{
			'name': 'MUN',
			'yAxis': 1,
			'data': <?php echo json_encode($arr_tests['mun']); ?>
		},{
			'name': 'Raw SCC',
			'yAxis': 2,
			'data': <?php echo json_encode($arr_tests['scc_cnt']); ?>
		}
	];

$('#block-canvas0').highcharts(options);
var chart = $('#block-canvas0').highcharts();
for (var i =1;i < 9;i++){
	if ( chart.series[i].visible){
		chart.series[i].hide();
	}
}

$('.chart-container').show();
</script>
<script type="text/javascript">
$(function() {
	  $(".incr-lact-tests").bind("click", function(e) {    
	    var contentID  = $(e.target).attr("data-target");
	    var contentURL = $(e.target).attr("href");
       	$(contentID).html('Loading...');
        $(contentID).load(contentURL, function(){
             $("#cow-lookup-tabs").tab();
        });
	  });
	});
</script>
