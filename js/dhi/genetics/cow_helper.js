	// this function allows you to change chart options immediately before chart is rendered
	function pre_render(options_in){
		//if there is a second yAxis, change the color
		if(typeof options_in.yAxis[1] != 'undefined'){
			options_in.yAxis[1].title.style.color = var_arr_graph_colors[1];
		}
		return options_in;
	}
	
	//this function is called in the graph_helper.js file after the JSON data file has loaded.  It can make report specific updates after the data has been loaded (see commented code for example)
	function post_render(app_data){
		if(typeof(app_data) !== 'undefined'){
			if(typeof(app_data['block']) !== 'undefined'){
				//$('#block-links > ul > li > a').css('text-decoration', 'none');
				//$('#block-links > ul > li > a').css('font-weight', 'normal');
				//$('#' + app_data['block']).css('text-decoration', 'underline');
				//$('#' + app_data['block']).css('font-weight', 'bold');
				//$('input[name=block][value=' + value_in + ']').attr("checked", true);

				/* BEGIN EXAMPLE
				 if(typeof(chart) != 'undefined' && app_data['block'] == 'weighted_avg_scc' && typeof(app_data['avg_weighted_avg'] != 'undefined')){
					//chart is global variable declared in graph_helper.js
					$.each(chart.series[0].data, function(i, point) {
					    if(point.y > app_data['avg_weighted_avg']) {
					    	point.update({color: 'red'}, FALSE);
					    }
					});
				}
				chart.redraw();
				END EXAMPLE */
			}
		}
	}
	
	//Implementation: add class "dropdown" to containing UL
	//menu functions located in as_app_helper.js
/*	$(document).ready(function(){
		var nm_index = 0;
		var arr_qtile_class = new Array('', 'qtile1', 'qtile2', 'qtile3', 'qtile4');
		var table = document.getElementById('cow_selection_guide'),
		rowLength = table.rows.length;
		for (var i = 1; i < rowLength; i += 1) {
		    var row = table.rows[i];
		    if(i == 1){
		    	colLength = 10;
		    	for (var j = 0; j < colLength; j += 1) {
					if(row.cells[j].id == 'net_merit_amt' || row.cells[j].id == 'est_net_merit_amt') nm_index = j;
				}
		    }
		    else if(i > 2) row.cells[nm_index].className = arr_qtile_class[row.cells[(nm_index + 1)].innerHTML];
		}
	}); */
