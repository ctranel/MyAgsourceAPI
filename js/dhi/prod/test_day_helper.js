	// this function allows you to change chart options immediately before chart is rendered
	function pre_render(options_in, section_data){
		/*if there is a second yAxis, change the color
		if(typeof options_in.yAxis[1] != 'undefined') options_in.yAxis[1].title.style.color = var_arr_graph_colors[1];
		if(section_data['block'] == 'risk_group_analysis'){Highcharts.setOptions({
			 colors: ['#D18FBA', '#D5EAFF', '#BFBFFF']
			});
		}
		if(section_data['block'] == 'infection_by_lactation_gr' || section_data['block'] == 'weighted_average_scc_-_la'){
			Highcharts.setOptions({
				legend: {enabled: false}
			});
		}
		else{
			Highcharts.setOptions({
				legend: {enabled: true}
			});
		}
		if(section_data['block'] == 'distribution_of_herd_by_s'){
			Highcharts.setOptions({
				yaxis: {max: 100}
			});
		} */
	}
	
	//this function is called in the graph_helper.js file after the JSON data file has loaded.  It can make report specific updates after the data has been loaded (see commented code for example)
	function post_render(section_data){
		if(typeof(section_data) === 'undefined' || typeof(section_data['block']) === 'undefined') return false;
		if(typeof(chart) != 'undefined' && section_data['block'] == 'test_day_results'){
			$('th[id^="scc_cnt"]').each(function(){
				$('#test_day_results').find("tr td:nth-child(" + ($(this).index() + 1) + ")").each(function(){
					if(parseInt($(this).html().replace(/,/g,'')) >= 200){
						$(this).css('color', 'red').css('fontWeight', 'bold');
					}
				});
			});
		}
	}
