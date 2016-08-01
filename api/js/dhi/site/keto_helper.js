	//this function is called in the graph_helper.js file after the JSON data file has loaded, but before chart is rendered.
	//It can make report specific updates after the data has been loaded
	function pre_render(options_in, client_data){
		
		if(typeof(client_data) !== 'undefined'){
			if(client_data['block'] === 'overall_keto_prev'){
				options_in.colors[2] = '#F2EF27';
				options_in.colors[3] = '#F21D21';
				options_in.plotOptions.line = {marker: {enabled: false}};
				options_in.yAxis.plotLines = [
				    {color: '#F2EF27', value: 5, width: 2},
				    {color: '#F21D21', value: 15, width: 2},
				];
				options_in.xAxis.maxPadding = 0;
				options_in.xAxis.startOnTick = false;
				options_in.xAxis.endOnTick = false;
				options_in.series.push({name: '5% Target', type: 'line', data:[]});
				options_in.series.push({name: '15% Target', type: 'line', data:[]});
			}
			
			if(client_data['block'] === 'overall_early_prev_graph'){
				options_in.colors[2] = '#F21D21';
				options_in.series.push({name: '10% Target', type: 'line', data:[]});
				options_in.yAxis.plotLines = [
				    {color: '#F21D21', value: 10, width: 2},
				];
			}
			
			options_in.plotOptions.line = {marker: {enabled: false}};
		}
		return options_in;
	}
