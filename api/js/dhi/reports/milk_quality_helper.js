	// this function allows you to change chart options immediately before chart is rendered
	function pre_render(options_in, client_data){
		//if there is a second yAxis, change the color
		if(typeof options_in.yAxis[1] != 'undefined') options_in.yAxis[1].title.style.color = var_arr_graph_colors[1];
		if(client_data['block'] == 'risk_group_analysis'){
			options_in.colors = ['#D18FBA', '#D5EAFF', '#BFBFFF']
		}
		if(client_data['block'] == 'weighted_average_scc_-_la'){
			options_in.legend.enabled = false;
		}
		else{
			options_in.legend.enabled = true;
		}
		return options_in;
	}
	
	//this function is called in the graph_helper.js file after the JSON data file has loaded.  It can make report specific updates after the data has been loaded (see commented code for example)
	function post_render(client_data, block_index){
		if(typeof(client_data) !== 'undefined'){
			if(typeof(client_data['block']) !== 'undefined'){
				client_data['avg_weighted_avg'] = 200;
				 if(client_data['block'] == 'weighted_average_scc_-_la' && typeof(client_data['avg_weighted_avg']) != 'undefined' && typeof(chart[block_index].series) != 'undefined'){
					//chart is global variable declared in graph_helper.js
					$.each(chart[block_index].series[0].data, function(i, point) {
					    if(point.y > client_data['avg_weighted_avg']) {
					    	point.update({color: 'red'}, false);
					    }
					});
					chart[block_index].redraw();
				}
			}
		}
	}
