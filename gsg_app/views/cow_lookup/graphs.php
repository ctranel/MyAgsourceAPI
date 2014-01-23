<?php	
if(isset($lact_num)):
	if($lact_num > 1):?>
		<a class="button incr-lact-tests" data-target="#tests" data-toggle="tab" href="<?php echo site_url('cow_lookup/tests/' . $serial_num . '/' . ($lact_num - 1)); ?>">Previous Lactation</a>
<?php 
	endif;
	if($lact_num < $curr_lact_num):?>
		<a class="button incr-lact-tests" data-target="#tests" data-toggle="tab" href="<?php echo site_url('cow_lookup/tests/' . $serial_num . '/' . ($lact_num + 1)); ?>">Next Lactation</a>
<?php
	endif;
endif; ?>
<div class="chart-container odd">
	<?php if(isset($before_chart)) echo $before_chart ?>
	<div class="jqPlot jqplot-target" id="chart1"></div>
	<a name="chart1"><div id="graph-canvas0" data-block="cow_lookup" class="chart"></div></a>
	<?php if(isset($after_chart)) echo $after_chart ?>
</div>

<script type="text/javascript">
$('#graph-canvas0').highcharts({
	chart: {
		type: 'line'
	},
	tooltip: {
		formatter: function() {
			return 'DIM =<b>'+ this.x +'</b>  : '+ this.point.series.name + '= <b>' + this.y;
		}
	},
	subtitle: {
		text: 'Lactation <?php if(isset($lact_num)) echo $lact_num; ?>'
	},
	title: {
		useHTML: false,
		text: 'Name = <?php if(isset($barn_name)) echo $barn_name; ?>'
	},
	yAxis: [
		{
			title: {
				text: 'Lbs Milk'
			}
		},{
			title: {
				text: 'Lab Units'
			},
			opposite: true
		}, {
			title: {
				text: 'SCC'
			},
			opposite: true
		}
	],
	xAxis: {
		title: {
			text: 'Days in Milk',
			type: 'linear'
		}
	},
	series: [
		{
			name: 'Milk lbs',
			yAxis: 0,
			data: <?php echo json_encode($arr_tests['td_milk_lbs']); ?>
		},{
			name: 'Fat Corrected Milk',
			yAxis: 0,
			data: <?php echo json_encode($arr_tests['fcm_lbs']); ?>
		},{
			name: 'Energy Corrected Milk',
			yAxis: 0,
			data: <?php echo json_encode($arr_tests['ecm_lbs']); ?>
		},{
			name: 'Management Milk',
			yAxis: 0,
			data: <?php echo json_encode($arr_tests['mlm_lbs']); ?>
		},{
			name: 'Linear SCC',
			yAxis: 1,
			data: <?php echo json_encode($arr_tests['linear_score']); ?>
		},{
			name: '%Protein',
			yAxis: 1,
			data: <?php echo json_encode($arr_tests['pro_pct']); ?>
		},{
			name: '% Fat',
			yAxis: 1,
			data: <?php echo json_encode($arr_tests['fat_pct']); ?>
		},{
			name: 'SNF',
			yAxis: 1,
			data: <?php echo json_encode($arr_tests['snf_pct']); ?>
		},{
			name: 'MUN',
			yAxis: 1,
			data: <?php echo json_encode($arr_tests['mun']); ?>
		},{
			name: 'Raw SCC',
			yAxis: 2,
			data: <?php echo json_encode($arr_tests['scc_cnt']); ?>
		}
	]
});

var chart = $('#graph-canvas0').highcharts();
for (i =1;i < 9;i++){
	if ( chart.series[i].visible)  chart.series[i].hide()
}
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
