	var base_options = {
		chart: {
			defaultSeriesType: 'column'
		},
		xAxis: {
//			dateTimeLabelFormats: {	day: '%m-%e-%Y', month: '%b %Y', year: '%Y'},
//			minPadding: .03,
//			maxPadding: .03,
//			tickInterval: 18144000000,
//			startOnTick: true
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
	               color: '#c0c0c0',
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
        			color: '#cococo'
				}
	        },
	        line: {
	            dataLabels: {
        			color: '#cococo'
				}
	        }
		},
        series: [{
 	    }]
	};

//	var chart = new Highcharts.Chart(options);
	Highcharts.setOptions(base_options);
	
	function updateChart(event, this_in, divid, field_in, value_in){
		//set_styles(this_in, divid);
		$('input[name=' + field_in + '][value=' + value_in + ']').attr("checked", true);
		var block = $('input:radio[name=block]:checked').val();
		var pstring = $('input:radio[name=pstring]:checked').val();

		if(typeof pstring === 'undefined') pstring = 0;

		//load and process ajax data  base_url is defined globally in the controller
		switch(block ){
			case "test_day_rha_prod": 
				$('#graph-canvas').hide();
				load_table(base_url + '/ajax_report/' + block + '/' + pstring + '/table/null/null/web/null', false);
				//$('#table-title-line').html('High Somatic Cell Cows');
				$('#table-wrapper').show();
				break;
			case "shipped": 
				$('#graph-canvas').hide();
				load_table(base_url + '/ajax_report/' + block + '/' + pstring + '/table/null/null/web/null', false);
				$('#table-title-line').html('Milk Shipped');
				$('#table-wrapper').show();
				break;
			case "current_cow_avg": 
				$('#graph-canvas').hide();
				load_table(base_url + '/ajax_report/' + block + '/' + pstring + '/table/null/null/web/null', false);
				$('#table-title-line').html('Cows Currently in the Herd - Averages');
				$('#table-wrapper').show();
				break;
			default: //all graphs
				$('#table-wrapper').hide();
				$('#graph-canvas').show();
				load_chart(base_url + '/ajax_report/' + block + '/' + pstring + '/chart/null/null/web/null', false);
		}
		return false;
//alert(JSON.stringify(section_data));
	}
	
	//this function is called in the graph_helper.js file after the JSON data file has loaded
	function process_return(section_data){
		if(typeof(section_data) !== 'undefined'){
			if(typeof(section_data['block']) !== 'undefined'){
				$('#block-links > ul > li > a').css('text-decoration', 'none');
				$('#block-links > ul > li > a').css('font-weight', 'normal');
				$('#' + section_data['block']).css('text-decoration', 'underline');
				$('#' + section_data['block']).css('font-weight', 'bold');
				//$('input[name=block][value=' + value_in + ']').attr("checked", true);
				/*
				 * SCRIPT TO MODIFY DATA POINT PROPERTIES ON A CONDITIONAL BASIS
				if(typeof(chart) != 'undefined' && section_data['block'] == 'weighted_avg_scc' && typeof(section_data['avg_weighted_avg'] != 'undefined')){
					//chart is global variable declared in graph_helper.js
					$.each(chart.series[0].data, function(i, point) {
					    if(point.y > section_data['avg_weighted_avg']) {
					    	point.update({color: 'red'});
					    }
					});
				}*/
			}
			if(typeof(section_data['pstring']) !== 'undefined'){
				$('#pstring-links > ul > li > a').css('text-decoration', 'none');
				$('#pstring-links > ul > li > a').css('font-weight', 'normal');
				$('#' + section_data['pstring']).css('text-decoration','underline');
				$('#' + section_data['pstring']).css('font-weight','bold');
			}
			if(typeof(section_data['test_date']) !== 'undefined'){
				$('#herd-summary-date').html(section_data['test_date']);
			}
			if(typeof($('#pdf-link')) != 'undefined'){
//				var output = 'array';
//				if(section_data['block'] == 'infection_summary') output = 'html';
//				$('#pdf-link').attr('href', base_url + '/' + section_data['block'] + '/' + section_data['pstring'] + '/' + output/pdf);
				$('#pdf-link').attr('href', '#');
				var submit_url =  base_url + '/display/' + section_data['block'] + '/' + section_data['sort_by'] + '/' + section_data['sort_order'];
				$('#pdf-link').unbind('click');
				$('#pdf-link').bind('click', function(){return submit_table_sort_link('report_criteria', submit_url + '/pdf', submit_url);});
			}
			if(typeof($('#csv-link')) != 'undefined'){
				//$('#csv-link').attr('href', base_url + '/csv/' + section_data['block'] + '/' + section_data['pstring']);
				$('#csv-link').attr('href', '#');
				var submit_url =  base_url + '/display/' + section_data['block'] + '/' + section_data['sort_by'] + '/' + section_data['sort_order'];
				$('#csv-link').unbind('click');
				$('#csv-link').bind('click', function(){return submit_table_sort_link('report_criteria', submit_url + '/csv', submit_url);});
			}
		}
	}