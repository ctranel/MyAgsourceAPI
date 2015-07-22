	// this function allows you to change chart options immediately before chart is rendered
	function pre_render(options_in){
		//Boxplots with trend lines
		if(client_data['block'] === 'transition_cow_index_tci_' || client_data['block'] === 'ratio_of_first_test_fat_t' || client_data['block'] === 'first_test_linear_score_b'){
			var series_adjustment = 500000000;
			var boxplot_count = options_in.series.length / 2;
			options_in.tooltip.xDateFormat = '%b %Y';
	    	
			
			if(client_data['block'] === 'transition_cow_index_tci_'){
				options_in.plotOptions.columnrange.tooltip.pointFormat = '<span style="color:{point.color}">\u25CF</span> <b> {series.name}</b><br/>' + // docs
		    			'75th Percentile Value: {point.low}<br/>' +
		    			'Median Value: {point.med}<br/>' +
		    			'25th Percentile Value: {point.high}<br/>';
			}
			if(client_data['block'] === 'first_test_linear_score_b' || client_data['block'] === 'ratio_of_first_test_fat_t'){
				options_in.plotOptions.columnrange.tooltip.pointFormat = '<span style="color:{point.color}">\u25CF</span> <b> {series.name}</b><br/>' + // docs
		    			'25th Percentile Value: {point.high}<br/>' +
		    			'Median Value: {point.med}<br/>' +
		    			'75th Percentile Value: {point.low}<br/>';
			}
			
			for(i in options_in.series){
				// set properties for trend series
				if(i % 2 === 1){
					options_in.series[i]['color'] = var_arr_graph_colors[(i-1) / 2];
					options_in.series[i]['marker'] = {'enabled': false};
					options_in.series[i]['enableMouseTracking'] = false;
					options_in.series[i]['linkedTo'] = ':previous';
				}
				//set offsets for boxplot series
				if(i % 2 === 0 && i > 0){
					var offset = getSeriesOffset(boxplot_count, i, series_adjustment);
					for(c in options_in.series[i]['data']){
						//update first value of this and corresponding trend series
						options_in.series[i]['data'][c]['x'] += offset;
						options_in.series[parseInt(i) + 1]['data'][c]['x'] += offset;
					}
				}
			}
		}
		
		//if fpr, set yaxis tick interval to .2
		if(client_data['block'] == 'ratio_of_first_test_fat_t'){
			options_in.yAxis.tickInterval = .2;
		}
		else{
			options_in.yAxis.tickInterval = null;
		}
		return options_in;
	}
	
	function getSeriesOffset(num_series, series_idx, adjustment){
		var offset = 0;;
		if(num_series == 2){
			if(series_idx == 0) {
				offset -= adjustment;
			}
			if(series_idx == 2) {
				offset += adjustment;
			}
		}
		if(num_series == 3){
			if(series_idx == 0) {
				offset -= (adjustment * 2);
			}
			if(series_idx == 4) {
				offset += (adjustment * 2);
			}
		}
		return offset;
	}
