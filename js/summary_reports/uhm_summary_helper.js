	// this function allows you to change chart options immediately before chart is rendered
	function pre_render(options_in, section_data){
		//if there is a second yAxis, change the color
		if(typeof options_in.yAxis[1] != 'undefined') options_in.yAxis[1].title.style.color = var_arr_graph_colors[1];
		if(section_data['block'] == 'uhm_risk'){Highcharts.setOptions({
			 colors: ['#50B432', '#ED561B', '#DDDF00']
			});
		}
	}
	
	//this function is called in the graph_helper.js file after the JSON data file has loaded.  It can make report specific updates after the data has been loaded (see commented code for example)
	function post_render(section_data){
		if(typeof(section_data) !== 'undefined'){
			if(typeof(section_data['block']) !== 'undefined'){
				//$('#block-links > ul > li > a').css('text-decoration', 'none');
				//$('#block-links > ul > li > a').css('font-weight', 'normal');
				//$('#' + app_data['block']).css('text-decoration', 'underline');
				//$('#' + app_data['block']).css('font-weight', 'bold');
				//$('input[name=block][value=' + value_in + ']').attr("checked", true);

				 if(typeof(chart) != 'undefined' && section_data['block'] == 'weighted_average_scc_-_la' && typeof(section_data['avg_weighted_avg'] != 'undefined')){
					//chart is global variable declared in graph_helper.js
					$.each(chart.series[0].data, function(i, point) {
					    if(point.y > section_data['avg_weighted_avg']) {
					    	point.update({color: 'red'}, FALSE);
					    }
					});
					chart.redraw();
				}
			}
		}
	}