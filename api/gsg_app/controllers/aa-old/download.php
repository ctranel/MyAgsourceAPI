<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Download extends MY_Controller {
	
	function __construct(){
		parent::__construct();
	}

	function bench_all(){

	}

/**
 * @method index()
 * 
 * @description Downloads the file passed in the parameter.  Must include "index" when creating link (e.g., .../download/index/filename.ext).  This file is not used when downloading dynamic data.
 * 
 * @param string filename to be downloaded from img directory
 * @access	public
 * @return	void
 */
	function index($filename){
		$this->session->keep_all_flashdata();
		if(file_exists(PROJ_DIR . FS_SEP . "img" . FS_SEP . $filename)){
			$this->load->helper('download');
			$data = file_get_contents(PROJ_DIR . FS_SEP . "img" . FS_SEP . $filename); // Read the file's contents
			
			force_download($filename, $data);
		}
		else show_404();//echo "cannot find file " . PROJ_DIR . FS_SEP . "img" . FS_SEP . $filename;
		exit;
	}
}
