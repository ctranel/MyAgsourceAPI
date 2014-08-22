//global variables used in this script (included in the imported js file)
var chart = new Array(); //array of chart object

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
	if(typeof(el.style) !== 'undefined'){
		el.style.fontWeight = 'bold';
	}
}

function updateBlock(container_div_id, block_in, block_index, sort_field, sort_order, display, first){
//load and process ajax data - base_url and page are defined globally in the controller
	var params = '';
	var cache_bust = Math.floor(Math.random()*1000);
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
	if(!server_path) {
		alert("No data found.");
		return false;
	}
	if(typeof(div_id) === 'undefined' || !div_id) div_id = 'table-canvas0';
	$('#table-wrapper' + block_index).find('table').hide();
	$('#waiting-icon' + block_index).show();
	$.get(server_path, '', function(data) { process_table(div_id, block_index, data); })
		.fail(function(){console.log(this.responseText);});
	//cancel link when called from anchor tag
	return false;
}

function load_chart(server_path, div_id, block_index, params){
	if(!server_path) {
		alert("No data found.");
		return false;
	}
	if(typeof(div_id) == 'undefined' || !div_id) div_id = 'graph-canvas0';
	
	$('#chart-container' + block_index).hide();
	$('#waiting-icon' + block_index).show();
	$.get(server_path, '', function(data) { process_chart(div_id, data); })
		.fail(function(jqXHR, textStatus, errorThrown){console.log(errorThrown);});
}

