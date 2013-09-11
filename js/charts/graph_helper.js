//global variables used in this script (included in the imported js file)
var chart = new Array(); //array of chart object
var chart_data = new Array(); //must match variable in the javascript files that are downloaded (report_chart_helper.php)
var table_data = new Array(); //must match variable in the javascript files that are downloaded (report_chart_helper.php)
//var app_data;
//var base_url; set in controller
//var herd_code; set in controller
//var page; set in controller
var table_cnt = 0;
var chart_cnt = 0;
var var_arr_graph_colors = ['#F15928', '#585C5F', '#08A04A', '#006C70', '#98E8F9']; 
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
			pointWidth: 20,
            dataLabels: {
               enabled: true,
               align: 'right',
               color: '#C0C0C0',
//	               color: 'rgba(14,59,112,1)',
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

/*set width of page and charts DO WITH HEAD.JS
var doc_width = $(window).innerWidth() < 1000 ? $(window).innerWidth() : 1200;
var container_width = Math.floor(doc_width * .95);
$('#container').width(container_width);
if(doc_width >= 768) {
	var chart_width = Math.floor(container_width * .47);
	global_options['chart']['width'] = chart_width;
	$(".chart-odd, .chart-even, .chart").css("width", chart_width);
	$(".chart-last-odd").css("max-width", '600px');
}
else {
	global_options['chart']['width'] = Math.floor(container_width * .95);
	$(".chart-odd, .chart-even").css("float", "none");
	$(".chart-odd, .chart-even").css("clear", "both");
}
*/
function updateFilter(event, this_in, divid, field_in, value_in){
	//set_styles(this_in, divid);
	$('input[name=' + field_in + '][value=' + value_in + ']').attr("checked", true);
	$('#report_criteria').submit();
//	var block = $('input:radio[name=block]:checked').val();
//	var pstring = $('input:radio[name=pstring]:checked').val();

//	if(typeof pstring === 'undefined') pstring = 0;
}


//to be used when we move to loading pages within a section via ajax.  Will need to have one "block" incrementor, rather than incrementers for charts and tables
function updatePage(ev){
	//clear current blocks
	//get block urls for page (ajax or included in function call?)
	//for each block
		//create container div
		updateBlock();
}

function updateBlock(container_div_id, block_in, sort_field, sort_order, display){//}, title, benchmark_text){
	//load and process ajax data - base_url and page are defined globally in the controller
	block = block_in;
	var params = '';
	var cache_bust = Math.floor(Math.random()*1000);
	//var block = $('input:radio[name=block]:checked').val();
	var pstring = $('input:radio[name=pstring]:checked').val();
	if($("#report-filter")){
		params = encodeURIComponent(JSON.stringify($("#report-filter").serializeObject()));
	}

	if(typeof(pstring) == 'undefined') pstring = 0;
	if(typeof(sort_field) == 'undefined') sort_field = null;
	if(typeof(sort_order) == 'undefined') sort_order = null;
	switch(display){
		case "table": 
			load_table(base_url + '/ajax_report/' + page + '/' + block + '/' + pstring + '/' + display + '/' + sort_field + '/' + sort_order + '/web/null/' + table_cnt + '/' + params + '/' + cache_bust, container_div_id, table_cnt, sort_field, sort_order, block, params);
			//$('#table-title-line' + table_cnt).html(title);
			//$('#table-subtitle-line' + table_cnt).html('Herd ' + herd_code);
			//$('#table-benchmark-line' + table_cnt).html(benchmark_text);
			table_cnt++;
			break;
		case "chart":
			load_chart(base_url + '/ajax_report/' + page + '/' + block + '/' + pstring + '/' + display + '/' + sort_field + '/' + sort_order + '/web/null/' + chart_cnt + '/null/' + cache_bust, container_div_id);
			chart_cnt++;
			break;
	}
	return false;
}

function load_table(server_path, div_id, tbl_cnt_in, sort_field, sort_order, block_in, params){
	block = block_in;
	if(typeof(sort_field) == 'undefined') sort_field = null;
	if(typeof(sort_order) == 'undefined') sort_order = null;
	if(params = '' && $("#report-filter")){
		params = encodeURIComponent(JSON.stringify($("#report-filter").serializeObject()));
	}

	if(!server_path) {
		var pstring = $('input:radio[name=pstring]:checked').val();
		if(typeof(pstring) == 'undefined') pstring = 0;
		if(typeof(block) != 'undefined' && typeof(pstring) != 'undefined'){
			var cache_bust = Math.floor(Math.random()*1000);
			server_path = base_url + '/ajax_report/' + page + '/' + block_in + '/' + pstring + '/table/' + sort_field + '/' + sort_order + '/web/null/' + tbl_cnt_in + '/' + params + '/' + cache_bust;
		}
		else {
			alert("No data found.");
			return false;
		}
	}
	if(typeof(div_id) === 'undefined' || !div_id) div_id = 'table-canvas0';
	//load javascript file (with a cache-busting parameter) rather than calling with AJAX for consistency with charts
	head.js(server_path, function() { process_table(div_id, tbl_cnt_in); });
	//cancel link when called from anchor tag
	return false;
}

function load_chart(server_path, div_id){
	if(!server_path) {
		alert("No data found.");
		return false;
	}
	if(typeof(div_id) == 'undefined' || !div_id) div_id = 'graph-canvas0';
	
	//load javascript file rather than calling with AJAX so that functions can be imported.  This javascript file will call the process_data function
	head.js(server_path, function() { process_chart(div_id); });
}

function process_chart(div_id){ //chart_data is defined globally at the top of this page
	chart_index = div_id.charAt( div_id.length-1 );
	if(typeof(chart_data[chart_index]) != 'undefined'){
		// set up the temporary array that holds the data
		var tmpData = {};
		if(typeof chart_data[chart_index].section_data !== 'undefined') section_data = chart_data[chart_index].section_data;
		if(typeof chart_data[chart_index].data === 'undefined' || chart_data[chart_index].data == false){
			$('#' + div_id).html('<p class-"chart-error">Sorry, there is no data available for this report.  Please try again, or contact AgSource at 1-800-236-0097 for assistance.</p>');
			//return false;
		}
		else if(typeof(section_data.redirect) !== 'undefined'){
			if(section_data.redirect == 'login') window.location.href = window.location.protocol + window.location.host + window.location.path;
		}
		else if(typeof(section_data.error) !== 'undefined'){
			$('#' + div_id).html('<p class-"chart-error">' + section_data.error + '</p>');
		}
		else{
			tmpData = chart_data[chart_index].data;
			var count = 0;
			//convert the options array to 
			if(typeof chart_data[chart_index].config !== 'undefined'){
				var options = chart_data[chart_index].config;
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
//document.write(JSON.stringify(options))
			options.chart.renderTo = div_id;
//alert(typeof before_render);
			if(typeof pre_render == 'function') pre_render(options, section_data);
//alert(JSON.stringify(options));
//pausecomp(10);
			chart[chart_index] = new Highcharts.Chart(options);
//remove null series
			while(chart[chart_index].series.length > count) chart[chart_index].series[count].remove(true);
		}
	}

	if(typeof(section_data) == "object" && typeof post_render == 'function') post_render(section_data);
	$('.chart-container').css('display', 'block');
}
/*
function pausecomp(millis)
{
	var date = new Date();
	var curDate = null;
	
	do { curDate = new Date(); } 
	while(curDate-date < millis);
} */


function process_table(div_id, tbl_cnt_in){
	if(typeof(table_data[tbl_cnt_in]) != 'undefined'){
		var tmpData = {};
		if(typeof table_data[tbl_cnt_in].section_data !== 'undefined') section_data = table_data[tbl_cnt_in].section_data;
		if(typeof table_data[tbl_cnt_in].html === 'undefined' || table_data[tbl_cnt_in].html == false){
			$('#' + div_id).html('<p class-"chart-error">Sorry, there is no data available for the ' + table_data[chart_index].config.title.text + ' report.  Please try again, or contact AgSource at 1-800-236-0097 for assistance.</p>');
			exit;
		}
		else{
			$('#' + div_id).html(table_data[tbl_cnt_in].html);
		}
	}
	if(typeof(section_data) == "object" && typeof post_render == 'function') post_render(section_data);
	$('.table-wrapper').css('display', 'block');
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
