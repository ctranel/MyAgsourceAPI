	// this function allows you to change chart options immediately before chart is rendered
	function pre_render(options_in){
		//if there is a second yAxis, change the color
		if(typeof options_in.yAxis[1] != 'undefined'){
			options_in.yAxis[1].title.style.color = '#f00';//var_arr_graph_colors[1];
		}
		if(section_data['block'] == 'ratio_of_first_test_fat_t' || section_data['block'] == 'transition_cow_index_tci_' || section_data['block'] == 'first_test_linear_score_b'){
			options_in.colors = [var_arr_graph_colors[0], var_arr_graph_colors[0], var_arr_graph_colors[1], var_arr_graph_colors[1]];
		}

		//if fpr, set yaxis tick interval to .2
		if(section_data['block'] == 'ratio_of_first_test_fat_t'){
			options_in.yAxis.tickInterval = .2;
		}
		else{
			options_in.yAxis.tickInterval = null;
		}
		return options_in;
	}