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
		'plotOptions' => array(
			'area' => array(
				'stacking' => 'normal'
			)
		),
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
		'plotOptions' => array(
			'column' => array(
				'stacking' => 'normal',
				'shadow' => false,
				'borderWidth' => 0
			)
		),
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
		'plotOptions' => array(
			'column' => array(
				'stacking' => NULL,
				'shadow' => false,
				'borderWidth' => 0
			)
		),
	);
}

function get_bar_options(){
	return array(
		'chart' => array(
			'defaultSeriesType' => 'bar'
		),
		'xAxis' => array('type'=>'linear', 'categories'=>array()),
	);
}

function get_boxplot_options(){
	return array(
		'chart' => array(
			'type'=>'boxplot'
		),
		'xAxis' => array(
			'type'=>'datetime',
			'categories' => NULL //clear out previously declared categories
		),
		'yAxis' => array(
			array(
				'type'=>'linear',
			)
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
	);
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
	);
}

function prep_output($output, $graph, $report_count, $file_format = NULL){
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
		if($file_format == 'pdf' || $file_format == 'csv') return $return_val;
    	else {
			echo $return_val;
    	}
    }
 
    else // load the default view
        var_dump($graph);
    	//$this->load->view('default', $graph);
}
