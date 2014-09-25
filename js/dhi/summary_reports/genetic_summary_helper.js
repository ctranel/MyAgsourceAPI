// this function allows you to change chart options immediately before chart is rendered
	function pre_render(options_in){
		//if there is a second yAxis, change the color
		if(typeof options_in.yAxis[1] != 'undefined'){
			options_in.yAxis[1].title.style.color = var_arr_graph_colors[1];
		}
		
		if( section_data['block'] == 'pta_pl_dpr'){
			options_in.yAxis[1].title.style.color = var_arr_graph_colors[1];
		}
		
		var pstring_index = options_in.subtitle.text.indexOf('Pstring');
		if(pstring_index > 0){
			options_in.subtitle.text = options_in.subtitle.text.substring(0,pstring_index);
		}
		if(typeof(options_in.xAxis[0].label) === 'undefined'){
			options_in.xAxis[0].label = {};
		}

		if( section_data['block'] == 'inbreeding_trend' ||
			section_data['block'] == 'net_merit' ||
			section_data['block'] == 'cheese_merit' ||
			section_data['block'] == 'scs' ||
			section_data['block'] == 'dpr' ||
			section_data['block'] == 'prod_life'
		){
			options_in.xAxis[0].labels.formatter = function(){return Highcharts.dateFormat('%Y', this.value);};
		} else {	
			options_in.xAxis[0].labels.formatter = function(){return Highcharts.dateFormat('%b %Y', this.value);};
		}
		return options_in;
	}
