// this function allows you to change chart options immediately before chart is rendered
	function pre_render(options_in){
		//if there is a second yAxis, change the color
		if(typeof options_in.yAxis[1] != 'undefined'){
			options_in.yAxis[1].title.style.color = var_arr_graph_colors[1];
		}
		
		if( client_data['block'] == 'pta_pl_dpr'){
			options_in.colors = ['#FF9168', '#FFB295', '#75C4E4', '#ACDCEF', '#E07F8D', '#97C4A4'];
			options_in.yAxis[0].title.style.color = options_in.colors[0];
			options_in.yAxis[1].title.style.color = options_in.colors[2];
		}
		
		if(typeof(options_in.xAxis[0].label) === 'undefined'){
			options_in.xAxis[0].label = {};
		}

		if( client_data['block'] == 'inbreeding_trend' ||
			client_data['block'] == 'net_merit' ||
			client_data['block'] == 'cheese_merit' ||
			client_data['block'] == 'scs' ||
			client_data['block'] == 'dpr' ||
			client_data['block'] == 'prod_life'
		){
			options_in.xAxis[0].labels.formatter = function(){return Highcharts.dateFormat('%Y', this.value);};
		} else if(client_data['block'] == 'cow_cheese_merit' ||
				client_data['block'] == 'cow_net_merit' ||
				client_data['block'] == 'cow_pta_dpr' ||
				client_data['block'] == 'cow_pta_fatpro' ||
				client_data['block'] == 'cow_pta_fatpro_pct' ||
				client_data['block'] == 'cow_pta_milk' ||
				client_data['block'] == 'cow_pta_pl' ||
				client_data['block'] == 'cow_pta_scs' ||
				client_data['block'] == 'pta_cm' ||
				client_data['block'] == 'pta_pl_dpr' ||
				client_data['block'] == 'sire_nm' ||
				client_data['block'] == 'sire_pta_fp_lbs' ||
				client_data['block'] == 'sire_pta_fp_pct' ||
				client_data['block'] == 'sire_pta_milk' ||
				client_data['block'] == 'sire_pta_scs'
			) {	
			options_in.xAxis[0].labels.formatter = function(){return Highcharts.dateFormat('%b %Y', this.value);};
		}
		return options_in;
	}
