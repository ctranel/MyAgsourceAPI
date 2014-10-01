//shameless hack to temporarily fix issue with filter form being displayed
$('#filters').hide();
	//this function is called in the graph_helper.js file after the JSON data file has loaded.  It can make report specific updates after the data has been loaded (see commented code for example)
	function pre_render(options_in, section_data){
		if(typeof(section_data) !== 'undefined'){
			if(typeof(section_data['block']) !== 'undefined'){
				//$('#block-links > ul > li > a').css('text-decoration', 'none');
				//$('#block-links > ul > li > a').css('font-weight', 'normal');
				//$('#' + app_data['block']).css('text-decoration', 'underline');
				//$('#' + app_data['block']).css('font-weight', 'bold');
				//$('input[name=block][value=' + value_in + ']').attr("checked", true);
				//&& typeof(section_data['avg_weighted_avg']) != 'undefined' 
				 if(typeof(chart) != 'undefined' && section_data['block'] == 'prev3_test_mgmt_mun') {
					 
					 //chart is global variable declared in graph_helper.js
					 options_in.xAxis.tickPositions = [options_in.series[0].data[0][0],
						                        options_in.series[0].data[1][0],
						                        options_in.series[0].data[2][0]];
				}
			}
		}
		return options_in;
	}
