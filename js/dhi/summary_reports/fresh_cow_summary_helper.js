	// this function allows you to change chart options immediately before chart is rendered
	function pre_render(options_in){

		//if fpr, set yaxis tick interval to .2
		if(client_data['block'] == 'ratio_of_first_test_fat_t'){
			options_in.yAxis.tickInterval = .2;
		}
		else{
			options_in.yAxis.tickInterval = null;
		}
		return options_in;
	}