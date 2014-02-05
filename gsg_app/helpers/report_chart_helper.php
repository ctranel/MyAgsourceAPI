<?php
function get_chart_options($chart_type){
	switch($chart_type){
		case 'stacked area':
			return get_stacked_area_options();
			break;
		case 'scatter':
			return get_scatter_options();
			break;
		case 'stacked column':
			return get_stacked_column_options();
			break;
		case 'column':
			return get_column_options();
			break;
		case 'bar':
			return get_bar_options();
			break;
		case 'boxplot':
			return get_boxplot_options();
			break;
		default: //line
			return get_line_options();
			break;
	}
}


function get_stacked_area_options(){
	return array(       
		 'chart' => array(
            'type' => 'area'
        ),
		'xAxis' => array(
			'type'=>'datetime',
			'categories' => NULL //clear out previously declared categories
		),

		'yAxis' => array(
			array(
				'type'=>'linear',
			)
		),
		//'legend' => array('enabled' => 'true'),
		'plotOptions' => array(
			'area' => array(
				'stacking' => 'normal'
			)
		),
/*		'series' => array(
			array('type' => 'area', 'connectNulls' => TRUE),
			array('type' => 'area', 'connectNulls' => TRUE),
			array('type' => 'area', 'connectNulls' => TRUE)
		),
*/		'tooltip' => array(
			'formatter' => "function(){return '<b>'+ Highcharts.dateFormat('%B %e, %Y', this.x) +'</b><br/>'+this.series.name +': '+ this.y +'<br/>'+'Combined Total: '+ this.point.stackTotal;}"
		)
	);
}

function get_stacked_column_options(){
	return array(
		'chart' => array(
			'defaultSeriesType' => 'column'
		),
		'xAxis' => array(
			'type'=>'datetime',
			'categories' => NULL //clear out previously declared categories
		),

		'yAxis' => array(
			array(
				'type'=>'linear',
			)
		),
		//'legend' => array('enabled' => 'true'),
		'plotOptions' => array(
			'column' => array(
				'stacking' => 'normal',
				'pointWidth' => 17,
				'shadow' => false,
				'borderWidth' => 0
			)
		),
		'tooltip' => array(
			'formatter' => "function(){return '<b>'+ Highcharts.dateFormat('%B %e, %Y', this.x) +'</b><br/>'+this.series.name +': '+ this.y +'<br/>'+'Combined Total: '+ this.point.stackTotal;}"
		)
	);
}

function get_column_options(){
	return array(
		'chart' => array(
			'defaultSeriesType' => 'column'
		),
		'xAxis' => array(
			'type'=>'datetime',
			'categories' => NULL, //clear out previously declared categories
			'labels' => array('formatter' => "function(){return Highcharts.dateFormat('%b %e, %Y', this.value);}", 'rotation' => -35, 'align' => 'left', 'x' => -50, 'y' => 55)
		),

		'yAxis' => array(
			array(
				'type'=>'linear',
			)
		),
		//'legend' => array('enabled' => 'true'),
		'plotOptions' => array(
			'column' => array(
				'stacking' => NULL,
				'shadow' => false,
				'borderWidth' => 0
			)
		),
		'tooltip' => array(
			'formatter' => "function(){return '<b>'+ Highcharts.dateFormat('%B %e, %Y', this.x) +'</b><br/>'+this.series.name +': '+ this.y;}"
		)
	);
}

function get_bar_options(){
	return array(
		'chart' => array(
			'defaultSeriesType' => 'bar'
		),
		'legend' => array('enabled'=>FALSE),
		'xAxis' => array('type'=>'linear', 'categories'=>array()),
	);
}

function get_boxplot_options(){
	return array(
		'xAxis' => array(
			'type'=>'datetime',
			'categories' => NULL //clear out previously declared categories
		),
		'yAxis' => array(
			array(
				'type'=>'linear',
			)
		),
		'plotOptions' => array(
			'boxplot' => array(
				'pointWidth' => 17,
				'shadow' => false,
				'borderWidth' => 0,
				'borderColor' => 'rgba(255, 255, 255, 0.1)',
			)
		),
		'tooltip' => array(
			'formatter' => 'function(){
								var p = this.point; 
								if(this.series.options.type != "boxplot"){
									return "<b>"+ Highcharts.dateFormat("%B %Y", this.x) +"</b><br/>"+this.series.name +": "+ this.y;
								}
								else {
									return "<b>" + Highcharts.dateFormat("%B %Y", this.x) +"</b><br/>" + this.series.name +"<br/>75th Percentile: "+ p.open + "<br/>25th Percentile: "+ p.close;
								}
							}'
		)
	);
}

function get_line_options(){
	return array(
		'xAxis' => array(
			'type'=>'datetime',
			'categories' => NULL, //clear out previously declared categories
			'labels' => array('formatter' => "function(){return Highcharts.dateFormat('%b %e, %Y', this.value);}", 'rotation' => -35, 'align' => 'left', 'x' => -50, 'y' => 55)
		),

		'yAxis' => array(
			array(
				'type'=>'linear',
			)
		),
		'legend' => array('enabled' => TRUE),
/*		'series' => array(
			array('type' => 'line', 'name' => NULL, 'connectNulls' => TRUE),
			array('type' => 'line', 'marker' => array('radius' => 4), 'connectNulls' => TRUE),
			array('type' => 'line', 'connectNulls' => TRUE)
		)
*/	);
}

function get_scatter_options(){
	return array(
			'chart' => array(
				'defaultSeriesType' => 'scatter'
			),
			'xAxis' => array(
					'type'=>'datetime',
					'categories' => NULL, //clear out previously declared categories
					'labels' => array('formatter' => "function(){return Highcharts.dateFormat('%b %e, %Y', this.value);}", 'rotation' => -35, 'align' => 'left', 'x' => -50, 'y' => 55)
			),

			'yAxis' => array(
					array(
							'type'=>'linear',
					)
			),
			'legend' => array('enabled' => TRUE),
	);
}

function prep_output($output, $graph, $report_count, $file_format = NULL){
//print_r($graph);
	if ($output == 'ajax') $this->load->view('data', $graph);
	elseif ($output == 'html') {
		if($file_format = 'pdf') return $graph['html'];
		else {
			echo $graph['html'];
			exit;
		}
	}
	elseif ($output == 'array') return $graph['data'];
    elseif ($output == 'chart' || $output == 'table') {
		// Set the Javascript header
    	header("Content-type: text/javascript"); //being sent as javascript file, not json
    	if($output == 'table') $return_val = 'table_data[' . $report_count . '] = ' . json_encode_jsfunc($graph) . ';';
    	elseif($output == 'chart'){
			$return_val = 'chart_data[' . $report_count . '] = ' . json_encode_jsfunc($graph) . ';';
    	} 
    	//	    	$return_val = json_func_expr($return_val);
		if($file_format == 'pdf' || $file_format == 'csv') return $return_val;
    	else {
			echo $return_val;
    	}
    }
 
    else // load the default view
        var_dump($graph);
    	//$this->load->view('default', $graph);
}
