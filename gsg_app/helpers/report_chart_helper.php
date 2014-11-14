<?php

function prep_output($output, $graph, $report_count, $file_format = NULL){
	if ($output == 'ajax') $this->load->view('data', $graph);
	elseif ($output == 'html') {
		if($file_format = 'pdf') return $graph['html'];
		else {
			echo $graph['html'];
			exit;
		}
	}
	elseif ($output == 'array'){
		return $graph['data'];
	}
    elseif ($output == 'chart' || $output == 'table') {
    	//Set the Javascript header 
    	header("Content-type: application/json"); //being sent as json
    	if($output == 'table'){
    		$return_val = json_encode_jsfunc($graph);
    	}
    	elseif($output == 'chart'){
			$return_val = json_encode_jsfunc($graph);
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
