var global_options = {
	chart: {
		backgroundColor: null
	},
	title: {
		style: {
			color: '#EF5C29',
			fontWeight: 'bold'
		}
	},
	colors: [var_arr_graph_colors[0], var_arr_graph_colors[1], var_arr_graph_colors[2], var_arr_graph_colors[3], var_arr_graph_colors[4]],
	credits: {
		href: window.location.origin + '/' + server_path + '/index.php',
		text: '© AgSource Cooperative Services'
	},
	xAxis: {
		gridLineColor : '#c0c0c0',
		type: 'datetime',
		categories: null
			//tickInterval: 7 * 24 * 3600 * 1000, // one week
			//formatter: function() { return Highcharts.dateFormat('%A, %b %e, %Y', this.value); }
	},
	yAxis: {
		allowDecimals: false,
		type: 'linear'
	},
	tooltip : {
		formatter : function(){
			return this.y;
			//return this.y + ["th","st","nd","rd"][!(this.y%10>3||Math.floor(this.y%100/10)==1)*this.y%10] + ' Percentile';
		}
	},
	plotOptions: {
		boxplot: {
			grouping: false,
			whiskerWidth: 0,
            pointWidth: 8,
            lineWidth: 8,
            medianColor: null
		},
		area: {
			marker: { 
				enabled: false
			},
			stacking: 'normal'
		},
		column: {
			marker: { 
				enabled: false
			},
			stacking: 'normal'
		},
		series: {
			cursor: 'pointer',
            shadow: false
		},
        bar: {
            dataLabels: {
               enabled: true,
               align: 'right',
               color: '#C0C0C0',
               formatter:function(){
                    return this.point.value;   
               }
            }
        },
        scatter: {
            dataLabels: {
                enabled: true,
                align: 'right',
                color: '#AA4643',
                formatter:function(){
                     return this.point.val;   
                }
            }
        },
        spline: {
            dataLabels: {
    			color: '#c0c0c0'
			}
        },
        line: {
            dataLabels: {
    			color: '#c0c0c0'
			}
        }
	},
    series: [{
    }]
	// SET MORE THEME-RELATED VARIABLES (COLOR, ETC)?
};

Highcharts.setOptions(global_options);

/*
 * Called after base options are set in the options JSON object
 */

function get_chart_options(options_json, chart_type){
	switch(chart_type){
		case 'stacked area':
			return get_stacked_area_options(options_json);
			break;
		case 'scatter':
			return get_scatter_options(options_json);
			break;
		case 'stacked column':
			return get_stacked_column_options(options_json);
			break;
		case 'column':
			return get_column_options(options_json);
			break;
		case 'bar':
			return get_bar_options(options_json);
			break;
		case 'boxplot':
			return get_boxplot_options(options_json);
			break;
		default: //line
			return get_line_options(options_json);
			break;
	}
}


function get_stacked_area_options(options_json){
	options_json.chart.type = 'area';
	options_json.plotOptions = {
		'column':{
			'stacking': 'normal',
		}
	};
	return options_json;
/*	return {      
		'chart':{
            'type': 'area'
		 },
		'xAxis':{
			'type':'datetime',
			'categories': null //clear out previously declared categories
		},
		'yAxis':{
			'type':'linear'
		},
		'plotOptions':{
			'area' :{
				'stacking':'normal'
			}
		}
	}; */
}

function get_stacked_column_options(options_json){
	options_json.chart.type = 'column';
	options_json.plotOptions = {
		'column':{
			'stacking': 'normal',
			'shadow': false,
			'borderWidth': 0
		}
	};
	return options_json;
/*	return {
		'chart':{
            'type': 'column'
		 },
		 		'xAxis':{
			'type':'datetime',
			'categories': null //clear out previously declared categories
		},
		'yAxis':{
			'type':'linear'
		},
		'plotOptions':{
			'column':{
				'stacking': 'normal',
				'shadow': false,
				'borderWidth': 0
			}
		}
	}; */
}

function get_column_options(options_json){
	options_json.chart.type = 'column';
	options_json.plotOptions = {
		'column':{
			'stacking': null,
			'shadow': false,
			'borderWidth': 0
		}
	};
	return options_json;
/*		return {
		'chart':{
            'type': 'column'
		 },
		 	'xAxis':{
			'type':'datetime',
			'categories': null //clear out previously declared categories
		},
		'yAxis':{
			'type':'linear'
		},
		'plotOptions':{
			'column':{
				'stacking': null,
				'shadow': false,
				'borderWidth': 0
			}
		}
	}; */
}

function get_bar_options(options_json){
	options_json.chart.type = 'bar';
	options_json.xAxis.type = 'linear';
	options_json.xAxis.categories = [];
	return options_json;
/*	return {
		'chart':{
            'type': 'bar'
		 },
		'xAxis':{
			'type':'linear',
			'categories': []
		}
	}; */
}

function get_boxplot_options(options_json){
	options_json.chart.type = 'boxplot';
	return options_json;
/*	return {
		'chart':{
            'type': 'boxplot'
		 },
		'xAxis':{
			'type':'datetime',
			'categories': null //clear out previously declared categories
		},
		'yAxis':{
			'type':'linear'
		},
	}; */
}

function get_line_options(options_json){
	options_json.chart.type = 'line';
	return options_json;
}

function get_scatter_options(options_json){
	options_json.chart.type = 'scatter';
	return options_json;
}