function process_chart(div_id, data_in){
	block_index = div_id.charAt( div_id.length-1 );
	var options = base_options;
	options = get_chart_options(options, data_in.chart_type);
	options.title = {"text": data_in.description};
	options.exporting = {"filename": data_in.name};
	if (typeof(data_in.pstring) === 'undefined'){
		options.subtitle = {"text": "Herd: " + data_in.herd_code};
	} 
	else {
		options.subtitle = {"text": "Herd: " + data_in.herd_code + ' Pstring: ' + data_in.pstring};
	}

	if(typeof(data_in) === 'undefined'){
		$('#' + div_id).html('<p class-"chart-error">Sorry, the requested data was not able to be retrieved.  Please try again, or contact AgSource for assistance.</p>');
	}
	if(typeof(data_in) === 'string'){
		$('#' + div_id).html('<p class-"chart-error">' + data_in + '</p>');
	}
	if(typeof(data_in) === 'object'){
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
					if(data_in.arr_axes.x[c].data_type == 'datetime'){
						options.xAxis[cnt].labels = {"rotation": -35, "align": 'left', "x": -50, "y": 55};
					}
					else{
						options.xAxis[cnt].labels = {"rotation": -35, "y": 25};
					}
				}
				//set x axis label
				if(typeof(options.xAxis[cnt].labels) === 'undefined'){
					options.xAxis[cnt].labels = {};
				}
				options.xAxis[cnt].labels.formatter = getAxisLabelFormat(options.xAxis[cnt].type);
				cnt++;
			}
			if(Object.size(options.xAxis) <= 1){
				options.xAxis = options.xAxis[0];
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
				if(data_in.chart_type != 'bar' && data_in.arr_axes.y[x].opposite === true){
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

				/*BLOCKS_SELECT_FIELDS TABLE HAS A COLUMN FOR AXES_INDEX, DO WE NEED THIS BLOCK?
				if(typeof($a['db_field_name']) != 'undefined' && !empty($a['db_field_name']) && $a['opposite']){
					$tmp_key = array_search($a['db_field_name'], $arr_fieldnames);
					$this->graph['config']['series'][$tmp_key]['yAxis'] = 1;
				}*/

				/*Since this is being built entirely on the client, and built on to the object as we go, is this necessary?
				if(data_in.arr_axes.y.length > 1) {
					if(typeof(data_in.arr_axes.y[x]) != 'undefined'){
						$this->graph['config']['yAxis'][$cnt] = $.extend(true, $this->graph['config']['yAxis'][$cnt], $tmp_array);
					}
					else{
						$this->graph['config']['yAxis'][$cnt] = $tmp_array;
					}
				}
				else {
					if(typeof($this->graph['config']['yAxis']) != 'undefined'){
						$this->graph['config']['yAxis'] = array_merge($this->graph['config']['yAxis'][$cnt], $tmp_array);
					}
					else{
						$this->graph['config']['yAxis'] = $tmp_array;
					}
				}
				cnt++;*/
				var um = undefined;
				if(typeof(data_in.arr_axes.y[x].labels) === 'undefined'){
					options.yAxis[cnt].labels = {};
				}
				options.yAxis[cnt].labels.formatter = getAxisLabelFormat(options.yAxis[cnt].type, um);
				cnt++;
			}
			if(Object.size(options.yAxis) <= 1){
				options.yAxis = options.yAxis[0];
			}
		}
		else{
			alert('No yAxis data');
		}
		//end set axis labels
		//set tooltip format
		if(typeof(data_in.tooltip) === 'undefined'){
			options.tooltip = {};
		}
		//@todo: line below will break if there is ever a chart with multiple x axes
		options.tooltip.formatter = getTooltipFormat(options.type, options.xAxis.type);
		if(typeof(data_in.series) !== 'undefined'){
			options.series = data_in.series;
		}
		if(typeof(data_in.section_data) !== 'undefined'){
			section_data = data_in.section_data;
		}
		if(typeof(data_in.data) === 'undefined' || data_in.data == false){
			var block_header = '<h2 class="block">'+options.title.text+'</h2>';
			block_header += '<h3 class="block">'+options.subtitle.text+'</h3>';
			block_header += '<p class-"chart-error">Sorry, there is no current data available for this item.  Please contact <a href="mailto:custserv@myagsource.com">customer service</a> if you believe this is in error.</p>';
				$('#' + div_id).html(block_header);
		}
		else if(typeof(section_data.redirect) !== 'undefined'){
			if(section_data.redirect == 'login') window.location.href = window.location.protocol + window.location.host + window.location.path;
		}
		else if(typeof(section_data.error) !== 'undefined'){
			$('#' + div_id).html('<p class-"chart-error">' + section_data.error + '</p>');
		}
		else{
			/*started with base_options object and built on it, so there should be no need to merge 
			if(typeof options !== 'undefined'){
				// combine with base options, but don't overwrite those from (try jquery.extend?)
				if(typeof(base_options) != 'undefined'){
					for(var i in base_options) {
						if(typeof(options[i]) == 'undefined') options[i] = base_options[i];
					}
				}
			}*/
			//add data to object
			var tmpData = data_in.data;
			var count = 0;
			for(var x in tmpData){
				if(typeof options.series[count] === 'undefined'){
					options.series[count] = {};
				}
				options.series[count].data = tmpData[x];
				count++;
			}
			options.chart.renderTo = div_id;
			if(typeof pre_render == 'function'){
				pre_render(options, section_data);
			}
console.log(JSON.stringify(options));		

			chart[block_index] = new Highcharts.Chart(options);
			while(chart[block_index].series.length > count){//(Object.size(chart[block_index].series) > count){
				chart[block_index].series[count].remove(true);
			}
		}
		if(typeof(section_data) == "object" && typeof post_render == 'function'){
			post_render(section_data);
		}
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
			$('#' + div_id).html('<p class-"chart-error">Sorry, there is no data available for the ' + table_data.section_data.block + ' report.  Please try again, or contact AgSource for assistance.</p>');
			$('#waiting-icon' + block_index).hide();
			return false;
		}
		else{
			$('#' + div_id).html(table_data.html);
		}
		if(typeof(table_data.section_data) == "object" && typeof post_render == 'function'){
			post_render(table_data.section_data);
		}
		//attach events to new data fields
		attachDataFieldEvents();
	}

	$('#waiting-icon' + block_index).hide();
	$('#table-wrapper' + block_index).find('table').show();
}

function getAxisLabelFormat(axis_type){
	if(axis_type === "datetime"){
		return function(){return Highcharts.dateFormat('%b %e, %Y', this.value);};
	}
	else{
		return function(){return this.value;};
	}
}

function getTooltipFormat(chart_type, xaxis_type){
	if(xaxis_type === "datetime"){
		if(chart_type === "boxplot"){
			return function(){
				var p = this.point;
				if(this.series.options.type === "boxplot" || typeof(this.series.options.type) === "undefined"){
					return "<b>" + Highcharts.dateFormat("%B %Y", this.x) +"</b><br/>" + this.series.name +"<br/>75th Percentile: "+ p.q1 + "<br/>50th Percentile: "+ p.median + "<br/>25th Percentile: "+ p.q3;
				}
				else {
					return false;
					//return "<b>"+ Highcharts.dateFormat("%B %Y", this.x) +"</b><br/>"+this.series.name +": "+ this.y;
				}
			};
		}
		else{
			return function(){return '<b>' + this.series.name + ':</b><br>' + Highcharts.dateFormat('%B %e, %Y', this.x) + ' - ' + this.y + ' ' + this.series.um;};
		}
	}
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
