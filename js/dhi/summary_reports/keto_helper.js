// this function allows you to change chart options immediately before chart is rendered
	function pre_render(options_in){
		//if there is a second yAxis, change the color
		if(client_data['block'] == 'overall_keto_prev' ||
			client_data['block'] == 'overall_early_prev_graph' 
			) {	
			options_in.xAxis[0].labels.formatter = function(){return Highcharts.dateFormat('%b %Y', this.value);};
			options_in.series[2]['marker'] = {'enabled': false};
			options_in.series[3]['marker'] = {'enabled': false};			
		}
		
		return options_in;
	}
