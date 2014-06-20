//global variables used in this script (included in the imported js file)
var chart = new Array(); //array of chart object
var chart_data = new Array(); //must match variable in the javascript files that are downloaded (report_chart_helper.php)
var table_data = new Array(); //must match variable in the javascript files that are downloaded (report_chart_helper.php)

//var var_arr_graph_colors = ['#FF3C3C','#FF5A5A','#FF7878','#FF9696','#FFB4B4']; //monochrome
//var var_arr_graph_colors = ['#643b3b', '#825a5a', '#a07878', '#bd9696', '#dcb2b2'];  //monochrome
//var var_arr_graph_colors = ['#F15928', '#585C5F', '#08A04A', '#006C70', '#98E8F9']; //dpn?
var var_arr_graph_colors = ['#E4B577', '#75C4E4', '#B6B6A5', '#E07F8D', '#97C4A4']; 
//var var_arr_graph_colors = ['#00838C', '#939E77', '#B03500', '#BA91A8', '#97C4A4']; 
//var var_arr_graph_colors = ['#D54C18', '#48495B', '#264071', '#9CA294'];
var base_options = {chart: {backgroundColor: null}, yAxis:{}};//base options are specific to each report, see report helper functions (js files)
//get current base url
if (!window.location.origin) window.location.origin = window.location.protocol+"//"+window.location.host;
var pathArray = window.location.href.split( '/' );
var server_path = (typeof(pathArray[3]) == "string") ? pathArray[3] : '';

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
		gridLineColor : '#c0c0c0'
			//type: 'datetime',
			//tickInterval: 7 * 24 * 3600 * 1000, // one week
			//formatter: function() { return Highcharts.dateFormat('%A, %b %e, %Y', this.value); }
	},
	yAxis: {
		allowDecimals: false
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

function updateFilter(event, this_in, divid, field_in, value_in){
	$('input[name=' + field_in + '][value=' + value_in + ']').attr("checked", true);
	$('#filter-form').submit();
}


//to be used when we move to loading pages within a section via ajax.  Will need to have one "block" incrementor, rather than incrementers for charts and tables
function updatePage(el){
	var div_id;
	var block_name;
	var block_index;
	var sort_field;
	var sort_order;
	var display;
	var first = true;
	$('.chart').each(function(){
		div_id = $(this).attr('id');
		block_name = $(this).attr('data-block');
		block_index = div_id.replace('graph-canvas','');
		sort_field = null;
		sort_order = null;
		display = 'chart';
		updateBlock(div_id, block_name, block_index, sort_field, sort_order, display, first);
		first = false;
	});
	$('.table-container').each(function(){
		div_id = $(this).attr('id');
		block_name = $(this).attr('data-block');
		block_index = div_id.replace('table-canvas','');
		sort_field = null;
		sort_order = null;
		display = 'table';
		updateBlock(div_id, block_name, block_index, sort_field, sort_order, display, first);
		first = false;
	});
	
	$('.pstring-link').css('fontWeight', 'normal');
	el.style.fontWeight = 'bold';
}

function updateBlock(container_div_id, block_in, block_index, sort_field, sort_order, display, first){//}, title, benchmark_text){
//load and process ajax data - base_url and page are defined globally in the controller
	var params = '';
	var cache_bust = Math.floor(Math.random()*1000);
	//var pstring = $('.pstring-filter-item > input:checked').val();
	if($("#filter-form")){
		params = encodeURIComponent(JSON.stringify($("#filter-form").serializeObject()));
	}
	if(typeof(sort_field) == 'undefined') sort_field = null;
	if(typeof(sort_order) == 'undefined') sort_order = null;
	switch(display){
		case "table": 
			load_table(base_url + '/ajax_report/' + encodeURIComponent(page) + '/' + encodeURIComponent(block_in) + '/' + display + '/' + encodeURIComponent(sort_field) + '/' + sort_order + '/web/null/' + block_index + '/' + params + '/' + first + '/' + cache_bust, container_div_id, block_index, params);
			break;
		case "chart":
			load_chart(base_url + '/ajax_report/' + encodeURIComponent(page) + '/' + encodeURIComponent(block_in) + '/' + display + '/' + encodeURIComponent(sort_field) + '/' + sort_order + '/web/null/' + block_index + '/' + params + '/' + first + '/' + cache_bust, container_div_id, block_index, params);
			break;
	}
	return false;
}

function load_table(server_path, div_id, block_index, params){
	/* if(typeof(params) == 'undefined' && $("#filter-form")){
		params = encodeURIComponent(JSON.stringify($("#filter-form").serializeObject()));
	} */
	if(!server_path) {
		alert("No data found.");
		return false;
	}
	if(typeof(div_id) === 'undefined' || !div_id) div_id = 'table-canvas0';
	$('#table-wrapper' + block_index).find('table').hide();
	$('#waiting-icon' + block_index).show();
	
	//load javascript file (with a cache-busting parameter) rather than calling with AJAX for consistency with charts
	head.js(server_path, function() { process_table(div_id, block_index); });
	//cancel link when called from anchor tag
	return false;
}

function load_chart(server_path, div_id, block_index, params){
	/* if(typeof(params) == 'undefined' && $("#filter-form")){
		params = encodeURIComponent(JSON.stringify($("#filter-form").serializeObject()));
	} */
	if(!server_path) {
		alert("No data found.");
		return false;
	}
	if(typeof(div_id) == 'undefined' || !div_id) div_id = 'graph-canvas0';
	
	//load javascript file rather than calling with AJAX so that functions can be imported.  This javascript file will call the process_data function
	$('#chart-container' + block_index).hide();
	$('#waiting-icon' + block_index).show();
	head.js(server_path, function() { process_chart(div_id); });
}

function process_chart(div_id){ //chart_data is defined globally at the top of this page
	block_index = div_id.charAt( div_id.length-1 );
	if(typeof(chart_data[block_index]) != 'undefined'){
		// set up the temporary array that holds the data
		var tmpData = {};
		if(typeof chart_data[block_index].section_data !== 'undefined') section_data = chart_data[block_index].section_data;
		if(typeof chart_data[block_index].data === 'undefined' || chart_data[block_index].data == false){
			var block_header = '<h2 class="block">'+chart_data[block_index].config.title.text+'</h2>';
			block_header += '<h3 class="block">'+chart_data[block_index].config.subtitle.text+'</h3>';
			block_header += '<p class-"chart-error">Sorry, there is no data available for this item.  Please contact <a href="mailto:custserv@myagsource.com">customer service</a> if you believe this is in error.</p>';
				$('#' + div_id).html(block_header);
		}
		else if(typeof(section_data.redirect) !== 'undefined'){
			if(section_data.redirect == 'login') window.location.href = window.location.protocol + window.location.host + window.location.path;
		}
		else if(typeof(section_data.error) !== 'undefined'){
			$('#' + div_id).html('<p class-"chart-error">' + section_data.error + '</p>');
		}
		else{
			tmpData = chart_data[block_index].data;
			var count = 0;
			//convert the options array to 
			if(typeof chart_data[block_index].config !== 'undefined'){
				var options = chart_data[block_index].config;
				// combine with base options, but don't overwrite those from 
				if(typeof(base_options) != 'undefined'){
					var i;
					for(i in base_options) {
						if(typeof(options[i]) == 'undefined') options[i] = base_options[i];
					}
				}
			}
			for(x in tmpData){
				if(typeof options.series[count] === 'undefined') options.series[count] = {};
				options.series[count].data = tmpData[x];
				count++;
			}
			options.chart.renderTo = div_id;
			if(typeof pre_render == 'function') pre_render(options, section_data);
			chart[block_index] = new Highcharts.Chart(options);
			while(chart[block_index].series.length > count) chart[block_index].series[count].remove(true);
		}
	}

	if(typeof(section_data) == "object" && typeof post_render == 'function') post_render(section_data);

	$('#waiting-icon' + block_index).hide();
	$('#chart-container' + block_index).show();
}

function process_table(div_id){
	block_index = div_id.charAt( div_id.length-1 );
	if(typeof(table_data[block_index]) != 'undefined'){
		var tmpData = {};
		if(typeof table_data[block_index].section_data !== 'undefined') section_data = table_data[block_index].section_data;
		if(typeof table_data[block_index].html === 'undefined' || table_data[block_index].html == false){
			$('#' + div_id).html('<p class-"chart-error">Sorry, there is no data available for the ' + table_data[chart_index].config.title.text + ' report.  Please try again, or contact AgSource for assistance.</p>');
			exit;
		}
		else{
			$('#' + div_id).html(table_data[block_index].html);
		}
	}

	if(typeof(section_data) == "object" && typeof post_render == 'function') post_render(section_data);
	$('#waiting-icon' + block_index).hide();
	$('#table-wrapper' + block_index).find('table').show();
	//attach events to new data fields
	attachDataFieldEvents();
}

function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;

	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";

	if(typeof(arr) == 'object') { //Array/Hashes/Objects
	 for(var item in arr) {
	  var value = arr[item];
	 
	  if(typeof(value) == 'object') { //If it is an array,
	   dumped_text += level_padding + "'" + item + "' ...\n";
	   dumped_text += dump(value,level+1);
	  } else {
	   dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
	  }
	 }
	} else { //Stings/Chars/Numbers etc.
	 dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}
