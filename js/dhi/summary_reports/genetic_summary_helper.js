// this function allows you to change chart options immediately before chart is rendered
	function pre_render(options_in){
		//if there is a second yAxis, change the color
		if(typeof options_in.yAxis[1] != 'undefined'){
			options_in.yAxis[1].title.style.color = var_arr_graph_colors[1];
		}
		
		if( section_data['block'] == 'pta_pl_dpr'){
			options_in.colors = ['#FF9168', '#FFB295', '#75C4E4', '#ACDCEF', '#E07F8D', '#97C4A4'];
			options_in.yAxis[0].title.style.color = options_in.colors[0];
			options_in.yAxis[1].title.style.color = options_in.colors[2];
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
		} else if(section_data['block'] == 'cow_cheese_merit' ||
				section_data['block'] == 'cow_net_merit' ||
				section_data['block'] == 'cow_pta_dpr' ||
				section_data['block'] == 'cow_pta_fatpro' ||
				section_data['block'] == 'cow_pta_fatpro_pct' ||
				section_data['block'] == 'cow_pta_milk' ||
				section_data['block'] == 'cow_pta_pl' ||
				section_data['block'] == 'cow_pta_scs' ||
				section_data['block'] == 'pta_cm' ||
				section_data['block'] == 'pta_pl_dpr' ||
				section_data['block'] == 'sire_nm' ||
				section_data['block'] == 'sire_pta_fp_lbs' ||
				section_data['block'] == 'sire_pta_fp_pct' ||
				section_data['block'] == 'sire_pta_milk' ||
				section_data['block'] == 'sire_pta_scs'
			) {	
			options_in.xAxis[0].labels.formatter = function(){return Highcharts.dateFormat('%b %Y', this.value);};
		}
		return options_in;
	}
