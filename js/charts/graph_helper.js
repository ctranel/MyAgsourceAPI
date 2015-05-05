//global variables used in this script (included in the imported js file)
var chart = new Array(); //array of chart objects

//set width of page and charts
head.ready(function() {
	var container_width = document.getElementById('container').offsetWidth;//Math.floor(doc_width * .95);
	var chart_width = 520;
	if(container_width >= 768) {
		chart_width = Math.floor(container_width * .47);
	}
	else {
		chart_width = Math.floor(container_width * .95);
		$(".chart-odd, .chart-even").css("float", "none");
		$(".chart-odd, .chart-even").css("clear", "both");
	}
	global_options['chart']['width'] = chart_width;
	//add 2 to width to prevent scrollbar
	$(".chart-odd, .chart-even, .chart-last-odd, .chart-only").css("width", (chart_width + 2));
	$(".highcharts-container, .chart").css("width", chart_width);
});
/*
function updateFilter(event, this_in, divid, field_in, value_in){
	$('input[name=' + field_in + '][value=' + value_in + ']').attr("checked", true);
	$('#filter-form').submit();
}
*/


//to be used when we move to loading pages within a section via ajax.  Will need to have one "block" incrementor, rather than incrementers for charts and tables
function updatePage(el){
	var div_id;
	var block_name;
	var block_index;
	var sort_field;
	var sort_order;
	var first = true;
	$('.chart').each(function(){
		div_id = $(this).attr('id');
		block_name = $(this).attr('data-block');
		block_index = div_id.replace('block-canvas','');
		sort_field = null;
		sort_order = null;
		updateBlock(div_id, block_name, block_index, sort_field, sort_order, first);
		first = false;
	});
	$('.table-container').each(function(){
		div_id = $(this).attr('id');
		block_name = $(this).attr('data-block');
		block_index = div_id.replace('block-canvas','');
		sort_field = null;
		sort_order = null;
		updateBlock(div_id, block_name, block_index, sort_field, sort_order, first);
		first = false;
	});
}

