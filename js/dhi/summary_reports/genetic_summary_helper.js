// this function allows you to change chart options immediately before chart is rendered
	function pre_render(options_in){
		//if there is a second yAxis, change the color
		if(typeof options_in.yAxis[1] != 'undefined'){
			options_in.yAxis[1].title.style.color = var_arr_graph_colors[1];
		}
		
		var pstring_index = options_in.subtitle.text.indexOf('Pstring');
		if(pstring_index > 0){
			options_in.subtitle.text = options_in.subtitle.text.substring(0,pstring_index);
		}
		return options_in;
	}