function updateBlock(container_div_id, block_in, block_index, sort_field, sort_order, first){
//load and process ajax data - page_url and page are defined globally in the controller
	var params = '';
	var cache_bust = Math.floor(Math.random()*1000);
	if($("#filter-form")){
		params = encodeURIComponent(JSON.stringify($("#filter-form").serializeObject()));
	}
	if(typeof(sort_field) == 'undefined') sort_field = null;
	if(typeof(sort_order) == 'undefined') sort_order = null;
	load_block(site_url + 'report_block/ajax_report/' + encodeURIComponent(page_url.replace(/\//g, '|')) + '/' + encodeURIComponent(block_in) + '/' + encodeURIComponent(sort_field) + '/' + sort_order + '/web/null/' + block_index + '/' + params + '/' + first + '/' + cache_bust, container_div_id, block_index, params);
	setFixedNav();
	return false;
}

function load_block(server_path, div_id, block_index, params){
	if(!server_path) {
		alert("No data found.");
		return false;
	}
	if(typeof(div_id) === 'undefined' || !div_id){
		div_id = 'block-canvas0';
	}

	$('#table-wrapper' + block_index).find('table').hide();
	$('#chart-container' + block_index).hide();

	$('#waiting-icon' + block_index).show();

	$.get(server_path, '', function(data) { process_block(div_id, block_index, data); })
		.fail(function(){console.log(this.responseText);})
		.fail(function(jqXHR, textStatus, errorThrown){console.log(errorThrown);});
	//cancel link when called from anchor tag
	return;
}

function process_block(div_id, block_index, data){
	//existance of html property indicates table, if undefined, it is a chart
	if(typeof data.html === 'undefined'){
		process_chart(div_id, data);
	}
	else{
		process_table(div_id, block_index, data);
	}
}

function process_chart(div_id, data_in){
	block_index = div_id.charAt( div_id.length-1 );
	var options = global_options;

	var um = undefined;
	options = get_chart_options(options, data_in.chart_type);
	
	options.title = {"text": data_in.description};
	options.exporting = {"filename": data_in.name};
	
	//subtitle
	var subtitle = '';
	if (typeof(data_in.filter_text) !== 'undefined' && data_in.filter_text !== null && data_in.filter_text !== ''){
		subtitle += data_in.filter_text;
	}
	if (typeof(data_in.supplemental) !== 'undefined' && data_in.supplemental !== null && data_in.supplemental !== ''){
		subtitle += '<div class="supplemental-link">' + data_in.supplemental + '</div>';
	}
	if(subtitle !== ''){
		options.subtitle = {"text": subtitle, "useHTML": true};
	}
	//end subtitle

	if(typeof(data_in) === 'undefined'){
		$('#' + div_id).html('<p class-"chart-error">Sorry, the requested data was not able to be retrieved.  Please try again, or contact AgSource for assistance.</p>');
	}
	if(typeof(data_in) === 'string'){
		$('#' + div_id).html('<p class-"chart-error">' + data_in + '</p>');
	}

	if(typeof(data_in) === 'object'){
		if(typeof(data_in.arr_axes) !== 'undefined'){
			var x_len = Object.size(data_in.arr_axes.x);

			if(x_len > 0){
				if(typeof(options.xAxis) === 'undefined'){
					options.xAxis = {};
				}
				var cnt = 0;
				for(var c in data_in.arr_axes.x){
					if(typeof(options.xAxis[cnt]) === 'undefined'){
						options.xAxis[cnt] = {};
					}
					options.xAxis[cnt].categories = typeof(data_in.arr_axes.x[c].categories) !== "undefined" ? data_in.arr_axes.x[c].categories : null;
					options.xAxis[cnt].type = data_in.arr_axes.x[c].data_type;
	
					if(data_in.chart_type != 'bar'){
						options.xAxis[cnt].title = {"text": data_in.arr_axes.x[c].text};
						if(data_in.arr_axes.x[c].data_type != null && data_in.arr_axes.x[c].data_type.indexOf('date') >= 0){
							options.xAxis[cnt].labels = {"rotation": -35};//, "align": 'left', "x": -50, "y": 55};
						}
						else{
	//						options.xAxis[cnt].labels = {"rotation": -35, "y": 25};
						}
					}
					
					if(typeof(options.xAxis[cnt].labels) === 'undefined'){
						options.xAxis[cnt].labels = {};
					}
					
					options.xAxis[cnt].labels.formatter = getAxisLabelFormat(options.xAxis[cnt].type);
					//set x axis label
					if(typeof(options.xAxis[cnt].labels) === 'undefined'){
						options.xAxis[cnt].labels = {};
					}
					cnt++;
				}
			}
			else{
				alert("No x axis data");
			}
	
			var y_len = Object.size(data_in.arr_axes.y);
			if(y_len > 0){
				if(typeof(options.yAxis) === 'undefined'){
					options.yAxis = {};
				}
				var cnt = 0;
				for(var x in data_in.arr_axes.y){
					if(typeof(options.yAxis[cnt]) === 'undefined'){
						options.yAxis[cnt] = {};
					}
					if(data_in.chart_type != 'bar' && data_in.arr_axes.y[x].opposite == true){
						options.yAxis[cnt].opposite = true;
					}
					if(typeof(data_in.arr_axes.y[x].text) != 'undefined'){
						options.yAxis[cnt].title = {"text": data_in.arr_axes.y[x].text};
						//placeholder to allow color changes pre and post render
						options.yAxis[cnt].title.style = {"color": ''};
					}
					if(typeof(data_in.arr_axes.y[x].data_type) != 'undefined'){
						options.yAxis[cnt].type = data_in.arr_axes.y[x].data_type;
					}
					if(typeof(data_in.arr_axes.y[x].max) != 'undefined'){
						options.yAxis[cnt].max = data_in.arr_axes.y[x].max;
					}
					if(typeof(data_in.arr_axes.y[x].min) != 'undefined'){
						options.yAxis[cnt].min = data_in.arr_axes.y[x].min;
					}
					if(typeof(data_in.arr_axes.y[x].labels) === 'undefined'){
						options.yAxis[cnt].labels = {};
					}
					options.yAxis[cnt].labels.formatter = getAxisLabelFormat(options.yAxis[cnt].type);
					cnt++;
				}
			}
			else{
				alert('No yAxis data');
			}
		}
		else if(data_in.chart_type !== 'pie') {
			alert('No Axis Data');
		}
		//end set axis labels
		if(typeof(data_in.series) !== 'undefined'){
			if(data_in.chart_type === 'pie'){
				//use the info from the second series
				options.series[0] = data_in.series[1];
			}
			else{
				options.series = data_in.series;
			}
		}
		if(typeof(data_in.client_data) !== 'undefined'){
			client_data = data_in.client_data;
		}
		if(typeof(data_in.data) === 'undefined' || data_in.data == false){
			var block_header = '<h2 class="block">'+options.title.text+'</h2>';
			block_header += '<h3 class="block">'+options.subtitle.text+'</h3>';
			block_header += '<p class-"chart-error">Sorry, there is no current data available for this item.  Please contact <a href="mailto:custserv@myagsource.com">customer service</a> if you believe this is in error.</p>';
				$('#' + div_id).html(block_header);
		}
		else if(typeof(client_data.redirect) !== 'undefined'){
			if(client_data.redirect == 'login') window.location.href = window.location.protocol + window.location.host + window.location.path;
		}
		else if(typeof(client_data.error) !== 'undefined'){
			$('#' + div_id).html('<p class-"chart-error">' + client_data.error + '</p>');
		}
		else{
			//add data to object
			var tmpData = data_in.data;
			//if type is chart we need to change the structure of the data
			if(data_in.chart_type === 'pie'){
				var inner_count = 0;
				var new_data = [];
				new_data[0] = [];
				for(var x in tmpData){
					new_data[0][inner_count] = [];
					var new_count = 0;
					for(i in tmpData[x]){
						new_data[0][inner_count][new_count] = tmpData[x][i];
						new_count++;
					}
					inner_count++;
				}
				tmpData = new_data;
			}
			
			var count = 0;
			for(var x in tmpData){
				if(typeof options.series[count] === 'undefined'){
					options.series[count] = {};
				}
				if(typeof(options.series[count].um) !== 'undefined'){
					um = options.series[count].um;
				}
				options.series[count].data = tmpData[x];
				count++;
			}
			//set tooltip format
			if(typeof(options.tooltip) === 'undefined'){//typeof(data_in.tooltip) === 'undefined' && 
				options.tooltip = {};
			}
			if(typeof(options.tooltip.pointFormat) === 'undefined' && typeof(options.tooltip.formatter) === 'undefined'){
			//@todo: line below will break if there is ever a chart with multiple x axes
				options.tooltip.formatter = getTooltipFormat(options.chart.type, options.xAxis.type, um);
			}
			options.chart.renderTo = div_id;
			if(typeof pre_render == 'function'){
				options = pre_render(options, client_data);
			}
			chart[block_index] = new Highcharts.Chart(options);
			while(chart[block_index].series.length > options.series.length){//(Object.size(chart[block_index].series) > count){
				chart[block_index].series[chart[block_index].series.length].remove(true);
			}
		}
		if(typeof(client_data) == "object" && typeof post_render == 'function'){
			post_render(client_data);
		}
		//attach events to new blocks
		attachDataFieldEvents();
	}
	$('#waiting-icon' + block_index).hide();
	$('#chart-container' + block_index).show();
}

function process_table(div_id, block_index, table_data){
	if(typeof(table_data) === 'undefined'){
		$('#' + div_id).html('<p class-"chart-error">Sorry, the requested data was not able to be retrieved.  Please try again, or contact AgSource for assistance.</p>');
	}
	if(typeof(table_data) === 'string'){
		$('#' + div_id).html('<p class-"chart-error">' + table_data + '</p>');
	}
	if(typeof(table_data) === 'object'){
		if(typeof pre_render_table == 'function'){
			pre_render_table(div_id, table_data);
		}
		if(typeof table_data.html === 'undefined' || table_data.html == false){
			$('#' + div_id).html('<p class-"chart-error">Sorry, there is no data available for the ' + table_data.client_data.block + ' report.  Please try again, or contact AgSource for assistance.</p>');
			$('#waiting-icon' + block_index).hide();
			return false;
		}
		else{
			$('#' + div_id).html(table_data.html);
		}
		if(typeof(table_data.client_data) == "object" && typeof post_render == 'function'){
			post_render(table_data.client_data);
		}
		//@todo: after we convert tables to use JSON, we should only call this if count > ?
		//without setTimeout, the fixed header is not hidden when page loads
		setTimeout(function(){
			createFixedHeader($('#table-wrapper' + block_index).find('table').attr('id'));
		}, 0);
		//attach events to new data fields and table headers (needs to be below fixed header)
		attachDataFieldEvents();
		setFixedNav();
	}

	$('#waiting-icon' + block_index).hide();
	$('#table-wrapper' + block_index).find('table').show();
}

function getAxisLabelFormat(axis_type){
	if(axis_type !== null && axis_type.indexOf('date') >= 0){
		return function(){return Highcharts.dateFormat('%b %e, %Y', this.value);};
	}
	else{
		return function(){return this.value;};
	}
}

function getTooltipFormat(chart_type, xaxis_type, um){
	if(typeof(um) === 'undefined'){
		um = '';
	}
	if(chart_type === "boxplot"){
		return function(){
			var p = this.point;
			var n = parseInt(this.x, 10);
			if(this.series.options.type === "boxplot" || typeof(this.series.options.type) === "undefined"){
				if(!isNaN(n) && n == this.x && n.toString() == this.x.toString() && n > 100000000000 && n < 9999999999999){
					return "<b>" + Highcharts.dateFormat("%B %Y", this.x) +"</b><br>" + this.series.name +"<br>75th Percentile: "+ p.q1 + "<br>50th Percentile: "+ p.median + "<br>25th Percentile: "+ p.q3;
				}
				else{
					return '<b>' + this.x + ':</b><br>' + this.series.name +"<br>75th Percentile: "+ p.q1 + "<br>50th Percentile: "+ p.median + "<br>25th Percentile: "+ p.q3;
				};
			}
			else { //no tooltip for non-boxplot series on boxplot charts
				return false;
			}
		};
	}
	else {
		return function(){
			var n = parseInt(this.x, 10);
			if(!isNaN(n) && n == this.x && n.toString() == this.x.toString() && n > 100000000000 && n < 9999999999999){
				return '<b>' + this.series.name + ':</b><br>' + Highcharts.dateFormat('%B %e, %Y', this.x) + ' - ' + this.y + ' ' + um;
			}
			else{
				return '<b>' + this.series.name + ':</b><br>' + this.x + ' - ' + this.y + ' ' + um;
			};
		};
	}
}

function customFormatGtLt(pointName) {
    return pointName.replace(/</gm, '&lt;').replace(/>/gm, '&gt;').replace(/<=/gm, '&le;').replace(/>=/gm, '&ge;');
}

//debugging functions
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
